<?php

namespace App\Http\Controllers;

use App\Models\EconomicCalendarEvent;
use App\Models\Trade;
use App\Services\CryptoIntelligenceService;
use App\Services\MarketNewsService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class NewsController extends Controller
{
    public function index(Request $request, MarketNewsService $newsService, CryptoIntelligenceService $cryptoService)
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'in:all,markets,stocks,crypto,economy,forex,commodities'],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'symbol' => ['nullable', 'string', 'in:BTC,ETH'],
        ]);

        $category = $filters['category'] ?? 'all';
        $query = trim($filters['q'] ?? '');
        $error = null;

        try {
            $articles = collect($newsService->headlines($category));
        } catch (Throwable $exception) {
            report($exception);
            $articles = collect();
            $error = 'Live headlines are temporarily unavailable. Please try again shortly.';
        }

        if ($query !== '') {
            $needle = mb_strtolower($query);
            $articles = $articles->filter(fn (array $article) => str_contains(
                mb_strtolower($article['title'].' '.$article['description'].' '.$article['source'].' '.implode(' ', $article['tickers'] ?? [])),
                $needle
            ))->values();
        }

        $page = (int) ($filters['page'] ?? 1);
        $perPage = 12;
        $paginator = new LengthAwarePaginator(
            $articles->forPage($page, $perPage)->values(),
            $articles->count(),
            $perPage,
            $page,
            ['path' => route('news.index'), 'query' => $request->except('page')]
        );

        $viewData = [
            'articles' => $paginator,
            'category' => $category,
            'query' => $query,
            'error' => $error,
            'categories' => MarketNewsService::CATEGORIES,
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('news._results', $viewData)->render(),
                'total' => $paginator->total(),
                'category' => $category,
                'query' => $query,
                'error' => $error,
            ]);
        }

        $symbol = $filters['symbol'] ?? 'BTC';
        try {
            $market = $cryptoService->snapshot($symbol);
        } catch (Throwable $exception) {
            report($exception);
            $market = ['venues' => [], 'errors' => ['Crypto market data is temporarily unavailable.'], 'summary' => [
                'price' => null, 'change_24h' => null, 'open_interest_usd' => 0, 'funding_rate' => null,
                'volume_24h_usd' => 0, 'venues_online' => 0,
            ], 'updated_at' => now()->toIso8601String()];
        }

        $calendar = EconomicCalendarEvent::query()
            ->whereBetween('scheduled_at', [now()->subHours(6), now()->addDays(7)])
            ->orderBy('scheduled_at')
            ->limit(30)
            ->get();
        $trackedMarkets = Trade::query()->where('user_id', auth()->id())->latest('date')->limit(100)->pluck('pair')
            ->map(fn ($pair) => strtoupper((string) $pair))->filter()->unique()->take(8)->values();

        return view('news.index', $viewData + compact('market', 'symbol', 'calendar', 'trackedMarkets'));
    }
}
