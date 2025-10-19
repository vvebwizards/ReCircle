@extends('layouts.app')

@section('content')
<style>
    .chat-container {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
        min-height: 100vh;
        padding: 8rem 0 2rem 0;
    }
    
    .chat-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 1rem;
        height: 80vh;
    }
    
    .conversations-sidebar {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        padding: 1.5rem;
        overflow-y: auto;
    }
    
    .chat-main {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .chat-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(135deg, #059669, #047857);
        color: white;
    }
    
    .chat-messages {
        flex: 1;
        padding: 1rem;
        overflow-y: auto;
        background: #f8fafc;
    }
    
    .chat-input {
        padding: 1rem;
        border-top: 1px solid #e5e7eb;
        background: white;
    }
    
    .conversation-item {
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 0.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid transparent;
        user-select: none;
        position: relative;
    }
    
    .conversation-item:hover {
        background: #f1f5f9;
        border-color: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .conversation-item.active {
        background: #059669;
        color: white;
    }
    
    .conversation-item:active {
        transform: translateY(0);
    }
    
    .btn-start-conversation {
        background: linear-gradient(135deg, #059669, #047857);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-start-conversation:hover {
        background: linear-gradient(135deg, #047857, #065f46);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
    }
    
    .btn-new-conversation {
        background: linear-gradient(135deg, #059669, #047857);
        color: white;
        border: none;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .btn-new-conversation:hover {
        background: linear-gradient(135deg, #047857, #065f46);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);
    }
    
    .message {
        margin-bottom: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .message.sent {
        flex-direction: row-reverse;
    }
    
    .message-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #059669, #047857);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        flex-shrink: 0;
    }
    
    .message-content {
        max-width: 70%;
        padding: 0.75rem 1rem;
        border-radius: 15px;
        position: relative;
    }
    
    .message.sent .message-content {
        background: #059669;
        color: white;
        border-bottom-right-radius: 5px;
    }
    
    .message.received .message-content {
        background: white;
        color: #374151;
        border: 1px solid #e5e7eb;
        border-bottom-left-radius: 5px;
    }
    
    .message-time {
        font-size: 0.75rem;
        opacity: 0.7;
        margin-top: 0.25rem;
    }
    
    .unread-badge {
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: bold;
    }
    
    .chat-input-form {
        display: flex;
        gap: 0.75rem;
        align-items: flex-end;
    }
    
    .message-input {
        flex: 1;
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 25px;
        resize: none;
        max-height: 100px;
        font-family: inherit;
    }
    
    .message-input:focus {
        outline: none;
        border-color: #059669;
    }
    
    .send-button {
        background: #059669;
        color: white;
        border: none;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .send-button:hover {
        background: #047857;
        transform: scale(1.05);
    }
    
    .empty-chat {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #6b7280;
        text-align: center;
    }
    
    .empty-chat i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .pickup-info {
        background: #f1f5f9;
        padding: 0.75rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-size: 0.875rem;
    }
    
    .typing-indicator {
        display: none;
        padding: 0.75rem 1rem;
        color: #6b7280;
        font-style: italic;
    }
    
    @media (max-width: 768px) {
        .chat-wrapper {
            grid-template-columns: 1fr;
            height: auto;
        }
        
        .conversations-sidebar {
            max-height: 200px;
        }
    }
</style>

<div class="chat-container">
    <div class="chat-wrapper">
        <!-- Sidebar des conversations -->
        <div class="conversations-sidebar">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fa-solid fa-comments mr-2"></i>
                    Conversations
                </h3>
                <div class="flex items-center gap-2">
                    <button onclick="refreshConversations()" class="text-sm text-blue-600 hover:text-blue-800">
                        <i class="fa-solid fa-refresh mr-1"></i>
                        Actualiser
                    </button>
                </div>
            </div>
            
            @if($conversations->count() > 0)
                @foreach($conversations as $conversation)
                    <div class="conversation-item" 
                         data-user-id="{{ $conversation['other_user']->id }}"
                         data-pickup-id="{{ $conversation['pickup']?->id }}"
                         onclick="handleConversationClick(this)"
                         style="cursor: pointer;">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="message-avatar">
                                    {{ substr($conversation['other_user']->name, 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">{{ $conversation['other_user']->name }}</div>
                                    <div class="text-sm text-gray-600">
                                        {{ Str::limit($conversation['latest_message']->message, 40) }}
                                    </div>
                                    @if($conversation['pickup'])
                                        <div class="text-xs text-blue-600 mt-1">
                                            <i class="fa-solid fa-truck mr-1"></i>
                                            Pickup #{{ $conversation['pickup']->id }}
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500 mt-1">
                                            <i class="fa-solid fa-comment mr-1"></i>
                                            Conversation générale
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                @if($conversation['unread_count'] > 0)
                                    <div class="unread-badge">{{ $conversation['unread_count'] }}</div>
                                @endif
                                <div class="text-xs text-gray-500">
                                    {{ $conversation['latest_message']->created_at->diffForHumans() }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ $conversation['total_messages'] }} msg
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center text-gray-500 py-8">
                    <i class="fa-solid fa-comment-slash text-3xl mb-2"></i>
                    <p>Aucune conversation</p>
                    <div class="mt-4">
                        <button onclick="showStartConversationForm()" class="btn-start-conversation">
                            <i class="fa-solid fa-plus mr-2"></i>
                            Démarrer une conversation
                        </button>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Zone de chat principale -->
        <div class="chat-main">
            @if($otherUser && $pickup)
                <!-- En-tête du chat -->
                <div class="chat-header">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="message-avatar">
                                {{ substr($otherUser->name, 0, 1) }}
                            </div>
                            <div>
                                <h4 class="font-semibold">{{ $otherUser->name }}</h4>
                                <p class="text-sm opacity-90">{{ $otherUser->email }}</p>
                            </div>
                        </div>
                        <div class="text-sm opacity-90">
                            <i class="fa-solid fa-truck mr-1"></i>
                            Pickup #{{ $pickup->id }}
                        </div>
                    </div>
                </div>
                
                <!-- Informations du pickup -->
                <div class="pickup-info">
                    <div class="flex items-center justify-between">
                        <div>
                            <strong>Pickup #{{ $pickup->id }}</strong>
                            <p class="text-sm">{{ $pickup->pickup_address }}</p>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                {{ ucfirst($pickup->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Messages -->
                <div class="chat-messages" id="chat-messages">
                    @foreach($messages as $message)
                        @include('chat.partials.message', ['message' => $message, 'currentUserId' => $user->id])
                    @endforeach
                </div>
                
                <!-- Indicateur de frappe -->
                <div class="typing-indicator" id="typing-indicator">
                    <i class="fa-solid fa-circle-notch fa-spin mr-2"></i>
                    {{ $otherUser->name }} est en train d'écrire...
                </div>
                
                <!-- Zone de saisie -->
                <div class="chat-input">
                    <form class="chat-input-form" id="message-form">
                        <input type="hidden" name="receiver_id" value="{{ $otherUser->id }}">
                        <input type="hidden" name="pickup_id" value="{{ $pickup->id }}">
                        <textarea 
                            name="message" 
                            class="message-input" 
                            placeholder="Tapez votre message..."
                            rows="1"
                            id="message-input"></textarea>
                        <button type="submit" class="send-button">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            @else
                <!-- État vide -->
                <div class="empty-chat">
                    <i class="fa-solid fa-comments"></i>
                    <h3>Sélectionnez une conversation</h3>
                    <p>Choisissez une conversation dans la barre latérale pour commencer à discuter</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: white;
        border-radius: 15px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }
    
    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-header h3 {
        margin: 0;
        color: #1e293b;
        font-size: 1.25rem;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #6b7280;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #374151;
    }
    
    .form-select, .form-textarea {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: border-color 0.3s ease;
    }
    
    .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #059669;
    }
    
    .form-textarea {
        resize: vertical;
        min-height: 80px;
    }
    
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
    }
    
    .btn-cancel {
        background: #6b7280;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
    }
    
    .btn-submit {
        background: #059669;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
    }
    
    .btn-cancel:hover {
        background: #4b5563;
    }
    
    .btn-submit:hover {
        background: #047857;
    }
</style>

<script>
// Fonction simple pour gérer les clics sur les conversations
function handleConversationClick(element) {
    const userId = element.dataset.userId;
    const pickupId = element.dataset.pickupId;
    
    console.log('Conversation cliquée - User ID:', userId, 'Pickup ID:', pickupId);
    
    // Mettre à jour l'URL
    const url = new URL(window.location);
    url.searchParams.set('user_id', userId);
    if (pickupId) {
        url.searchParams.set('pickup_id', pickupId);
    }
    
    // Rediriger vers la nouvelle URL
    window.location.href = url.toString();
}

// Fonction pour actualiser les conversations
function refreshConversations() {
    console.log('Actualisation des conversations...');
    window.location.reload();
}

// Fonction pour démarrer une nouvelle conversation
function startNewConversation(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    const userId = formData.get('user_id');
    const pickupId = formData.get('pickup_id');
    const message = formData.get('message');
    
    if (!userId || !message) {
        alert('Veuillez sélectionner un utilisateur et écrire un message');
        return;
    }
    
    // Envoyer le premier message
    fetch('{{ route("chat.send") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            receiver_id: userId,
            pickup_id: pickupId || null,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fermer le modal
            closeStartConversationForm();
            
            // Rediriger vers la conversation
            const url = new URL(window.location);
            url.searchParams.set('user_id', userId);
            if (pickupId) {
                url.searchParams.set('pickup_id', pickupId);
            }
            window.location.href = url.toString();
        } else {
            alert('Erreur lors de la création de la conversation');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la création de la conversation');
    });
}

// Fonction pour gérer l'envoi de messages
function handleMessageSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const message = formData.get('message').trim();
    
    if (!message) return;
    
    const chatMessages = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    
    // Afficher le message immédiatement
    const messageHtml = `
        <div class="message sent">
            <div class="message-avatar">{{ substr($user->name, 0, 1) }}</div>
            <div class="message-content">
                <div>${message}</div>
                <div class="message-time">Maintenant</div>
            </div>
        </div>
    `;
    
    if (chatMessages) {
        chatMessages.insertAdjacentHTML('beforeend', messageHtml);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Vider le champ
    if (messageInput) {
        messageInput.value = '';
        messageInput.style.height = 'auto';
    }
    
    // Envoyer au serveur
    fetch('{{ route("chat.send") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            receiver_id: formData.get('receiver_id'),
            pickup_id: formData.get('pickup_id'),
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const lastMessage = chatMessages.lastElementChild;
            if (lastMessage) {
                lastMessage.outerHTML = data.html;
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

// Fonction pour auto-resize du textarea
function handleTextareaResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
}

// Initialisation quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    console.log('Chat JavaScript initialisé');
    
    // Auto-scroll vers le bas
    const chatMessages = document.getElementById('chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Auto-resize du textarea
    const messageInput = document.getElementById('message-input');
    if (messageInput) {
        messageInput.addEventListener('input', function() {
            handleTextareaResize(this);
        });
    }
    
    // Gestion du formulaire de message
    const messageForm = document.getElementById('message-form');
    if (messageForm) {
        messageForm.addEventListener('submit', handleMessageSubmit);
    }
    
    // Gestion du formulaire de démarrage de conversation
    const startConversationForm = document.getElementById('startConversationForm');
    if (startConversationForm) {
        startConversationForm.addEventListener('submit', startNewConversation);
    }
});
</script>
@endsection
