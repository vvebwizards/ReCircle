<?php

namespace App\Http\Controllers;

use App\Models\UserFaceDescriptor;
use App\Services\JwtService;
use Illuminate\Http\Request;

class FaceAuthController extends Controller
{
    public function __construct(private JwtService $jwt) {}

    public function showFacialLogin()
    {
        return view('auth.facial-login');
    }

    public function enroll(Request $request)
    {
        $request->validate([
            'userId' => 'required|exists:users,id',
            'descriptor' => 'required|array|min:128',
        ]);

        UserFaceDescriptor::updateOrCreate(
            ['user_id' => $request->userId],
            ['descriptor' => $request->descriptor, 'is_active' => true]
        );

        // Mark user as having facial recognition registered
        User::where('id', $request->userId)->update(['is_facial_registered' => true]);

        return response()->json(['success' => true]);
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'descriptor' => 'required|array|min:128',
        ]);

        $user = UserFaceDescriptor::findSimilarFace($request->descriptor);

        if ($user) {
            // Check if user is blocked
            if ($user->isBlocked()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been blocked. Please contact administrator.',
                ], 403);
            }

            // Update last used timestamp
            UserFaceDescriptor::where('user_id', $user->id)
                ->update(['last_used' => now()]);

            // Issue JWT token (same as regular login)
            $issued = $this->jwt->issue($user);

            // Prepare additional response data
            $additionalData = [
                'success' => true,
                'redirect' => route('dashboard'),
            ];

            if (! $user->onboarding_completed) {
                $additionalData['show_onboarding'] = true;
                $additionalData['user_id'] = $user->id;
            }

            return $this->tokenResponse($issued['token'], $issued['expires_at'], $additionalData);
        }

        return response()->json(['success' => false], 401);
    }

    private function tokenResponse(string $jwt, string $expiresAt, array $additionalData = []): \Illuminate\Http\JsonResponse
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
}
