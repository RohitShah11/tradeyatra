<?php

namespace App\Console\Commands;

use App\Models\FinancialJuiceNews;
use App\Services\MarketNewsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;
use WebSocket\Client;

class StreamFinancialJuiceNews extends Command
{
    protected $signature = 'news:stream-financial-juice
                            {--once : Stop after the first news event}
                            {--check : Verify the connection and stop after the hello message}';

    protected $description = 'Stream FinancialJuice headlines into the local news store';

    private bool $stop = false;

    public function handle(): int
    {
        $apiKey = (string) config('services.news.financial_juice_key');

        if ($apiKey === '') {
            $this->error('FINANCIAL_JUICE_API_KEY is not configured.');

            return self::FAILURE;
        }

        if (function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGTERM, fn () => $this->stop = true);
            pcntl_signal(SIGINT, fn () => $this->stop = true);
        }

        $attempt = 0;
        while (! $this->stop) {
            $client = null;

            try {
                $client = new Client((string) config('services.news.financial_juice_url'), [
                    'headers' => ['X-API-Key' => $apiKey],
                    'timeout' => 45,
                ]);
                $attempt = 0;
                $this->info('Connected to FinancialJuice.');

                while (! $this->stop && $client->isConnected()) {
                    $payload = json_decode((string) $client->receive(), true);
                    if (! is_array($payload)) {
                        continue;
                    }
                    if (($payload['type'] ?? null) === 'hello' && $this->option('check')) {
                        $delay = (int) ($payload['data']['delay_seconds'] ?? $payload['delay_seconds'] ?? 0);
                        $this->info("FinancialJuice authentication succeeded (feed delay: {$delay} seconds).");

                        return self::SUCCESS;
                    }
                    if (($payload['type'] ?? null) === 'error') {
                        throw new \RuntimeException('FinancialJuice rejected the connection: '.($payload['message'] ?? 'unknown error'));
                    }
                    if (($payload['type'] ?? null) !== 'news') {
                        continue;
                    }

                    $this->applyNewsEvent((string) ($payload['event'] ?? ''), $payload['data'] ?? null);
                    if ($this->option('once')) {
                        return self::SUCCESS;
                    }
                }
            } catch (Throwable $exception) {
                report($exception);
                $this->warn('FinancialJuice connection interrupted; reconnecting.');
            } finally {
                if ($client?->isConnected()) {
                    $client->close();
                }
            }

            if (! $this->stop) {
                $attempt++;
                sleep(min(30, 2 ** min($attempt, 5)) + random_int(0, 2));
            }
        }

        return self::SUCCESS;
    }

    private function applyNewsEvent(string $event, mixed $data): void
    {
        if ($event === 'deleted' && is_numeric($data)) {
            FinancialJuiceNews::query()->where('external_id', (int) $data)->delete();
            $this->clearNewsCache();

            return;
        }

        if (! in_array($event, ['created', 'updated'], true) || ! is_array($data)) {
            return;
        }

        foreach ($data as $item) {
            if (! is_array($item) || empty($item['newsId']) || blank($item['title'] ?? null)) {
                continue;
            }

            FinancialJuiceNews::query()->updateOrCreate(
                ['external_id' => (int) $item['newsId']],
                [
                    'title' => trim((string) $item['title']),
                    'description' => trim((string) ($item['description'] ?? '')),
                    'url' => filled($item['link'] ?? null) ? (string) $item['link'] : null,
                    'labels' => array_values(array_filter((array) ($item['labels'] ?? []), 'is_string')),
                    'published_at' => $item['datePublished'] ?? now(),
                ]
            );
        }

        $this->clearNewsCache();
    }

    private function clearNewsCache(): void
    {
        foreach (array_keys(MarketNewsService::CATEGORIES) as $category) {
            Cache::forget("market-news:v4:{$category}");
        }
    }
}
