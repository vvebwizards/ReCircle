<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    public function __construct(private JwtService $jwt) {}

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        $issued = $this->jwt->issue($user);

        return $this->tokenResponse($issued['token'], $issued['expires_at']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $token = $request->cookie(config('jwt.cookie'));
        if (! $token) {
            return response()->json(['message' => 'Missing token'], 401);
        }
        try {
            $decoded = $this->jwt->decode($token);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }
        $refreshed = $this->jwt->refresh($decoded);

        return $this->tokenResponse($refreshed['token'], $refreshed['expires_at']);
    }

    public function logout(): JsonResponse
    {
        return $this->forgetToken();
    }

    private function tokenResponse(string $jwt, string $expiresAt): JsonResponse
    {
        $minutes = (int) config('jwt.ttl');

        return response()->json([
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
        ])->withCookie(cookie(
            name: config('jwt.cookie'),
            value: $jwt,
            minutes: $minutes,
            path: '/',
            domain: null,
            secure: true,
            httpOnly: true,
            sameSite: 'lax'
        ));
    }

    private function forgetToken(): JsonResponse
    {
        return response()->json(['message' => 'Logged out'])->withCookie(cookie(
            name: config('jwt.cookie'),
            value: '',
            minutes: -1,
            path: '/',
            domain: null,
            secure: true,
            httpOnly: true,
            sameSite: 'lax'
        ));
    }
}
