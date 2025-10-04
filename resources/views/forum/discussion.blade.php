{{-- resources/views/forum/discussion.blade.php --}}
@extends('layouts.app')

@section('title', $discussion->title . ' - ReCircle Forum')

@section('content')
<div class="container mx-auto px-4 py-8" style="padding-top: 100px; padding-bottom: 100px;">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li>
                <a href="{{ route('forum.index') }}" class="hover:text-gray-700">Forum</a>
            </li>
            <li>
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </li>
            <li>
                <a href="{{ route('forum.category', $category) }}" class="hover:text-gray-700">{{ $category->name }}</a>
            </li>
            <li>
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </li>
            <li class="text-gray-900 font-medium">
                {{ Str::limit($discussion->title, 50) }}
            </li>
        </ol>
    </nav>

    <!-- Discussion Header -->
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="px-6 py-4 border-b">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                        @if($discussion->is_pinned)
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fa-solid fa-thumbtack mr-1"></i>
                                Pinned
                            </span>
                        @endif
                        @if($discussion->is_locked)
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                <i class="fa-solid fa-lock mr-1"></i>
                                Locked
                            </span>
                        @endif
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                              style="background-color: {{ $category->color }}20; color: {{ $category->color }};">
                            {{ $category->name }}
                        </span>
                    </div>
                    
                    <h1 class="text-2xl font-bold text-gray-900 mb-3">{{ $discussion->title }}</h1>
                    
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-600 mr-2">
                                {{ strtoupper(substr($discussion->user->name, 0, 2)) }}
                            </div>
                            <span>{{ $discussion->user->name }}</span>
                        </div>
                        <span>•</span>
                        <span class="flex items-center">
                            <i class="fa-solid fa-clock mr-1"></i>
                            {{ $discussion->created_at->diffForHumans() }}
                        </span>
                        <span>•</span>
                        <span class="flex items-center">
                            <i class="fa-solid fa-eye mr-1"></i>
                            {{ $discussion->view_count }} views
                        </span>
                        <span>•</span>
                        <span class="flex items-center">
                            <i class="fa-solid fa-comment mr-1"></i>
                            {{ $discussion->reply_count }} replies
                        </span>
                    </div>
                </div>
                
                @if(auth()->id() === $discussion->user_id)
                    <div class="flex-shrink-0 ml-4">
                        <button class="text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Discussion Content -->
        <div class="px-6 py-6">
            <div class="prose max-w-none">
                {!! nl2br(e($discussion->content)) !!}
            </div>
            
            <!-- Voting for Discussion -->
            <div class="flex items-center space-x-4 mt-6 pt-6 border-t">
                <div class="flex items-center space-x-2">
                    <button onclick="vote('discussion', {{ $discussion->id }}, 'up')" 
                            class="p-2 rounded hover:bg-gray-100 {{ $discussion->userVote(auth()->user())?->type === 'up' ? 'text-green-600' : 'text-gray-400' }}">
                        <i class="fa-solid fa-arrow-up"></i>
                    </button>
                    <span id="vote-score-{{ $discussion->id }}" class="font-semibold text-gray-700">
                        {{ $discussion->voteScore() }}
                    </span>
                    <button onclick="vote('discussion', {{ $discussion->id }}, 'down')" 
                            class="p-2 rounded hover:bg-gray-100 {{ $discussion->userVote(auth()->user())?->type === 'down' ? 'text-red-600' : 'text-gray-400' }}">
                        <i class="fa-solid fa-arrow-down"></i>
                    </button>
                </div>
                
                <button class="flex items-center space-x-1 text-gray-500 hover:text-gray-700">
                    <i class="fa-regular fa-bookmark"></i>
                    <span>Save</span>
                </button>
                
                <button class="flex items-center space-x-1 text-gray-500 hover:text-gray-700">
                    <i class="fa-regular fa-flag"></i>
                    <span>Report</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Replies Section -->
    <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-gray-900">
                {{ $discussion->reply_count }} {{ Str::plural('Reply', $discussion->reply_count) }}
            </h2>
        </div>
        
        @if($replies->count() > 0)
            <div class="divide-y">
                @foreach($replies as $reply)
                    @include('forum.partials.reply', ['reply' => $reply, 'depth' => 0])
                @endforeach
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <i class="fa-solid fa-comments text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No replies yet</h3>
                <p class="text-gray-500">Be the first to reply to this discussion!</p>
            </div>
        @endif
    </div>

    <!-- Reply Form -->
    @if(!$discussion->is_locked)
        <div class="bg-white rounded-lg shadow-sm border" id="reply-form-section">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Post Your Reply</h3>
            </div>
            
            <form action="{{ route('forum.replies.store', $discussion) }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="parent_id" id="parent_id" value="">
                
                <div class="mb-4">
                    <label for="reply-content" class="block text-sm font-medium text-gray-700 mb-2">Your Reply</label>
                    <textarea name="content" id="reply-content" rows="6" required
                              placeholder="Share your thoughts, answer questions, or provide feedback..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Use Markdown for formatting
                    </div>
<button type="submit" class="btn btn-secondary">
    Post Reply
</button>

                </div>
            </form>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <i class="fa-solid fa-lock text-yellow-500 text-2xl mb-3"></i>
            <h3 class="text-lg font-medium text-yellow-800 mb-2">Discussion Locked</h3>
            <p class="text-yellow-700">This discussion has been locked and no longer accepts new replies.</p>
        </div>
    @endif
</div>

<!-- Voting Script -->
<script>
async function vote(type, id, voteType) {
    try {
        const response = await fetch('{{ route("forum.vote") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                votable_type: type,
                votable_id: id,
                type: voteType
            })
        });

        const data = await response.json();
        
        if (data.success) {
            // Update vote score
            document.getElementById(`vote-score-${id}`).textContent = data.vote_score;
            
            // Update button styles
            const buttons = document.querySelectorAll(`[onclick*="${id}"]`);
            buttons.forEach(btn => {
                btn.classList.remove('text-green-600', 'text-red-600');
                if (btn.onclick.toString().includes(voteType)) {
                    btn.classList.add(voteType === 'up' ? 'text-green-600' : 'text-red-600');
                }
            });
        }
    } catch (error) {
        console.error('Error voting:', error);
    }
}

// Function to reply to a specific comment
function replyTo(replyId, userName) {
    document.getElementById('parent_id').value = replyId;
    document.getElementById('reply-content').focus();
    document.getElementById('reply-content').placeholder = `Replying to ${userName}...`;
    
    // Scroll to reply form
    document.getElementById('reply-form-section').scrollIntoView({ 
        behavior: 'smooth' 
    });
}
</script>
@endsection