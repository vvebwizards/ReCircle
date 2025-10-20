<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || Auth::user()->role !== UserRole::ADMIN) {
            // For API/JSON requests return 403 JSON
            if ($request->expectsJson()) {
                abort(403, 'Access denied. Admin privileges required.');
            }

            // For web requests return a friendly view
            return response()->view('errors.admin_forbidden', [], 403);
        }

        return $next($request);
    }
}
