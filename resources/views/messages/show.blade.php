@extends('layouts.app')
@section('page_title','Messages')
@section('page_subtitle','A private conversation with the TradeYatra team')
@section('content')
<style>
.messenger-wrap{max-width:1040px;margin:0 auto}.chat-shell{height:clamp(560px,calc(100vh - 205px),760px);display:grid;grid-template-rows:auto minmax(0,1fr) auto;overflow:hidden;border-color:color-mix(in srgb,var(--accent-2) 18%,var(--line));background:color-mix(in srgb,var(--panel) 97%,transparent);box-shadow:0 24px 70px rgba(1,8,13,.22)}
.chat-head{display:flex;align-items:center;gap:13px;padding:15px 18px;border-bottom:1px solid var(--line);background:color-mix(in srgb,var(--panel) 96%,transparent);backdrop-filter:blur(16px)}.chat-avatar{position:relative;width:46px;height:46px;flex:0 0 46px;display:grid;place-items:center;border:1px solid rgba(255,255,255,.18);border-radius:15px;color:#fff;background:linear-gradient(135deg,#ff7a1a,#e94b08 48%,#17b8c9);box-shadow:0 9px 24px rgba(255,122,26,.2);font-size:13px;font-weight:950;letter-spacing:.03em}.chat-avatar:after{content:"";position:absolute;right:-2px;bottom:-2px;width:11px;height:11px;border:3px solid var(--panel);border-radius:50%;background:#22c55e}.chat-contact{min-width:0}.chat-contact strong{display:block;color:var(--ink);font-size:15px}.chat-contact small{display:flex;align-items:center;gap:6px;margin-top:2px;color:var(--muted);font-size:10px}.chat-head-actions{margin-left:auto;display:flex;align-items:center;gap:7px}.chat-page-link,.chat-private{display:flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid var(--line);border-radius:999px;color:var(--muted);background:rgba(255,255,255,.025);font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}.chat-page-link:hover{color:var(--ink);border-color:color-mix(in srgb,var(--accent) 40%,var(--line));background:color-mix(in srgb,var(--accent) 7%,transparent)}.chat-page-link svg,.chat-private svg{width:13px;height:13px;fill:none;stroke:currentColor;stroke-width:2}
.ticket-layout{min-height:0;overflow-y:auto;padding:24px 22px;background:radial-gradient(circle at 12% 8%,rgba(255,122,26,.055),transparent 18rem),radial-gradient(circle at 92% 88%,rgba(24,199,255,.045),transparent 20rem);scrollbar-width:thin;scrollbar-color:color-mix(in srgb,var(--accent) 40%,transparent) transparent}.chat-list{display:grid;gap:11px;align-content:end;min-height:100%}.chat-day{justify-self:center;margin:4px 0;padding:5px 9px;border:1px solid var(--line);border-radius:999px;color:var(--muted);background:color-mix(in srgb,var(--panel) 92%,transparent);font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}.chat-empty{align-self:center;justify-self:center;max-width:390px;padding:28px;text-align:center}.chat-empty-icon{width:58px;height:58px;display:grid;place-items:center;margin:0 auto 14px;border:1px solid color-mix(in srgb,var(--accent) 28%,var(--line));border-radius:19px;color:var(--accent);background:color-mix(in srgb,var(--accent) 9%,transparent)}.chat-empty-icon svg{width:28px;height:28px;fill:none;stroke:currentColor;stroke-width:1.8}.chat-empty strong{display:block;margin-bottom:6px;color:var(--ink);font-size:18px}.chat-empty p{margin:0;color:var(--muted);font-size:12px;line-height:1.65}
.chat-message{position:relative;max-width:min(76%,620px);padding:10px 13px;border:1px solid var(--line);border-radius:17px;box-shadow:0 7px 20px rgba(0,0,0,.07)}.chat-message.admin{justify-self:start;border-bottom-left-radius:5px;background:color-mix(in srgb,var(--panel) 90%,var(--accent-2) 10%)}.chat-message.user{justify-self:end;border-color:transparent;border-bottom-right-radius:5px;color:#fff;background:linear-gradient(135deg,#e95c14,#d94a08 46%,#b9410e)}.chat-message p{margin:0;white-space:pre-wrap;word-break:break-word;font-size:13px;line-height:1.55}.chat-message time{display:block;margin-top:5px;color:inherit;opacity:.62;font-size:8px;text-align:right;letter-spacing:.02em}
.chat-compose-wrap{padding:12px 14px;border-top:1px solid var(--line);background:color-mix(in srgb,var(--panel) 97%,transparent)}.chat-compose{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:end;gap:9px;padding:6px 6px 6px 14px;border:1px solid var(--line);border-radius:19px;background:var(--field-bg);transition:border-color .18s ease,box-shadow .18s ease}.chat-compose:focus-within{border-color:color-mix(in srgb,var(--accent) 70%,var(--line));box-shadow:0 0 0 3px color-mix(in srgb,var(--accent) 10%,transparent)}.chat-compose textarea{width:100%;min-height:40px;max-height:120px;padding:10px 0;border:0;color:var(--ink);background:transparent;outline:0;resize:none;font:inherit;line-height:1.45}.chat-compose textarea::placeholder{color:var(--muted)}.chat-send{width:42px;height:42px;min-height:42px;padding:0;border-radius:14px;box-shadow:0 8px 22px rgba(255,122,26,.22)}.chat-send svg{width:19px;height:19px;fill:none;stroke:currentColor;stroke-width:2}.chat-send:disabled{opacity:.55;cursor:wait}.chat-hint{margin:7px 5px 0;color:var(--muted);font-size:9px;text-align:center}
@media(max-width:680px){.messenger-wrap{margin:-4px}.chat-shell{height:calc(100dvh - 150px);min-height:500px;border-radius:14px}.chat-head{padding:12px}.chat-private{display:none}.chat-page-link span{display:none}.chat-page-link{padding:8px}.ticket-layout{padding:17px 12px}.chat-message{max-width:88%;padding:9px 12px}.chat-compose-wrap{padding:9px}.chat-hint{display:none}}
</style>
<div class="messenger-wrap">
    <section class="panel chat-shell" aria-label="Chat with TradeYatra">
        <header class="chat-head">
            <div class="chat-avatar" aria-hidden="true">TY</div>
            <div class="chat-contact"><strong>TradeYatra Team</strong><small>Online support · We reply as soon as possible</small></div>
            <div class="chat-head-actions"><a class="chat-page-link" href="{{ route('messages.show') }}" title="Open full Messages page"><svg viewBox="0 0 24 24"><path d="M14 3h7v7"></path><path d="m10 14 11-11"></path><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"></path></svg><span>Open</span></a></div>
        </header>
        <div class="ticket-layout"><div class="chat-list">
            @if($ticket && $ticket->messages->isNotEmpty())
                @php($lastDate = null)
                @foreach($ticket->messages as $message)
                    @php($messageDate = $message->created_at->toDateString())
                    @if($messageDate !== $lastDate)<div class="chat-day">{{ $message->created_at->isToday() ? 'Today' : ($message->created_at->isYesterday() ? 'Yesterday' : $message->created_at->format('d M Y')) }}</div>@php($lastDate = $messageDate)@endif
                    <article class="chat-message {{ $message->sender_type }}"><p>{{ $message->body }}</p><time>{{ $message->created_at->format('h:i A') }}</time></article>
                @endforeach
            @else
                <div class="chat-empty"><div class="chat-empty-icon"><svg viewBox="0 0 24 24"><path d="M21 12a8 8 0 0 1-8 8H7l-4 2 1.3-4.4A8.5 8.5 0 1 1 21 12Z"></path></svg></div><strong>How can we help?</strong><p>Send a message to the TradeYatra team. Your private conversation and replies will remain available here.</p></div>
            @endif
        </div></div>
        <div class="chat-compose-wrap">
            <form class="chat-compose" method="POST" action="{{ $ticket ? route('support.reply',$ticket) : route('messages.store') }}">@csrf<textarea name="message" maxlength="5000" rows="1" required aria-label="Message" placeholder="Type your message…">{{ old('message') }}</textarea><button class="btn chat-send" type="submit" aria-label="Send message"><svg viewBox="0 0 24 24"><path d="m22 2-7 20-4-9-9-4Z"></path><path d="M22 2 11 13"></path></svg></button></form>
            <div class="chat-hint">Press Enter to send · Shift + Enter for a new line</div>
        </div>
    </section>
</div>
@include('support._polling')
@include('support._messenger_ajax')
@endsection
