@php
    $isSent = $message->sender_id === $currentUserId;
    $messageClass = $isSent ? 'sent' : 'received';
@endphp

<div class="message {{ $messageClass }}">
    <div class="message-avatar">
        {{ substr($message->sender->name, 0, 1) }}
    </div>
    <div class="message-content">
        <div>{{ $message->message }}</div>
        <div class="message-time">
            {{ $message->created_at->format('H:i') }}
            @if($isSent && $message->is_read)
                <i class="fa-solid fa-check-double text-blue-500 ml-1"></i>
            @elseif($isSent)
                <i class="fa-solid fa-check text-gray-400 ml-1"></i>
            @endif
        </div>
    </div>
</div>
