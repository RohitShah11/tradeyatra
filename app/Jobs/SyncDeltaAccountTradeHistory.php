<?php

namespace App\Jobs;

use App\Models\DeltaAccount;
use App\Models\PlatformSetting;
use App\Services\DeltaTradeHistorySyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class SyncDeltaAccountTradeHistory implements ShouldQueue
{
    use Queueable;
    public int $tries = 2;
    public int $timeout = 90;
    public function __construct(public readonly int $deltaAccountId) {}

    public function handle(DeltaTradeHistorySyncService $service): void
    {
        if (! PlatformSetting::automaticTradeSyncEnabled()) {
            return;
        }

        $account = DeltaAccount::find($this->deltaAccountId);
        if (! $account || ! $account->user_id || ! $account->is_active || ! $account->auto_sync_enabled || ! $account->api_key || ! $account->api_secret) { return; }
        Cache::lock("delta-sync-account-{$account->id}", 120)->block(5, fn () => $service->sync($account, ['contract_types' => 'perpetual_futures']));
    }
}
