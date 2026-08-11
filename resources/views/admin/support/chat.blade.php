@extends('layouts.admin')
@section('title','Chat with '.$user->name)
@section('content')
<style>
.chat-page{max-width:920px;margin:auto}.chat-title{display:flex;align-items:center;gap:12px}.chat-avatar{width:44px;height:44px;display:grid;place-items:center;border-radius:50%;color:#fff;background:linear-gradient(135deg,#ff4c00,#12a5ba);font-weight:900}.support-layout{height:calc(100vh - 190px);min-height:520px;display:grid;grid-template-rows:1fr auto;overflow:hidden}.chat-list{min-height:0;overflow-y:auto;display:grid;gap:10px;align-content:end;padding:20px}.chat-empty{margin:auto;color:var(--muted);text-align:center}.chat-message{max-width:76%;padding:10px 13px;border:1px solid var(--line);border-radius:17px}.chat-message.user{justify-self:start;border-bottom-left-radius:4px}.chat-message.admin{justify-self:end;border-bottom-right-radius:4px;background:rgba(25,199,181,.1)}.chat-message p{margin:0;white-space:pre-wrap;word-break:break-word}.chat-message time{display:block;margin-top:5px;color:var(--muted);font-size:9px;text-align:right}.chat-compose{display:grid;grid-template-columns:1fr auto;gap:10px;padding:14px;border-top:1px solid var(--line)}.chat-compose textarea{min-height:46px;max-height:130px;border-radius:14px;resize:none}.chat-send{width:46px;height:46px;padding:0;border-radius:50%;font-size:19px}@media(max-width:680px){.support-layout{height:calc(100vh - 170px)}.chat-message{max-width:90%}}
</style>
<div class="chat-page">
    <div class="page-head"><div class="chat-title"><div class="chat-avatar">{{ strtoupper(substr($user->name,0,1)) }}</div><div><a class="muted" href="{{ route('admin.users.index') }}">← Messages</a><h1>{{ $user->name }}</h1><p>{{ $user->email }}</p></div></div><a class="btn secondary" href="{{ route('admin.users.show',$user) }}">View profile</a></div>
    <section class="panel support-layout">
        <div class="chat-list">
            @if($ticket && $ticket->messages->isNotEmpty())
                @foreach($ticket->messages as $message)
                    <article class="chat-message {{ $message->sender_type }}"><p>{{ $message->body }}</p><time>{{ $message->created_at->format('d M, h:i A') }}</time></article>
                @endforeach
            @else
                <div class="chat-empty">No messages yet. Send the first message to {{ $user->name }}.</div>
            @endif
        </div>
        <form class="chat-compose" method="POST" action="{{ $ticket ? route('admin.support.reply',$ticket) : route('admin.users.chat.store',$user) }}">@csrf<textarea class="input" name="message" maxlength="5000" required aria-label="Message" placeholder="Type a message…">{{ old('message') }}</textarea><button class="btn chat-send" type="submit" aria-label="Send message">➤</button></form>
    </section>
</div>
@include('support._polling')
@include('support._messenger_ajax')
@endsection
