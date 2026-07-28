@extends('layouts.app')

@section('page_title', 'Trading Calendar')
@section('page_subtitle', 'Review your trading rhythm, daily results, and monthly performance.')

@section('content')
@php($previousMonth = $calendarMonth->copy()->subMonth()->format('Y-m'))
@php($nextMonth = $calendarMonth->copy()->addMonth()->format('Y-m'))
@php($calendarQuery = fn ($month) => array_filter(['broker' => request('broker'), 'calendar_month' => $month]))
@php($winningDays = collect($calendarDays)->where('is_current_month', true)->where('net', '>', 0)->count())
@php($losingDays = collect($calendarDays)->where('is_current_month', true)->where('net', '<', 0)->count())
@php($tradingDays = collect($calendarDays)->where('is_current_month', true)->where('trades', '>', 0)->count())

<style>
    .calendar-shell { position:relative; overflow: hidden; padding: 0; }
    #calendarPageContent { position:relative; }
    #calendarPageContent.is-loading { min-height:280px; pointer-events:none; }
    #calendarPageContent.is-loading:before { content:""; position:absolute; z-index:20; inset:0; border-radius:16px; background:rgba(2,8,11,.62); backdrop-filter:blur(2px); }
    #calendarPageContent.is-loading:after { content:""; position:absolute; z-index:21; top:50%; left:50%; width:42px; height:42px; margin:-21px 0 0 -21px; border-radius:50%; background:conic-gradient(#ff7a1a 0 38%,#18c7ff 50% 82%,rgba(255,255,255,.12) 90% 100%); -webkit-mask:radial-gradient(farthest-side,transparent calc(100% - 5px),#000 0); mask:radial-gradient(farthest-side,transparent calc(100% - 5px),#000 0); filter:drop-shadow(0 0 11px rgba(24,199,255,.35)); animation:calendar-spin .7s linear infinite; }
    html[data-theme="light"] #calendarPageContent.is-loading:before { background:rgba(255,255,255,.7); }
    @keyframes calendar-spin { to { transform:rotate(360deg); } }
    .calendar-ajax-loader { position:fixed; z-index:85; top:82px; left:50%; display:flex; align-items:center; gap:10px; padding:10px 15px; border:1px solid color-mix(in srgb,var(--accent) 35%,var(--line)); border-radius:999px; color:var(--ink); background:color-mix(in srgb,var(--panel) 95%,transparent); box-shadow:0 14px 38px rgba(0,0,0,.25); opacity:0; visibility:hidden; transform:translate(-50%,-7px); transition:.15s; pointer-events:none; backdrop-filter:blur(12px); }
    .calendar-ajax-loader.active { opacity:1; visibility:visible; transform:translate(-50%,0); }
    .calendar-ajax-loader-spinner { width:17px; height:17px; border:2px solid color-mix(in srgb,var(--accent) 22%,transparent); border-top-color:var(--accent); border-right-color:var(--accent-2); border-radius:50%; animation:calendar-spin .65s linear infinite; }
    .calendar-hero { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 22px 24px; border-bottom: 1px solid var(--line); background: radial-gradient(circle at 5% 0%, rgba(255,139,31,.15), transparent 38%), radial-gradient(circle at 95% 10%, rgba(0,190,230,.12), transparent 34%); }
    .calendar-title-row { display: flex; align-items: center; gap: 14px; }
    .calendar-icon { width: 48px; height: 48px; display: grid; place-items: center; border-radius: 14px; color: #07131d; background: linear-gradient(135deg, #ff9d2e, #ff7418); box-shadow: 0 10px 30px rgba(255,126,24,.22); }
    .calendar-icon svg { width: 24px; height: 24px; }
    .calendar-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 24px; }
    .calendar-nav { display: flex; align-items: center; gap: 8px; }
    .calendar-nav .btn { min-width: 42px; padding-inline: 12px; }
    .month-form { display: flex; align-items: end; gap: 8px; }
    .month-form label { font-size: 11px; }
    .month-form input { min-width: 150px; }
    .calendar-scroll { overflow-x: auto; padding: 0 24px 24px; scrollbar-color: rgba(24,199,255,.45) transparent; }
    .calendar-grid { min-width: 790px; display: grid; grid-template-columns: repeat(7, minmax(104px, 1fr)); gap: 8px; }
    .calendar-weekday { color: var(--muted); font-size: 11px; font-weight: 900; text-align: center; text-transform: uppercase; letter-spacing: .1em; padding: 7px 4px; }
    .calendar-day {
        min-height: 112px;
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 12px;
        background: rgba(255,255,255,.045);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 10px;
    }
    .calendar-day.dim { opacity: .35; }
    .calendar-day.profit { border-color: rgba(0,230,168,.42); background: linear-gradient(145deg, rgba(0,230,168,.15), rgba(0,230,168,.035)); }
    .calendar-day.loss { border-color: rgba(255,59,79,.46); background: linear-gradient(145deg, rgba(255,59,79,.16), rgba(255,59,79,.035)); }
    .calendar-day.flat { border-color: rgba(255,156,54,.28); background: rgba(255,156,54,.06); }
    .calendar-day.clickable { cursor: pointer; text-align: left; color: inherit; font: inherit; }
    .calendar-day.clickable:hover, .calendar-day.clickable:focus {
        border-color: rgba(24,199,255,.58);
        box-shadow: 0 0 0 3px rgba(24,199,255,.12);
        outline: none;
        transform: translateY(-1px);
    }
    .day-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .day-number { width: 28px; height: 28px; display: grid; place-items: center; border-radius: 8px; font-size: 13px; font-weight: 900; background: rgba(255,255,255,.06); }
    .day-count { color: var(--muted); font-size: 10px; font-weight: 800; text-transform: uppercase; }
    .day-net { display: block; font-size: 15px; font-weight: 900; }
    .day-meta { color: var(--muted); font-size: 12px; }
    .calendar-stats { display: grid; grid-template-columns: 1.35fr repeat(4, 1fr); gap: 10px; margin: 0 24px 18px; }
    .calendar-stat { min-height: 84px; padding: 14px 16px; border: 1px solid var(--line); border-radius: 12px; background: rgba(255,255,255,.035); }
    .calendar-stat .value { display: block; margin-top: 7px; font-size: 20px; font-weight: 900; }
    .calendar-legend { display: flex; gap: 14px; flex-wrap: wrap; color: var(--muted); font-size: 12px; }
    .legend-dot { width: 8px; height: 8px; display: inline-block; border-radius: 50%; margin-right: 5px; }
    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 40;
        background: rgba(3, 10, 18, .78);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-backdrop.open { display: flex; }
    .trade-modal {
        width: min(980px, 100%);
        max-height: min(760px, calc(100vh - 40px));
        overflow: auto;
        border: 1px solid var(--line);
        border-radius: 8px;
        background: linear-gradient(180deg, rgba(16,31,45,.98), rgba(8,18,30,.98));
        box-shadow: 0 30px 90px rgba(0,0,0,.42);
    }
    .modal-header {
        position: sticky;
        top: 0;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 18px;
        border-bottom: 1px solid var(--line);
        background: rgba(8,18,30,.96);
    }
    .modal-body { padding: 18px; }
    .modal-close { min-width: 42px; padding-inline: 12px; }
    .trade-list { display: grid; gap: 12px; }
    .trade-item {
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: 14px;
        background: rgba(255,255,255,.045);
    }
    .trade-item-head { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
    .trade-meta { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; color: var(--muted); font-size: 12px; }
    .trade-meta strong { display: block; color: var(--ink); font-size: 14px; margin-top: 3px; }
    .trade-notes { margin-top: 10px; color: var(--muted); }
    .trade-notes:empty { display: none; }
    .trade-note-form { margin-top: 12px; display: grid; gap: 8px; }
    .trade-note-form textarea { min-height: 88px; }
    .trade-note-form .actions { justify-content: flex-end; }
    .trade-images { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
    .trade-images:empty { display:none; }
    .trade-image { width:86px; height:64px; display:block; overflow:hidden; border:1px solid var(--line); border-radius:8px; background:rgba(255,255,255,.04); }
    .trade-image img { width:100%; height:100%; object-fit:cover; }
    .note-upload { padding:10px 12px; border:1px dashed var(--line); border-radius:8px; background:rgba(255,255,255,.025); }
    .note-upload input { min-height:auto; padding:0; border:0; background:transparent; }
    .note-upload small { display:block; margin-top:5px; color:var(--muted); }
    @media (max-width: 900px) {
        .calendar-hero, .calendar-head { align-items: flex-start; flex-direction: column; }
        .calendar-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .calendar-stat:first-child { grid-column: span 2; }
        .trade-meta { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 560px) {
        .calendar-hero, .calendar-head { padding-inline: 16px; }
        .calendar-stats { margin-inline: 16px; }
        .calendar-scroll { padding-inline: 16px; }
        .month-form { width: 100%; }
        .month-form > div { flex: 1; }
        .month-form input { width: 100%; min-width: 0; }
    }
</style>

<div class="calendar-ajax-loader" id="calendarAjaxLoader" role="status" aria-live="polite" aria-hidden="true"><span class="calendar-ajax-loader-spinner"></span><strong>Updating calendar…</strong></div>
<div class="grid" id="calendarPageContent" aria-live="polite">
    <script type="application/json" id="calendarTradeDetailsData">@json($calendarTradeDetails)</script>
    @include('trades._exchange_tabs', ['routeName' => 'trades.calendar'])

    <div class="panel calendar-shell">
        <div class="calendar-hero">
            <div class="calendar-title-row">
                <span class="calendar-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg></span>
                <div><h2>{{ $calendarMonth->format('F Y') }}</h2><div class="muted">Your daily trading performance at a glance.</div></div>
            </div>
            <div class="calendar-legend"><span><i class="legend-dot" style="background:#00e6a8"></i>Profit</span><span><i class="legend-dot" style="background:#ff3b4f"></i>Loss</span><span><i class="legend-dot" style="background:#ff9d2e"></i>Breakeven</span></div>
        </div>

        <div class="calendar-head">
            <div>
                <div class="label">Selected month</div>
                <strong style="font-size:18px">{{ $calendarMonth->format('F Y') }}</strong>
            </div>
            <div class="calendar-nav">
                <a class="btn secondary" aria-label="Previous month" href="{{ route('trades.calendar', $calendarQuery($previousMonth)) }}">&#8592;</a>
                <a class="btn secondary" href="{{ route('trades.calendar', $calendarQuery(now()->format('Y-m'))) }}">Today</a>
                <a class="btn secondary" aria-label="Next month" href="{{ route('trades.calendar', $calendarQuery($nextMonth)) }}">&#8594;</a>
            </div>
            <form method="GET" action="{{ route('trades.calendar') }}" class="month-form">
                @if(request('broker'))<input type="hidden" name="broker" value="{{ request('broker') }}">@endif
                <div><label for="calendar_month">Jump to month</label><input id="calendar_month" type="month" name="calendar_month" value="{{ $calendarMonth->format('Y-m') }}"></div>
                <button class="btn" type="submit">View</button>
            </form>
        </div>

        <div class="calendar-stats">
            <div class="calendar-stat"><span class="label">Monthly net</span>@forelse($currencyStats as $row)<span class="value {{ $row['net'] >= 0 ? 'positive' : 'negative' }}">{{ $row['currency'] }} {{ number_format($row['net'], 2) }}</span>@empty<span class="value">—</span>@endforelse</div>
            <div class="calendar-stat"><span class="label">Trades</span><span class="value">{{ $calendarStats['total'] }}</span></div>
            <div class="calendar-stat"><span class="label">Win rate</span><span class="value">{{ number_format($calendarStats['win_rate'], 1) }}%</span></div>
            <div class="calendar-stat"><span class="label">Trading days</span><span class="value">{{ $tradingDays }}</span></div>
            <div class="calendar-stat"><span class="label">Green / red</span><span class="value"><span class="positive">{{ $winningDays }}</span> / <span class="negative">{{ $losingDays }}</span></span></div>
        </div>

        <div class="calendar-scroll"><div class="calendar-grid">
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                <div class="calendar-weekday">{{ $day }}</div>
            @endforeach
            @foreach($calendarDays as $day)
                @php($dayClass = $day['trades'] === 0 ? '' : ($day['net'] > 0 ? 'profit' : ($day['net'] < 0 ? 'loss' : 'flat')))
                @if($day['trades'] > 0)
                    <button type="button" class="calendar-day clickable {{ $day['is_current_month'] ? '' : 'dim' }} {{ $dayClass }}" data-date="{{ $day['date'] }}" data-label="{{ \Carbon\Carbon::parse($day['date'])->format('d M Y') }}">
                        <span class="day-top"><span class="day-number">{{ $day['day'] }}</span><span class="day-count">{{ $day['trades'] }} {{ $day['trades'] === 1 ? 'trade' : 'trades' }}</span></span>
                        <span>
                            <span class="day-net {{ $day['net'] >= 0 ? 'positive' : 'negative' }}">{{ $displayCurrency }} {{ number_format($day['net'], 2) }}</span>
                            <span class="day-meta">{{ number_format($day['win_rate'], 0) }}% win rate</span>
                        </span>
                    </button>
                @else
                    <div class="calendar-day {{ $day['is_current_month'] ? '' : 'dim' }} {{ $dayClass }}">
                        <div class="day-top"><div class="day-number">{{ $day['day'] }}</div></div>
                        <div class="day-meta">No activity</div>
                    </div>
                @endif
            @endforeach
        </div></div>
    </div>
</div>

<div class="modal-backdrop" id="tradeModalBackdrop" aria-hidden="true">
    <section class="trade-modal" role="dialog" aria-modal="true" aria-labelledby="tradeModalTitle">
        <div class="modal-header">
            <div>
                <h2 id="tradeModalTitle">Trades</h2>
                <div class="muted" id="tradeModalSummary"></div>
            </div>
            <button type="button" class="btn secondary modal-close" id="tradeModalClose" aria-label="Close trade details">Close</button>
        </div>
        <div class="modal-body">
            <div class="trade-list" id="tradeModalList"></div>
        </div>
    </section>
</div>

<script>
(() => {
function readCalendarTradeDetails() {
    const data = document.getElementById('calendarTradeDetailsData');
    if (!data) return {};
    try { return JSON.parse(data.textContent); } catch (error) { return {}; }
}

let calendarTradeDetails = readCalendarTradeDetails();
let calendarRequestController = null;
const calendarPageCache = new Map();
const tradeModalBackdrop = document.getElementById('tradeModalBackdrop');
const tradeModalTitle = document.getElementById('tradeModalTitle');
const tradeModalSummary = document.getElementById('tradeModalSummary');
const tradeModalList = document.getElementById('tradeModalList');
const tradeModalClose = document.getElementById('tradeModalClose');
const calendarAjaxLoader = document.getElementById('calendarAjaxLoader');
const csrfToken = '{{ csrf_token() }}';

function money(trade) {
    const value = Number(trade.net || 0);
    return `${trade.currency} ${value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));
}

function renderTradeImages(images = []) {
    return images.map((image) => `<a class="trade-image" href="${escapeHtml(image.url)}" target="_blank" rel="noopener"><img src="${escapeHtml(image.url)}" alt="Trade screenshot" loading="lazy"></a>`).join('');
}

function showToast(type, title, message) {
    let viewport = document.querySelector('.toast-viewport');

    if (! viewport) {
        viewport = document.createElement('div');
        viewport.className = 'toast-viewport';
        viewport.setAttribute('aria-live', 'polite');
        viewport.setAttribute('aria-atomic', 'true');
        document.body.prepend(viewport);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.setAttribute('role', 'status');
    toast.innerHTML = `
        <strong class="toast-title">${escapeHtml(title)}</strong>
        <div class="toast-message">${escapeHtml(message)}</div>
        <button class="toast-close" type="button" aria-label="Dismiss message">x</button>
    `;
    toast.querySelector('.toast-close').addEventListener('click', () => toast.remove());
    viewport.appendChild(toast);
    setTimeout(() => toast.remove(), 6000);
}

function openTradeModal(date, label) {
    const trades = calendarTradeDetails[date] || [];
    const net = trades.reduce((total, trade) => total + Number(trade.net || 0), 0);

    tradeModalTitle.textContent = `${label} Trades`;
    tradeModalSummary.textContent = `${trades.length} trade${trades.length === 1 ? '' : 's'} | ${trades[0]?.currency || '{{ $displayCurrency }}'} ${net.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} net`;
    tradeModalList.innerHTML = trades.map((trade) => `
        <article class="trade-item">
            <div class="trade-item-head">
                <div>
                    <strong>${escapeHtml(trade.pair)}</strong>
                    <div class="muted">${escapeHtml(trade.time)} | ${escapeHtml(trade.broker)} | ${escapeHtml(trade.asset_class)} | ${escapeHtml(trade.market_segment)}</div>
                </div>
                <div class="${Number(trade.net) >= 0 ? 'positive' : 'negative'}"><strong>${money(trade)}</strong></div>
            </div>
            <div class="trade-meta">
                <span>Side<strong>${escapeHtml(trade.side)}</strong></span>
                <span>Status<strong>${escapeHtml(trade.status)}</strong></span>
                <span>Qty<strong>${escapeHtml(trade.quantity)}</strong></span>
                <span>Strategy<strong>${escapeHtml(trade.strategy)}</strong></span>
                <span>Entry<strong>${escapeHtml(trade.entry)}</strong></span>
                <span>Exit<strong>${escapeHtml(trade.exit)}</strong></span>
                <span>Plan Followed<strong>${escapeHtml(trade.plan_followed)}</strong></span>
                <span><a class="btn secondary" href="${trade.edit_url}">Edit Trade</a></span>
            </div>
            <div class="trade-notes">${escapeHtml(trade.notes)}</div>
            <div class="trade-images">${renderTradeImages(trade.screenshots)}</div>
            <form class="trade-note-form" method="POST" enctype="multipart/form-data" action="${trade.notes_url}" data-trade-id="${trade.id}">
                <input type="hidden" name="_token" value="${csrfToken}">
                <label>Write Note</label>
                <textarea name="notes" maxlength="10000" placeholder="Write a new trade note here..."></textarea>
                <label class="note-upload">Add screenshots
                    <input type="file" name="screenshot[]" accept="image/jpeg,image/png,image/webp" multiple>
                    <small>JPG, PNG or WebP. Up to 6 images, 4 MB each.</small>
                </label>
                <div class="actions">
                    <button class="btn" type="submit">Save Note</button>
                </div>
            </form>
        </article>
    `).join('');

    tradeModalBackdrop.classList.add('open');
    tradeModalBackdrop.setAttribute('aria-hidden', 'false');
    tradeModalClose.focus();
}

tradeModalList.addEventListener('submit', async (event) => {
    const form = event.target.closest('.trade-note-form');

    if (! form) {
        return;
    }

    event.preventDefault();

    const button = form.querySelector('button[type="submit"]');
    const textarea = form.querySelector('textarea[name="notes"]');
    const originalButtonText = button.textContent;

    button.disabled = true;
    button.textContent = 'Saving...';

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
        });
        const payload = await response.json().catch(() => ({}));

        if (! response.ok) {
            throw new Error(payload.message || 'Could not save the trade note.');
        }

        const notes = payload.notes ?? '';
        const tradeItem = form.closest('.trade-item');
        const notesBlock = tradeItem.querySelector('.trade-notes');
        const imagesBlock = tradeItem.querySelector('.trade-images');
        notesBlock.textContent = notes;
        imagesBlock.innerHTML = renderTradeImages(payload.screenshots || []);

        Object.values(calendarTradeDetails).flat().forEach((trade) => {
            if (String(trade.id) === String(form.dataset.tradeId)) {
                trade.notes = notes;
                trade.screenshots = payload.screenshots || [];
            }
        });

        textarea.value = '';
        form.querySelector('input[type="file"]').value = '';
        showToast('success', 'Success', payload.message || 'Trade note saved.');
    } catch (error) {
        showToast('error', 'Error', error.message || 'Could not save the trade note.');
    } finally {
        button.disabled = false;
        button.textContent = originalButtonText;
    }
});

function closeTradeModal() {
    tradeModalBackdrop.classList.remove('open');
    tradeModalBackdrop.setAttribute('aria-hidden', 'true');
}

function requestCalendarPage(url, signal = null) {
    const key = new URL(url, window.location.href).toString();
    if (calendarPageCache.has(key)) return calendarPageCache.get(key);

    const request = fetch(key, {
        headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
        signal,
    }).then((response) => {
        if (!response.ok) throw new Error('The calendar could not be loaded.');
        return response.text();
    }).catch((error) => {
        calendarPageCache.delete(key);
        throw error;
    });

    calendarPageCache.set(key, request);
    return request;
}

function prefetchCalendarLinks() {
    const links = [...document.querySelectorAll('#calendarPageContent .calendar-nav a[href]')];
    const prefetch = () => links.forEach((link) => requestCalendarPage(link.href).catch(() => {}));
    if ('requestIdleCallback' in window) window.requestIdleCallback(prefetch, { timeout: 1200 });
    else setTimeout(prefetch, 250);
}

async function loadCalendarPage(url, updateHistory = true) {
    const page = document.getElementById('calendarPageContent');
    if (!page) return;

    calendarRequestController?.abort();
    const requestController = new AbortController();
    calendarRequestController = requestController;
    const loadingStartedAt = performance.now();
    page.classList.add('is-loading');
    page.setAttribute('aria-busy', 'true');
    calendarAjaxLoader?.classList.add('active');
    calendarAjaxLoader?.setAttribute('aria-hidden', 'false');

    try {
        await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        const html = await requestCalendarPage(url, requestController.signal);
        const documentCopy = new DOMParser().parseFromString(html, 'text/html');
        const nextPage = documentCopy.getElementById('calendarPageContent');
        if (!nextPage) throw new Error('The calendar response was incomplete.');

        page.innerHTML = nextPage.innerHTML;
        calendarTradeDetails = readCalendarTradeDetails();
        document.title = documentCopy.title || document.title;
        if (updateHistory) history.pushState({ calendar: true }, '', url);
        prefetchCalendarLinks();
    } catch (error) {
        if (error.name === 'AbortError') return;
        window.showAppToast?.('error', 'Calendar error', error.message || 'The calendar could not be loaded.');
    } finally {
        if (calendarRequestController === requestController) {
            const remainingLoaderTime = Math.max(0, 450 - (performance.now() - loadingStartedAt));
            if (remainingLoaderTime) await new Promise((resolve) => setTimeout(resolve, remainingLoaderTime));
            page.classList.remove('is-loading');
            page.removeAttribute('aria-busy');
            calendarAjaxLoader?.classList.remove('active');
            calendarAjaxLoader?.setAttribute('aria-hidden', 'true');
        }
    }
}

document.addEventListener('click', (event) => {
    const day = event.target.closest('.calendar-day.clickable');
    if (day) {
        openTradeModal(day.dataset.date, day.dataset.label);
        return;
    }

    const link = event.target.closest('#calendarPageContent a[href]');
    if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const url = new URL(link.href, window.location.href);
    if (url.origin !== window.location.origin || !url.pathname.endsWith('/calendar')) return;
    event.preventDefault();
    loadCalendarPage(url.toString());
}, { signal: window.tradeYatraNavigationSignal });

document.addEventListener('submit', (event) => {
    const form = event.target.closest('#calendarPageContent .month-form');
    if (!form) return;
    event.preventDefault();
    const url = new URL(form.action, window.location.href);
    new FormData(form).forEach((value, key) => url.searchParams.set(key, value));
    loadCalendarPage(url.toString());
}, { signal: window.tradeYatraNavigationSignal });

window.addEventListener('popstate', () => {
    if (window.location.pathname.endsWith('/calendar')) loadCalendarPage(window.location.href, false);
}, { signal: window.tradeYatraNavigationSignal });

prefetchCalendarLinks();

tradeModalClose.addEventListener('click', closeTradeModal);
tradeModalBackdrop.addEventListener('click', (event) => {
    if (event.target === tradeModalBackdrop) {
        closeTradeModal();
    }
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && tradeModalBackdrop.classList.contains('open')) {
        closeTradeModal();
    }
}, { signal: window.tradeYatraNavigationSignal });
})();
</script>
@endsection
