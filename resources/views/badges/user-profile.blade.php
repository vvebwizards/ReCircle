{{-- resources/views/badges/user-profile.blade.php --}}
@extends('layouts.app')

@section('title', $user->name . '\'s Achievements - ReCircle')

@section('content')
<div class="min-h-screen" style="background-color: #1a202c; padding-top: 50px; padding-bottom: 100px;">
    <div class="container mx-auto px-4 pt-20">
        <!-- User Header -->
        <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-6 mb-8 transition-all duration-300 hover:shadow-xl hover:border-emerald-500/30">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center text-white text-2xl font-bold transition-transform duration-300 hover:scale-110">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white">{{ $user->name }}</h1>
                        <p class="text-gray-400">{{ $user->rank }}</p>
                        <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                            <span>Member since {{ $user->created_at->format('M Y') }}</span>
                            <span class="text-gray-600">•</span>
                            <span>{{ $stats->days_active ?? 0 }} days active</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-emerald-400">{{ $stats->total_points ?? 0 }}</div>
                    <div class="text-sm text-gray-400">Total Points</div>
                </div>
            </div>

            <!-- Level Progress -->
            @if($stats)
            <div class="mt-6">
                <div class="flex justify-between text-sm text-gray-400 mb-2">
                    <span>Level {{ $stats->level }}</span>
                    <span>{{ $stats->points_to_next_level }} points to level {{ $stats->level + 1 }}</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-3">
                    <div class="bg-gradient-to-r from-emerald-400 to-teal-500 h-3 rounded-full transition-all duration-1000 ease-out" 
                         style="width: {{ $stats->level_progress }}%"></div>
                </div>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Earned Badges -->
            <div class="lg:col-span-2">
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-teal-500/30">
                    <div class="px-6 py-4 border-b border-gray-700">
                        <h2 class="text-xl font-semibold text-white">Earned Badges</h2>
                        <p class="text-gray-400 text-sm mt-1">{{ $user->badges->count() }} achievements unlocked</p>
                    </div>
                    
                    @if($badges->flatten()->count() > 0)
                        <div class="p-6">
                            @foreach(['platinum', 'gold', 'silver', 'bronze'] as $type)
                                @if(isset($badges[$type]) && $badges[$type]->count() > 0)
                                    <div class="mb-8">
                                        <h3 class="text-lg font-semibold text-white mb-4 capitalize flex items-center">
                                            <span class="w-3 h-3 rounded-full mr-2
                                                {{ $type === 'platinum' ? 'bg-gray-400' : '' }}
                                                {{ $type === 'gold' ? 'bg-yellow-500' : '' }}
                                                {{ $type === 'silver' ? 'bg-gray-300' : '' }}
                                                {{ $type === 'bronze' ? 'bg-amber-600' : '' }}"></span>
                                            {{ $type }} Badges ({{ $badges[$type]->count() }})
                                        </h3>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            @foreach($badges[$type] as $badge)
                                                <div class="border border-gray-700 rounded-xl p-4 hover:bg-gray-750/50 transition-all duration-300 transform hover:scale-[1.02] group">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white transition-transform duration-300 group-hover:scale-110 shadow-lg"
                                                             style="background-color: {{ $badge->color }};">
                                                            <i class="fa-solid {{ $badge->icon }}"></i>
                                                        </div>
                                                        <div class="flex-1">
                                                            <h4 class="font-semibold text-white group-hover:text-emerald-300 transition-colors duration-200">{{ $badge->name }}</h4>
                                                            <p class="text-sm text-gray-400">{{ $badge->description }}</p>
                                                            <!-- FIX: Use Carbon to parse the date -->
                                                            @php
                                                                $earnedAt = \Carbon\Carbon::parse($badge->pivot->earned_at);
                                                            @endphp
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                Earned {{ $earnedAt->diffForHumans() }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <i class="fa-solid fa-trophy text-4xl text-gray-600 mb-4"></i>
                            <h3 class="text-lg font-medium text-white mb-2">No badges yet</h3>
                            <p class="text-gray-400 mb-4">Start participating in the community to earn your first badge!</p>
                            <a href="{{ route('forum.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-500 transition-all duration-300 transform hover:scale-105">
                                Explore Discussions
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Next Achievements -->
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-amber-500/30">
                    <div class="px-4 py-3 border-b border-gray-700">
                        <h3 class="text-sm font-semibold text-white">Next Achievements</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        @if(count($nextBadges) > 0)
                            @foreach($nextBadges as $nextBadge)
                                <div class="border border-gray-700 rounded-xl p-3 hover:bg-gray-750/50 transition-all duration-200 group">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm opacity-50 transition-transform duration-200 group-hover:scale-110"
                                                 style="background-color: {{ $nextBadge['badge']->color }};">
                                                <i class="fa-solid {{ $nextBadge['badge']->icon }}"></i>
                                            </div>
                                            <span class="text-sm font-medium text-gray-300 group-hover:text-white transition-colors duration-200">{{ $nextBadge['badge']->name }}</span>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $nextBadge['current'] }}/{{ $nextBadge['badge']->threshold }}</span>
                                    </div>
                                    <div class="w-full bg-gray-700 rounded-full h-2">
                                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" 
                                             style="width: {{ $nextBadge['progress'] }}%"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $nextBadge['remaining'] }} more to go
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4 text-gray-500">
                                <i class="fa-solid fa-flag-checkered text-xl mb-2"></i>
                                <p class="text-sm">All available badges earned!</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- User Stats -->
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-teal-500/30">
                    <div class="px-4 py-3 border-b border-gray-700">
                        <h3 class="text-sm font-semibold text-white">Community Stats</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Discussions</span>
                            <span class="font-semibold text-white">{{ $stats->discussions_count ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Replies</span>
                            <span class="font-semibold text-white">{{ $stats->replies_count ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Solutions</span>
                            <span class="font-semibold text-white">{{ $stats->solutions_provided ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Likes Received</span>
                            <span class="font-semibold text-white">{{ $stats->likes_received ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Current Streak</span>
                            <span class="font-semibold text-white">{{ $stats->current_streak ?? 0 }} days</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-emerald-900/30 rounded-2xl border border-emerald-700/50 p-4 backdrop-blur-sm">
                    <h4 class="text-sm font-semibold text-emerald-300 mb-3">Earn More Points</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-emerald-200">Start a discussion</span>
                            <span class="font-semibold text-emerald-400">+10 pts</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-emerald-200">Post a reply</span>
                            <span class="font-semibold text-emerald-400">+5 pts</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-emerald-200">Provide solution</span>
                            <span class="font-semibold text-emerald-400">+25 pts</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-emerald-200">Receive like</span>
                            <span class="font-semibold text-emerald-400">+2 pts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection