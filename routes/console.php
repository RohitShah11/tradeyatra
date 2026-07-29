<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\SyncSharkAccountTradeHistory;
use App\Models\SharkAccount;
use App\Jobs\SyncDeltaAccountTradeHistory;
use App\Models\DeltaAccount;
use App\Models\PlatformSetting;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('shark:sync-active-accounts', function () {
    if (! PlatformSetting::automaticTradeSyncEnabled()) {
        $this->info('Automatic trade sync is disabled by the administrator.');
        return;
    }

    $accounts = SharkAccount::query()
        ->whereNotNull('user_id')
        ->where('is_active', true)
        ->where('auto_sync_enabled', true)
        ->whereNotNull('api_key')
        ->whereNotNull('api_secret')
        ->get();

    foreach ($accounts as $account) {
        SyncSharkAccountTradeHistory::dispatch($account->id);
    }

    $this->info("Queued {$accounts->count()} SharkExchange trade-history sync jobs.");
})->purpose('Queue latest trade-history sync jobs for active SharkExchange accounts');

Schedule::command('shark:sync-active-accounts')->everyFiveMinutes()->withoutOverlapping();

Artisan::command('delta:sync-active-accounts', function () {
    if (! PlatformSetting::automaticTradeSyncEnabled()) {
        $this->info('Automatic trade sync is disabled by the administrator.');
        return;
    }

    $accounts = DeltaAccount::query()->where('is_active', true)->where('auto_sync_enabled', true)->whereNotNull('api_key')->whereNotNull('api_secret')->get();
    foreach ($accounts as $account) { SyncDeltaAccountTradeHistory::dispatch($account->id); }
    $this->info("Queued {$accounts->count()} Delta Exchange sync jobs.");
})->purpose('Queue Delta fill sync jobs for active accounts');

Schedule::command('delta:sync-active-accounts')->everyFiveMinutes()->withoutOverlapping();
