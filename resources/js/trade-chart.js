import {
    CandlestickSeries,
    ColorType,
    HistogramSeries,
    LineStyle,
    createChart,
    createSeriesMarkers,
} from 'lightweight-charts';

const container = document.getElementById('tradeReviewChart');
const payloadElement = document.getElementById('tradeChartPayload');

if (container && payloadElement) {
    const trade = JSON.parse(payloadElement.textContent || '{}');
    const intervalSelect = document.getElementById('tradeChartInterval');
    const state = document.getElementById('tradeChartState');
    const source = document.getElementById('tradeChartSource');
    const warning = document.getElementById('tradeChartWarning');
    const cssColor = (name, fallback) => getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;
    let requestController = null;

    const dateTimeFormatter = new Intl.DateTimeFormat('en-IN', {
        timeZone: trade.timezone || 'Asia/Kolkata',
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });

    const themeOptions = () => ({
        layout: {
            background: { type: ColorType.Solid, color: cssColor('--panel', '#0b1722') },
            textColor: cssColor('--muted', '#8fa8b8'),
            attributionLogo: true,
        },
        grid: {
            vertLines: { color: cssColor('--line', 'rgba(120,214,255,.12)') },
            horzLines: { color: cssColor('--line', 'rgba(120,214,255,.12)') },
        },
        rightPriceScale: { borderColor: cssColor('--line', 'rgba(120,214,255,.18)'), scaleMargins: { top: 0.08, bottom: 0.2 } },
        timeScale: { borderColor: cssColor('--line', 'rgba(120,214,255,.18)'), timeVisible: true, secondsVisible: false, rightOffset: 8 },
        crosshair: {
            vertLine: { color: cssColor('--muted', '#8fa8b8'), labelBackgroundColor: cssColor('--panel-2', '#101f2d') },
            horzLine: { color: cssColor('--muted', '#8fa8b8'), labelBackgroundColor: cssColor('--panel-2', '#101f2d') },
        },
    });

    const chart = createChart(container, {
        ...themeOptions(),
        autoSize: true,
        localization: {
            timeFormatter: (time) => dateTimeFormatter.format(new Date(Number(time) * 1000)),
        },
    });

    const candles = chart.addSeries(CandlestickSeries, {
        upColor: '#20e6a4',
        downColor: '#ff6171',
        borderUpColor: '#20e6a4',
        borderDownColor: '#ff6171',
        wickUpColor: '#20e6a4',
        wickDownColor: '#ff6171',
        priceLineVisible: false,
    });
    const volume = chart.addSeries(HistogramSeries, {
        priceFormat: { type: 'volume' },
        priceScaleId: 'volume',
        lastValueVisible: false,
        priceLineVisible: false,
    });
    chart.priceScale('volume').applyOptions({ scaleMargins: { top: 0.83, bottom: 0 } });

    const priceLines = [
        { price: trade.entryPrice, title: 'ENTRY', color: '#18c7ff', lineStyle: LineStyle.Solid, lineWidth: 2 },
        { price: trade.stopLoss, title: 'PLANNED SL', color: '#ff6171', lineStyle: LineStyle.Dashed, lineWidth: 2 },
        { price: trade.takeProfit, title: 'PLANNED TP', color: '#20e6a4', lineStyle: LineStyle.Dashed, lineWidth: 2 },
        { price: trade.exitPrice, title: 'EXIT', color: '#b889ff', lineStyle: LineStyle.Dotted, lineWidth: 2 },
    ];

    priceLines.filter((line) => Number.isFinite(line.price)).forEach((line) => candles.createPriceLine({
        ...line,
        axisLabelVisible: true,
        lineVisible: true,
    }));

    const markersPlugin = createSeriesMarkers(candles, []);

    function setState(mode, message = '') {
        if (!state) return;
        if (mode === 'ready') {
            state.hidden = true;
            return;
        }

        state.hidden = false;
        const wrapper = document.createElement('div');
        if (mode === 'loading') {
            const spinner = document.createElement('span');
            spinner.className = 'trade-chart-spinner';
            wrapper.appendChild(spinner);
        }
        const heading = document.createElement('strong');
        heading.textContent = mode === 'loading' ? 'Loading market candles' : 'Chart unavailable';
        const copy = document.createElement('span');
        copy.textContent = message || (mode === 'loading' ? 'Preparing the trade view…' : 'The candles could not be loaded.');
        wrapper.append(heading, copy);
        state.replaceChildren(wrapper);
    }

    function nearestCandleTime(data, timestamp) {
        if (!Number.isFinite(timestamp) || !data.length) return null;

        return data.reduce((nearest, candle) => (
            Math.abs(candle.time - timestamp) < Math.abs(nearest - timestamp) ? candle.time : nearest
        ), data[0].time);
    }

    function priceText(value) {
        if (!Number.isFinite(value)) return '';
        const decimals = Math.abs(value) < 1 ? 6 : (Math.abs(value) < 100 ? 4 : 2);
        return Number(value).toLocaleString('en-IN', { maximumFractionDigits: decimals });
    }

    function tradeMarkers(data) {
        const markers = [];
        const isLong = String(trade.side).toLowerCase() === 'long';
        const entryTime = trade.entryTime === null || trade.entryTime === undefined
            ? null
            : nearestCandleTime(data, Number(trade.entryTime));
        const exitTime = trade.exitTime === null || trade.exitTime === undefined
            ? null
            : nearestCandleTime(data, Number(trade.exitTime));

        if (entryTime !== null && Number.isFinite(trade.entryPrice)) {
            markers.push({
                time: entryTime,
                position: isLong ? 'belowBar' : 'aboveBar',
                color: '#18c7ff',
                shape: isLong ? 'arrowUp' : 'arrowDown',
                text: `${isLong ? 'BUY' : 'SELL'} ${priceText(trade.entryPrice)}`,
            });
        }

        if (exitTime !== null && Number.isFinite(trade.exitPrice)) {
            markers.push({
                time: exitTime,
                position: isLong ? 'aboveBar' : 'belowBar',
                color: Number(trade.netPnl) >= 0 ? '#20e6a4' : '#ff6171',
                shape: isLong ? 'arrowDown' : 'arrowUp',
                text: `${isLong ? 'SELL' : 'BUY'} ${priceText(trade.exitPrice)} · ${Number(trade.netPnl) >= 0 ? 'PROFIT' : 'LOSS'}`,
            });
        }

        return markers.sort((a, b) => a.time - b.time);
    }

    function applyPricePrecision(data) {
        const sample = [trade.entryPrice, trade.stopLoss, trade.takeProfit, trade.exitPrice, data[0]?.close]
            .find((value) => Number.isFinite(value));
        if (!Number.isFinite(sample)) return;
        const precision = Math.abs(sample) < 1 ? 6 : (Math.abs(sample) < 100 ? 4 : 2);
        candles.applyOptions({ priceFormat: { type: 'price', precision, minMove: 10 ** -precision } });
    }

    async function loadCandles() {
        requestController?.abort();
        requestController = new AbortController();
        setState('loading');
        if (warning) warning.textContent = '';

        try {
            const url = new URL(container.dataset.candlesUrl, window.location.href);
            url.searchParams.set('interval', intervalSelect?.value || '15m');
            const response = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: requestController.signal,
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || 'The market-data request failed.');

            const data = Array.isArray(result.candles) ? result.candles : [];
            if (!data.length) throw new Error('No candles were returned for this trade period.');
            candles.setData(data);
            volume.setData(data.map((candle) => ({
                time: candle.time,
                value: candle.volume || 0,
                color: candle.close >= candle.open ? 'rgba(32,230,164,.22)' : 'rgba(255,97,113,.22)',
            })));
            applyPricePrecision(data);
            markersPlugin.setMarkers(tradeMarkers(data));
            chart.timeScale().fitContent();
            if (source) source.textContent = `${result.source} · ${result.symbol} · ${result.interval}`;
            if (warning) {
                warning.textContent = result.warning
                    || (!trade.entryTime ? 'An entry time is required to place the entry marker on a candle.' : '')
                    || (!trade.exitTime && Number.isFinite(trade.exitPrice) ? 'Exit price is shown, but an exit time is required to place the exit marker on a candle.' : '');
            }
            setState('ready');
        } catch (error) {
            if (error.name !== 'AbortError') setState('error', error.message || 'The candles could not be loaded.');
        }
    }

    intervalSelect?.addEventListener('change', loadCandles);
    new MutationObserver(() => chart.applyOptions(themeOptions())).observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    loadCandles();
}
