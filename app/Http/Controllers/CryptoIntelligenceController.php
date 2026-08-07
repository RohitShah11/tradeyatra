<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CryptoIntelligenceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['symbol' => ['nullable', 'string', 'in:BTC,ETH']]);
        $symbol = $filters['symbol'] ?? 'BTC';
        return redirect(route('news.index', ['symbol' => $symbol]).'#crypto-pulse');
    }

}
