<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use App\Services\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    public function __construct(private JwtService $jwt) {}

    public function login(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'twofa_code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        // Enforce 2FA when enabled
        if ($user->two_factor_enabled) {
            $code = trim((string) ($data['twofa_code'] ?? ''));
            $recovery = trim((string) ($data['recovery_code'] ?? ''));
            $verified = false;
            if ($code !== '' && $user->two_factor_secret) {
                $verified = $twoFactor->verify($user->two_factor_secret, $code);
            }
            if (! $verified && $recovery !== '' && $user->two_factor_recovery_codes) {
                $codes = json_decode($user->two_factor_recovery_codes, true) ?: [];
                $idx = array_search(strtoupper($recovery), array_map('strtoupper', $codes), true);
                if ($idx !== false) {
                    $verified = true;
                    array_splice($codes, (int) $idx, 1); // one-time use
                    $user->two_factor_recovery_codes = json_encode(array_values($codes));
                    $user->save();
                }
            }

            if (! $verified) {
                // If the client supplied a code (or recovery) but it failed, return 422 to allow showing an inline error.
                if ($code !== '' || $recovery !== '') {
                    return response()->json([
                        'invalid_twofa' => true,
                        'message' => 'Invalid two-factor code',
                    ], 422);
                }

                // Otherwise signal that 2FA is required and the client should prompt for a code.
                return response()->json([
                    'requires_twofa' => true,
                    'message' => 'Two-factor authentication required',
                ], 403);
            }
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

        $secure = app()->environment('production');

        return response()->json([
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
        ])->withCookie(cookie(
            name: config('jwt.cookie'),
            value: $jwt,
            minutes: $minutes,
            path: '/',
            domain: null,
            secure: $secure,
            httpOnly: true,
            sameSite: 'lax'
        ));
    }

    private function forgetToken(): JsonResponse
    {
        $secure = app()->environment('production');

        return response()->json(['message' => 'Logged out'])->withCookie(cookie(
            name: config('jwt.cookie'),
            value: '',
            minutes: -1,
            path: '/',
            domain: null,
            secure: $secure,
            httpOnly: true,
            sameSite: 'lax'
        ));
    }
}
