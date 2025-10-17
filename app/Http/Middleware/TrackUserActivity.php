<?php

// app/Http/Middleware/TrackUserActivity.php

namespace App\Http\Middleware;

use App\Services\BadgeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function __construct(private BadgeService $badgeService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Track activity for authenticated users
        if (auth()->check()) {
            $this->badgeService->updateUserStats(auth()->user(), 'daily_activity');
            $this->badgeService->checkAndAwardBadges(auth()->user(), 'daily_activity');
        }

        return $response;
    }
}
