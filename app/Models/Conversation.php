<?php
// app/Models/Conversation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'last_message_id',
        'unread_count_user_one',
        'unread_count_user_two',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    // Relationships
    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    public function messages()
    {
        return Message::betweenUsers($this->userOne, $this->userTwo);
    }

    // Helper methods
    public function getOtherUser(User $user): User
    {
        return $this->user_one_id === $user->id ? $this->userTwo : $this->userOne;
    }

    public function getUnreadCountForUser(User $user): int
    {
        if ($this->user_one_id === $user->id) {
            return $this->unread_count_user_one;
        } elseif ($this->user_two_id === $user->id) {
            return $this->unread_count_user_two;
        }

        return 0;
    }

    public function markAsReadForUser(User $user): void
    {
        if ($this->user_one_id === $user->id) {
            $this->update(['unread_count_user_one' => 0]);
        } elseif ($this->user_two_id === $user->id) {
            $this->update(['unread_count_user_two' => 0]);
        }

        // Mark all messages as read
        $this->messages()
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function incrementUnreadCount(User $receiver): void
    {
        if ($this->user_one_id === $receiver->id) {
            $this->increment('unread_count_user_one');
        } elseif ($this->user_two_id === $receiver->id) {
            $this->increment('unread_count_user_two');
        }
    }
}