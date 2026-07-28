<?php

namespace App\Jobs;

use App\Models\SharkAccount;
use App\Services\SharkTradeHistorySyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class SyncSharkAccountTradeHistory implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 90;

    public function __construct(public readonly int $sharkAccountId)
    {
    }

    public function handle(SharkTradeHistorySyncService $syncService): void
    {
        $account = SharkAccount::query()->find($this->sharkAccountId);

        if (! $account || ! $account->user_id || ! $account->is_active || ! $account->auto_sync_enabled || ! $account->api_key || ! $account->api_secret) {
            return;
        }

        Cache::lock("shark-sync-account-{$account->id}", 120)->block(5, function () use ($account, $syncService) {
            $syncService->sync($account, [
                'sortOrder' => 'desc',
                'pageSize' => 100,
            ]);
        });
    }
}
