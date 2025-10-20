<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OTPHP\TOTP;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorService
{
    public function generateSecret(): string
    {
        // OTPHP v11 uses parameter name "digest" instead of "algorithm"
        $totp = TOTP::create(secret: null, period: 30, digest: 'sha1', digits: 6);
        // Ensure issuer label appears in apps
        $totp->setLabel(config('app.name').' Login');
        $totp->setIssuer(config('app.name'));

        return $totp->getSecret();
    }

    public function provisioningUri(string $secret, string $accountName): string
    {
        $totp = TOTP::create(secret: $secret, period: 30, digest: 'sha1', digits: 6);
        $totp->setLabel($accountName);
        $totp->setIssuer(config('app.name'));

        return $totp->getProvisioningUri();
    }

    public function verify(string $secret, string $code, int $window = 1): bool
    {
        try {
            $totp = TOTP::create(secret: $secret, period: 30, digest: 'sha1', digits: 6);

            return $totp->verify($code, null, $window);
        } catch (\Throwable $e) {
            Log::warning('2FA verify error: '.$e->getMessage());

            return false;
        }
    }

    public function qrSvg(string $otpauthUri, int $size = 200): string
    {
        // Use Bacon QR Code via simple-qrcode to produce an inline SVG
        if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            return QrCode::format('svg')->size($size)->generate($otpauthUri);
        }

        return '';
    }

    /** @return array<string> */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::upper(Str::random(10));
        }

        return $codes;
    }

    /**
     * Generate and store a one-time email code for 2FA and return it.
     * Code is valid for $ttl seconds and consumed on successful verification.
     */
    public function generateEmailCode(\App\Models\User $user, int $ttl = 600): string
    {
        $code = (string) random_int(100000, 999999);
        $key = $this->emailCodeCacheKey($user->id);
        Cache::put($key, $code, $ttl);

        return $code;
    }

    /** Verify a provided email OTP and consume it on success. */
    public function verifyEmailCode(\App\Models\User $user, string $code): bool
    {
        $key = $this->emailCodeCacheKey($user->id);
        $expected = Cache::get($key);
        if (! $expected) {
            return false;
        }
        if (hash_equals((string) $expected, (string) $code)) {
            Cache::forget($key);

            return true;
        }

        return false;
    }

    private function emailCodeCacheKey(int $userId): string
    {
        return '2fa:email:code:'.$userId;
    }
}
