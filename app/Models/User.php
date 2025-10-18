<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'role',
        'password',
        'avatar',
        'onboarding_completed',
        'blocked_at',
        'block_reason',
        'blocked_by',
        'failed_login_attempts',
        'locked_until',
        'is_facial_registered',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'bool',
            'two_factor_confirmed_at' => 'datetime',
            'role' => UserRole::class,
            'blocked_at' => 'datetime',
            'locked_until' => 'datetime',
            'is_facial_registered' => 'bool',
        ];
    }

    // Check if user is currently blocked
    public function isBlocked(): bool
    {
        return ! is_null($this->blocked_at);
    }

    // Check if user is temporarily locked due to failed login attempts
    public function isLockedOut(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    // Increment failed login attempts
    public function incrementFailedLoginAttempts(): void
    {
        $this->increment('failed_login_attempts');
        $this->save();
    }

    // Reset failed login attempts
    public function resetFailedLoginAttempts(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    // Lock user for specified duration (in minutes)
    public function lockForDuration(int $minutes): void
    {
        $this->update([
            'locked_until' => now()->addMinutes($minutes),
        ]);
    }

    // Check if facial recognition fallback should be triggered
    public function shouldTriggerFacialFallback(): bool
    {
        $maxAttempts = config('auth.max_failed_attempts', 3);

        return $this->failed_login_attempts >= $maxAttempts && $this->is_facial_registered;
    }

    // Scope for blocked users
    public function scopeBlocked($query)
    {
        return $query->whereNotNull('blocked_at');
    }

    // Scope for active (non-blocked) users
    public function scopeActive($query)
    {
        return $query->whereNull('blocked_at');
    }

    // Relationships
    public function wasteItems(): HasMany
    {
        return $this->hasMany(WasteItem::class, 'generator_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'maker_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'maker_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchModel::class, 'maker_id');
    }

    public function pickups(): HasMany
    {
        return $this->hasMany(Pickup::class, 'courier_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class, 'maker_id');
    }

    // Relationship to the admin who blocked this user
    public function blockedByUser()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at', 'message')
            ->withTimestamps()
            ->orderBy('user_badges.earned_at', 'desc');
    }

    public function stats()
    {
        return $this->hasOne(UserStat::class);
    }

    // Helper methods for badges
    public function hasBadge($badgeId): bool
    {
        return $this->badges->contains('id', $badgeId);
    }

    public function getPointsAttribute()
    {
        return $this->stats?->total_points ?? 0;
    }

    public function getLevelAttribute()
    {
        return $this->stats?->level ?? 1;
    }

    // Get user's rank based on points
    public function getRankAttribute()
    {
        if (! $this->stats) {
            return 'Newcomer';
        }

        $points = $this->stats->total_points;

        return match (true) {
            $points >= 5000 => 'Eco Champion',
            $points >= 2500 => 'Sustainability Expert',
            $points >= 1000 => 'Circular Economy Advocate',
            $points >= 500 => 'Waste Warrior',
            $points >= 100 => 'Eco Enthusiast',
            default => 'Newcomer',
        };
    }

    public function deliveriesAsCourier()
    {
        return $this->hasMany(\App\Models\Delivery::class, 'courier_id');
    }

    /**
     * Notifications reçues par cet utilisateur
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    /**
     * Notifications non lues
     */
    public function unreadNotifications(): HasMany
    {
        return $this->notifications()->whereNull('read_at');
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    public function reclamations(): HasMany
    {
        return $this->hasMany(Reclamation::class);
    }

    public function reclamationResponses(): HasMany
    {
        return $this->hasMany(ReclamationResponse::class, 'admin_id');
    }
}
