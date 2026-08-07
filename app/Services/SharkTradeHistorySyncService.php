<?php

namespace App\Services;

use App\Models\SharkAccount;
use App\Models\SyncLog;
use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use RuntimeException;
use Throwable;

class SharkTradeHistorySyncService
{
    public function sync(SharkAccount $account, array $params = []): SyncLog
    {
        $params = array_merge([
            'sortOrder' => 'desc',
            'pageSize' => 100,
        ], array_filter($params, fn ($value) => $value !== null && $value !== ''));

        try {
            $account->update(['last_sync_started_at' => now()]);

            $client = new SharkExchangeClient($account);

            $tradesPayload = $client->tradeHistory($params);
            $openOrdersPayload = $this->safeSharkCall(fn () => $client->openOrders($params));
            $ordersPayload = $this->safeSharkCall(fn () => $client->orderHistory($params));
            $positionsPayload = $this->safeSharkCall(fn () => $client->positions('OPEN', Arr::only($params, ['symbol', 'sortOrder', 'pageSize'])));
            $wallet = $this->safeSharkCall(fn () => $client->futuresWallet(['marginAsset' => $account->margin_asset]));
            $imported = $this->reconcileStoredPayload($account, $tradesPayload);
            $ordersCount = count($this->records($openOrdersPayload)) + count($this->records($ordersPayload));

            $account->update(['last_synced_at' => now()]);

            return SyncLog::create([
                'user_id' => $account->user_id,
                'shark_account_id' => $account->id,
                'status' => 'success',
                'message' => $imported || $ordersCount ? 'Sync completed.' : 'Sync completed, but Shark returned no new realized PnL trades or orders.',
                'imported_count' => $imported,
                'orders_count' => $ordersCount,
                'positions_count' => count($this->records($positionsPayload)),
                'wallet_snapshot' => $wallet,
                'raw_payload' => [
                    'trade_history' => $tradesPayload,
                    'open_orders' => $openOrdersPayload,
                    'order_history' => $ordersPayload,
                    'positions' => $positionsPayload,
                ],
            ]);
        } catch (Throwable $exception) {
            return SyncLog::create([
                'user_id' => $account->user_id,
                'shark_account_id' => $account->id,
                'status' => 'failed',
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function safeSharkCall(callable $callback): array
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            return [
                'sync_warning' => $exception->getMessage(),
                'data' => [],
            ];
        }
    }

    public function reconcileStoredPayload(SharkAccount $account, array $payload): int
    {
        if (! $account->user_id) {
            throw new RuntimeException('The Shark account must belong to a user before trades can be imported.');
        }

        $count = 0;
        $records = $this->records($payload);
        $allocatedFees = $this->allocatedBrokerFees($records);

        foreach ($records as $index => $record) {
            $tradeId = $this->first($record, ['tradeId', 'id', 'uuid']);
            $orderId = $this->first($record, ['orderId', 'order_id']);
            $realizedProfit = $this->realizedProfit($record);

            if (abs($realizedProfit) < 0.00000001) {
                continue;
            }

            $openingExecution = $this->openingExecutionFor($records, $record);

            $trade = Trade::query()
                ->where('user_id', $account->user_id)
                ->when($tradeId, fn ($query) => $query->where('shark_trade_id', $tradeId))
                ->when(! $tradeId && $orderId, fn ($query) => $query->where('shark_order_id', $orderId))
                ->first();

            $values = [
                'user_id' => $account->user_id,
                'date' => $this->dateFromRecord($openingExecution ?? $record),
                'time' => $openingExecution ? $this->timeFromRecord($openingExecution) : null,
                'pair' => $this->first($record, ['symbol', 'pair', 'contractPair']) ?: 'UNKNOWN',
                // Realized-P&L history contains the closing execution. A SELL closes
                // a long position, while a BUY closes a short position.
                'trade_type' => $this->directionFromClosingExecution($record),
                'status' => 'Closed',
                'broker' => 'SharkExchange',
                'lot_size' => $this->number($this->first($record, ['quantity', 'qty', 'executedQty'])),
                'quantity' => $this->number($this->first($record, ['quantity', 'qty', 'executedQty'])),
                'entry_price' => $openingExecution
                    ? $this->number($this->first($openingExecution, ['price', 'avgPrice', 'averagePrice']))
                    : null,
                'exit_price' => $this->number($this->first($record, ['price', 'avgPrice', 'averagePrice'])),
                'exit_date' => $this->dateFromRecord($record),
                'exit_time' => $this->timeFromRecord($record),
                'profit' => max(0, $realizedProfit),
                'loss' => abs(min(0, $realizedProfit)),
                'trading_fees' => $allocatedFees[$index] ?? $this->brokerFee($record),
                'emotion' => 'Imported',
                'strategy' => $this->first($record, ['type', 'orderType']) ?: 'SharkExchange import',
                'notes' => $this->tradeNotes($record),
                'shark_order_id' => $orderId,
                'shark_trade_id' => $tradeId,
                'shark_position_id' => $this->first($record, ['positionId', 'position_id']),
                'shark_payload' => $record,
                'imported_at' => now(),
            ];

            if ($trade) {
                $trade->update($values);
            } else {
                Trade::create($values);
                $count++;
            }
        }

        return $count;
    }

    private function directionFromClosingExecution(array $record): string
    {
        return strtoupper((string) $this->first($record, ['side', 'orderSide'])) === 'SELL'
            ? 'Long'
            : 'Short';
    }

    private function openingExecutionFor(array $records, array $closingExecution): ?array
    {
        $positionId = (string) $this->first($closingExecution, ['positionId', 'position_id']);
        $closingSide = strtoupper((string) $this->first($closingExecution, ['side', 'orderSide']));
        $closingTime = $this->recordTimestamp($closingExecution);

        if ($positionId === '' || $closingSide === '' || $closingTime === null) {
            return null;
        }

        return collect($records)
            ->filter(function (array $record) use ($positionId, $closingSide, $closingTime): bool {
                $recordPositionId = (string) $this->first($record, ['positionId', 'position_id']);
                $recordSide = strtoupper((string) $this->first($record, ['side', 'orderSide']));
                $recordTime = $this->recordTimestamp($record);

                return $recordPositionId === $positionId
                    && $recordSide !== ''
                    && $recordSide !== $closingSide
                    && $recordTime !== null
                    && $recordTime < $closingTime;
            })
            ->sortByDesc(fn (array $record) => $this->recordTimestamp($record))
            ->first();
    }

    private function recordTimestamp(array $record): ?int
    {
        $timestamp = $this->first($record, ['time', 'createdAt', 'updatedAt', 'timestamp', 'tradeTime']);

        if (! $timestamp) {
            return null;
        }

        return is_numeric($timestamp)
            ? (int) $timestamp
            : Carbon::parse($timestamp)->getTimestampMs();
    }

    private function allocatedBrokerFees(array $records): array
    {
        $groups = [];

        foreach ($records as $index => $record) {
            $positionId = (string) ($this->first($record, ['positionId', 'position_id']) ?: 'execution-'.$index);
            $groups[$positionId]['fees'] = ($groups[$positionId]['fees'] ?? 0) + $this->brokerFee($record);

            $realizedProfit = $this->realizedProfit($record);
            if (abs($realizedProfit) >= 0.00000001) {
                $groups[$positionId]['closings'][$index] = abs($realizedProfit);
            }
        }

        $allocated = [];
        foreach ($groups as $group) {
            $closings = $group['closings'] ?? [];
            if ($closings === []) {
                continue;
            }

            $weight = array_sum($closings);
            foreach ($closings as $index => $realizedProfit) {
                $allocated[$index] = $group['fees'] * ($weight > 0 ? $realizedProfit / $weight : 1 / count($closings));
            }
        }

        return $allocated;
    }

    private function records(array $payload): array
    {
        foreach (['data.rows', 'data.result', 'data', 'rows', 'result'] as $path) {
            $value = Arr::get($payload, $path);
            if (is_array($value) && array_is_list($value)) {
                return $value;
            }
        }

        return array_is_list($payload) ? $payload : [];
    }

    private function first(array $record, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (Arr::has($record, $key)) {
                return Arr::get($record, $key);
            }
        }

        return null;
    }

    private function number(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function realizedProfit(array $record): float
    {
        return $this->number($this->first($record, [
            'realizedProfitInMarginAsset',
            'realizedPnlInMarginAsset',
            'realizedProfit',
            'realizedPnl',
            'pnl',
            'profit',
        ]));
    }

    private function brokerFee(array $record): float
    {
        $feeIncludingTax = abs($this->number($this->first($record, [
            'feeInMarginAsset',
            'commissionInMarginAsset',
            'tradingFeeInMarginAsset',
            'fee',
            'commission',
            'tradingFee',
        ])));
        $taxRate = max(0, (float) config('services.shark.fee_tax_rate', 0.18));

        return $feeIncludingTax / (1 + $taxRate);
    }

    private function tradeNotes(array $record): string
    {
        $parts = array_filter([
            'Role: ' . ($this->first($record, ['role']) ?: ''),
            'Order type: ' . ($this->first($record, ['type', 'orderType']) ?: ''),
            'Contract: ' . ($this->first($record, ['contractType']) ?: ''),
            'Margin asset: ' . ($this->first($record, ['marginAsset']) ?: ''),
            'Client order: ' . ($this->first($record, ['clientOrderId']) ?: ''),
        ], fn (string $part) => ! str_ends_with($part, ': '));

        return implode("\n", $parts);
    }

    private function dateFromRecord(array $record): string
    {
        $timestamp = $this->first($record, ['time', 'createdAt', 'updatedAt', 'timestamp', 'tradeTime']);

        if (is_numeric($timestamp)) {
            return Carbon::createFromTimestampMs((int) $timestamp)->toDateString();
        }

        return $timestamp ? Carbon::parse($timestamp)->toDateString() : now()->toDateString();
    }

    private function timeFromRecord(array $record): ?string
    {
        $timestamp = $this->first($record, ['time', 'createdAt', 'updatedAt', 'timestamp', 'tradeTime']);

        if (! $timestamp) {
            return null;
        }

        return (is_numeric($timestamp) ? Carbon::createFromTimestampMs((int) $timestamp) : Carbon::parse($timestamp))->format('H:i');
    }
}
