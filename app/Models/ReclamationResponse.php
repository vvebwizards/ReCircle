<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReclamationResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'reclamation_id',
        'admin_id',
        'response_message',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function reclamation(): BelongsTo
    {
        return $this->belongsTo(Reclamation::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Helper methods
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-500 text-white',
            'resolved' => 'bg-green-500 text-white',
            'rejected' => 'bg-red-500 text-white',
            default => 'bg-gray-200 text-gray-800',
        };
    }
}
