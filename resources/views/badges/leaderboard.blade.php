{{-- resources/views/badges/leaderboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Community Leaderboard - ReCircle')

@section('content')
<div class="min-h-screen" style="background-color: #1a202c; padding-top: 50px; padding-bottom: 100px;">
<div class="container mx-auto px-4 pt-20" >
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-white mb-4">Community Leaderboard</h1>
        <p class="text-gray-400 max-w-2xl mx-auto">
            See who's making the biggest impact in our community. Top contributors are recognized for their valuable contributions.
        </p>
    </div>

    <!-- Leaderboard -->
    <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-amber-500/30">
        <div class="px-6 py-4 border-b border-gray-700">
            <h2 class="text-xl font-semibold text-white">Top Contributors</h2>
        </div>
        
        <div class="divide-y divide-gray-700">
            @foreach($topUsers as $index => $user)
                <div class="px-6 py-4 hover:bg-gray-750/50 transition-all duration-200 group border-l-4 border-transparent hover:border-emerald-500">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Rank -->
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-transform duration-200 group-hover:scale-110
                                {{ $index === 0 ? 'bg-amber-400 text-gray-900' : '' }}
                                {{ $index === 1 ? 'bg-gray-400 text-gray-900' : '' }}
                                {{ $index === 2 ? 'bg-amber-600 text-white' : '' }}
                                {{ $index > 2 ? 'bg-emerald-500 text-white' : '' }}">
                                #{{ $index + 1 }}
                            </div>
                            
                            <!-- User Avatar -->
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold transition-transform duration-200 group-hover:scale-110">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            
                            <!-- User Info -->
                            <div>
                                <h3 class="font-semibold text-white group-hover:text-emerald-300 transition-colors duration-200">{{ $user->name }}</h3>
                                <p class="text-sm text-gray-400">{{ $user->rank }}</p>
                            </div>
                        </div>
                        
                        <!-- Stats -->
                        <div class="flex items-center space-x-6">
                            <div class="text-right">
                                <div class="font-bold text-white">{{ $user->stats->total_points }}</div>
                                <div class="text-xs text-gray-400">Points</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-white">{{ $user->badges_count }}</div>
                                <div class="text-xs text-gray-400">Badges</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-white">Lv. {{ $user->stats->level }}</div>
                                <div class="text-xs text-gray-400">Level</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mt-3 flex items-center space-x-3">
                        <div class="flex-1 bg-gray-700 rounded-full h-2">
                            <div class="bg-gradient-to-r from-emerald-400 to-teal-500 h-2 rounded-full transition-all duration-1000 ease-out" 
                                 style="width: {{ $user->stats->level_progress }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500">{{ $user->stats->level_progress }}% to next level</span>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Call to Action -->
        <div class="px-6 py-8 border-t border-gray-700 text-center">
            <h3 class="text-lg font-semibold text-white mb-2">Want to see your name here?</h3>
            <p class="text-gray-400 mb-4">Start participating in discussions and helping others to climb the leaderboard!</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('forum.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-500 transition-all duration-300 transform hover:scale-105">
                    <i class="fa-solid fa-comments mr-2"></i>
                    Browse Discussions
                </a>
                <a href="{{ route('badges.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-600 text-sm font-medium rounded-lg text-gray-300 bg-gray-700 hover:bg-gray-600 hover:text-white transition-all duration-300 transform hover:scale-105">
                    <i class="fa-solid fa-trophy mr-2"></i>
                    View All Badges
                </a>
            </div>
        </div>
    </div>
</div>
</div>
@endsection