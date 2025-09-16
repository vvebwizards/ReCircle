<?php

return [
    'secret' => env('JWT_SECRET', env('APP_KEY')),
    // minutes
    'ttl' => env('JWT_TTL', 60),
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), // 14 days
    'algo' => env('JWT_ALGO', 'HS256'),
    'cookie' => env('JWT_COOKIE', 'access_token'),
    'required_claims' => ['iss', 'sub', 'iat', 'exp'],
    'issuer' => env('JWT_ISSUER', 'recircle-api'),
];
