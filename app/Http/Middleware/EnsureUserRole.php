<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (Auth::check()) {
            $userRole = Auth::user()->role;

            if ($userRole->value === $role) {
                return $next($request);
            }
        }

        // For JSON/API requests, return JSON 403
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }

        // For web requests, show the access denied view we created
        return response()->view('errors.admin_forbidden', [], 403);
    }
}
