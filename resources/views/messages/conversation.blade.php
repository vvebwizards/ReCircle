{{-- resources/views/messages/conversation.blade.php --}}
@extends('layouts.app')

@section('title', 'Chat with ' . $user->name . ' - ReCircle')

@section('content')
<div class="min-h-screen" style="background-color: #1a202c;">
    <div class="container mx-auto px-4 pt-20" style="padding-top: 100px; padding-bottom: 100px;">
        <!-- Header -->
        <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-6 mb-8 transition-all duration-300 hover:shadow-xl hover:border-emerald-500/30">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('messages.index') }}" class="text-gray-400 hover:text-gray-300 transition-colors duration-200">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <div class="w-12 h-12 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ $user->name }}</h1>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="text-sm text-gray-400">{{ $user->rank }}</span>
                            <span class="text-gray-600">•</span>
                            <span class="text-sm text-gray-400">{{ $user->stats->total_points ?? 0 }} points</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('profiles.show', $user) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-600 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-300">
                        <i class="fa-solid fa-user mr-2"></i>
                        View Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Messages Panel -->
            <div class="lg:col-span-3">
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-teal-500/30">
                    <!-- Messages Container -->
                    <div class="h-96 overflow-y-auto p-6 space-y-4" id="messages-container">
                        @if($messages->count() > 0)
                            @foreach($messages->reverse() as $message)
                                <div class="flex items-start space-x-3 {{ $message->sender_id === auth()->id() ? 'justify-end' : '' }} message-item"
                                     data-message-id="{{ $message->id }}">
                                    @if($message->sender_id !== auth()->id())
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr($message->sender->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    
                                    <div class="max-w-xs lg:max-w-md {{ $message->sender_id === auth()->id() ? 'order-first' : '' }}">
                                        <div class="bg-gray-700 rounded-2xl p-4 hover:bg-gray-650 transition-colors duration-200 
                                            {{ $message->sender_id === auth()->id() ? 'bg-emerald-900/30 border border-emerald-700/50' : '' }}">
                                            <p class="text-white text-sm whitespace-pre-wrap">{{ $message->content }}</p>
                                        </div>
                                        <div class="flex items-center space-x-2 mt-1 text-xs text-gray-500 {{ $message->sender_id === auth()->id() ? 'justify-end' : '' }}">
                                            <span>{{ $message->created_at->diffForHumans() }}</span>
                                            @if($message->sender_id === auth()->id())
                                                @if($message->is_read)
                                                    <i class="fa-solid fa-check-double text-emerald-400" title="Read"></i>
                                                @else
                                                    <i class="fa-solid fa-check text-gray-400" title="Sent"></i>
                                                @endif
                                            @endif
                                        </div>
                                    </div>

                                    @if($message->sender_id === auth()->id())
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-12">
                                <i class="fa-solid fa-comment-slash text-4xl text-gray-600 mb-4"></i>
                                <h3 class="text-lg font-medium text-white mb-2">No messages yet</h3>
                                <p class="text-gray-400">Start the conversation by sending a message below!</p>
                            </div>
                        @endif
                    </div>

                    <!-- Message Form -->
                    <div class="border-t border-gray-700 p-6">
                        <form action="{{ route('messages.store', $user) }}" method="POST" id="message-form">
                            @csrf
                            <div class="flex space-x-4">
                                <div class="flex-1">
                                    <textarea name="content" id="message-content" rows="3" required
                                              placeholder="Type your message... (Max 5000 characters)"
                                              class="w-full px-4 py-3 border border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-gray-700 text-white placeholder-gray-400 resize-none transition-all duration-200"
                                              maxlength="5000"></textarea>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-xs text-gray-500" id="char-count">0/5000</span>
                                        <button type="submit" 
                                                class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
                                                id="send-button">
                                            <i class="fa-solid fa-paper-plane mr-2"></i>
                                            Send
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Pagination -->
                @if($messages->hasPages())
                    <div class="mt-4">
                        {{ $messages->links('pagination::simple-tailwind') }}
                    </div>
                @endif
            </div>

            <!-- User Info Sidebar -->
            <div class="space-y-6">
                <!-- User Card -->
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-6 transition-all duration-300 hover:shadow-xl hover:border-emerald-500/30">
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center text-white text-xl font-bold mx-auto mb-4">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <h3 class="font-semibold text-white mb-2">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-400 mb-4">{{ $user->rank }}</p>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                            <div class="text-center p-2 bg-gray-750 rounded-lg">
                                <div class="font-bold text-white">{{ $user->stats->discussions_count ?? 0 }}</div>
                                <div class="text-gray-400">Discussions</div>
                            </div>
                            <div class="text-center p-2 bg-gray-750 rounded-lg">
                                <div class="font-bold text-white">{{ $user->badges_count ?? 0 }}</div>
                                <div class="text-gray-400">Badges</div>
                            </div>
                        </div>

                        <a href="{{ route('profiles.show', $user) }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-600 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-all duration-300">
                            <i class="fa-solid fa-user mr-2"></i>
                            View Full Profile
                        </a>
                    </div>
                </div>

                <!-- Conversation Info -->
                <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 p-6 transition-all duration-300 hover:shadow-xl hover:border-teal-500/30">
                    <h4 class="text-sm font-semibold text-white mb-4">Conversation Info</h4>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Total Messages</span>
                            <span class="font-semibold text-white">{{ $messages->total() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Conversation Started</span>
                            <span class="text-gray-300">{{ $conversation->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Last Activity</span>
                            <span class="text-gray-300">{{ $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : 'Never' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Messaging Guidelines -->
                <div class="bg-amber-900/30 rounded-2xl border border-amber-700/50 p-4 backdrop-blur-sm">
                    <h4 class="text-sm font-semibold text-amber-300 mb-3">Messaging Guidelines</h4>
                    <ul class="text-xs text-amber-200 space-y-2">
                        <li class="flex items-start">
                            <i class="fa-solid fa-handshake mt-0.5 mr-2 text-amber-400"></i>
                            <span>Be respectful and professional</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-recycle mt-0.5 mr-2 text-amber-400"></i>
                            <span>Focus on waste transformation topics</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-solid fa-ban mt-0.5 mr-2 text-amber-400"></i>
                            <span>No spam or promotional content</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageForm = document.getElementById('message-form');
    const messageContent = document.getElementById('message-content');
    const charCount = document.getElementById('char-count');
    const sendButton = document.getElementById('send-button');
    const messagesContainer = document.getElementById('messages-container');

    // Character count
    messageContent.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = `${length}/5000`;
        
        if (length > 5000) {
            charCount.classList.add('text-red-400');
            sendButton.disabled = true;
        } else {
            charCount.classList.remove('text-red-400');
            sendButton.disabled = length === 0;
        }
    });

    // Auto-resize textarea
    messageContent.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Scroll to bottom of messages
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    scrollToBottom();

    // Handle form submission
    messageForm.addEventListener('submit', function(e) {
        const content = messageContent.value.trim();
        if (content.length === 0 || content.length > 5000) {
            e.preventDefault();
            return;
        }

        sendButton.disabled = true;
        sendButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Sending...';
    });

    // Check for new messages periodically (simple version)
    setInterval(() => {
        // In a real app, you'd use WebSockets or AJAX to check for new messages
        // This is a placeholder for real-time functionality
    }, 30000);
});
</script>
@endsection