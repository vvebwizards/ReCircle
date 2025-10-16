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
        'user_id',
        'message',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Helper methods to determine the author
    public function isFromAdmin(): bool
    {
        return ! is_null($this->admin_id);
    }

    public function isFromUser(): bool
    {
        return ! is_null($this->user_id);
    }

    public function getAuthorAttribute()
    {
        return $this->isFromAdmin() ? $this->admin : $this->user;
    }

    public function getAuthorTypeAttribute(): string
    {
        return $this->isFromAdmin() ? 'admin' : 'user';
    }
}
