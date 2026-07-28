<?php

namespace App\Http\Controllers;

use App\Models\SharkAccount;
use App\Models\SyncLog;
use App\Services\AnalyticsTracker;
use App\Services\SharkExchangeClient;
use App\Services\SharkTradeHistorySyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Throwable;

class SharkExchangeController extends Controller
{
    public function settings(Request $request)
    {
        return view('shark.settings', [
            'account' => SharkAccount::active(auth()->id()) ?? new SharkAccount([
                'base_url' => 'https://api.sharkexchange.in',
                'public_base_url' => 'https://api.sharkexchange.in',
                'default_symbol' => 'BTCINR',
                'margin_asset' => 'INR',
                'auto_sync_enabled' => true,
            ]),
            'logs' => SyncLog::query()->where('user_id', auth()->id())->whereNotNull('shark_account_id')->latest()->limit(8)->get(),
            'syncIp' => $this->syncIpDetails($request),
        ]);
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'api_key' => ['nullable', 'string', 'max:512'],
            'api_secret' => ['nullable', 'string', 'max:512'],
            'base_url' => ['required', Rule::in(['https://api.sharkexchange.in'])],
            'public_base_url' => ['required', Rule::in(['https://api.sharkexchange.in'])],
            'default_symbol' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{2,20}$/'],
            'margin_asset' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{2,10}$/'],
            'auto_sync_enabled' => ['nullable', 'boolean'],
        ]);

        SharkAccount::query()->where('user_id', auth()->id())->update(['is_active' => false]);

        $account = SharkAccount::query()->where('user_id', auth()->id())->latest()->first() ?? new SharkAccount;
        $settingsData = $data;

        if ($account->exists && blank($settingsData['api_key'] ?? null)) {
            unset($settingsData['api_key']);
        }

        if ($account->exists && blank($settingsData['api_secret'] ?? null)) {
            unset($settingsData['api_secret']);
        }

        $account->fill([
            ...$settingsData,
            'user_id' => auth()->id(),
            'is_active' => true,
            'auto_sync_enabled' => $request->boolean('auto_sync_enabled', true),
        ]);

        $account->save();

        return redirect()->route('shark.settings')->with('success', 'SharkExchange settings saved.');
    }

    public function sync(Request $request, SharkTradeHistorySyncService $syncService, AnalyticsTracker $analytics)
    {
        $account = SharkAccount::active(auth()->id());

        if (! $account || ! $account->api_key || ! $account->api_secret) {
            return redirect()->route('shark.settings')->with('error', 'Add your SharkExchange API key and secret before syncing.');
        }

        $params = [
            'sortOrder' => 'desc',
            'pageSize' => (int) $request->input('pageSize', 100),
        ];

        if ($request->filled('symbol')) {
            $params['symbol'] = strtoupper($request->symbol);
        }

        $log = $syncService->sync($account, $params);

        foreach (['all', 'SharkExchange'] as $scope) {
            Cache::forget('trade-filter-options:'.auth()->id().':'.$scope);
        }

        if ($log->status === 'failed') {
            $analytics->track($request, 'broker_connection_failed', ['broker' => 'shark']);

            return redirect()->route('shark.sync')->with('error', 'SharkExchange sync failed: '.$log->message);
        }

        $analytics->track($request, 'broker_connection_success', ['broker' => 'shark']);

        return redirect()->route('shark.sync')->with('success', "Sync complete. Imported {$log->imported_count} new trades.");
    }

    public function syncPage(Request $request)
    {
        return view('shark.sync', [
            'account' => SharkAccount::active(auth()->id()),
            'logs' => SyncLog::query()->where('user_id', auth()->id())->latest()->limit(20)->get(),
            'syncIp' => $this->syncIpDetails($request),
        ]);
    }

    public function market(Request $request)
    {
        $logs = SyncLog::query()
            ->where('user_id', auth()->id())
            ->whereNotNull('raw_payload');

        $sharkLog = (clone $logs)->whereNotNull('shark_account_id')->latest()->first();
        $deltaLog = (clone $logs)->whereNotNull('delta_account_id')->latest()->first();

        return view('shark.market', compact('sharkLog', 'deltaLog'));
    }

    private function syncIpDetails(Request $request): array
    {
        return [
            'ipv4' => config('services.broker_sync.ipv4'),
            'ipv6' => config('services.broker_sync.ipv6'),
            'public' => config('services.broker_sync.ipv4'),
            'server' => $request->server('SERVER_ADDR'),
            'visitor' => $request->ip(),
        ];
    }
}
