<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use App\Services\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
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
            'email_code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }
        if ($user->isBlocked()) {
            return response()->json([
                'message' => 'Your account has been blocked. Please contact administrator.',
            ], 403);
        }

        // Enforce 2FA when enabled
        if ($user->two_factor_enabled) {
            $code = trim((string) ($data['twofa_code'] ?? ''));
            $emailCode = trim((string) ($data['email_code'] ?? ''));
            $recovery = trim((string) ($data['recovery_code'] ?? ''));
            $verified = false;
            if ($code !== '' && $user->two_factor_secret) {
                $verified = $twoFactor->verify($user->two_factor_secret, $code);
            }
            if (! $verified && $emailCode !== '') {
                $verified = $twoFactor->verifyEmailCode($user, $emailCode);
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
                if ($code !== '' || $emailCode !== '' || $recovery !== '') {
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

        // Check if user needs onboarding
        if (! $user->onboarding_completed) {
            return $this->tokenResponse($issued['token'], $issued['expires_at'], [
                'show_onboarding' => true,
                'user_id' => $user->id,
            ]);
        }

        return $this->tokenResponse($issued['token'], $issued['expires_at']);
    }

    // Send a one-time code to the user's verified email after verifying credentials (without issuing a JWT)
    public function sendEmailCode(Request $request, TwoFactorService $twoFactor): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! \Illuminate\Support\Facades\Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email not verified'], 403);
        }

        $code = $twoFactor->generateEmailCode($user);
        // Send synchronously to avoid requiring a queue worker
        Notification::sendNow($user, new \App\Notifications\TwoFactorEmailCode($code));
        // In local env, also log the code for easier testing when MAIL_MAILER=log
        if (app()->isLocal()) {
            Log::info('2FA email code generated', ['user_id' => $user->id, 'email' => $user->email, 'code' => $code]);
        }

        return response()->json(['message' => 'Email code sent']);
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

    private function tokenResponse(string $jwt, string $expiresAt, array $additionalData = []): JsonResponse
    {
        $minutes = (int) config('jwt.ttl');

        $secure = app()->environment('production');

        $responseData = [
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
        ];

        // Merge additional data if provided
        if (! empty($additionalData)) {
            $responseData = array_merge($responseData, $additionalData);
        }

        return response()->json($responseData)->withCookie(cookie(
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
