<h1>New contact message</h1>
<p><strong>From:</strong> {{ $contactMessage->name }} &lt;{{ $contactMessage->email }}&gt;</p>
<p><strong>Topic:</strong> {{ ucfirst($contactMessage->subject) }}</p>
<p><strong>Message:</strong></p>
<p>{!! nl2br(e($contactMessage->message)) !!}</p>
