<?php

namespace App\Services;

use App\Models\FinancialJuiceNews;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class MarketNewsService
{
    public const CATEGORIES = [
        'all' => 'Top stories',
        'markets' => 'Markets',
        'stocks' => 'Stocks',
        'crypto' => 'Crypto',
        'economy' => 'Economy',
        'forex' => 'Forex',
        'commodities' => 'Commodities',
    ];

    private const QUERIES = [
        'all' => 'financial markets OR stock market OR cryptocurrency OR economy',
        'markets' => 'financial markets OR stock market',
        'stocks' => 'stocks OR equities OR Nifty OR Sensex',
        'crypto' => 'cryptocurrency OR bitcoin OR ethereum',
        'economy' => 'economy OR inflation OR interest rates OR central bank',
        'forex' => 'forex OR currencies OR rupee OR dollar',
        'commodities' => 'commodities OR gold OR crude oil OR silver',
    ];

    private const ALPHA_TOPICS = [
        'markets' => 'financial_markets',
        'stocks' => 'financial_markets',
        'crypto' => 'blockchain',
        'economy' => 'economy_macro',
        'forex' => 'financial_markets',
        'commodities' => 'energy_transportation',
    ];

    public function headlines(string $category = 'all'): array
    {
        $category = array_key_exists($category, self::QUERIES) ? $category : 'all';
        $minutes = max(1, (int) config('services.news.cache_minutes', 10));

        return Cache::remember("market-news:v4:{$category}", now()->addMinutes($minutes), function () use ($category) {
            $providerArticles = [];
            $providerException = null;

            if (filled(config('services.news.alpha_vantage_key'))) {
                try {
                    $providerArticles = $this->alphaVantageHeadlines($category);

                    if ($providerArticles === []) {
                        $providerArticles = $this->googleHeadlines($category);
                    }
                } catch (Throwable $exception) {
                    report($exception);
                    $providerException = $exception;
                }
            }

            if ($providerArticles === []) {
                try {
                    $providerArticles = $this->googleHeadlines($category);
                } catch (Throwable $exception) {
                    report($exception);
                    $providerException = $exception;
                }
            }

            try {
                $financialJuiceArticles = $this->financialJuiceHeadlines($category);
            } catch (Throwable $exception) {
                report($exception);
                $financialJuiceArticles = [];
            }

            $articles = collect($providerArticles)
                ->concat($financialJuiceArticles)
                ->unique(fn (array $article) => mb_strtolower($article['url'].'|'.$article['title']))
                ->sortByDesc('published_at')
                ->values()
                ->all();

            if ($articles === [] && $providerException !== null) {
                throw $providerException;
            }

            return $articles;
        });
    }

    private function financialJuiceHeadlines(string $category): array
    {
        if (blank(config('services.news.financial_juice_key'))) {
            return [];
        }

        return FinancialJuiceNews::query()
            ->where('published_at', '>=', now()->subDays(7))
            ->latest('published_at')
            ->limit(300)
            ->get()
            ->map(function (FinancialJuiceNews $item) {
                $labels = collect($item->labels ?? [])->filter()->map(fn ($label) => (string) $label)->values();
                $sentiment = $this->headlineSentiment($item->title.' '.$item->description);

                return [
                    'title' => $item->title,
                    'description' => str(strip_tags($item->description ?? ''))->squish()->limit(220)->value(),
                    'url' => $item->url ?: 'https://www.financialjuice.com/home',
                    'source' => 'FinancialJuice',
                    'published_at' => $item->published_at->toAtomString(),
                    'sentiment' => $sentiment,
                    'sentiment_score' => null,
                    'sentiment_method' => 'headline_estimate',
                    'tickers' => $labels->take(5)->all(),
                    'provider' => 'FinancialJuice',
                    '_search_text' => mb_strtolower($item->title.' '.$item->description.' '.$labels->implode(' ')),
                ];
            })
            ->filter(fn (array $article) => $this->matchesCategory($article['_search_text'], $category))
            ->map(function (array $article) {
                unset($article['_search_text']);

                return $article;
            })
            ->values()
            ->all();
    }

    private function matchesCategory(string $text, string $category): bool
    {
        if ($category === 'all') {
            return true;
        }

        $terms = match ($category) {
            'markets' => ['market', 'index', 'futures', 'yield', 'risk'],
            'stocks' => ['stock', 'equity', 'shares', 'nifty', 'sensex', 'nasdaq', 's&p', 'dow'],
            'crypto' => ['crypto', 'bitcoin', 'btc', 'ethereum', 'eth', 'blockchain'],
            'economy' => ['economy', 'inflation', 'cpi', 'gdp', 'employment', 'central bank', 'fed', 'ecb'],
            'forex' => ['forex', 'currency', 'usd', 'eur', 'gbp', 'jpy', 'aud', 'cad', 'chf', 'nzd', 'inr'],
            'commodities' => ['commodity', 'gold', 'silver', 'oil', 'crude', 'natural gas', 'xau'],
            default => [],
        };

        return collect($terms)->contains(fn (string $term) => str_contains($text, $term));
    }

    private function alphaVantageHeadlines(string $category): array
    {
        $parameters = [
            'function' => 'NEWS_SENTIMENT',
            'sort' => 'LATEST',
            'limit' => 200,
            'apikey' => config('services.news.alpha_vantage_key'),
        ];

        if (isset(self::ALPHA_TOPICS[$category])) {
            $parameters['topics'] = self::ALPHA_TOPICS[$category];
        }

        $payload = Http::acceptJson()
            ->timeout(10)
            ->retry(2, 250)
            ->get('https://www.alphavantage.co/query', $parameters)
            ->throw()
            ->json();

        if (isset($payload['Information']) || isset($payload['Note']) || isset($payload['Error Message'])) {
            throw new RuntimeException($payload['Information'] ?? $payload['Note'] ?? $payload['Error Message']);
        }

        return collect($payload['feed'] ?? [])->map(function (array $item) {
            $tickers = collect($item['ticker_sentiment'] ?? [])
                ->sortByDesc(fn (array $ticker) => (float) ($ticker['relevance_score'] ?? 0))
                ->take(5)
                ->pluck('ticker')
                ->filter()
                ->values()
                ->all();

            return [
                'title' => trim($item['title'] ?? ''),
                'description' => str(strip_tags($item['summary'] ?? ''))->squish()->limit(220)->value(),
                'url' => $item['url'] ?? '',
                'source' => trim($item['source'] ?? 'Market news'),
                'published_at' => $this->alphaTimestamp($item['time_published'] ?? ''),
                'sentiment' => $this->normaliseSentiment($item['overall_sentiment_label'] ?? null),
                'sentiment_score' => isset($item['overall_sentiment_score']) ? (float) $item['overall_sentiment_score'] : null,
                'sentiment_method' => 'alpha_vantage',
                'tickers' => $tickers,
                'provider' => 'Alpha Vantage',
            ];
        })->filter(fn (array $article) => $article['title'] !== '' && $article['url'] !== '')
            ->unique('url')
            ->sortByDesc('published_at')
            ->values()
            ->all();
    }

    private function googleHeadlines(string $category): array
    {
        $locale = config('services.news.locale', 'en-IN');
        $country = config('services.news.country', 'IN');
        $language = (string) str($locale)->before('-')->lower();
        $url = 'https://news.google.com/rss/search?'.http_build_query([
            'q' => self::QUERIES[$category].' when:7d',
            'hl' => $locale,
            'gl' => $country,
            'ceid' => $country.':'.$language,
        ]);

        $response = Http::accept('application/rss+xml, application/xml')
            ->timeout(8)
            ->retry(2, 200)
            ->get($url)
            ->throw();

        return $this->parse($response->body());
    }

    private function parse(string $xml): array
    {
        $previous = libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml, \SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($feed === false) {
            throw new RuntimeException('The market news provider returned an invalid feed.');
        }

        $articles = [];
        foreach ($feed->channel->item ?? [] as $item) {
            $title = trim((string) $item->title);
            $source = trim((string) ($item->source ?? 'Market news'));
            $description = trim(strip_tags((string) $item->description));
            $publishedAt = strtotime((string) $item->pubDate) ?: time();
            $sentiment = $this->headlineSentiment($title.' '.$description);

            if ($title === '' || blank((string) $item->link)) {
                continue;
            }

            $articles[] = [
                'title' => $title,
                'description' => str($description)->squish()->limit(220)->value(),
                'url' => (string) $item->link,
                'source' => $source ?: 'Market news',
                'published_at' => date(DATE_ATOM, $publishedAt),
                'sentiment' => $sentiment,
                'sentiment_score' => null,
                'sentiment_method' => 'headline_estimate',
                'tickers' => [],
                'provider' => 'Google News',
            ];
        }

        return collect($articles)->unique('url')->sortByDesc('published_at')->values()->all();
    }

    private function alphaTimestamp(string $value): string
    {
        $timestamp = \DateTimeImmutable::createFromFormat('Ymd\\THis', $value, new \DateTimeZone('UTC'));

        return ($timestamp ?: new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(DATE_ATOM);
    }

    private function normaliseSentiment(?string $label): ?string
    {
        $label = strtolower((string) $label);

        return match (true) {
            str_contains($label, 'bullish') => 'bullish',
            str_contains($label, 'bearish') => 'bearish',
            str_contains($label, 'neutral') => 'neutral',
            default => null,
        };
    }

    private function headlineSentiment(string $text): string
    {
        $text = mb_strtolower($text);
        $bullish = ['rally', 'rallies', 'gain', 'gains', 'surge', 'surges', 'rise', 'rises', 'record high', 'breakout', 'beats estimates', 'growth', 'recovery', 'bullish', 'upgrade'];
        $bearish = ['fall', 'falls', 'drop', 'drops', 'slump', 'slumps', 'crash', 'sell-off', 'decline', 'declines', 'misses estimates', 'recession', 'bearish', 'downgrade', 'plunge'];
        $score = 0;

        foreach ($bullish as $phrase) {
            $score += substr_count($text, $phrase);
        }

        foreach ($bearish as $phrase) {
            $score -= substr_count($text, $phrase);
        }

        return match (true) {
            $score > 0 => 'bullish',
            $score < 0 => 'bearish',
            default => 'neutral',
        };
    }
}
