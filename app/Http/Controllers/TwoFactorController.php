<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function setup(Request $request, TwoFactorService $twoFactor): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (! $user->two_factor_secret) {
            $user->two_factor_secret = $twoFactor->generateSecret();
            $user->save();
        }

        $otpauth = $twoFactor->provisioningUri($user->two_factor_secret, $user->email);
        $svg = $twoFactor->qrSvg($otpauth, 220);
        $codes = $user->two_factor_recovery_codes ? json_decode($user->two_factor_recovery_codes, true) : $twoFactor->generateRecoveryCodes();
        if (! $user->two_factor_recovery_codes) {
            $user->two_factor_recovery_codes = json_encode($codes);
            $user->save();
        }

        return response()->json([
            'otpauth_uri' => $otpauth,
            'qr_svg' => $svg,
            'recovery_codes' => $codes,
            'enabled' => (bool) $user->two_factor_enabled,
        ]);
    }

    public function enable(Request $request, TwoFactorService $twoFactor): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $data = $request->validate(['code' => ['required', 'string']]);
        if ($user->two_factor_secret && $twoFactor->verify($user->two_factor_secret, $data['code'])) {
            $user->two_factor_enabled = true;
            $user->two_factor_confirmed_at = now();
            $user->save();

            return response()->json(['message' => 'Two-factor enabled']);
        }

        return response()->json(['message' => 'Invalid code'], 422);
    }

    public function disable(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return response()->json(['message' => 'Two-factor disabled']);
    }
}
