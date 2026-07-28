@php($baseQuery = request()->except('broker', 'page'))
<div class="analysis-tabs" style="display:flex;gap:10px;flex-wrap:wrap;">
    <a class="btn {{ request('broker') ? 'secondary' : '' }}" href="{{ route($routeName, $baseQuery) }}">All Exchanges</a>
    <a class="btn {{ request('broker') === 'SharkExchange' ? '' : 'secondary' }}" href="{{ route($routeName, array_merge($baseQuery, ['broker' => 'SharkExchange'])) }}">SharkExchange</a>
    <a class="btn {{ request('broker') === 'Delta Exchange' ? '' : 'secondary' }}" href="{{ route($routeName, array_merge($baseQuery, ['broker' => 'Delta Exchange'])) }}">Delta Exchange</a>
</div>
