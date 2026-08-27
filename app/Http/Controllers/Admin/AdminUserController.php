<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPageSession;
use App\Models\SyncLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $users = User::query()
            ->with('latestActivitySession')
            ->withCount(['trades', 'sharkAccounts'])
            ->when($search, fn ($query) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function show(Request $request, User $user): View
    {
        $user->loadCount(['trades', 'sharkAccounts', 'syncLogs', 'aiConversations']);
        $user->load('latestActivitySession');
        $recentActivitySessions = $user->activitySessions()->latest('last_seen_at')->limit(10)->get();
        $pageUsage = UserPageSession::query()->where('user_id', $user->id)
            ->where('started_at', '>=', now()->subDays(30))
            ->selectRaw('path, SUM(active_seconds) as active_seconds, COUNT(*) as visits')
            ->groupBy('path')->orderByDesc('active_seconds')->limit(10)->get();
        $activeToday = (int) $user->pageSessions()->where('started_at', '>=', today())->sum('active_seconds');
        $activeSevenDays = (int) $user->pageSessions()->where('started_at', '>=', now()->subDays(7))->sum('active_seconds');

        $tradeSearch = trim((string) $request->query('trade_search'));
        $tradeBroker = trim((string) $request->query('trade_broker'));
        $tradeQuery = $user->trades()
            ->when($tradeSearch, fn ($query) => $query->where(fn ($inner) => $inner
                ->where('pair', 'like', "%{$tradeSearch}%")
                ->orWhere('strategy', 'like', "%{$tradeSearch}%")))
            ->when($tradeBroker, fn ($query) => $query->where('broker', $tradeBroker));

        $matchingTradeCount = (clone $tradeQuery)->count();
        $tradeTotals = (clone $tradeQuery)
            ->selectRaw("COALESCE(NULLIF(currency, ''), ?) as currency, COALESCE(SUM(profit), 0) as total_profit, COALESCE(SUM(loss), 0) as total_loss, COALESCE(SUM(trading_fees), 0) as total_fees", [$user->currency ?: 'INR'])
            ->groupByRaw("COALESCE(NULLIF(currency, ''), ?)", [$user->currency ?: 'INR'])
            ->orderBy('currency')
            ->get();
        $trades = (clone $tradeQuery)
            ->latest('date')
            ->latest('time')
            ->paginate(20, ['*'], 'trades_page')
            ->withQueryString();
        $tradeBrokers = $user->trades()->whereNotNull('broker')->where('broker', '!=', '')
            ->distinct()->orderBy('broker')->pluck('broker');
        $walletBalances = collect([
            'SharkExchange' => $this->latestWalletLog($user, 'shark_account_id'),
            'Delta Exchange' => $this->latestWalletLog($user, 'delta_account_id'),
        ])->flatMap(fn ($log, $broker) => $this->walletBalances($log, $broker, $user->currency ?: 'INR'));

        if ($walletBalances->isEmpty()) {
            $latestTradeBalance = $user->trades()->whereNotNull('current_balance')
                ->latest('date')->latest('time')->first(['current_balance', 'currency', 'date']);
            if ($latestTradeBalance) {
                $walletBalances = collect([[
                    'broker' => 'Journal',
                    'currency' => $latestTradeBalance->currency ?: ($user->currency ?: 'INR'),
                    'balance' => (float) $latestTradeBalance->current_balance,
                    'synced_at' => $latestTradeBalance->date,
                ]]);
            }
        }

        return view('admin.users.show', compact(
            'user', 'recentActivitySessions', 'pageUsage', 'activeToday', 'activeSevenDays',
            'trades', 'matchingTradeCount', 'tradeTotals', 'tradeSearch', 'tradeBroker', 'tradeBrokers', 'walletBalances'
        ));
    }

    private function latestWalletLog(User $user, string $accountColumn): ?SyncLog
    {
        return SyncLog::query()->where('user_id', $user->id)->whereNotNull($accountColumn)
            ->where('status', 'success')->whereNotNull('wallet_snapshot')->latest()->first();
    }

    private function walletBalances(?SyncLog $log, string $broker, string $fallbackCurrency): array
    {
        if (! $log || ! is_array($log->wallet_snapshot)) {
            return [];
        }

        $snapshot = $log->wallet_snapshot;
        $records = data_get($snapshot, 'result', data_get($snapshot, 'data', data_get($snapshot, 'balances', $snapshot)));
        $records = is_array($records) && array_is_list($records) ? $records : [$records];

        return collect($records)->map(function ($record) use ($broker, $fallbackCurrency, $log) {
            if (! is_array($record)) return null;
            $balance = collect(['balance', 'walletBalance', 'wallet_balance', 'totalWalletBalance', 'total_balance', 'equity', 'amount', 'availableBalance', 'available_balance'])
                ->map(fn ($key) => data_get($record, $key))->first(fn ($value) => $value !== null && $value !== '' && is_numeric($value));
            if ($balance === null) return null;

            return [
                'broker' => $broker,
                'currency' => (string) ($record['asset_symbol'] ?? $record['asset'] ?? $record['currency'] ?? $record['symbol'] ?? $fallbackCurrency),
                'balance' => (float) $balance,
                'synced_at' => $log->created_at,
            ];
        })->filter(fn ($balance) => $balance !== null && abs($balance['balance']) >= 0.00000001)->values()->all();
    }
}
