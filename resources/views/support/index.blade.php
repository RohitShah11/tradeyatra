@extends('layouts.app')
@section('page_title','Support Center')
@section('page_subtitle','Get help with your account, exchange connections, syncs, and trade data.')
@section('content')
<style>
    .support-head{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:16px}.support-filters{display:flex;gap:8px;flex-wrap:wrap}.support-filter{padding:8px 12px;border:1px solid var(--line);border-radius:999px;color:var(--muted);font-size:11px;font-weight:850}.support-filter.active{color:#fff;border-color:transparent;background:linear-gradient(115deg,#ff4c00,#bf4911 42%,#12a5ba)}.ticket-list{display:grid;gap:11px}.ticket-item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:15px;padding:17px;border:1px solid var(--line);border-radius:14px;background:var(--panel-bg);transition:.16s}.ticket-item:hover{transform:translateY(-2px);border-color:color-mix(in srgb,var(--accent) 35%,var(--line))}.ticket-title{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.ticket-title strong{font-size:14px}.ticket-meta{margin-top:6px;color:var(--muted);font-size:10px}.ticket-side{text-align:right}.ticket-status,.ticket-priority,.ticket-unread{display:inline-flex;padding:4px 8px;border-radius:999px;font-size:9px;font-weight:900;text-transform:uppercase}.ticket-status{color:var(--accent-2);background:color-mix(in srgb,var(--accent-2) 11%,transparent)}.ticket-priority{color:var(--muted);background:rgba(255,255,255,.045)}.ticket-priority.high,.ticket-priority.urgent{color:var(--bad);background:color-mix(in srgb,var(--bad) 10%,transparent)}.ticket-unread{color:#fff;background:var(--accent)}.support-empty{padding:45px;text-align:center}.support-empty p{color:var(--muted)}@media(max-width:640px){.support-head{align-items:stretch;flex-direction:column}.ticket-item{grid-template-columns:1fr}.ticket-side{text-align:left}}
</style>
<div id="supportApp" data-page-title="Support Center" data-page-subtitle="Get help with your account, exchange connections, syncs, and trade data.">
<div class="support-head"><div class="support-filters">@foreach([''=>'All','open'=>'Open','waiting_on_support'=>'Waiting for support','waiting_on_user'=>'Waiting for you','resolved'=>'Resolved'] as $value=>$label)<a class="support-filter {{ $status===$value?'active':'' }}" href="{{ route('support.index',array_filter(['status'=>$value])) }}">{{ $label }}</a>@endforeach</div><a class="btn" href="{{ route('support.create') }}">New support ticket</a></div>
<div class="ticket-list">
@forelse($tickets as $ticket)
<a class="ticket-item" href="{{ route('support.show',$ticket) }}"><div><div class="ticket-title"><strong>{{ $ticket->subject }}</strong>@if($ticket->user_unread_count)<span class="ticket-unread">{{ $ticket->user_unread_count }} new</span>@endif</div><div class="ticket-meta">{{ $ticket->ticket_number }} · {{ str_replace('_',' ',$ticket->category) }} · Updated {{ optional($ticket->last_replied_at)->diffForHumans() }}</div></div><div class="ticket-side"><span class="ticket-status">{{ str_replace('_',' ',$ticket->status) }}</span> <span class="ticket-priority {{ $ticket->priority }}">{{ $ticket->priority }}</span></div></a>
@empty
<div class="panel support-empty"><h2>No support tickets</h2><p>Create a ticket whenever you need help. Your full conversation will stay available here.</p><a class="btn" href="{{ route('support.create') }}">Ask for help</a></div>
@endforelse
</div>
<div style="margin-top:18px">{{ $tickets->links() }}</div>
</div>
@include('support._ajax')
@endsection
