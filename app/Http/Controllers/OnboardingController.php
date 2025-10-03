<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OnboardingController extends Controller
{
    public function __construct(private TwoFactorService $twoFactorService) {}

    public function setup2FA(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Use existing TwoFactorService to generate secret
        if (! $user->two_factor_secret) {
            $user->two_factor_secret = $this->twoFactorService->generateSecret();
            $user->save();
        }

        // Generate provisioning URI and QR code using existing service
        $provisioningUri = $this->twoFactorService->provisioningUri(
            $user->two_factor_secret,
            $user->email
        );
        $qrSvg = $this->twoFactorService->qrSvg($provisioningUri, 200);

        return response()->json([
            'success' => true,
            'secret' => $user->two_factor_secret,
            'provisioning_uri' => $provisioningUri,
            'qr_svg' => $qrSvg,
        ]);
    }

    public function verify2FA(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Use existing TwoFactorService to verify code
        if ($user->two_factor_secret && $this->twoFactorService->verify($user->two_factor_secret, $request->code)) {
            $user->two_factor_enabled = true;
            $user->two_factor_confirmed_at = now();
            $user->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid verification code'], 400);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Delete old avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $avatarPath;
        $user->save();

        return response()->json([
            'success' => true,
            'avatar_url' => Storage::url($avatarPath),
        ]);
    }

    public function completeOnboarding(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user->onboarding_completed = true;
        $user->save();

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard'),
        ]);
    }
}
