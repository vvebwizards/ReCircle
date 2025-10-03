<?php

// app/Http/Middleware/CheckBlockedUser.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isBlocked()) {
            auth()->logout();

            return response()->view('auth.blocked', [
                'message' => 'Your account has been blocked. Please contact administrator.',
            ], 403);
        }

        return $next($request);
    }
}
