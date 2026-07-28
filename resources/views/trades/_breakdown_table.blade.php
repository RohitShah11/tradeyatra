@php($displayCurrency = $displayCurrency ?? auth()->user()->currency ?? 'INR')
<table>
    <thead><tr><th>{{ $labelTitle }}</th><th>Trades</th><th>Win Rate</th><th>Net</th></tr></thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                <td>{{ $row[$labelKey] }}</td>
                <td>{{ $row['count'] }}</td>
                <td>{{ number_format($row['win_rate'], 2) }}%</td>
                <td class="{{ $row['net'] >= 0 ? 'positive' : 'negative' }}">{{ $displayCurrency }} {{ number_format($row['net'], 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">No data yet.</td></tr>
        @endforelse
    </tbody>
</table>
