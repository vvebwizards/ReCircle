<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Pickup;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Afficher la page de chat
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $pickupId = $request->get('pickup_id');
        $otherUserId = $request->get('user_id');

        // Récupérer les conversations
        $conversations = $this->getConversations($user);

        // Si un pickup est spécifié, récupérer les messages
        $messages = collect();
        $otherUser = null;
        $pickup = null;

        if ($pickupId) {
            $pickup = Pickup::with(['wasteItem.generator', 'courier'])->find($pickupId);
            if ($pickup) {
                // Déterminer l'autre utilisateur
                if ($user->id === $pickup->wasteItem->generator_id) {
                    $otherUser = $pickup->courier;
                } elseif ($pickup->courier_id && $user->id === $pickup->courier_id) {
                    $otherUser = $pickup->wasteItem->generator;
                }

                if ($otherUser) {
                    $messages = ChatMessage::with(['sender', 'receiver'])
                        ->betweenUsers($user->id, $otherUser->id)
                        ->where('pickup_id', $pickupId)
                        ->orderBy('created_at', 'asc')
                        ->get();

                    // Marquer les messages comme lus
                    ChatMessage::where('receiver_id', $user->id)
                        ->where('pickup_id', $pickupId)
                        ->where('is_read', false)
                        ->update(['is_read' => true, 'read_at' => now()]);
                }
            }
        } elseif ($otherUserId) {
            $otherUser = User::find($otherUserId);
            if ($otherUser) {
                $messages = ChatMessage::with(['sender', 'receiver'])
                    ->betweenUsers($user->id, $otherUserId)
                    ->orderBy('created_at', 'asc')
                    ->get();

                // Marquer les messages comme lus
                ChatMessage::where('receiver_id', $user->id)
                    ->where('sender_id', $otherUserId)
                    ->where('is_read', false)
                    ->update(['is_read' => true, 'read_at' => now()]);
            }
        }

        return view('chat.index', compact('conversations', 'messages', 'otherUser', 'pickup', 'user'));
    }

    /**
     * Envoyer un message
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
            'pickup_id' => 'nullable|exists:pickups,id',
            'message_type' => 'in:text,image,file,location',
        ]);

        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'pickup_id' => $request->pickup_id,
            'message' => $request->message,
            'message_type' => $request->message_type ?? 'text',
        ]);

        $message->load(['sender', 'receiver']);

        // Ici on pourrait ajouter une notification WebSocket
        // Pour l'instant, on retourne le message créé

        return response()->json([
            'success' => true,
            'message' => $message,
            'html' => view('chat.partials.message', ['message' => $message, 'currentUserId' => Auth::id()])->render(),
        ]);
    }

    /**
     * Récupérer les messages d'une conversation
     */
    public function getMessages(Request $request): JsonResponse
    {
        $request->validate([
            'other_user_id' => 'required|exists:users,id',
            'pickup_id' => 'nullable|exists:pickups,id',
        ]);

        $messages = ChatMessage::with(['sender', 'receiver'])
            ->betweenUsers(Auth::id(), $request->other_user_id);

        if ($request->pickup_id) {
            $messages->where('pickup_id', $request->pickup_id);
        }

        $messages = $messages->orderBy('created_at', 'asc')->get();

        // Marquer comme lus
        ChatMessage::where('receiver_id', Auth::id())
            ->where('sender_id', $request->other_user_id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Marquer les messages comme lus
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'other_user_id' => 'required|exists:users,id',
            'pickup_id' => 'nullable|exists:pickups,id',
        ]);

        $query = ChatMessage::where('receiver_id', Auth::id())
            ->where('sender_id', $request->other_user_id)
            ->where('is_read', false);

        if ($request->pickup_id) {
            $query->where('pickup_id', $request->pickup_id);
        }

        $updated = $query->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'updated_count' => $updated,
        ]);
    }

    /**
     * Récupérer les conversations de l'utilisateur
     */
    private function getConversations($user)
    {
        // Récupérer TOUS les messages avec chaque utilisateur
        $allMessages = ChatMessage::with(['sender', 'receiver', 'pickup.wasteItem'])
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Grouper par utilisateur ET pickup pour séparer les conversations
        $conversations = $allMessages->groupBy(function ($message) use ($user) {
            $otherUserId = $message->sender_id === $user->id ? $message->receiver_id : $message->sender_id;
            $pickupId = $message->pickup_id ?? 'general';

            return $otherUserId.'_'.$pickupId;
        })
            ->map(function ($messages, $groupKey) use ($user) {
                // Trier par date décroissante pour avoir le plus récent en premier
                $sortedMessages = $messages->sortByDesc('created_at');
                $latestMessage = $sortedMessages->first();

                // Compter les messages non lus
                $unreadCount = $messages->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();

                // Récupérer l'autre utilisateur
                $otherUser = $latestMessage->sender_id === $user->id
                    ? $latestMessage->receiver
                    : $latestMessage->sender;

                // Récupérer le pickup pour cette conversation
                $pickup = $latestMessage->pickup;

                return [
                    'other_user' => $otherUser,
                    'latest_message' => $latestMessage,
                    'unread_count' => $unreadCount,
                    'pickup' => $pickup,
                    'total_messages' => $messages->count(),
                    'last_activity' => $latestMessage->created_at,
                    'group_key' => $groupKey,
                ];
            })
            ->sortByDesc(function ($conversation) {
                return $conversation['last_activity'];
            });

        return $conversations;
    }
}
