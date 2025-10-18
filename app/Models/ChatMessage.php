<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'pickup_id',
        'message',
        'message_type',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Relation avec l'expéditeur
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Relation avec le destinataire
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Relation avec le pickup
     */
    public function pickup(): BelongsTo
    {
        return $this->belongsTo(Pickup::class);
    }

    /**
     * Marquer le message comme lu
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Scope pour les messages non lus
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope pour les messages entre deux utilisateurs
     */
    public function scopeBetweenUsers($query, $userId1, $userId2)
    {
        return $query->where(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId1)->where('receiver_id', $userId2)
                ->orWhere('sender_id', $userId2)->where('receiver_id', $userId1);
        });
    }
}
