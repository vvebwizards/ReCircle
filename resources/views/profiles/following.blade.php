{{-- resources/views/profiles/following.blade.php --}}
@extends('layouts.app')

@section('title', $user->name . "'s Following - ReCircle")

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
                        <h1 class="text-2xl font-bold text-white">People {{ $user->name }} Follows</h1>
                        <p class="text-gray-400">{{ $following->total() }} people followed</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-emerald-400">{{ $user->following_count }}</div>
                    <div class="text-sm text-gray-400">Total Following</div>
                </div>
            </div>
        </div>

        <!-- Following List -->
        <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-teal-500/30">
            <div class="px-6 py-4 border-b border-gray-700">
                <h2 class="text-lg font-semibold text-white">Following</h2>
            </div>
            
            @if($following->count() > 0)
                <div class="divide-y divide-gray-700">
                    @foreach($following as $followedUser)
                        <div class="px-6 py-4 hover:bg-gray-750/50 transition-all duration-200 group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <a href="{{ route('profiles.show', $followedUser) }}" class="flex items-center space-x-4 flex-1">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold transition-transform duration-200 group-hover:scale-110">
                                            {{ strtoupper(substr($followedUser->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-white group-hover:text-emerald-300 transition-colors duration-200">{{ $followedUser->name }}</h3>
                                            <div class="flex items-center space-x-3 text-sm text-gray-400 mt-1">
                                                <span>{{ $followedUser->stats->total_points ?? 0 }} points</span>
                                                <span class="text-gray-600">•</span>
                                                <span>{{ $followedUser->badges_count }} badges</span>
                                                <span class="text-gray-600">•</span>
                                                <span>{{ $followedUser->followers_count }} followers</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                
                                @if(auth()->id() !== $followedUser->id)
                                    <div class="flex-shrink-0">
                                        @if(auth()->user()->isFollowing($followedUser))
                                            <form action="{{ route('profiles.unfollow', $followedUser) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="px-4 py-2 border border-gray-600 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-300 transform hover:scale-105">
                                                    Following
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('profiles.follow', $followedUser) }}" method="POST">
                                                @csrf
                                                <button type="submit" 
                                                        class="px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-500 transition-all duration-300 transform hover:scale-105">
                                                    Follow
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-700">
                    {{ $following->links('pagination::simple-tailwind') }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <i class="fa-solid fa-user-friends text-4xl text-gray-600 mb-4"></i>
                    <h3 class="text-lg font-medium text-white mb-2">Not following anyone</h3>
                    <p class="text-gray-400">This user isn't following anyone yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection