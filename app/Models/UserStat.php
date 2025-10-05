<?php

// app/Models/UserStat.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'discussions_count',
        'replies_count',
        'likes_received',
        'solutions_provided',
        'days_active',
        'total_points',
        'current_streak',
        'longest_streak',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Calculate user level based on total points
    public function getLevelAttribute(): int
    {
        return (int) floor(sqrt($this->total_points / 100)) + 1;
    }

    // Calculate points needed for next level
    public function getPointsToNextLevelAttribute(): int
    {
        $nextLevel = $this->level + 1;

        return (int) (pow($nextLevel - 1, 2) * 100) - $this->total_points;
    }

    // Calculate level progress percentage
    public function getLevelProgressAttribute(): float
    {
        $currentLevelPoints = pow($this->level - 1, 2) * 100;
        $nextLevelPoints = pow($this->level, 2) * 100;
        $pointsInLevel = $this->total_points - $currentLevelPoints;
        $totalPointsInLevel = $nextLevelPoints - $currentLevelPoints;

        return min(100, ($pointsInLevel / $totalPointsInLevel) * 100);
    }
}
