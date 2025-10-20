<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class JwtService
{
    /**
     * @return array{token:string,expires_at:string}
     */
    public function issue(Authenticatable $user, ?int $ttlMinutes = null): array
    {
        $ttl = $ttlMinutes ?? (int) Config::get('jwt.ttl');
        $now = Date::now();
        $exp = $now->copy()->addMinutes($ttl);

        $payload = [
            'iss' => Config::get('jwt.issuer'),
            'sub' => $user->getAuthIdentifier(),
            'iat' => $now->getTimestamp(),
            'exp' => $exp->getTimestamp(),
            'jti' => (string) Str::uuid(),
        ];

        $token = JWT::encode($payload, $this->secret(), $this->algo());

        return [
            'token' => $token,
            'expires_at' => $exp->toIso8601String(),
        ];
    }

    public function decode(string $jwt): object
    {
        return JWT::decode($jwt, new Key($this->secret(), $this->algo()));
    }

    /**
     * @return array{token:string,expires_at:string}
     */
    public function refresh(object $decoded): array
    {
        // Keep same subject, regenerate jti & iat/exp
        $userId = $decoded->sub ?? null;
        $ttl = (int) Config::get('jwt.ttl');
        $now = Date::now();
        $exp = $now->copy()->addMinutes($ttl);

        $payload = [
            'iss' => Config::get('jwt.issuer'),
            'sub' => $userId,
            'iat' => $now->getTimestamp(),
            'exp' => $exp->getTimestamp(),
            'jti' => (string) Str::uuid(),
        ];

        $token = JWT::encode($payload, $this->secret(), $this->algo());

        return [
            'token' => $token,
            'expires_at' => $exp->toIso8601String(),
        ];
    }

    private function secret(): string
    {
        return (string) Config::get('jwt.secret');
    }

    private function algo(): string
    {
        return (string) Config::get('jwt.algo');
    }
}
