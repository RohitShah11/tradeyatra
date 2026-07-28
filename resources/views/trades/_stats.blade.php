@php($hasCurrencyStats = isset($currencyStats))
@php($displayCurrency = auth()->user()->currency ?? 'INR')
<div class="grid cols-4">
    <div class="card">
        @if($hasCurrencyStats)
            <div class="label">Net P&amp;L by currency</div>
            @forelse($currencyStats as $row)<div class="metric {{ $row['net'] >= 0 ? 'positive' : 'negative' }}">{{ $row['currency'] }} {{ number_format($row['net'], 2) }}</div>@empty<div class="metric">-</div>@endforelse
        @else
            <div class="label">Net P&amp;L</div><div class="metric {{ $stats['net'] >= 0 ? 'positive' : 'negative' }}">{{ $displayCurrency }} {{ number_format($stats['net'], 2) }}</div>
        @endif
    </div>
    <div class="card"><div class="label">Win Rate</div><div class="metric">{{ number_format($stats['win_rate'], 2) }}%</div></div>
    <div class="card"><div class="label">Trades</div><div class="metric">{{ $stats['total'] }}</div></div>
    <div class="card">
        @if($hasCurrencyStats)
            <div class="label">Fees by currency</div>
            @forelse($currencyStats as $row)<div class="metric">{{ $row['currency'] }} {{ number_format($row['fees'], 2) }}</div>@empty<div class="metric">-</div>@endforelse
        @else
            <div class="label">Balance</div><div class="metric">{{ $displayCurrency }} {{ number_format((float) $stats['balance'], 2) }}</div>
        @endif
    </div>
</div>
