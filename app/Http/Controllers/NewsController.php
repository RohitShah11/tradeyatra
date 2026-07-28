<?php

namespace App\Http\Controllers;

use App\Services\MarketNewsService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class NewsController extends Controller
{
    public function index(Request $request, MarketNewsService $newsService)
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'in:all,markets,stocks,crypto,economy,forex,commodities'],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
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

        return view('news.index', $viewData);
    }
}
