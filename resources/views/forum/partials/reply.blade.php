{{-- resources/views/forum/partials/reply.blade.php --}}
<div class="px-6 py-4 {{ $depth > 0 ? 'pl-' . ($depth * 4 + 6) : '' }} hover:bg-gray-50 transition-colors" 
     id="reply-{{ $reply->id }}">
    <!-- Reply Header -->
    <div class="flex items-start justify-between mb-3">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-600">
                {{ strtoupper(substr($reply->user->name, 0, 2)) }}
            </div>
            <div>
                <span class="text-sm font-medium text-gray-900">{{ $reply->user->name }}</span>
                <span class="text-xs text-gray-500 ml-2">{{ $reply->created_at->diffForHumans() }}</span>
                @if($reply->is_answer)
                    <span class="inline-flex items-center ml-2 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                        <i class="fa-solid fa-check mr-1"></i>
                        Solution
                    </span>
                @endif
            </div>
        </div>
        
        <div class="flex items-center space-x-2">
            @if(auth()->id() === $discussion->user_id && !$discussion->is_locked && !$reply->is_answer)
                <form action="{{ route('forum.replies.mark-answer', $reply) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="text-xs text-green-600 hover:text-green-800 font-medium">
                        Mark as Solution
                    </button>
                </form>
                <span class="text-gray-300">•</span>
            @endif
            
            @if(!$discussion->is_locked)
                <button onclick="replyTo({{ $reply->id }}, '{{ $reply->user->name }}')" 
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Reply
                </button>
            @endif
        </div>
    </div>
    
    <!-- Reply Content -->
    <div class="prose prose-sm max-w-none mb-3">
        {!! nl2br(e($reply->content)) !!}
    </div>
    
    <!-- Reply Actions -->
    <div class="flex items-center space-x-4 text-sm text-gray-500">
        <div class="flex items-center space-x-1">
            <button onclick="vote('reply', {{ $reply->id }}, 'up')" 
                    class="p-1 rounded hover:bg-gray-100 {{ $reply->userVote(auth()->user())?->type === 'up' ? 'text-green-600' : 'text-gray-400' }}">
                <i class="fa-solid fa-arrow-up text-xs"></i>
            </button>
            <span id="vote-score-{{ $reply->id }}" class="font-semibold text-gray-700 text-xs">
                {{ $reply->voteScore() }}
            </span>
            <button onclick="vote('reply', {{ $reply->id }}, 'down')" 
                    class="p-1 rounded hover:bg-gray-100 {{ $reply->userVote(auth()->user())?->type === 'down' ? 'text-red-600' : 'text-gray-400' }}">
                <i class="fa-solid fa-arrow-down text-xs"></i>
            </button>
        </div>
        
        @if($reply->hasReplies())
            <button class="flex items-center space-x-1 text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-comments text-xs"></i>
                <span class="text-xs">{{ $reply->replies->count() }} {{ Str::plural('reply', $reply->replies->count()) }}</span>
            </button>
        @endif
    </div>
    
    <!-- Nested Replies -->
    @if($reply->replies->count() > 0)
        <div class="mt-4 space-y-4 border-l-2 border-gray-100 ml-4 pl-4">
            @foreach($reply->replies as $nestedReply)
                @include('forum.partials.reply', ['reply' => $nestedReply, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>