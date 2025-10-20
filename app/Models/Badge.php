<?php

// app/Models/Badge.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'type',
        'criteria',
        'threshold',
        'is_active',
        'points',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at', 'message')
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'bronze' => 'bg-amber-500',
            'silver' => 'bg-gray-400',
            'gold' => 'bg-yellow-500',
            'platinum' => 'bg-blue-400',
            default => 'bg-gray-500',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return ucfirst($this->type);
    }
}
