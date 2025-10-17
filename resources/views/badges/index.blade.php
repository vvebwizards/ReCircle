{{-- resources/views/badges/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Badges & Achievements - ReCircle')

@section('content')
<div class="container mx-auto px-4 pt-20">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-white mb-4">Badges & Achievements</h1>
        <p class="text-gray-400 max-w-2xl mx-auto">
            Earn badges by being an active member of our community. Show off your achievements and track your progress!
        </p>
    </div>

    <!-- Badges by Type -->
    @foreach(['platinum', 'gold', 'silver', 'bronze'] as $type)
        @if(isset($badges[$type]) && $badges[$type]->count() > 0)
            <div class="mb-12">
                <div class="flex items-center mb-6">
                    @php
                        $typeColors = [
                            'platinum' => 'from-gray-400 to-gray-300',
                            'gold' => 'from-yellow-500 to-yellow-300',
                            'silver' => 'from-gray-300 to-gray-200',
                            'bronze' => 'from-amber-700 to-amber-500'
                        ];
                    @endphp
                    <div class="w-8 h-8 rounded-full bg-gradient-to-r {{ $typeColors[$type] }} mr-3"></div>
                    <h2 class="text-2xl font-bold text-white capitalize">{{ $type }} Badges</h2>
                    <span class="ml-3 px-3 py-1 bg-gray-700 text-gray-300 rounded-full text-sm">
                        {{ $badges[$type]->count() }} badges
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($badges[$type] as $badge)
                        <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-6 hover:shadow-xl hover:border-emerald-500/30 transition-all duration-300 transform hover:scale-[1.02]">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg shadow-lg"
                                         style="background-color: {{ $badge->color }};">
                                        <i class="fa-solid {{ $badge->icon }}"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="font-semibold text-white">{{ $badge->name }}</h3>
                                        <p class="text-sm text-emerald-400">{{ $badge->points }} points</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
                                    {{ $badge->type === 'platinum' ? 'bg-gray-700 text-gray-300' : '' }}
                                    {{ $badge->type === 'gold' ? 'bg-yellow-900/50 text-yellow-300 border border-yellow-700/50' : '' }}
                                    {{ $badge->type === 'silver' ? 'bg-gray-700 text-gray-300' : '' }}
                                    {{ $badge->type === 'bronze' ? 'bg-amber-900/50 text-amber-300 border border-amber-700/50' : '' }}">
                                    {{ $badge->type }}
                                </span>
                            </div>
                            
                            <p class="text-gray-400 text-sm mb-4">{{ $badge->description }}</p>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <span>Earned by {{ $badge->users_count }} members</span>
                                <span>Threshold: {{ $badge->threshold }}</span>
                            </div>
                            
                            @if($badge->criteria === 'first_discussion')
                                <div class="mt-3 px-3 py-1 bg-blue-900/30 text-blue-300 rounded text-xs inline-block border border-blue-700/50">
                                    <i class="fa-solid fa-bolt mr-1"></i>One-time achievement
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    <!-- Call to Action -->
    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl p-8 text-center text-white shadow-2xl border border-emerald-400/30">
        <h2 class="text-2xl font-bold mb-4">Start Earning Badges Today!</h2>
        <p class="mb-6 max-w-2xl mx-auto text-emerald-100">
            Participate in discussions, help others, and be an active community member to unlock amazing badges and achievements.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('forum.index') }}" 
               class="bg-white text-emerald-600 px-6 py-3 rounded-lg font-semibold hover:bg-emerald-50 transition-all duration-300 transform hover:scale-105">
                Browse Discussions
            </a>
            <a href="{{ route('forum.discussions.create') }}" 
               class="border border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-emerald-600 transition-all duration-300 transform hover:scale-105">
                Start a Discussion
            </a>
        </div>
    </div>
</div>
@endsection