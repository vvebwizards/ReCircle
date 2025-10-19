{{-- resources/views/messages/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Messages - ReCircle')

@section('content')
<div class="min-h-screen" style="background-color: #1a202c;">
    <div class="container mx-auto px-4 pt-20" style="padding-top: 100px; padding-bottom: 100px;">
        <!-- Header -->
        <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-6 mb-8 transition-all duration-300 hover:shadow-xl hover:border-emerald-500/30">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Messages</h1>
                    <p class="text-gray-400 mt-1">Private conversations with community members</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-emerald-400">{{ auth()->user()->unreadMessagesCount() }}</div>
                    <div class="text-sm text-gray-400">Unread messages</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Conversations List -->
            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-teal-500/30">
                    <div class="px-4 py-3 border-b border-gray-700">
                        <h2 class="text-lg font-semibold text-white">Conversations</h2>
                    </div>
                    
                    @if($conversations->count() > 0)
                        <div class="divide-y divide-gray-700 max-h-96 overflow-y-auto">
                            @foreach($conversations as $conversation)
                                @php
                                    $otherUser = $conversation->getOtherUser(auth()->user());
                                    $unreadCount = $conversation->getUnreadCountForUser(auth()->user());
                                    $lastMessage = $conversation->lastMessage;
                                @endphp
                                <a href="{{ route('messages.show', $otherUser) }}" 
                                   class="block px-4 py-3 hover:bg-gray-750/50 transition-all duration-200 group border-l-4 {{ $unreadCount > 0 ? 'border-emerald-500' : 'border-transparent' }}">
                                    <div class="flex items-center space-x-3">
                                        <div class="relative">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center text-white text-sm font-bold transition-transform duration-200 group-hover:scale-110">
                                                {{ strtoupper(substr($otherUser->name, 0, 2)) }}
                                            </div>
                                            @if($unreadCount > 0)
                                                <div class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 flex items-center justify-center text-xs text-white font-bold">
                                                    {{ $unreadCount }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <h3 class="font-medium text-white group-hover:text-emerald-300 transition-colors duration-200 truncate">
                                                    {{ $otherUser->name }}
                                                </h3>
                                                @if($lastMessage)
                                                    <span class="text-xs text-gray-500 flex-shrink-0 ml-2">
                                                        {{ $lastMessage->created_at->shortAbsoluteDiffForHumans() }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($lastMessage)
                                                <p class="text-sm text-gray-400 truncate mt-1">
                                                    {{ $lastMessage->sender_id === auth()->id() ? 'You: ' : '' }}
                                                    {{ Str::limit($lastMessage->content, 40) }}
                                                </p>
                                            @else
                                                <p class="text-sm text-gray-500 italic mt-1">No messages yet</p>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-12 text-center">
                            <i class="fa-solid fa-comments text-4xl text-gray-600 mb-4"></i>
                            <h3 class="text-lg font-medium text-white mb-2">No conversations yet</h3>
                            <p class="text-gray-400">Start a conversation with another user!</p>
                        </div>
                    @endif

                    <!-- Pagination -->
                    @if($conversations->hasPages())
                        <div class="px-4 py-3 border-t border-gray-700">
                            {{ $conversations->links('pagination::simple-tailwind') }}
                        </div>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="mt-6 bg-emerald-900/30 rounded-2xl border border-emerald-700/50 p-4 backdrop-blur-sm">
                    <h4 class="text-sm font-semibold text-emerald-300 mb-3">Messaging Tips</h4>
                    <ul class="text-xs text-emerald-200 space-y-2">
                        <li class="flex items-start">
                            <i class="fa-solid fa-shield-alt mt-0.5 mr-2 text-emerald-400"></i>
                            <span>Keep conversations professional and respectful</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-recycle mt-0.5 mr-2 text-emerald-400"></i>
                            <span>Discuss waste transformation projects and ideas</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-flag mt-0.5 mr-2 text-emerald-400"></i>
                            <span>Report any inappropriate messages to admins</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Welcome/Instructions Panel -->
            <div class="lg:col-span-3">
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-8 text-center transition-all duration-300 hover:shadow-xl hover:border-emerald-500/30">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center text-white text-2xl mx-auto mb-6">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-4">Welcome to Messages</h2>
                    <p class="text-gray-400 mb-6 max-w-md mx-auto">
                        Connect privately with other community members to discuss waste transformation projects, 
                        share ideas, and collaborate on circular economy initiatives.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <div class="text-center p-4 bg-gray-750 rounded-xl hover:bg-gray-700 transition-colors duration-200">
                            <i class="fa-solid fa-user-friends text-emerald-400 text-xl mb-2"></i>
                            <h3 class="font-semibold text-white mb-1">Connect</h3>
                            <p class="text-sm text-gray-400">Find collaborators</p>
                        </div>
                        <div class="text-center p-4 bg-gray-750 rounded-xl hover:bg-gray-700 transition-colors duration-200">
                            <i class="fa-solid fa-lightbulb text-amber-400 text-xl mb-2"></i>
                            <h3 class="font-semibold text-white mb-1">Discuss</h3>
                            <p class="text-sm text-gray-400">Share ideas privately</p>
                        </div>
                        <div class="text-center p-4 bg-gray-750 rounded-xl hover:bg-gray-700 transition-colors duration-200">
                            <i class="fa-solid fa-shield-alt text-blue-400 text-xl mb-2"></i>
                            <h3 class="font-semibold text-white mb-1">Secure</h3>
                            <p class="text-sm text-gray-400">Private conversations</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">
                        Select a conversation from the list or visit user profiles to start messaging.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection