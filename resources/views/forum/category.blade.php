{{-- resources/views/forum/category.blade.php --}}
@extends('layouts.app')

@section('title', $category->name . ' - ReCircle Forum')

@section('content')
<div class="container mx-auto px-4 py-8" style="padding-top: 100px; padding-bottom: 100px;">
    <!-- Category Header -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-16 h-16 rounded-lg flex items-center justify-center text-2xl" 
                     style="background-color: {{ $category->color }}20; color: {{ $category->color }};">
                    <i class="fa-solid {{ $category->icon }}"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $category->name }}</h1>
                    <p class="text-gray-600 mt-1">{{ $category->description }}</p>
                    <div class="flex items-center space-x-4 mt-3 text-sm text-gray-500">
                        <span>{{ $discussions->total() }} discussions</span>
                    </div>
                </div>
            </div>
<a href="{{ route('forum.discussions.create') }}?category={{ $category->id }}" class="btn btn-secondary">
    <i class="fa-solid fa-plus mr-2"></i>
    New Discussion
</a>

        </div>
    </div>

    <!-- Discussions List -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900">Discussions</h2>
            <div class="flex space-x-2">
                <button class="px-3 py-1 text-sm border rounded-md hover:bg-gray-50">Latest</button>
                <button class="px-3 py-1 text-sm border rounded-md hover:bg-gray-50">Popular</button>
            </div>
        </div>
        
        @if($discussions->count() > 0)
            <div class="divide-y">
                @foreach($discussions as $discussion)
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
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
                                </div>
                                
                                <a href="{{ route('forum.discussion', ['category' => $category, 'discussion' => $discussion]) }}" 
                                   class="text-lg font-medium text-gray-900 hover:text-blue-600 block">
                                    {{ $discussion->title }}
                                </a>
                                
                                <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-user mr-1"></i>
                                        {{ $discussion->user->name }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-clock mr-1"></i>
                                        {{ $discussion->created_at->diffForHumans() }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fa-solid fa-eye mr-1"></i>
                                        {{ $discussion->view_count }}
                                    </span>
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
                                        <div class="text-xs text-gray-600">by {{ $discussion->lastReplier->name }}</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t">
                {{ $discussions->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <i class="fa-solid fa-comments text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No discussions yet</h3>
                <p class="text-gray-500 mb-4">Be the first to start a discussion in this category!</p>
                <a href="{{ route('forum.discussions.create') }}?category={{ $category->id }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Start a Discussion
                </a>
            </div>
        @endif
    </div>
</div>
@endsection