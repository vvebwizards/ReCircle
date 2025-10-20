<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'notifiable_id',
        'notifiable_type',
        'type',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur (admin)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notifiable_id');
    }

    /**
     * Relation avec le pickup
     */
    public function pickup(): BelongsTo
    {
        return $this->belongsTo(Pickup::class, 'data->pickup_id');
    }

    /**
     * Vérifier si la notification a été lue
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Marquer comme lue
     */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Marquer comme non lue
     */
    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }
}
