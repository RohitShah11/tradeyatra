@php($hasFilters = collect(request()->except('page'))->filter(fn($value) => $value !== null && $value !== '')->isNotEmpty())
<details class="trade-filter-panel" {{ $hasFilters ? 'open' : '' }}>
    <summary><span><svg class="icon"><use href="#icon-settings"></use></svg>Filter journal</span><span class="filter-summary-copy">{{ $hasFilters ? 'Filters applied' : 'Search by exchange, market, date, or strategy' }}</span></summary>
    <form method="GET" class="trade-filter-form" action="{{ $action ?? route('trades.index') }}">
        @if(empty($fixedQuery['broker']))
            <div><label for="filterBroker">Exchange</label><select id="filterBroker" name="broker"><option value="">Shark + Delta</option>@foreach($filterOptions['brokers'] as $broker)<option value="{{ $broker }}" @selected(request('broker') === $broker)>{{ $broker === 'SharkExchange' ? 'Shark Exchange' : $broker }}</option>@endforeach</select></div>
        @endif
        <div><label for="filterPair">Market</label><input id="filterPair" name="pair" value="{{ request('pair') }}" placeholder="Search market" list="journalPairs"><datalist id="journalPairs">@foreach($filterOptions['pairs'] as $pair)<option value="{{ $pair }}">@endforeach</datalist></div>
        <div><label for="filterStatus">Status</label><select id="filterStatus" name="status"><option value="">Any status</option>@foreach($filterOptions['statuses'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></div>
        <div><label for="filterSource">Source</label><select id="filterSource" name="source"><option value="">All sources</option><option value="imported" @selected(request('source') === 'imported')>Imported</option><option value="manual" @selected(request('source') === 'manual')>Manual</option></select></div>
        <div><label for="filterAsset">Asset class</label><select id="filterAsset" name="asset_class"><option value="">All assets</option>@foreach($filterOptions['asset_classes'] as $asset)<option value="{{ $asset }}" @selected(request('asset_class') === $asset)>{{ $asset }}</option>@endforeach</select></div>
        <div><label for="filterSegment">Segment</label><select id="filterSegment" name="market_segment"><option value="">All segments</option>@foreach($filterOptions['market_segments'] as $segment)<option value="{{ $segment }}" @selected(request('market_segment') === $segment)>{{ $segment }}</option>@endforeach</select></div>
        <div><label for="filterStrategy">Strategy</label><input id="filterStrategy" name="strategy" value="{{ request('strategy') }}" placeholder="Search strategy" list="journalStrategies"><datalist id="journalStrategies">@foreach($filterOptions['strategies'] as $strategy)<option value="{{ $strategy }}">@endforeach</datalist></div>
        <div><label for="filterFrom">From date</label><input id="filterFrom" type="date" name="from_date" value="{{ request('from_date') }}"></div>
        <div><label for="filterTo">To date</label><input id="filterTo" type="date" name="to_date" value="{{ request('to_date') }}"></div>
        <div><label for="filterPageSize">Rows</label><select id="filterPageSize" name="per_page">@foreach([25,50,100] as $size)<option value="{{ $size }}" @selected((int)request('per_page',25) === $size)>{{ $size }} per page</option>@endforeach</select></div>
        <div class="filter-actions"><button class="btn" type="submit">Apply filters</button><a class="btn secondary" href="{{ $action ?? route('trades.index') }}">Reset</a><a class="btn secondary" href="{{ route('trades.export', array_merge(request()->query(), $fixedQuery ?? [])) }}">Export CSV</a></div>
    </form>
</details>
