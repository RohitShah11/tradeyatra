<?php

namespace App\Services;

use App\Models\DeltaAccount;
use App\Models\SyncLog;
use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Throwable;

class DeltaTradeHistorySyncService
{
    public function sync(DeltaAccount $account, array $params = []): SyncLog
    {
        $params = array_filter(array_merge(['page_size' => 50, 'contract_types' => 'perpetual_futures'], $params), fn ($value) => $value !== null && $value !== '');

        try {
            $account->update(['last_sync_started_at' => now()]);
            $client = new DeltaExchangeClient($account);
            $fills = $client->fills($params);
            $orders = $this->safeCall(fn () => $client->orderHistory($params));
            $positions = $this->safeCall(fn () => $client->positions(Arr::only($params, ['product_ids', 'contract_types'])));
            $wallet = $this->safeCall(fn () => $client->walletBalances());
            $transactions = $client->allWalletTransactions(Arr::only($params, ['page_size', 'after', 'before', 'start_time', 'end_time', 'asset_ids']));
            $imported = $this->importRealizedTransactions($account, $transactions, $fills);
            $account->update(['last_synced_at' => now()]);

            return SyncLog::create([
                'user_id' => $account->user_id,
                'delta_account_id' => $account->id,
                'status' => 'success',
                'message' => $imported ? 'Delta realized P&L sync completed.' : 'Delta sync completed with no new realized wallet transactions.',
                'imported_count' => $imported,
                'orders_count' => count($orders['result'] ?? []),
                'positions_count' => count($positions['result'] ?? []),
                'wallet_snapshot' => $wallet,
                'raw_payload' => ['wallet_transactions' => $transactions, 'fills' => $fills, 'orders' => $orders, 'positions' => $positions],
            ]);
        } catch (Throwable $exception) {
            return SyncLog::create([
                'user_id' => $account->user_id,
                'delta_account_id' => $account->id,
                'status' => 'failed',
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function importRealizedTransactions(DeltaAccount $account, array $payload, array $fillsPayload): int
    {
        $count = 0;
        $symbols = collect($fillsPayload['result'] ?? [])->filter(fn ($fill) => isset($fill['product_id'], $fill['product_symbol']))
            ->mapWithKeys(fn ($fill) => [(string) $fill['product_id'] => $fill['product_symbol']]);

        foreach ($payload['result'] ?? [] as $transaction) {
            $transactionId = (string) ($transaction['id'] ?? $transaction['uuid'] ?? '');
            $type = strtolower((string) ($transaction['transaction_type'] ?? ''));
            $amount = (float) ($transaction['amount'] ?? 0);
            if ($transactionId === '' || ! isset($transaction['product_id']) || ! in_array($type, ['cashflow', 'settlement'], true) || abs($amount) < 0.00000001) { continue; }

            $createdAt = Carbon::parse($transaction['created_at'] ?? now());
            $productId = isset($transaction['product_id']) ? (string) $transaction['product_id'] : null;
            $symbol = Arr::get($transaction, 'meta_data.product_symbol')
                ?? ($productId ? $symbols->get($productId) : null)
                ?? ($productId ? 'PRODUCT-'.$productId : 'DELTA-WALLET');
            if ($this->isOptionSymbol($symbol)) { continue; }
            $asset = (string) ($transaction['asset_symbol'] ?? 'USD');
            $positionSize = (float) Arr::get($transaction, 'meta_data.position_size', 0);
            $commission = $this->matchingCommission($transaction, $payload['result'] ?? []);

            $trade = Trade::firstOrCreate(
                ['user_id' => $account->user_id, 'exchange' => 'delta', 'external_trade_id' => 'wallet-'.$transactionId],
                [
                    'date' => $createdAt->toDateString(),
                    'time' => $createdAt->format('H:i'),
                    'pair' => $symbol,
                    'asset_class' => 'Crypto',
                    'market_segment' => 'Derivatives',
                    'currency' => in_array($asset, ['INR', 'USD'], true) ? $asset : 'USD',
                    'trade_type' => $positionSize < 0 ? 'Short' : 'Long',
                    'strategy' => 'Delta realized P&L',
                    'quantity' => abs($positionSize),
                    'entry_price' => (float) Arr::get($transaction, 'meta_data.entry_price', 0),
                    'exit_price' => (float) Arr::get($transaction, 'meta_data.exit_price', 0),
                    'profit' => max(0, $amount),
                    'loss' => abs(min(0, $amount)),
                    'trading_fees' => $commission,
                    'emotion' => 'Imported',
                    'broker' => 'Delta Exchange',
                    'exchange_payload' => $transaction,
                    'status' => 'Closed',
                    'notes' => 'Imported from Delta wallet transaction ledger ('.$type.'). Amount is the credited/debited realized value reported by Delta.',
                    'imported_at' => now(),
                ]
            );
            if ($trade->wasRecentlyCreated) { $count++; }
        }
        return $count;
    }

    private function matchingCommission(array $cashflow, array $transactions): float
    {
        $cashflowTime = Carbon::parse($cashflow['created_at'] ?? now());
        return abs((float) collect($transactions)
            ->filter(fn ($item) => ($item['transaction_type'] ?? null) === 'commission'
                && (string) ($item['product_id'] ?? '') === (string) ($cashflow['product_id'] ?? '')
                && abs(Carbon::parse($item['created_at'] ?? now())->diffInMilliseconds($cashflowTime, false)) <= 2000)
            ->sum(fn ($item) => (float) ($item['amount'] ?? 0)));
    }

    private function isOptionSymbol(string $symbol): bool
    {
        return str_starts_with(strtoupper($symbol), 'C-') || str_starts_with(strtoupper($symbol), 'P-');
    }

    private function safeCall(callable $callback): array
    {
        try { return $callback(); }
        catch (Throwable $exception) { return ['success' => false, 'result' => [], 'sync_warning' => $exception->getMessage()]; }
    }
}
