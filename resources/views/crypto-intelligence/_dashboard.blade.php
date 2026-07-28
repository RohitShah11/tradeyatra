@php($summary = $market['summary'])
@foreach($market['errors'] as $message)<div class="ci-warning">{{ $message }}</div>@endforeach

<div class="ci-stats">
    <div class="panel ci-stat"><small>{{ $symbol }} average price</small><strong>{{ $summary['price'] ? '$'.number_format($summary['price'], 2) : 'Unavailable' }}</strong><div class="ci-note">Across {{ $summary['venues_online'] }} connected venues</div></div>
    <div class="panel ci-stat"><small>Average 24h move</small><strong class="{{ ($summary['change_24h'] ?? 0) >= 0 ? 'ci-positive' : 'ci-negative' }}">{{ $summary['change_24h'] !== null ? number_format($summary['change_24h'], 2).'%' : 'Unavailable' }}</strong><div class="ci-note">Perpetual futures price change</div></div>
    <div class="panel ci-stat"><small>Combined open interest</small><strong>${{ number_format($summary['open_interest_usd'] / 1000000000, 2) }}B</strong><div class="ci-note">Estimated USD notional</div></div>
    <div class="panel ci-stat"><small>Average funding</small><strong class="{{ ($summary['funding_rate'] ?? 0) >= 0 ? 'ci-positive' : 'ci-negative' }}">{{ $summary['funding_rate'] !== null ? number_format($summary['funding_rate'] * 100, 4).'%' : 'Unavailable' }}</strong><div class="ci-note">Positive means longs pay shorts</div></div>
</div>

<div class="ci-charts">
    <div class="panel ci-chart-card">
        <div><h2>Open interest distribution</h2><p>USD notional by exchange, in millions</p></div>
        <div class="ci-chart-wrap"><canvas id="ciOpenInterestChart" aria-label="Open interest by exchange"></canvas></div>
    </div>
    <div class="panel ci-chart-card">
        <div><h2>Funding rate comparison</h2><p>Current perpetual funding percentage</p></div>
        <div class="ci-chart-wrap"><canvas id="ciFundingChart" aria-label="Funding rates by exchange"></canvas></div>
    </div>
</div>

<div class="panel">
    <h2>{{ $symbol }} exchange comparison</h2>
    <div class="table-wrap">
        <table class="ci-table">
            <thead><tr><th>Exchange</th><th>Price</th><th>24h change</th><th>Funding</th><th>Open interest</th><th>24h volume</th><th>Next funding</th></tr></thead>
            <tbody>
                @forelse($market['venues'] as $venue)
                    <tr>
                        <td class="ci-exchange">{{ $venue['exchange'] }}</td>
                        <td>${{ number_format($venue['price'], 2) }}</td>
                        <td class="{{ $venue['change_24h'] >= 0 ? 'ci-positive' : 'ci-negative' }}">{{ number_format($venue['change_24h'], 2) }}%</td>
                        <td class="{{ $venue['funding_rate'] >= 0 ? 'ci-positive' : 'ci-negative' }}">{{ number_format($venue['funding_rate'] * 100, 4) }}%</td>
                        <td>${{ number_format($venue['open_interest_usd'] / 1000000, 1) }}M</td>
                        <td>${{ number_format($venue['volume_24h_usd'] / 1000000000, 2) }}B</td>
                        <td>{{ $venue['next_funding_at'] ? \Illuminate\Support\Carbon::parse($venue['next_funding_at'])->diffForHumans() : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">No exchange data is available at this time.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="ci-disclaimer">Public derivatives data is informational and may differ between exchanges. It is not financial advice or an execution price.</p>
</div>
