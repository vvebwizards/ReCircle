{{-- resources/views/profiles/show.blade.php --}}
@extends('layouts.app')

@section('title', $user->name . ' - ReCircle Profile')

@section('content')
<div class="min-h-screen" style="background-color: #1a202c;">
    <div class="container mx-auto px-4 pt-20" style="padding-top: 100px; padding-bottom: 100px;">
        <!-- Profile Header -->
        <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-6 mb-8 transition-all duration-300 hover:shadow-xl hover:border-emerald-500/30">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-6">
                    <div class="relative">
                        <div class="w-24 h-24 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center text-white text-2xl font-bold transition-transform duration-300 hover:scale-110">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        @if($user->stats && $user->stats->level > 1)
                            <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-xs font-bold text-white border-2 border-gray-800">
                                {{ $user->stats->level }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <h1 class="text-3xl font-bold text-white">{{ $user->name }}</h1>
                            @if($user->stats)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-emerald-900/50 text-emerald-300 border border-emerald-700/50">
                                    {{ $user->rank }}
                                </span>
                            @endif
                        </div>
                        <p class="text-gray-400 mb-4">{{ $user->bio ?? 'Community member focused on circular economy and waste transformation.' }}</p>
                        
                        <div class="flex items-center space-x-6 text-sm text-gray-400">
                            <span class="flex items-center">
                                <i class="fa-solid fa-calendar mr-2"></i>
                                Joined {{ $user->created_at->format('M Y') }}
                            </span>
                            <span class="flex items-center">
                                <i class="fa-solid fa-clock mr-2"></i>
                                {{ $user->stats->days_active ?? 0 }} days active
                            </span>
                            @if($user->location)
                                <span class="flex items-center">
                                    <i class="fa-solid fa-location-dot mr-2"></i>
                                    {{ $user->location }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Follow Stats -->
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white">{{ $user->followers_count }}</div>
                        <div class="text-sm text-gray-400">Followers</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white">{{ $user->following_count }}</div>
                        <div class="text-sm text-gray-400">Following</div>
                    </div>
                    
                    <!-- Follow Button -->
                    @if(auth()->id() !== $user->id)
                        <div class="ml-4">
{{-- Replace the unfollow form --}}
@if($isFollowing)
    <form action="{{ route('profiles.unfollow', $user) }}" method="POST">
        @csrf
        <button type="submit" 
                class="px-6 py-2 border border-gray-600 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-300 transform hover:scale-105">
            <i class="fa-solid fa-user-minus mr-2"></i>
            Unfollow
        </button>
    </form>
@else
    <form action="{{ route('profiles.follow', $user) }}" method="POST">
        @csrf
        <button type="submit" 
                class="px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-500 transition-all duration-300 transform hover:scale-105">
            <i class="fa-solid fa-user-plus mr-2"></i>
            Follow
        </button>
    </form>
@endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Stats Bar -->
            @if($user->stats)
            <div class="mt-6 pt-6 border-t border-gray-700">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-emerald-400">{{ $user->stats->total_points ?? 0 }}</div>
                        <div class="text-sm text-gray-400">Points</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $user->badges_count }}</div>
                        <div class="text-sm text-gray-400">Badges</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $user->stats->discussions_count ?? 0 }}</div>
                        <div class="text-sm text-gray-400">Discussions</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">{{ $user->stats->replies_count ?? 0 }}</div>
                        <div class="text-sm text-gray-400">Replies</div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Recent Activity -->
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-teal-500/30">
                    <div class="px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-white">Recent Activity</h2>
                        <a href="{{ route('profiles.activity', $user) }}" class="text-sm text-emerald-400 hover:text-emerald-300 font-medium transition-colors duration-200">
                            View All →
                        </a>
                    </div>
                    
                    @if($recentDiscussions->count() > 0 || $recentReplies->count() > 0)
                        <div class="divide-y divide-gray-700">
                            <!-- Recent Discussions -->
                            @foreach($recentDiscussions as $discussion)
                                <div class="px-6 py-4 hover:bg-gray-750/50 transition-all duration-200 group">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 text-sm transition-transform duration-200 group-hover:scale-110">
                                            <i class="fa-solid fa-comment"></i>
                                        </div>
                                        <div class="flex-1">
                                            <a href="{{ route('forum.discussion', ['category' => $discussion->category, 'discussion' => $discussion]) }}" 
                                               class="font-medium text-white hover:text-emerald-300 transition-colors duration-200 block">
                                                Started: {{ $discussion->title }}
                                            </a>
                                            <div class="flex items-center space-x-2 mt-1 text-sm text-gray-400">
                                                <span>{{ $discussion->created_at->diffForHumans() }}</span>
                                                <span class="text-gray-600">•</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border" 
                                                      style="background-color: {{ $discussion->category->color }}20; color: {{ $discussion->category->color }}; border-color: {{ $discussion->category->color }}30;">
                                                    {{ $discussion->category->name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Recent Replies -->
                            @foreach($recentReplies as $reply)
                                <div class="px-6 py-4 hover:bg-gray-750/50 transition-all duration-200 group">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-teal-500/20 flex items-center justify-center text-teal-400 text-sm transition-transform duration-200 group-hover:scale-110">
                                            <i class="fa-solid fa-reply"></i>
                                        </div>
                                        <div class="flex-1">
                                            <a href="{{ route('forum.discussion', ['category' => $reply->discussion->category, 'discussion' => $reply->discussion]) }}#reply-{{ $reply->id }}" 
                                               class="font-medium text-white hover:text-emerald-300 transition-colors duration-200 block">
                                                Replied to: {{ $reply->discussion->title }}
                                            </a>
                                            <div class="flex items-center space-x-2 mt-1 text-sm text-gray-400">
                                                <span>{{ $reply->created_at->diffForHumans() }}</span>
                                                <span class="text-gray-600">•</span>
                                                <span class="text-gray-300">{{ Str::limit($reply->content, 100) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-12 text-center">
                            <i class="fa-solid fa-inbox text-4xl text-gray-600 mb-4"></i>
                            <h3 class="text-lg font-medium text-white mb-2">No activity yet</h3>
                            <p class="text-gray-400">This user hasn't been active in the community yet.</p>
                        </div>
                    @endif
                </div>

                <!-- Badges Preview -->
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-amber-500/30">
                    <div class="px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-white">Recent Badges</h2>
                        <a href="{{ route('badges.user-profile', $user) }}" class="text-sm text-emerald-400 hover:text-emerald-300 font-medium transition-colors duration-200">
                            View All →
                        </a>
                    </div>
                    
                    @if($user->badges->count() > 0)
                        <div class="p-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            @foreach($user->badges->take(8) as $badge)
                                <div class="text-center group cursor-pointer transform hover:scale-105 transition-transform duration-300">
                                    <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center text-white text-xl shadow-lg transition-all duration-300 group-hover:shadow-xl"
                                         style="background-color: {{ $badge->color }};"
                                         title="{{ $badge->name }} - {{ $badge->description }}">
                                        <i class="fa-solid {{ $badge->icon }}"></i>
                                    </div>
                                    <div class="text-sm font-medium text-white group-hover:text-amber-300 transition-colors duration-200">{{ $badge->name }}</div>
                                    <div class="text-xs text-gray-400 mt-1 capitalize">{{ $badge->type }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-12 text-center">
                            <i class="fa-solid fa-trophy text-4xl text-gray-600 mb-4"></i>
                            <h3 class="text-lg font-medium text-white mb-2">No badges yet</h3>
                            <p class="text-gray-400">This user hasn't earned any badges yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Follow Actions -->
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-6 transition-all duration-300 hover:shadow-xl hover:border-emerald-500/30">
                    <h3 class="text-lg font-semibold text-white mb-4">Connect</h3>
                    <div class="space-y-3">
                        @if(auth()->id() !== $user->id)
                            @if($isFollowing)
                                <form action="{{ route('profiles.unfollow', $user) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full flex items-center justify-center px-4 py-2 border border-gray-600 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-300 transform hover:scale-105">
                                        <i class="fa-solid fa-user-minus mr-2"></i>
                                        Unfollow
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('profiles.follow', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-500 transition-all duration-300 transform hover:scale-105">
                                        <i class="fa-solid fa-user-plus mr-2"></i>
                                        Follow User
                                    </button>
                                </form>
                            @endif
                        @endif
                        
                        <a href="{{ route('profiles.followers', $user) }}" 
                           class="w-full flex items-center justify-between px-4 py-3 border border-gray-600 rounded-lg hover:bg-gray-700 transition-all duration-200 group">
                            <div class="flex items-center">
                                <i class="fa-solid fa-users mr-3 text-gray-400 group-hover:text-emerald-400 transition-colors duration-200"></i>
                                <span class="text-gray-300 group-hover:text-white transition-colors duration-200">Followers</span>
                            </div>
                            <span class="bg-gray-700 px-2 py-1 rounded text-xs font-medium text-gray-300 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-200">
                                {{ $user->followers_count }}
                            </span>
                        </a>
                        
                        <a href="{{ route('profiles.following', $user) }}" 
                           class="w-full flex items-center justify-between px-4 py-3 border border-gray-600 rounded-lg hover:bg-gray-700 transition-all duration-200 group">
                            <div class="flex items-center">
                                <i class="fa-solid fa-user-friends mr-3 text-gray-400 group-hover:text-teal-400 transition-colors duration-200"></i>
                                <span class="text-gray-300 group-hover:text-white transition-colors duration-200">Following</span>
                            </div>
                            <span class="bg-gray-700 px-2 py-1 rounded text-xs font-medium text-gray-300 group-hover:bg-teal-500 group-hover:text-white transition-all duration-200">
                                {{ $user->following_count }}
                            </span>
                        </a>
                    </div>
                </div>

                <!-- User Stats -->
                @if($user->stats)
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-6 transition-all duration-300 hover:shadow-xl hover:border-teal-500/30">
                    <h3 class="text-lg font-semibold text-white mb-4">Community Stats</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm text-gray-400 mb-1">
                                <span>Level Progress</span>
                                <span>{{ $user->stats->level_progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2">
                                <div class="bg-gradient-to-r from-emerald-400 to-teal-500 h-2 rounded-full transition-all duration-1000 ease-out" 
                                     style="width: {{ $user->stats->level_progress }}%"></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 text-center">
                                Level {{ $user->stats->level }} • {{ $user->stats->points_to_next_level }} to next level
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="text-center p-3 bg-gray-750 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                                <div class="font-bold text-white">{{ $user->stats->discussions_count }}</div>
                                <div class="text-gray-400">Discussions</div>
                            </div>
                            <div class="text-center p-3 bg-gray-750 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                                <div class="font-bold text-white">{{ $user->stats->replies_count }}</div>
                                <div class="text-gray-400">Replies</div>
                            </div>
                            <div class="text-center p-3 bg-gray-750 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                                <div class="font-bold text-white">{{ $user->stats->solutions_provided }}</div>
                                <div class="text-gray-400">Solutions</div>
                            </div>
                            <div class="text-center p-3 bg-gray-750 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                                <div class="font-bold text-white">{{ $user->stats->likes_received }}</div>
                                <div class="text-gray-400">Likes</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Similar Users -->
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-6 transition-all duration-300 hover:shadow-xl hover:border-amber-500/30">
                    <h3 class="text-lg font-semibold text-white mb-4">Similar Users</h3>
                    <div class="space-y-3">
                        @php
                            $similarUsers = \App\Models\User::where('id', '!=', $user->id)
                                ->whereHas('stats')
                                ->with('stats')
                                ->withCount('badges')
                                ->inRandomOrder()
                                ->take(5)
                                ->get();
                        @endphp
                        
                        @foreach($similarUsers as $similarUser)
                            <a href="{{ route('profiles.show', $similarUser) }}" 
                               class="flex items-center justify-between p-3 hover:bg-gray-750 rounded-lg transition-all duration-200 group">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center text-white text-xs font-bold transition-transform duration-200 group-hover:scale-110">
                                        {{ strtoupper(substr($similarUser->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-white group-hover:text-emerald-300 transition-colors duration-200">{{ $similarUser->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $similarUser->stats->total_points ?? 0 }} pts</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fa-solid fa-medal text-amber-400 text-xs"></i>
                                    <span class="text-xs font-medium text-gray-300">{{ $similarUser->badges_count }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection