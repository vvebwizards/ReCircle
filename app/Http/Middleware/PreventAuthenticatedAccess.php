<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventAuthenticatedAccess
{
    /**
     * If the user is authenticated, keep them on the current page by redirecting back.
     * If there's no previous URL, redirect to their role-specific dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If Laravel's Auth already knows the user, proceed to redirect back.
        if (! Auth::check()) {
            // Try to detect a JWT token (Bearer header or configured cookie) and set the user
            $token = null;
            $authHeader = $request->header('Authorization');
            if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
                $token = substr($authHeader, 7);
            } else {
                $cookieName = config('jwt.cookie');
                $token = $request->cookie($cookieName);
            }

            if ($token) {
                try {
                    $jwt = app(JwtService::class)->decode($token);
                    if (isset($jwt->sub) && $user = User::find($jwt->sub)) {
                        auth()->setUser($user);
                    }
                } catch (\Throwable $e) {
                    // ignore invalid token and treat as guest
                }
            }
        }

        if (Auth::check()) {
            // If this is an API/JSON request, return 403 JSON
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden.'], 403);
            }

            // Avoid redirect loop if the user is already on their dashboard
            $route = $request->route();
            if ($route && $route->getName() === 'dashboard') {
                return $next($request);
            }

            // For web requests, redirect authenticated users to their dashboard
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
