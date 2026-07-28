<?php

namespace App\Http\Controllers;

use App\Models\DailyPlan;
use App\Models\DeltaAccount;
use App\Models\SharkAccount;
use App\Models\SyncLog;
use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TradeController extends Controller
{
    public function dashboard(Request $request)
    {
        $trades = $this->filteredTrades($request)->orderBy('date')->get();
        $recentTrades = $this->resultTradesQuery()->latest('date')->latest('time')->limit(8)->get();
        $todayTrades = $this->resultTradesQuery()->whereDate('date', Carbon::today())->get();
        $openTrades = Trade::where('user_id', auth()->id())->where('status', 'Open')->latest()->get();
        $sharkWalletLog = $this->latestWalletLog('shark_account_id');
        $deltaWalletLog = $this->latestWalletLog('delta_account_id');

        return view('trades.dashboard', [
            'trades' => $trades,
            'recentTrades' => $recentTrades,
            'todayTrades' => $todayTrades,
            'openTrades' => $openTrades,
            'account' => SharkAccount::active(auth()->id()),
            'deltaAccount' => DeltaAccount::active(auth()->id()),
            'sharkWallet' => $this->walletSummary($sharkWalletLog),
            'deltaWallet' => $this->walletSummary($deltaWalletLog),
            'stats' => $this->stats($trades),
            'equity' => $this->equitySeries($trades),
            'exchangeReports' => [
                'shark' => $this->dashboardExchangeReport($trades->where('broker', 'SharkExchange'), auth()->user()->currency ?: 'INR'),
                'delta' => $this->dashboardExchangeReport($trades->where('broker', 'Delta Exchange'), 'USD'),
            ],
            'dailyPlan' => DailyPlan::query()
                ->where('user_id', auth()->id())
                ->whereDate('plan_date', Carbon::today())
                ->first(),
        ]);
    }

    public function saveDailyPlan(Request $request)
    {
        $validated = $request->validate([
            'content' => ['nullable', 'string', 'max:5000'],
            'plan_date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
        ]);
        $planDate = Carbon::createFromFormat('Y-m-d', $validated['plan_date'] ?? Carbon::today()->toDateString())->startOfDay();

        $plan = DailyPlan::query()
            ->where('user_id', auth()->id())
            ->whereDate('plan_date', $planDate)
            ->first();
        $content = trim($validated['content'] ?? '');
        $plan
            ? $plan->update(['content' => $content])
            : DailyPlan::query()->create(['user_id' => auth()->id(), 'plan_date' => $planDate->toDateString(), 'content' => $content]);

        $message = $planDate->isToday() ? "Today's trading plan has been saved." : 'The trading plan for '.$planDate->format('d M Y').' has been saved.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function dailyPlan(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ]);
        $planDate = Carbon::createFromFormat('Y-m-d', $validated['date'])->startOfDay();
        $plan = DailyPlan::query()
            ->where('user_id', auth()->id())
            ->whereDate('plan_date', $planDate)
            ->first();

        return response()->json([
            'date' => $planDate->toDateString(),
            'date_label' => $planDate->format('l, d F Y'),
            'title' => $planDate->isToday() ? "Today's trading plan" : 'Trading plan for '.$planDate->format('d M Y'),
            'content' => $plan?->content ?? '',
            'has_plan' => (bool) $plan,
            'previous_date' => $planDate->copy()->subDay()->toDateString(),
            'next_date' => $planDate->isToday() ? null : $planDate->copy()->addDay()->toDateString(),
        ]);
    }

    private function dashboardExchangeReport($trades, string $currency): array
    {
        $now = now();
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $now->copy()->endOfWeek(Carbon::SUNDAY);
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $weeklyTrades = $trades->filter(fn (Trade $trade) => $trade->date?->between($weekStart, $weekEnd));
        $monthlyTrades = $trades->filter(fn (Trade $trade) => $trade->date?->between($monthStart, $monthEnd));

        $weeklyDaily = $weeklyTrades->groupBy(fn (Trade $trade) => $trade->date?->toDateString())
            ->map(fn ($items) => [
                'trades' => $items->count(),
                'net' => (float) $items->sum(fn (Trade $trade) => $trade->net_pnl),
            ]);

        $daily = $monthlyTrades->groupBy(fn (Trade $trade) => $trade->date?->day)
            ->map(fn ($items) => [
                'trades' => $items->count(),
                'net' => (float) $items->sum(fn (Trade $trade) => $trade->net_pnl),
                'fees' => (float) $items->sum('trading_fees'),
            ]);

        return [
            'currency' => $currency,
            'week' => array_merge($this->stats($weeklyTrades), [
                'label' => $weekStart->format('d M').' - '.$weekEnd->format('d M Y'),
                'days' => collect(range(0, 6))->map(function (int $offset) use ($weekStart, $weeklyDaily) {
                    $date = $weekStart->copy()->addDays($offset);
                    $summary = $weeklyDaily->get($date->toDateString(), ['trades' => 0, 'net' => 0]);

                    return [
                        'label' => $date->format('D'),
                        'date' => $date->format('d M'),
                        'trades' => $summary['trades'],
                        'net' => $summary['net'],
                    ];
                })->all(),
            ]),
            'month' => array_merge($this->stats($monthlyTrades), [
                'label' => $monthStart->format('F Y'),
                'days' => collect(range(1, $monthEnd->day))->map(fn (int $day) => [
                    'day' => $day,
                    'date' => $monthStart->copy()->day($day)->format('d M Y'),
                    'trades' => $daily->get($day, ['trades' => 0])['trades'],
                    'net' => $daily->get($day, ['net' => 0])['net'],
                    'fees' => $daily->get($day, ['fees' => 0])['fees'],
                ])->all(),
            ]),
        ];
    }

    private function latestWalletLog(string $accountColumn): ?SyncLog
    {
        return SyncLog::query()
            ->where('user_id', auth()->id())
            ->whereNotNull($accountColumn)
            ->where('status', 'success')
            ->whereNotNull('wallet_snapshot')
            ->latest()
            ->first();
    }

    private function walletSummary(?SyncLog $log): array
    {
        if (! $log || ! is_array($log->wallet_snapshot)) {
            return ['balances' => [], 'synced_at' => null];
        }

        $snapshot = $log->wallet_snapshot;
        $records = data_get($snapshot, 'result', data_get($snapshot, 'data', data_get($snapshot, 'balances', $snapshot)));

        if (! is_array($records)) {
            $records = [$snapshot];
        } elseif (! array_is_list($records)) {
            $records = [$records];
        }

        $balances = collect($records)->map(function ($record) {
            if (! is_array($record)) {
                return null;
            }

            $balance = $this->firstNumericValue($record, ['balance', 'walletBalance', 'wallet_balance', 'totalWalletBalance', 'total_balance', 'equity', 'amount']);
            $available = $this->firstNumericValue($record, ['availableBalance', 'available_balance', 'available', 'free_balance', 'free']);

            if ($balance === null && $available === null) {
                return null;
            }

            return [
                'currency' => (string) ($record['asset_symbol'] ?? $record['asset'] ?? $record['currency'] ?? $record['symbol'] ?? auth()->user()->currency ?? 'INR'),
                'balance' => $balance ?? $available,
                'available' => $available,
            ];
        })->filter()->values()->all();

        return ['balances' => $balances, 'synced_at' => $log->created_at];
    }

    private function firstNumericValue(array $record, array $keys): ?float
    {
        foreach ($keys as $key) {
            $value = data_get($record, $key);
            if ($value !== null && $value !== '' && is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
    }

    public function analysis(Request $request)
    {
        $trades = $this->filteredTrades($request)->orderBy('date')->get();

        return view('trades.analysis', [
            'stats' => $this->stats($trades),
            'weeklyAnalysis' => $this->periodAnalysis($trades, 'week')->take(16)->values(),
            'monthlyAnalysis' => $this->periodAnalysis($trades, 'month')->take(12)->values(),
            'currencyStats' => $this->currencyStats($trades),
            'displayCurrency' => $this->singleCurrency($trades),
        ]);
    }

    public function calendar(Request $request)
    {
        $calendarMonth = $this->calendarMonth($request->input('calendar_month'));
        $calendarTrades = $this->filteredTrades($request)
            ->whereBetween('date', [$calendarMonth->toDateString(), $calendarMonth->copy()->endOfMonth()->toDateString()])
            ->orderBy('date')
            ->get();

        return view('trades.calendar', [
            'calendarMonth' => $calendarMonth,
            'calendarDays' => $this->calendarDays($calendarMonth, $calendarTrades),
            'calendarTradeDetails' => $this->calendarTradeDetails($calendarTrades),
            'calendarStats' => $this->stats($calendarTrades),
            'currencyStats' => $this->currencyStats($calendarTrades),
            'displayCurrency' => $this->singleCurrency($calendarTrades),
        ]);
    }

    public function index(Request $request)
    {
        $trades = $this->tradeListQuery($request)
            ->latest('date')->latest('time')->latest('id')
            ->simplePaginate($this->tradeListPageSize($request))
            ->withQueryString();

        return view('trades.index', [
            'trades' => $trades,
            'stats' => $this->stats($trades->getCollection()),
            'currencyStats' => $this->currencyStats($trades->getCollection()),
            'filterOptions' => $this->tradeFilterOptions(),
        ]);
    }

    public function sharkTrades(Request $request)
    {
        return $this->exchangeTrades($request, 'SharkExchange', 'Shark Exchange Trades', 'Only trades imported from or assigned to SharkExchange.');
    }

    public function deltaTrades(Request $request)
    {
        return $this->exchangeTrades($request, 'Delta Exchange', 'Delta Exchange Trades', 'Delta fills and trades kept separate from your SharkExchange history.');
    }

    private function exchangeTrades(Request $request, string $broker, string $title, string $subtitle)
    {
        $trades = $this->tradeListQuery($request)
            ->where('broker', $broker)
            ->latest('date')->latest('time')->latest('id')
            ->simplePaginate($this->tradeListPageSize($request))
            ->withQueryString();

        return view('trades.index', [
            'trades' => $trades,
            'stats' => $this->stats($trades->getCollection()),
            'currencyStats' => $this->currencyStats($trades->getCollection()),
            'pageTitle' => $title,
            'pageSubtitle' => $subtitle,
            'filterAction' => $broker === 'Delta Exchange' ? route('trades.delta') : route('trades.shark'),
            'fixedQuery' => ['broker' => $broker],
            'emptyMessage' => "No {$broker} trades yet. Run its sync to import records.",
            'filterOptions' => $this->tradeFilterOptions($broker),
        ]);
    }

    public function create()
    {
        return view('trades.create', ['trade' => new Trade([
            'date' => now()->toDateString(),
            'status' => 'Closed',
            'asset_class' => 'Crypto',
            'market_segment' => 'Futures',
            'currency' => auth()->user()->currency ?? 'INR',
        ])]);
    }

    public function store(Request $request)
    {
        Trade::create($this->payload($request) + ['user_id' => auth()->id()]);
        $this->forgetTradeFilterOptions();

        return redirect()->route('trades.index')->with('success', 'Trade saved.');
    }

    public function edit(Trade $trade)
    {
        abort_unless($trade->user_id === auth()->id(), 404);

        return view('trades.edit', compact('trade'));
    }

    public function update(Request $request, Trade $trade)
    {
        abort_unless($trade->user_id === auth()->id(), 404);

        $trade->update($this->payload($request, $trade));
        $this->forgetTradeFilterOptions();

        return redirect()->route('trades.index')->with('success', 'Trade updated.');
    }

    public function updateNotes(Request $request, Trade $trade)
    {
        abort_unless($trade->user_id === auth()->id(), 404);

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:10000'],
            'screenshot' => ['nullable', 'array', 'max:6'],
            'screenshot.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $updates = [];
        if ($request->filled('notes')) {
            $updates['notes'] = $data['notes'];
        }

        $uploadedScreenshots = $this->uploadScreenshots($request);
        if ($uploadedScreenshots !== []) {
            $updates['screenshot'] = json_encode(array_merge($this->existingScreenshots($trade), $uploadedScreenshots));
        }

        if ($updates !== []) {
            $trade->update($updates);
        }

        $screenshots = collect($this->existingScreenshots($trade))->map(fn ($image) => [
            'name' => $image,
            'url' => route('trades.screenshot', [$trade, 'filename' => $image]),
        ])->values();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Trade note and screenshots saved.',
                'notes' => (string) $trade->notes,
                'screenshots' => $screenshots,
            ]);
        }

        return back()->with('success', 'Trade note saved.');
    }

    public function destroy(Trade $trade)
    {
        abort_unless($trade->user_id === auth()->id(), 404);

        $trade->delete();
        $this->forgetTradeFilterOptions();

        return redirect()->route('trades.index')->with('success', 'Trade deleted.');
    }

    public function screenshot(Trade $trade, string $filename)
    {
        abort_unless($trade->user_id === auth()->id(), 404);
        abort_unless($filename === basename($filename) && in_array($filename, $this->existingScreenshots($trade), true), 404);

        $privatePath = Storage::disk('local')->path('trade-screenshots/'.$filename);
        $legacyPath = public_path('uploads/'.$filename);
        $path = File::isFile($privatePath) ? $privatePath : $legacyPath;
        abort_unless(File::isFile($path), 404);

        return response()->file($path, [
            'Cache-Control' => 'private, no-store',
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $filename).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function export(Request $request)
    {
        $trades = $this->filteredTrades($request)->latest('date')->latest('time')->get();
        $filename = 'trading-journal-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($trades) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Time', 'Broker', 'Asset Class', 'Segment', 'Symbol', 'Side', 'Status', 'Quantity', 'Entry', 'Exit', 'Profit', 'Loss', 'Fees', 'Net PnL', 'Currency', 'Strategy', 'Plan Followed', 'Setup Quality', 'Mistakes', 'Source', 'Notes']);

            foreach ($trades as $trade) {
                fputcsv($out, [
                    optional($trade->date)->toDateString(),
                    $trade->time,
                    $trade->broker,
                    $trade->asset_class,
                    $trade->market_segment,
                    $trade->pair,
                    $trade->trade_type,
                    $trade->status,
                    $trade->quantity ?: $trade->lot_size,
                    $trade->entry_price,
                    $trade->exit_price,
                    $trade->profit,
                    $trade->loss,
                    $trade->trading_fees,
                    $trade->net_pnl,
                    $trade->currency,
                    $trade->strategy,
                    $trade->plan_followed ? 'Yes' : 'No',
                    $trade->setup_quality,
                    implode('|', $trade->mistake_tags ?: []),
                    $trade->imported_at ? 'Imported' : 'Manual',
                    $trade->notes,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function filteredTrades(Request $request)
    {
        return $this->resultTradesQuery()
            ->when($request->filled('broker'), fn ($query) => $query->where('broker', $request->broker))
            ->when($request->filled('pair'), fn ($query) => $query->where('pair', 'like', '%'.$request->pair.'%'))
            ->when($request->filled('asset_class'), fn ($query) => $query->where('asset_class', $request->asset_class))
            ->when($request->filled('market_segment'), fn ($query) => $query->where('market_segment', $request->market_segment))
            ->when($request->filled('strategy'), fn ($query) => $query->where('strategy', 'like', '%'.$request->strategy.'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->input('source') === 'imported', fn ($query) => $query->whereNotNull('imported_at'))
            ->when($request->input('source') === 'manual', fn ($query) => $query->whereNull('imported_at'))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('date', '<=', $request->to_date));
    }

    private function tradeListQuery(Request $request)
    {
        return $this->filteredTrades($request)->select([
            'id', 'user_id', 'date', 'time', 'pair', 'broker', 'asset_class', 'market_segment',
            'trade_type', 'status', 'quantity', 'lot_size', 'entry_price', 'exit_price',
            'currency', 'profit', 'loss', 'trading_fees', 'imported_at', 'screenshot',
        ]);
    }

    private function tradeListPageSize(Request $request): int
    {
        $size = (int) $request->input('per_page', 25);

        return in_array($size, [25, 50, 100], true) ? $size : 25;
    }

    private function tradeFilterOptions(?string $fixedBroker = null): array
    {
        $cacheKey = 'trade-filter-options:'.auth()->id().':'.($fixedBroker ?: 'all');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($fixedBroker) {
            $base = Trade::query()->where('user_id', auth()->id())
                ->when($fixedBroker, fn ($query) => $query->where('broker', $fixedBroker));

            $values = function (string $column) use ($base): array {
                return (clone $base)->whereNotNull($column)->where($column, '!=', '')
                    ->distinct()->orderBy($column)->pluck($column)->values()->all();
            };

            return [
                'brokers' => ['SharkExchange', 'Delta Exchange'],
                'pairs' => $values('pair'),
                'asset_classes' => $values('asset_class'),
                'market_segments' => $values('market_segment'),
                'strategies' => $values('strategy'),
                'statuses' => $values('status'),
            ];
        });
    }

    private function forgetTradeFilterOptions(): void
    {
        foreach (['all', 'SharkExchange', 'Delta Exchange'] as $scope) {
            Cache::forget('trade-filter-options:'.auth()->id().':'.$scope);
        }
    }

    private function resultTradesQuery()
    {
        return Trade::query()
            ->where('user_id', auth()->id())
            ->where(function ($query) {
                $query->where('exchange', '!=', 'delta')
                    ->orWhereNull('exchange')
                    ->orWhere(function ($delta) {
                        $delta->where('pair', 'not like', 'C-%')->where('pair', 'not like', 'P-%');
                    });
            })
            ->where(function ($query) {
                $query->where('profit', '>', 0)
                    ->orWhere('loss', '>', 0);
            });
    }

    private function payload(Request $request, ?Trade $trade = null): array
    {
        $submittedTime = $request->input('time');
        if (is_string($submittedTime) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $submittedTime)) {
            $request->merge(['time' => substr($submittedTime, 0, 5)]);
        }

        $data = $request->validate([
            'date' => ['required', 'date'],
            'time' => ['nullable', 'date_format:H:i'],
            'pair' => ['required', 'string', 'max:40'],
            'asset_class' => ['required', 'in:Equity,Options,Futures,Crypto,Commodity,Currency'],
            'market_segment' => ['required', 'in:Intraday,Delivery,Futures,Options,Scalping,Swing,Positional'],
            'currency' => ['required', 'in:INR,USD'],
            'trade_type' => ['required', 'in:Long,Short'],
            'status' => ['required', 'in:Open,Closed,Cancelled'],
            'broker' => ['nullable', 'string', 'max:80'],
            'strategy' => ['nullable', 'string', 'max:120'],
            'market_condition' => ['nullable', 'string', 'max:120'],
            'lot_size' => ['nullable', 'numeric'],
            'quantity' => ['nullable', 'numeric'],
            'entry_price' => ['nullable', 'numeric'],
            'exit_price' => ['nullable', 'numeric'],
            'leverage' => ['nullable', 'numeric'],
            'risk_amount' => ['nullable', 'numeric'],
            'profit' => ['nullable', 'numeric'],
            'loss' => ['nullable', 'numeric'],
            'trading_fees' => ['nullable', 'numeric'],
            'current_balance' => ['nullable', 'numeric'],
            'setup_quality' => ['nullable', 'integer', 'between:1,5'],
            'plan_followed' => ['nullable', 'boolean'],
            'emotion' => ['nullable', 'string', 'max:120'],
            'exit_reason' => ['nullable', 'string', 'max:120'],
            'mistake_tags' => ['nullable', 'array'],
            'mistake_tags.*' => ['string', 'max:60'],
            'notes' => ['nullable', 'string'],
            'screenshot.*' => ['nullable', 'image', 'max:4096'],
        ]);

        $data['plan_followed'] = $request->boolean('plan_followed');
        $data['mistake_tags'] = array_values(array_filter($data['mistake_tags'] ?? []));
        $data['broker'] = $data['broker'] ?: 'SharkExchange';
        $data['currency'] = $data['currency'] ?: (auth()->user()->currency ?? 'INR');
        $data['screenshot'] = json_encode(array_merge($this->existingScreenshots($trade), $this->uploadScreenshots($request)));

        return $data;
    }

    private function uploadScreenshots(Request $request): array
    {
        if (! $request->hasFile('screenshot')) {
            return [];
        }

        return collect($request->file('screenshot'))->map(function ($file) {
            $extension = $file->guessExtension() ?: 'jpg';
            $name = Str::uuid().'.'.$extension;
            Storage::disk('local')->putFileAs('trade-screenshots', $file, $name);

            return $name;
        })->all();
    }

    private function existingScreenshots(?Trade $trade): array
    {
        if (! $trade?->screenshot) {
            return [];
        }

        $images = json_decode($trade->screenshot, true);

        return is_array($images) ? $images : [$trade->screenshot];
    }

    private function breakdownRow($items, string $label, string $key): array
    {
        return [
            $key => $label,
            'count' => $items->count(),
            'net' => $items->sum(fn (Trade $trade) => $trade->net_pnl),
            'win_rate' => $this->stats($items)['win_rate'],
        ];
    }

    private function periodAnalysis($trades, string $period)
    {
        return $trades
            ->groupBy(function (Trade $trade) use ($period) {
                $date = $trade->date ? $trade->date->copy() : null;

                if (! $date) {
                    return 'Unknown';
                }

                return $period === 'week'
                    ? $date->startOfWeek(Carbon::MONDAY)->toDateString()
                    : $date->startOfMonth()->toDateString();
            })
            ->map(function ($items, $periodStart) use ($period) {
                $start = $periodStart === 'Unknown' ? null : Carbon::parse($periodStart);
                $end = $start
                    ? ($period === 'week' ? $start->copy()->endOfWeek(Carbon::SUNDAY) : $start->copy()->endOfMonth())
                    : null;
                $stats = $this->stats($items);

                return [
                    'label' => $start
                        ? ($period === 'week' ? $start->format('d M').' - '.$end->format('d M Y') : $start->format('M Y'))
                        : 'Unknown',
                    'trades' => $stats['total'],
                    'net' => $stats['net'],
                    'profit' => $stats['profit'],
                    'loss' => $stats['loss'],
                    'win_rate' => $stats['win_rate'],
                    'avg_trade' => $stats['total'] ? round($stats['net'] / $stats['total'], 2) : 0,
                    'best' => $stats['best'],
                    'worst' => $stats['worst'],
                    'sort_date' => $start?->toDateString() ?? '0000-00-00',
                ];
            })
            ->sortByDesc('sort_date')
            ->values();
    }

    private function calendarMonth(?string $month): Carbon
    {
        try {
            return $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth() : now()->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }

    private function calendarDays(Carbon $month, $trades): array
    {
        $daily = $trades->groupBy(fn (Trade $trade) => optional($trade->date)->toDateString())
            ->map(function ($items) {
                $stats = $this->stats($items);

                return [
                    'trades' => $stats['total'],
                    'net' => $stats['net'],
                    'win_rate' => $stats['win_rate'],
                ];
            });

        $cursor = $month->copy()->startOfWeek(Carbon::MONDAY);
        $end = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $days = [];

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $summary = $daily->get($key, ['trades' => 0, 'net' => 0, 'win_rate' => 0]);

            $days[] = [
                'date' => $key,
                'day' => $cursor->day,
                'is_current_month' => $cursor->isSameMonth($month),
                'trades' => $summary['trades'],
                'net' => $summary['net'],
                'win_rate' => $summary['win_rate'],
            ];

            $cursor->addDay();
        }

        return $days;
    }

    private function calendarTradeDetails($trades): array
    {
        return $trades
            ->groupBy(fn (Trade $trade) => optional($trade->date)->toDateString())
            ->map(function ($items) {
                return $items->map(fn (Trade $trade) => [
                    'id' => $trade->id,
                    'time' => $trade->time ?: '-',
                    'pair' => $trade->pair,
                    'broker' => $trade->broker ?: 'Unclassified',
                    'asset_class' => $trade->asset_class ?: 'Unclassified',
                    'market_segment' => $trade->market_segment ?: 'Unclassified',
                    'side' => $trade->trade_type,
                    'status' => $trade->status,
                    'quantity' => (string) ($trade->quantity ?: $trade->lot_size ?: '-'),
                    'entry' => (string) ($trade->entry_price ?: '-'),
                    'exit' => (string) ($trade->exit_price ?: '-'),
                    'net' => round($trade->net_pnl, 2),
                    'currency' => $trade->currency ?: (auth()->user()->currency ?? 'INR'),
                    'strategy' => $trade->strategy ?: 'Unclassified',
                    'plan_followed' => $trade->plan_followed ? 'Yes' : 'No',
                    'notes' => (string) $trade->notes,
                    'screenshots' => collect($this->existingScreenshots($trade))->map(fn ($image) => [
                        'name' => $image,
                        'url' => route('trades.screenshot', [$trade, 'filename' => $image]),
                    ])->values(),
                    'edit_url' => route('trades.edit', $trade),
                    'notes_url' => route('trades.notes.update', $trade),
                ])->values();
            })
            ->all();
    }

    private function stats($trades): array
    {
        $total = $trades->count();
        $profit = (float) $trades->sum('profit');
        $loss = (float) $trades->sum('loss');
        $fees = (float) $trades->sum('trading_fees');
        $net = $trades->sum(fn (Trade $trade) => $trade->net_pnl);
        $wins = $trades->filter(fn (Trade $trade) => $trade->net_pnl > 0);
        $losers = $trades->filter(fn (Trade $trade) => $trade->net_pnl < 0);

        return [
            'total' => $total,
            'profit' => $profit,
            'loss' => $loss,
            'fees' => $fees,
            'net' => $net,
            'wins' => $wins->count(),
            'losses' => $losers->count(),
            'win_rate' => $total > 0 ? round(($wins->count() / $total) * 100, 2) : 0,
            'avg_win' => $wins->count() ? round($wins->avg(fn (Trade $trade) => $trade->net_pnl), 2) : 0,
            'avg_loss' => $losers->count() ? round(abs($losers->avg(fn (Trade $trade) => $trade->net_pnl)), 2) : 0,
            'best' => $trades->max(fn (Trade $trade) => $trade->net_pnl) ?? 0,
            'worst' => $trades->min(fn (Trade $trade) => $trade->net_pnl) ?? 0,
            'balance' => $this->latestBalance($trades),
        ];
    }

    private function currencyStats($trades): array
    {
        return $trades->groupBy(fn (Trade $trade) => $trade->currency ?: 'INR')
            ->map(fn ($items, $currency) => [
                'currency' => $currency,
                'profit' => (float) $items->sum('profit'),
                'loss' => (float) $items->sum('loss'),
                'fees' => (float) $items->sum('trading_fees'),
                'net' => (float) $items->sum(fn (Trade $trade) => $trade->net_pnl),
            ])->values()->all();
    }

    private function singleCurrency($trades): string
    {
        $currencies = $trades->map(fn (Trade $trade) => $trade->currency ?: 'INR')->unique()->values();

        return $currencies->count() === 1 ? $currencies->first() : (auth()->user()->currency ?? 'INR');
    }

    private function latestBalance($trades): float
    {
        $tradeBalance = $trades
            ->filter(fn (Trade $trade) => $trade->current_balance !== null)
            ->sortByDesc(fn (Trade $trade) => ($trade->date?->format('Y-m-d') ?? '').' '.($trade->time ?? ''))
            ->first()?->current_balance;

        if ($tradeBalance !== null) {
            return (float) $tradeBalance;
        }

        $anyTradeBalance = Trade::query()
            ->where('user_id', auth()->id())
            ->whereNotNull('current_balance')
            ->latest('date')
            ->latest('time')
            ->value('current_balance');

        if ($anyTradeBalance !== null) {
            return (float) $anyTradeBalance;
        }

        $wallet = SyncLog::query()
            ->where('user_id', auth()->id())
            ->whereNotNull('wallet_snapshot')
            ->latest()
            ->first()?->wallet_snapshot;

        return (float) data_get($wallet, 'walletBalance', data_get($wallet, 'balance', data_get($wallet, 'availableBalance', 0)));
    }

    private function equitySeries($trades): array
    {
        $running = 0;

        return $trades->map(function (Trade $trade) use (&$running) {
            $running = $trade->current_balance ?? ($running + $trade->net_pnl);

            return [
                'date' => optional($trade->date)->format('d M') ?? $trade->date,
                'value' => round((float) $running, 2),
            ];
        })->values()->all();
    }
}
