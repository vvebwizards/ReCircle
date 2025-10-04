{{-- resources/views/forum/index.blade.php --}}
@extends('layouts.app') {{-- Use your main layout --}}

@section('title', 'Forum - ReCircle')

@section('content')
<div class="container mx-auto px-4 py-8" style="padding-top: 100px; padding-bottom: 100px;">
    <!-- Hero Section -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">ReCircle Community Forum</h1>
            <p class="text-gray-600 mb-4">Connect, share knowledge, and collaborate with other waste transformation enthusiasts</p>
<a href="{{ route('forum.discussions.create') }}" class="btn btn-primary">
    <i class="fa-solid fa-plus mr-2"></i>
    Start a Discussion
</a>

        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Categories -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold text-gray-900">Forum Categories</h2>
                </div>
                <div class="divide-y">
                    @foreach($categories as $category)
                        <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center" 
                                         style="background-color: {{ $category->color }}20; color: {{ $category->color }};">
                                        <i class="fa-solid {{ $category->icon }} text-xl"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('forum.category', $category) }}" 
                                           class="text-lg font-semibold text-gray-900 hover:text-blue-600">
                                            {{ $category->name }}
                                        </a>
                                        <p class="text-gray-600 text-sm mt-1">{{ $category->description }}</p>
                                        <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                            <span>{{ $category->discussions_count }} discussions</span>
                                            @if($category->latestDiscussion)
                                                <span>•</span>
                                                <span>Latest: 
                                                    <a href="{{ route('forum.discussion', ['category' => $category, 'discussion' => $category->latestDiscussion]) }}" 
                                                       class="text-blue-600 hover:text-blue-800">
                                                        {{ Str::limit($category->latestDiscussion->title, 40) }}
                                                    </a>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Recent Discussions -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Discussions</h2>
                </div>
                <div class="divide-y">
                    @foreach($recentDiscussions as $discussion)
                        <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <a href="{{ route('forum.discussion', ['category' => $discussion->category, 'discussion' => $discussion]) }}" 
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
                                <div class="flex-shrink-0 ml-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                          style="background-color: {{ $discussion->category->color }}20; color: {{ $discussion->category->color }};">
                                        {{ $discussion->category->name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Popular Discussions -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-4 py-3 border-b">
                    <h3 class="text-sm font-semibold text-gray-900">Popular Discussions</h3>
                </div>
                <div class="divide-y">
                    @foreach($popularDiscussions as $discussion)
                        <div class="px-4 py-3">
                            <a href="{{ route('forum.discussion', ['category' => $discussion->category, 'discussion' => $discussion]) }}" 
                               class="text-sm font-medium text-gray-900 hover:text-blue-600 block">
                                {{ Str::limit($discussion->title, 50) }}
                            </a>
                            <div class="flex items-center space-x-2 mt-1 text-xs text-gray-500">
                                <span>{{ $discussion->view_count }} views</span>
                                <span>•</span>
                                <span>{{ $discussion->reply_count }} replies</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="px-4 py-3 border-b">
                    <h3 class="text-sm font-semibold text-gray-900">Community Stats</h3>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total Discussions</span>
                        <span class="font-semibold">{{ $recentDiscussions->count() }}+</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Active Members</span>
                        <span class="font-semibold">{{ $categories->sum('discussions_count') }}+</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Categories</span>
                        <span class="font-semibold">{{ $categories->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Community Guidelines -->
            <div class="bg-blue-50 rounded-lg border border-blue-200 p-4">
                <h4 class="text-sm font-semibold text-blue-900 mb-2">Community Guidelines</h4>
                <ul class="text-xs text-blue-800 space-y-1">
                    <li>• Be respectful and constructive</li>
                    <li>• Share your waste transformation experiences</li>
                    <li>• Help others with your expertise</li>
                    <li>• Follow the circular economy principles</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection