<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FacialRecognitionService;
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
    public function __construct(
        private JwtService $jwt,
        private FacialRecognitionService $facialService
    ) {}

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

        // Check if user exists and password is correct
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            // If user exists, increment failed login attempts
            if ($user) {
                $user->incrementFailedLoginAttempts();
                $maxAttempts = config('auth.max_failed_attempts', 3);

                // Check if we've reached the maximum failed attempts
                if ($user->failed_login_attempts >= $maxAttempts) {
                    if ($user->is_facial_registered && ! $user->isBlocked()) {
                        // User has facial recognition - trigger fallback
                        return response()->json([
                            'requires_facial_fallback' => true,
                            'message' => 'Too many failed attempts. Please verify your identity using facial recognition.',
                            'failed_attempts' => $user->failed_login_attempts,
                            'max_attempts' => $maxAttempts,
                        ], 423); // 423 Locked - custom status for facial fallback required
                    } else {
                        // User doesn't have facial recognition - lock account temporarily
                        $lockoutDuration = config('auth.lockout_duration_minutes', 30);
                        $user->lockForDuration($lockoutDuration);

                        return response()->json([
                            'account_locked' => true,
                            'message' => "Too many failed attempts. Account locked for {$lockoutDuration} minutes.",
                            'failed_attempts' => $user->failed_login_attempts,
                            'max_attempts' => $maxAttempts,
                            'locked_until' => $user->locked_until,
                            'suggestion' => 'Consider setting up facial recognition for faster recovery in the future.',
                        ], 429); // 429 Too Many Requests
                    }
                }
            }

            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }
        if ($user->isBlocked()) {
            return response()->json([
                'message' => 'Your account has been blocked. Please contact administrator.',
            ], 403);
        }

        // Check if user is temporarily locked out
        if ($user->isLockedOut()) {
            return response()->json([
                'message' => 'Account is temporarily locked due to multiple failed attempts.',
                'locked_until' => $user->locked_until,
                'account_locked' => true,
            ], 429);
        }

        // Check if user is temporarily locked out
        if ($user->isLockedOut()) {
            return response()->json([
                'message' => 'Account temporarily locked due to failed login attempts. Please try again later.',
                'locked_until' => $user->locked_until,
            ], 423);
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

        // Reset failed login attempts on successful login
        $user->resetFailedLoginAttempts();

        $issued = $this->jwt->issue($user);

        // Create the additional data array with user role for dashboard redirection
        $additionalData = [
            'user_role' => $user->role->value,
        ];

        // Check if user needs onboarding
        if (! $user->onboarding_completed) {
            $additionalData['show_onboarding'] = true;
            $additionalData['user_id'] = $user->id;
        }

        return $this->tokenResponse($issued['token'], $issued['expires_at'], $additionalData);
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

    /**
     * Handle facial recognition fallback authentication
     */
    public function facialFallback(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'descriptor' => ['required', 'array', 'min:128'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Check if user can use facial fallback
        if (! $this->facialService->canUseFacialFallback($user)) {
            return response()->json([
                'message' => 'Facial fallback not available for this user',
            ], 400);
        }

        // Verify facial identity
        $result = $this->facialService->verifyFacialIdentity($data['email'], $data['descriptor']);

        if ($result['success']) {
            // Process successful facial verification
            $this->facialService->processFacialFallbackSuccess($user);

            // Issue JWT token
            $issued = $this->jwt->issue($user);

            $additionalData = [
                'message' => 'Facial verification successful',
                'facial_fallback_used' => true,
            ];

            if (! $user->onboarding_completed) {
                $additionalData['show_onboarding'] = true;
                $additionalData['user_id'] = $user->id;
            }

            return $this->tokenResponse($issued['token'], $issued['expires_at'], $additionalData);
        } else {
            // Process failed facial verification
            $lockoutInfo = $this->facialService->processFacialFallbackFailure($user);

            return response()->json([
                'message' => 'Facial verification failed',
                'facial_verification_failed' => true,
                ...$lockoutInfo,
            ], 401);
        }
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
