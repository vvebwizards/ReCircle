<?php

namespace App\Services;

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
}
