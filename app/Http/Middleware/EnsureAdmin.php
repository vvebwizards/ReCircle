<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     * Only allows users with the ADMIN role to proceed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || $user->role !== UserRole::ADMIN) {
            // If the request expects JSON, return 403 JSON response
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden. Admins only.'], 403);
            }

            // Otherwise render the access denied view (generic)
            return response()->view('errors.admin_forbidden', [], 403);
        }

        return $next($request);
    }
}
