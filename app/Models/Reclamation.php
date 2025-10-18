<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reclamation extends Model
{
    use HasFactory;

    /**
     * Fillable attributes
     */
    protected $fillable = [
        'user_id',
        'topic',
        'description',
        'status',
        'severity',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // =======================
    // Relationships
    // =======================

    /**
     * The user who created the reclamation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Responses related to this reclamation
     */
    public function responses(): HasMany
    {
        return $this->hasMany(ReclamationResponse::class);
    }

    // =======================
    // Scopes
    // =======================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    // Severity scopes
    public function scopeHighSeverity($query)
    {
        return $query->where('severity', 'high');
    }

    public function scopeMediumSeverity($query)
    {
        return $query->where('severity', 'medium');
    }

    public function scopeLowSeverity($query)
    {
        return $query->where('severity', 'low');
    }

    // =======================
    // Helper Methods
    // =======================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    // Severity helper methods
    public function isHighSeverity(): bool
    {
        return $this->severity === 'high';
    }

    public function isMediumSeverity(): bool
    {
        return $this->severity === 'medium';
    }

    public function isLowSeverity(): bool
    {
        return $this->severity === 'low';
    }

    public function hasResponse(): bool
    {
        return $this->responses()->exists();
    }

    // =======================
    // Attribute Helpers
    // =======================

    /**
     * Get the CSS badge class for the status
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-500 text-white',
            'in_progress' => 'bg-blue-500 text-white',
            'resolved' => 'bg-green-500 text-white',
            'closed' => 'bg-gray-500 text-white',
            default => 'bg-gray-200 text-gray-800',
        };
    }

    /**
     * Get the CSS badge class for the severity
     */
    public function getSeverityBadgeAttribute(): string
    {
        return match ($this->severity) {
            'high' => 'bg-red-500 text-white',
            'medium' => 'bg-orange-500 text-white',
            'low' => 'bg-green-500 text-white',
            default => 'bg-gray-200 text-gray-800',
        };
    }

    /**
     * Get the severity label
     */
    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            'high' => 'High',
            'medium' => 'Medium',
            'low' => 'Low',
            default => 'Unknown',
        };
    }
}
