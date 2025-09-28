<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('role:admin') or multiple: role:admin,generator
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = auth()->user();
        // If not authenticated just abort (let upstream auth middleware handle redirect/exception)
        if (! $user) {
            return $this->deny($request);
        }

        $allowed = array_map('trim', explode(',', $roles));
        if (! in_array($user->role, $allowed, true)) {
            return $this->deny($request);
        }

        return $next($request);
    }

    private function deny(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        // For web, either redirect to home or show 403
        return abort(403, 'Forbidden');
    }
}
