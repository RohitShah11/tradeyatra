<?php

namespace App\Http\Controllers;

use App\Services\CryptoIntelligenceService;
use Illuminate\Http\Request;

class CryptoIntelligenceController extends Controller
{
    public function index(Request $request, CryptoIntelligenceService $service)
    {
        $filters = $request->validate(['symbol' => ['nullable', 'string', 'in:BTC,ETH']]);
        $symbol = $filters['symbol'] ?? 'BTC';
        $market = $service->snapshot($symbol);
        $viewData = compact('market', 'symbol') + ['symbols' => ['BTC', 'ETH']];

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('crypto-intelligence._dashboard', $viewData)->render(),
                'symbol' => $symbol,
                'chart' => $this->chartData($market),
            ]);
        }

        return view('crypto-intelligence.index', $viewData);
    }

    private function chartData(array $market): array
    {
        return [
            'labels' => collect($market['venues'])->pluck('exchange')->values()->all(),
            'openInterest' => collect($market['venues'])->pluck('open_interest_usd')->map(fn ($value) => round($value / 1000000, 2))->values()->all(),
            'funding' => collect($market['venues'])->pluck('funding_rate')->map(fn ($value) => round($value * 100, 5))->values()->all(),
        ];
    }
}
