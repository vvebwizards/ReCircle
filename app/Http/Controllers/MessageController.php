<?php

// app/Http/Controllers/MessageController.php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        $conversations = Conversation::with(['lastMessage', 'userOne', 'userTwo'])
            ->where('user_one_id', auth()->id())
            ->orWhere('user_two_id', auth()->id())
            ->orderByDesc('last_message_at')
            ->paginate(20);

        // Mark all conversations as read for notifications
        foreach ($conversations as $conversation) {
            if ($conversation->getUnreadCountForUser(auth()->user()) > 0) {
                $conversation->markAsReadForUser(auth()->user());
            }
        }

        return view('messages.index', compact('conversations'));
    }

    public function show(User $user): View
    {
        $conversation = auth()->user()->getConversationWith($user);

        if (! $conversation) {
            // Create a new conversation if it doesn't exist
            $conversation = Conversation::create([
                'user_one_id' => min(auth()->id(), $user->id),
                'user_two_id' => max(auth()->id(), $user->id),
                'last_message_at' => now(),
            ]);
        }

        // Mark messages as read
        $conversation->markAsReadForUser(auth()->user());

        $messages = $conversation->messages()
            ->with(['sender', 'receiver'])
            ->latest()
            ->paginate(20);

        return view('messages.conversation', compact('user', 'conversation', 'messages'));
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string|min:1|max:5000',
            'parent_id' => 'nullable|exists:messages,id',
        ]);

        // Check if users can message each other
        if (! auth()->user()->canMessage($user)) {
            return back()->with('error', 'You cannot message this user.');
        }

        $conversation = auth()->user()->getConversationWith($user);

        if (! $conversation) {
            $conversation = Conversation::create([
                'user_one_id' => min(auth()->id(), $user->id),
                'user_two_id' => max(auth()->id(), $user->id),
            ]);
        }

        // Create the message
        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        // Update conversation
        $conversation->update([
            'last_message_id' => $message->id,
            'last_message_at' => now(),
        ]);

        // Increment unread count for receiver
        $conversation->incrementUnreadCount($user);

        return back()->with('success', 'Message sent!');
    }

    public function destroy(Message $message): RedirectResponse
    {
        if ($message->sender_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $message->delete();

        return back()->with('success', 'Message deleted!');
    }

    public function markAsRead(Message $message): JsonResponse
    {
        if ($message->receiver_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $message->markAsRead();

        return response()->json(['success' => true]);
    }

    // AJAX endpoint for checking new messages
    public function checkNewMessages(): JsonResponse
    {
        $unreadCount = auth()->user()->unreadMessagesCount();
        $conversationsWithUnread = Conversation::where(function ($query) {
            $query->where('user_one_id', auth()->id())
                ->orWhere('user_two_id', auth()->id());
        })->where(function ($query) {
            $query->where('unread_count_user_one', '>', 0)
                ->orWhere('unread_count_user_two', '>', 0);
        })->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'conversations_with_unread' => $conversationsWithUnread,
        ]);
    }
}
