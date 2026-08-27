<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPageSession;
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
        $trades = (clone $tradeQuery)
            ->latest('date')
            ->latest('time')
            ->paginate(20, ['*'], 'trades_page')
            ->withQueryString();
        $tradeBrokers = $user->trades()->whereNotNull('broker')->where('broker', '!=', '')
            ->distinct()->orderBy('broker')->pluck('broker');

        return view('admin.users.show', compact(
            'user', 'recentActivitySessions', 'pageUsage', 'activeToday', 'activeSevenDays',
            'trades', 'matchingTradeCount', 'tradeSearch', 'tradeBroker', 'tradeBrokers'
        ));
    }
}
