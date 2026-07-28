@csrf
@if($trade->exists)
    @method('PUT')
@endif
<div class="panel form-grid">
    <div><label>Date</label><input type="date" name="date" value="{{ old('date', optional($trade->date)->toDateString() ?: $trade->date) }}" required></div>
    <div><label>Time</label><input type="time" name="time" value="{{ substr((string) old('time', $trade->time), 0, 5) }}"></div>
    <div><label>Pair</label><input name="pair" value="{{ old('pair', $trade->pair) }}" placeholder="BTCINR" required></div>
    <div><label>Broker</label><input name="broker" value="{{ old('broker', $trade->broker ?: 'SharkExchange') }}" list="broker-list"></div>
    <datalist id="broker-list">
        @foreach(['SharkExchange', 'Zerodha', 'Groww', 'Upstox', 'Angel One', 'Dhan', 'ICICI Direct', 'Kotak Neo', 'Fyers', 'Delta Exchange', 'Exness', 'Other'] as $broker)
            <option value="{{ $broker }}"></option>
        @endforeach
    </datalist>
    <div><label>Asset Class</label><select name="asset_class" required>@foreach(['Crypto', 'Equity', 'Options', 'Futures', 'Commodity', 'Currency'] as $asset)<option value="{{ $asset }}" @selected(old('asset_class', $trade->asset_class ?: 'Crypto') === $asset)>{{ $asset }}</option>@endforeach</select></div>
    <div><label>Segment</label><select name="market_segment" required>@foreach(['Futures', 'Options', 'Intraday', 'Delivery', 'Scalping', 'Swing', 'Positional'] as $segment)<option value="{{ $segment }}" @selected(old('market_segment', $trade->market_segment ?: 'Futures') === $segment)>{{ $segment }}</option>@endforeach</select></div>
    <div><label>Currency</label><select name="currency" required>@foreach(['INR', 'USD'] as $currency)<option value="{{ $currency }}" @selected(old('currency', $trade->currency ?: auth()->user()->currency ?? 'INR') === $currency)>{{ $currency }}</option>@endforeach</select></div>
    <div><label>Direction</label><select name="trade_type" required><option value="Long" @selected(old('trade_type', $trade->trade_type) === 'Long')>Long</option><option value="Short" @selected(old('trade_type', $trade->trade_type) === 'Short')>Short</option></select></div>
    <div><label>Status</label><select name="status" required>@foreach(['Open', 'Closed', 'Cancelled'] as $status)<option value="{{ $status }}" @selected(old('status', $trade->status) === $status)>{{ $status }}</option>@endforeach</select></div>
    <div><label>Strategy</label><input name="strategy" value="{{ old('strategy', $trade->strategy) }}" placeholder="Breakout, VWAP, S/R"></div>
    <div><label>Market Condition</label><input name="market_condition" value="{{ old('market_condition', $trade->market_condition) }}" placeholder="Trend, range, news"></div>
    <div><label>Quantity</label><input type="number" step="0.00000001" name="quantity" value="{{ old('quantity', $trade->quantity) }}"></div>
    <div><label>Lot Size</label><input type="number" step="0.01" name="lot_size" value="{{ old('lot_size', $trade->lot_size) }}"></div>
    <div><label>Entry Price</label><input type="number" step="0.00000001" name="entry_price" value="{{ old('entry_price', $trade->entry_price) }}"></div>
    <div><label>Exit Price</label><input type="number" step="0.00000001" name="exit_price" value="{{ old('exit_price', $trade->exit_price) }}"></div>
    <div><label>Leverage</label><input type="number" step="0.01" name="leverage" value="{{ old('leverage', $trade->leverage) }}"></div>
    <div><label>Risk Amount</label><input type="number" step="0.01" name="risk_amount" value="{{ old('risk_amount', $trade->risk_amount) }}"></div>
    <div><label>Profit</label><input type="number" step="0.01" name="profit" value="{{ old('profit', $trade->profit) }}"></div>
    <div><label>Loss</label><input type="number" step="0.01" name="loss" value="{{ old('loss', $trade->loss) }}"></div>
    <div><label>Fees</label><input type="number" step="0.01" name="trading_fees" value="{{ old('trading_fees', $trade->trading_fees) }}"></div>
    <div><label>Balance After</label><input type="number" step="0.01" name="current_balance" value="{{ old('current_balance', $trade->current_balance) }}"></div>
    <div><label>Setup Quality</label><select name="setup_quality"><option value="">Unrated</option>@for($i = 1; $i <= 5; $i++)<option value="{{ $i }}" @selected((string) old('setup_quality', $trade->setup_quality) === (string) $i)>{{ $i }}/5</option>@endfor</select></div>
    <div><label>Plan Followed</label><select name="plan_followed"><option value="1" @selected(old('plan_followed', $trade->plan_followed ?? true))>Yes</option><option value="0" @selected(old('plan_followed', $trade->plan_followed) === '0')>No</option></select></div>
    <div><label>Emotion</label><input name="emotion" value="{{ old('emotion', $trade->emotion) }}" placeholder="Calm, FOMO, patient"></div>
    <div><label>Exit Reason</label><input name="exit_reason" value="{{ old('exit_reason', $trade->exit_reason) }}" placeholder="Target, stop, rule break"></div>
    <div class="span-2">
        <label>Mistake Tags</label>
        @php($selectedMistakes = old('mistake_tags', $trade->mistake_tags ?: []))
        <select name="mistake_tags[]" multiple>
            @foreach(['Late Entry', 'Early Exit', 'Moved Stop', 'Oversized', 'No Plan', 'Revenge Trade', 'Ignored News'] as $tag)
                <option value="{{ $tag }}" @selected(in_array($tag, $selectedMistakes))>{{ $tag }}</option>
            @endforeach
        </select>
    </div>
    <div class="span-2"><label>Screenshots</label><input type="file" name="screenshot[]" multiple accept="image/*"></div>
    <div class="span-4"><label>Notes</label><textarea name="notes" placeholder="Setup, reasoning, execution, post-trade lesson">{{ old('notes', $trade->notes) }}</textarea></div>
    @if($trade->screenshot)
        <div class="span-4 screens">
            @foreach((json_decode($trade->screenshot, true) ?: [$trade->screenshot]) as $img)
                <a href="{{ route('trades.screenshot', [$trade, 'filename' => $img]) }}" target="_blank"><img src="{{ route('trades.screenshot', [$trade, 'filename' => $img]) }}" alt="Trade screenshot"></a>
            @endforeach
        </div>
    @endif
    <div class="span-4 actions">
        <button class="btn">{{ $trade->exists ? 'Update Trade' : 'Save Trade' }}</button>
        <a class="btn secondary" href="{{ route('trades.index') }}">Cancel</a>
    </div>
</div>
