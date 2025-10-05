<?php

// app/Services/BadgeService.php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use App\Models\UserStat;
use Illuminate\Support\Facades\DB;

class BadgeService
{
    public function checkAndAwardBadges(User $user, string $action, array $data = []): void
    {
        $badges = Badge::active()->get();

        foreach ($badges as $badge) {
            if ($this->qualifiesForBadge($user, $badge, $action, $data)) {
                $this->awardBadge($user, $badge, $data);
            }
        }
    }

    private function qualifiesForBadge(User $user, Badge $badge, string $action, array $data): bool
    {
        // Don't award if user already has this badge
        if ($user->hasBadge($badge->id)) {
            return false;
        }

        $stats = $user->stats ?? UserStat::create(['user_id' => $user->id]);

        return match ($badge->criteria) {
            'first_discussion' => $action === 'discussion_created' && $stats->discussions_count >= $badge->threshold,
            'discussion_count' => $action === 'discussion_created' && $stats->discussions_count >= $badge->threshold,
            'reply_count' => $action === 'reply_created' && $stats->replies_count >= $badge->threshold,
            'solution_count' => $action === 'solution_marked' && $stats->solutions_provided >= $badge->threshold,
            'likes_received' => $action === 'like_received' && $stats->likes_received >= $badge->threshold,
            'days_active' => $this->checkDailyActivity($user) && $stats->days_active >= $badge->threshold,
            'streak' => $stats->current_streak >= $badge->threshold,
            'total_points' => $stats->total_points >= $badge->threshold,
            default => false,
        };
    }

    private function awardBadge(User $user, Badge $badge, array $data): void
    {
        DB::transaction(function () use ($user, $badge, $data) {
            $user->badges()->attach($badge->id, [
                'earned_at' => now(),
                'message' => $this->getBadgeMessage($badge, $data),
            ]);

            // Update user points
            if ($user->stats) {
                $user->stats->increment('total_points', $badge->points);
            }

            // You can add notification logic here
            // Notification::send($user, new BadgeEarnedNotification($badge));
        });
    }

    private function getBadgeMessage(Badge $badge, array $data): string
    {
        return match ($badge->criteria) {
            'first_discussion' => 'Started your first discussion in the community!',
            'discussion_count' => "Created {$badge->threshold} discussions!",
            'reply_count' => "Posted {$badge->threshold} helpful replies!",
            'solution_count' => "Provided {$badge->threshold} solutions to community questions!",
            'likes_received' => "Received {$badge->threshold} likes from the community!",
            'days_active' => "Active in the community for {$badge->threshold} days!",
            'streak' => "Maintained a {$badge->threshold}-day activity streak!",
            'total_points' => "Reached {$badge->threshold} community points!",
            default => 'Earned a new achievement!',
        };
    }

    private function checkDailyActivity(User $user): bool
    {
        $stats = $user->stats;

        if (! $stats->last_activity_at || ! $stats->last_activity_at->isToday()) {
            $stats->increment('days_active');

            // Check streak
            if ($stats->last_activity_at && $stats->last_activity_at->isYesterday()) {
                $stats->increment('current_streak');
                $stats->longest_streak = max($stats->longest_streak, $stats->current_streak);
            } else {
                $stats->current_streak = 1;
            }

            $stats->last_activity_at = now();
            $stats->save();

            return true;
        }

        return false;
    }

    // Update user stats for various actions
    public function updateUserStats(User $user, string $action): void
    {
        $stats = $user->stats ?? UserStat::create(['user_id' => $user->id]);

        switch ($action) {
            case 'discussion_created':
                $stats->increment('discussions_count');
                $stats->increment('total_points', 10); // 10 points per discussion
                break;

            case 'reply_created':
                $stats->increment('replies_count');
                $stats->increment('total_points', 5); // 5 points per reply
                break;

            case 'solution_marked':
                $stats->increment('solutions_provided');
                $stats->increment('total_points', 25); // 25 points per solution
                break;

            case 'like_received':
                $stats->increment('likes_received');
                $stats->increment('total_points', 2); // 2 points per like received
                break;

            case 'daily_activity':
                $this->checkDailyActivity($user);
                break;
        }

        $stats->save();
    }
}
