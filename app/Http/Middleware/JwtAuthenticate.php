<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class JwtAuthenticate
{
    public function __construct(private JwtService $jwt) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);
        if (! $token) {
            throw new UnauthorizedHttpException('Bearer', 'Missing token');
        }
        try {
            $decoded = $this->jwt->decode($token);
        } catch (\Throwable $e) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid token');
        }

        if (! isset($decoded->sub) || ! $user = User::find($decoded->sub)) {
            throw new UnauthorizedHttpException('Bearer', 'User not found');
        }

        // Set the user on the request (no session)
        auth()->setUser($user);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        // 1. Authorization: Bearer
        $auth = $request->header('Authorization');
        if ($auth && str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        // 2. HttpOnly cookie
        $cookieName = config('jwt.cookie');

        return $request->cookie($cookieName);
    }
}
