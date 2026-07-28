<?php

namespace App\Http\Controllers;

use App\Models\DeltaAccount;
use App\Models\SyncLog;
use App\Services\AnalyticsTracker;
use App\Services\DeltaExchangeClient;
use App\Services\DeltaTradeHistorySyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Throwable;

class DeltaExchangeController extends Controller
{
    public function settings()
    {
        return view('delta.settings', [
            'account' => DeltaAccount::active(auth()->id()) ?? new DeltaAccount(['base_url' => 'https://api.india.delta.exchange', 'auto_sync_enabled' => true]),
            'logs' => $this->logs(8),
            'syncIp' => [
                'ipv4' => config('services.broker_sync.ipv4'),
                'ipv6' => config('services.broker_sync.ipv6'),
            ],
        ]);
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'], 'api_key' => ['nullable', 'string', 'max:512'],
            'api_secret' => ['nullable', 'string', 'max:512'], 'base_url' => ['required', Rule::in(['https://api.india.delta.exchange'])],
            'auto_sync_enabled' => ['nullable', 'boolean'],
        ]);
        DeltaAccount::where('user_id', auth()->id())->update(['is_active' => false]);
        $account = DeltaAccount::where('user_id', auth()->id())->latest()->first() ?? new DeltaAccount;
        if ($account->exists && blank($data['api_key'] ?? null)) {
            unset($data['api_key']);
        }
        if ($account->exists && blank($data['api_secret'] ?? null)) {
            unset($data['api_secret']);
        }
        $account->fill([...$data, 'user_id' => auth()->id(), 'is_active' => true, 'auto_sync_enabled' => $request->boolean('auto_sync_enabled')])->save();

        return redirect()->route('delta.settings')->with('success', 'Delta Exchange settings saved.');
    }

    public function testConnection(Request $request, AnalyticsTracker $analytics)
    {
        $account = $this->credentialedAccount();
        if (! $account) {
            return redirect()->route('delta.settings')->with('error', 'Save your Delta API key and secret first.');
        }
        try {
            (new DeltaExchangeClient($account))->profile();
            $analytics->track($request, 'broker_connection_success', ['broker' => 'delta']);

            return back()->with('success', 'Delta Exchange connection successful.');
        } catch (Throwable $e) {
            $analytics->track($request, 'broker_connection_failed', ['broker' => 'delta']);

            return back()->with('error', 'Delta connection failed: '.$e->getMessage());
        }
    }

    public function syncPage()
    {
        return view('delta.sync', ['account' => DeltaAccount::active(auth()->id()), 'logs' => $this->logs(20)]);
    }

    public function sync(Request $request, DeltaTradeHistorySyncService $service)
    {
        $account = $this->credentialedAccount();
        if (! $account) {
            return redirect()->route('delta.settings')->with('error', 'Save your Delta API key and secret first.');
        }
        $params = ['page_size' => min(50, max(1, (int) $request->input('page_size', 50)))];
        if ($request->filled('product_ids')) {
            $params['product_ids'] = $request->string('product_ids')->toString();
        }
        $log = $service->sync($account, $params);
        foreach (['all', 'Delta Exchange'] as $scope) {
            Cache::forget('trade-filter-options:'.auth()->id().':'.$scope);
        }

        return redirect()->route('delta.sync')->with($log->status === 'failed' ? 'error' : 'success', $log->status === 'failed' ? 'Delta sync failed: '.$log->message : "Delta sync complete. Imported {$log->imported_count} new realized P&L records.");
    }

    private function credentialedAccount(): ?DeltaAccount
    {
        $account = DeltaAccount::active(auth()->id());

        return $account && $account->api_key && $account->api_secret ? $account : null;
    }

    private function logs(int $limit)
    {
        return SyncLog::where('user_id', auth()->id())->whereNotNull('delta_account_id')->latest()->limit($limit)->get();
    }
}
