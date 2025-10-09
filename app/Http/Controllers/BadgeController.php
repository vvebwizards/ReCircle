<?php

// app/Http/Controllers/BadgeController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function index(): View
    {
        $badges = \App\Models\Badge::withCount('users')
            ->active()
            ->orderBy('type')
            ->orderBy('threshold')
            ->get()
            ->groupBy('type');

        return view('badges.index', compact('badges'));
    }

    public function showUserBadges(User $user): View
    {
        $badges = $user->badges()
            ->orderBy('user_badges.earned_at', 'desc')
            ->get()
            ->groupBy('type');

        $stats = $user->stats;
        $nextBadges = $this->getNextAvailableBadges($user);

        return view('badges.user-profile', compact('user', 'badges', 'stats', 'nextBadges'));
    }

    public function leaderboard(): View
    {
        // FIXED: Properly join with user_stats table
        $topUsers = User::whereHas('stats')
            ->with('stats')
            ->withCount('badges')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id') // Add this join
            ->orderByDesc('user_stats.total_points') // Order by the actual column
            ->select('users.*') // Select users columns
            ->take(20)
            ->get();

        return view('badges.leaderboard', compact('topUsers'));
    }

    private function getNextAvailableBadges(User $user): array
    {
        $earnedBadgeIds = $user->badges->pluck('id');
        $stats = $user->stats;

        $nextBadges = \App\Models\Badge::active()
            ->whereNotIn('id', $earnedBadgeIds)
            ->get()
            ->filter(function ($badge) use ($stats) {
                return match ($badge->criteria) {
                    'discussion_count' => $stats->discussions_count < $badge->threshold,
                    'reply_count' => $stats->replies_count < $badge->threshold,
                    'solution_count' => $stats->solutions_provided < $badge->threshold,
                    'likes_received' => $stats->likes_received < $badge->threshold,
                    'days_active' => $stats->days_active < $badge->threshold,
                    'streak' => $stats->current_streak < $badge->threshold,
                    'total_points' => $stats->total_points < $badge->threshold,
                    default => true,
                };
            })
            ->sortBy('threshold')
            ->take(5);

        return $nextBadges->map(function ($badge) use ($stats) {
            $progress = match ($badge->criteria) {
                'discussion_count' => min(100, ($stats->discussions_count / $badge->threshold) * 100),
                'reply_count' => min(100, ($stats->replies_count / $badge->threshold) * 100),
                'solution_count' => min(100, ($stats->solutions_provided / $badge->threshold) * 100),
                'likes_received' => min(100, ($stats->likes_received / $badge->threshold) * 100),
                'days_active' => min(100, ($stats->days_active / $badge->threshold) * 100),
                'streak' => min(100, ($stats->current_streak / $badge->threshold) * 100),
                'total_points' => min(100, ($stats->total_points / $badge->threshold) * 100),
                default => 0,
            };

            return [
                'badge' => $badge,
                'progress' => $progress,
                'current' => match ($badge->criteria) {
                    'discussion_count' => $stats->discussions_count,
                    'reply_count' => $stats->replies_count,
                    'solution_count' => $stats->solutions_provided,
                    'likes_received' => $stats->likes_received,
                    'days_active' => $stats->days_active,
                    'streak' => $stats->current_streak,
                    'total_points' => $stats->total_points,
                    default => 0,
                },
                'remaining' => max(0, $badge->threshold - match ($badge->criteria) {
                    'discussion_count' => $stats->discussions_count,
                    'reply_count' => $stats->replies_count,
                    'solution_count' => $stats->solutions_provided,
                    'likes_received' => $stats->likes_received,
                    'days_active' => $stats->days_active,
                    'streak' => $stats->current_streak,
                    'total_points' => $stats->total_points,
                    default => 0,
                }),
            ];
        })->toArray();
    }
}
