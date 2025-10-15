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

        abort(403, 'Access denied.');
    }
}
