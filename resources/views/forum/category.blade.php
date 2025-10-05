{{-- resources/views/forum/category.blade.php --}}
@extends('layouts.app')

@section('title', $category->name . ' - ReCircle Forum')

@section('content')
<div class="container mx-auto px-4 pt-20" style="padding-top: 100px; padding-bottom: 100px;">
    <!-- Category Header -->
    <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-6 mb-8 transition-all duration-300 hover:shadow-xl hover:border-emerald-500/30">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-16 h-16 rounded-xl flex items-center justify-center text-2xl transition-transform duration-300 hover:scale-110 shadow-lg"
                     style="background-color: {{ $category->color }}30; color: {{ $category->color }}; border: 1px solid {{ $category->color }}20;">
                    <i class="fa-solid {{ $category->icon }}"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $category->name }}</h1>
                    <p class="text-gray-400 mt-1">{{ $category->description }}</p>
                    <div class="flex items-center space-x-4 mt-3 text-sm text-gray-500">
                        <span>{{ $discussions->total() }} discussions</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('forum.discussions.create') }}?category={{ $category->id }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-500 transition-all duration-300 transform hover:scale-105">
                <i class="fa-solid fa-plus mr-2"></i>
                New Discussion
            </a>
        </div>
    </div>

    <!-- Discussions List -->
    <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-teal-500/30">
        <div class="px-6 py-4 border-b border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-white">Discussions</h2>
            <div class="flex space-x-2">
                <button class="px-3 py-1 text-sm border border-gray-600 rounded-lg hover:bg-gray-700 text-gray-300 transition-colors duration-200">Latest</button>
                <button class="px-3 py-1 text-sm border border-gray-600 rounded-lg hover:bg-gray-700 text-gray-300 transition-colors duration-200">Popular</button>
            </div>
        </div>
        
        @if($discussions->count() > 0)
            <div class="divide-y divide-gray-700">
                @foreach($discussions as $discussion)
                    <div class="px-6 py-4 hover:bg-gray-750/50 transition-all duration-200 group border-l-4 border-transparent hover:border-emerald-500">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    @if($discussion->is_pinned)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-amber-900/50 text-amber-300 border border-amber-700/50">
                                            <i class="fa-solid fa-thumbtack mr-1"></i>
                                            Pinned
                                        </span>
                                    @endif
                                    @if($discussion->is_locked)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-900/50 text-red-300 border border-red-700/50">
                                            <i class="fa-solid fa-lock mr-1"></i>
                                            Locked
                                        </span>
                                    @endif
                                </div>
                                
                                <a href="{{ route('forum.discussion', ['category' => $category, 'discussion' => $discussion]) }}" 
                                   class="text-lg font-medium text-white hover:text-emerald-300 transition-colors duration-200 block">
                                    {{ $discussion->title }}
                                </a>
                                
                                <div class="flex items-center space-x-4 mt-2 text-sm text-gray-400">
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-user mr-1"></i>
                                        <span class="text-gray-300">{{ $discussion->user->name }}</span>
                                    </span>
                                    <span class="text-gray-600">•</span>
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-clock mr-1"></i>
                                        {{ $discussion->created_at->diffForHumans() }}
                                    </span>
                                    <span class="text-gray-600">•</span>
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-eye mr-1"></i>
                                        {{ $discussion->view_count }}
                                    </span>
                                    <span class="text-gray-600">•</span>
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-comment mr-1"></i>
                                        {{ $discussion->reply_count }}
                                    </span>
                                </div>
                            </div>
                            
                            @if($discussion->last_reply_at)
                                <div class="flex-shrink-0 ml-6 text-right">
                                    <div class="text-sm text-gray-500">Last reply</div>
                                    <div class="text-xs text-gray-400">{{ $discussion->last_reply_at->diffForHumans() }}</div>
                                    @if($discussion->lastReplier)
                                        <div class="text-xs text-gray-500">by {{ $discussion->lastReplier->name }}</div>
                                    @endif
                                </div>
                            @endif
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
                <p class="text-gray-400 mb-4">Be the first to start a discussion in this category!</p>
                <a href="{{ route('forum.discussions.create') }}?category={{ $category->id }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-500 transition-all duration-300 transform hover:scale-105">
                    Start a Discussion
                </a>
            </div>
        @endif
    </div>
</div>
@endsection