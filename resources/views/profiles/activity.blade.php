{{-- resources/views/profiles/activity.blade.php --}}
@extends('layouts.app')

@section('title', $user->name . "'s Activity - ReCircle")

@section('content')
<div class="min-h-screen" style="background-color: #1a202c;">
    <div class="container mx-auto px-4 pt-20" style="padding-top: 100px; padding-bottom: 100px;">
        <!-- Header -->
        <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-6 mb-8 transition-all duration-300 hover:shadow-xl hover:border-emerald-500/30">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('profiles.show', $user) }}" class="text-gray-400 hover:text-gray-300 transition-colors duration-200">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ $user->name }}'s Activity</h1>
                        <p class="text-gray-400">All discussions and replies</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-emerald-400">{{ $discussions->total() + $replies->total() }}</div>
                    <div class="text-sm text-gray-400">Total Activities</div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex space-x-1 mb-6 bg-gray-800 rounded-2xl p-2 border border-gray-700">
            <button id="all-tab" class="tab-button flex-1 py-3 px-4 text-center rounded-xl font-medium text-white bg-emerald-600 transition-all duration-300">
                All Activity
            </button>
            <button id="discussions-tab" class="tab-button flex-1 py-3 px-4 text-center rounded-xl font-medium text-gray-400 hover:text-white hover:bg-gray-750 transition-all duration-300">
                Discussions ({{ $discussions->total() }})
            </button>
            <button id="replies-tab" class="tab-button flex-1 py-3 px-4 text-center rounded-xl font-medium text-gray-400 hover:text-white hover:bg-gray-750 transition-all duration-300">
                Replies ({{ $replies->total() }})
            </button>
        </div>

        <!-- All Activity Tab -->
        <div id="all-content" class="tab-content">
            <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-teal-500/30">
                @if($discussions->count() > 0 || $replies->count() > 0)
                    <!-- Combined activities - show latest 10 from both -->
                    @php
                        $combinedActivities = collect()
                            ->merge($discussions->take(5)->map(function($discussion) {
                                return ['type' => 'discussion', 'data' => $discussion];
                            }))
                            ->merge($replies->take(5)->map(function($reply) {
                                return ['type' => 'reply', 'data' => $reply];
                            }))
                            ->sortByDesc(function($activity) {
                                return $activity['data']->created_at;
                            })
                            ->take(10);
                    @endphp

                    <div class="divide-y divide-gray-700">
                        @foreach($combinedActivities as $activity)
                            <div class="px-6 py-4 hover:bg-gray-750/50 transition-all duration-200 group">
                                <div class="flex items-start space-x-4">
                                    <!-- Activity Icon -->
                                    @if($activity['type'] === 'discussion')
                                        <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 transition-transform duration-200 group-hover:scale-110">
                                            <i class="fa-solid fa-comment"></i>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-teal-500/20 flex items-center justify-center text-teal-400 transition-transform duration-200 group-hover:scale-110">
                                            <i class="fa-solid fa-reply"></i>
                                        </div>
                                    @endif
                                    
                                    <!-- Activity Content -->
                                    <div class="flex-1">
                                        @if($activity['type'] === 'discussion')
                                            <a href="{{ route('forum.discussion', ['category' => $activity['data']->category, 'discussion' => $activity['data']]) }}" 
                                               class="font-medium text-white hover:text-emerald-300 transition-colors duration-200 block mb-1">
                                                Started discussion: {{ $activity['data']->title }}
                                            </a>
                                            <p class="text-gray-400 text-sm mb-2">{{ Str::limit($activity['data']->content, 150) }}</p>
                                        @else
                                            <a href="{{ route('forum.discussion', ['category' => $activity['data']->discussion->category, 'discussion' => $activity['data']->discussion]) }}#reply-{{ $activity['data']->id }}" 
                                               class="font-medium text-white hover:text-emerald-300 transition-colors duration-200 block mb-1">
                                                Replied to: {{ $activity['data']->discussion->title }}
                                            </a>
                                            <p class="text-gray-400 text-sm mb-2">{{ Str::limit($activity['data']->content, 150) }}</p>
                                        @endif
                                        
                                        <div class="flex items-center space-x-3 text-sm text-gray-500">
                                            <span>{{ $activity['data']->created_at->diffForHumans() }}</span>
                                            <span class="text-gray-600">•</span>
                                            @if($activity['type'] === 'discussion')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border" 
                                                      style="background-color: {{ $activity['data']->category->color }}20; color: {{ $activity['data']->category->color }}; border-color: {{ $activity['data']->category->color }}30;">
                                                    {{ $activity['data']->category->name }}
                                                </span>
                                                <span class="text-gray-600">•</span>
                                                <span>{{ $activity['data']->reply_count }} replies</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border" 
                                                      style="background-color: {{ $activity['data']->discussion->category->color }}20; color: {{ $activity['data']->discussion->category->color }}; border-color: {{ $activity['data']->discussion->category->color }}30;">
                                                    {{ $activity['data']->discussion->category->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- View More Links -->
                    <div class="px-6 py-4 border-t border-gray-700 flex justify-between">
                        @if($discussions->total() > 5)
                            <a href="#discussions-content" onclick="showTab('discussions')" 
                               class="text-emerald-400 hover:text-emerald-300 font-medium transition-colors duration-200">
                                View All Discussions ({{ $discussions->total() }})
                            </a>
                        @endif
                        @if($replies->total() > 5)
                            <a href="#replies-content" onclick="showTab('replies')" 
                               class="text-teal-400 hover:text-teal-300 font-medium transition-colors duration-200">
                                View All Replies ({{ $replies->total() }})
                            </a>
                        @endif
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <i class="fa-solid fa-inbox text-4xl text-gray-600 mb-4"></i>
                        <h3 class="text-lg font-medium text-white mb-2">No activity yet</h3>
                        <p class="text-gray-400">This user hasn't been active in the community yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Discussions Tab -->
        <div id="discussions-content" class="tab-content hidden">
            <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-emerald-500/30">
                @if($discussions->count() > 0)
                    <div class="divide-y divide-gray-700">
                        @foreach($discussions as $discussion)
                            <div class="px-6 py-4 hover:bg-gray-750/50 transition-all duration-200 group">
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 transition-transform duration-200 group-hover:scale-110">
                                        <i class="fa-solid fa-comment"></i>
                                    </div>
                                    <div class="flex-1">
                                        <a href="{{ route('forum.discussion', ['category' => $discussion->category, 'discussion' => $discussion]) }}" 
                                           class="font-medium text-white hover:text-emerald-300 transition-colors duration-200 block mb-1">
                                            {{ $discussion->title }}
                                        </a>
                                        <p class="text-gray-400 text-sm mb-2">{{ Str::limit($discussion->content, 150) }}</p>
                                        <div class="flex items-center space-x-3 text-sm text-gray-500">
                                            <span>{{ $discussion->created_at->diffForHumans() }}</span>
                                            <span class="text-gray-600">•</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border" 
                                                  style="background-color: {{ $discussion->category->color }}20; color: {{ $discussion->category->color }}; border-color: {{ $discussion->category->color }}30;">
                                                {{ $discussion->category->name }}
                                            </span>
                                            <span class="text-gray-600">•</span>
                                            <span>{{ $discussion->reply_count }} replies</span>
                                            <span class="text-gray-600">•</span>
                                            <span>{{ $discussion->view_count }} views</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-700">
                        {{ $discussions->links('pagination::simple-tailwind') }}
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <i class="fa-solid fa-comments text-4xl text-gray-600 mb-4"></i>
                        <h3 class="text-lg font-medium text-white mb-2">No discussions yet</h3>
                        <p class="text-gray-400">This user hasn't started any discussions yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Replies Tab -->
        <div id="replies-content" class="tab-content hidden">
            <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-teal-500/30">
                @if($replies->count() > 0)
                    <div class="divide-y divide-gray-700">
                        @foreach($replies as $reply)
                            <div class="px-6 py-4 hover:bg-gray-750/50 transition-all duration-200 group">
                                <div class="flex items-start space-x-4">
                                    <div class="w-10 h-10 rounded-full bg-teal-500/20 flex items-center justify-center text-teal-400 transition-transform duration-200 group-hover:scale-110">
                                        <i class="fa-solid fa-reply"></i>
                                    </div>
                                    <div class="flex-1">
                                        <a href="{{ route('forum.discussion', ['category' => $reply->discussion->category, 'discussion' => $reply->discussion]) }}#reply-{{ $reply->id }}" 
                                           class="font-medium text-white hover:text-emerald-300 transition-colors duration-200 block mb-1">
                                            Replied to: {{ $reply->discussion->title }}
                                        </a>
                                        <p class="text-gray-400 text-sm mb-2">{{ Str::limit($reply->content, 150) }}</p>
                                        <div class="flex items-center space-x-3 text-sm text-gray-500">
                                            <span>{{ $reply->created_at->diffForHumans() }}</span>
                                            <span class="text-gray-600">•</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border" 
                                                  style="background-color: {{ $reply->discussion->category->color }}20; color: {{ $reply->discussion->category->color }}; border-color: {{ $reply->discussion->category->color }}30;">
                                                {{ $reply->discussion->category->name }}
                                            </span>
                                            @if($reply->is_answer)
                                                <span class="text-gray-600">•</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-900/50 text-green-300 border border-green-700/50">
                                                    <i class="fa-solid fa-check mr-1"></i> Solution
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-700">
                        {{ $replies->links('pagination::simple-tailwind') }}
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <i class="fa-solid fa-reply text-4xl text-gray-600 mb-4"></i>
                        <h3 class="text-lg font-medium text-white mb-2">No replies yet</h3>
                        <p class="text-gray-400">This user hasn't posted any replies yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('bg-emerald-600', 'text-white');
        button.classList.add('text-gray-400', 'hover:text-white', 'hover:bg-gray-750');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Activate selected tab
    document.getElementById(tabName + '-tab').classList.add('bg-emerald-600', 'text-white');
    document.getElementById(tabName + '-tab').classList.remove('text-gray-400', 'hover:text-white', 'hover:bg-gray-750');
}

// Initialize tabs
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.id.replace('-tab', '');
            showTab(tabName);
        });
    });
});
</script>
@endsection