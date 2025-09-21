<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $provider = $this->provider();
        // Let the provider generate cryptographically secure state; then store it.
        $authUrl = $provider->getAuthorizationUrl([
            'scope' => ['openid', 'email', 'profile'],
            // 'access_type' => 'offline', // Uncomment if you need refresh tokens
            // 'prompt' => 'consent',
        ]);
        $state = $provider->getState();
        $request->session()->put('google_oauth_state', $state);
        Log::debug('Google OAuth: stored state for redirect', [
            'state' => $state,
            'session_id' => $request->session()->getId(),
            'url' => $authUrl,
        ]);

        return redirect()->away($authUrl);
    }

    public function callback(Request $request, JwtService $jwt): Response|RedirectResponse
    {
        $storedState = (string) $request->session()->get('google_oauth_state', '');
        $queryState = (string) $request->query('state');
        if ($storedState === '' || $queryState === '' || ! hash_equals($storedState, $queryState)) {
            Log::warning('Google OAuth state mismatch', [
                'stored' => $storedState === '' ? null : $storedState,
                'query' => $queryState === '' ? null : $queryState,
                'session_id' => $request->session()->getId(),
                'host' => $request->getHost(),
                'full_url' => $request->fullUrl(),
                'referer' => $request->headers->get('referer'),
                'user_agent' => $request->userAgent(),
            ]);

            // Graceful fallback so user can retry; keep original state so a stray reload does not hide the issue.
            return redirect()->route('auth')->with('verify_message', 'Session expired or invalid. Please try Google sign-in again.');
        }
        // Only forget state after successful validation to allow a reload before continuing if needed.
        $request->session()->forget('google_oauth_state');

        $code = (string) $request->query('code', '');
        if ($code === '') {
            abort(400, 'Missing code');
        }

        $provider = $this->provider();
        try {
            $tokenIface = $provider->getAccessToken('authorization_code', ['code' => $code]);
            // Ensure concrete AccessToken type for provider signature
            $token = $tokenIface instanceof AccessToken ? $tokenIface : new AccessToken($tokenIface->jsonSerialize());
            $owner = $provider->getResourceOwner($token);
            $googleId = (string) $owner->getId();
            $email = '';
            $name = '';
            if ($owner instanceof GoogleUser) {
                $email = (string) $owner->getEmail();
                $name = (string) $owner->getName();
            } else {
                $arr = (array) $owner->toArray();
                $email = isset($arr['email']) ? (string) $arr['email'] : '';
                $name = isset($arr['name']) ? (string) $arr['name'] : ((string) ($arr['given_name'] ?? ''));
            }
        } catch (\Throwable $e) {
            Log::warning('Google OAuth error: '.$e->getMessage());

            return redirect()->route('auth')->with('verify_message', 'Google sign-in failed. Please try again.');
        }

        // Find existing user by Google ID or email
        $user = User::where('google_id', $googleId)->first();
        if (! $user && $email !== '') {
            $user = User::where('email', $email)->first();
        }

        if (! $user) {
            $user = new User;
            $user->name = $name !== '' ? $name : 'Google User';
            $user->email = $email !== '' ? $email : (strtolower(Str::random(8)).'@example.invalid');
            // Do not assign a default role; we'll ask the user to choose one.
            $user->password = Str::random(32); // random password placeholder
        }

        if (! $user->google_id) {
            $user->google_id = $googleId;
        }
        if ($email !== '' && ! $user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
        }
        $user->save();

        // Issue JWT and set cookie, then render a small bridge page that client-redirects to dashboard.
        // Some browsers drop Set-Cookie on cross-site 302 responses; a 200 HTML response is more reliable.
        $issued = $jwt->issue($user);

        $minutes = (int) config('jwt.ttl');
        $secure = app()->environment('production');

        // Decide where to send the user: choose role if none, otherwise dashboard
        $redirectTo = $user->role ? route('dashboard') : route('choose-role.show');

        return response()->view('auth.oauth-bridge', [
            'redirectTo' => $redirectTo,
        ])->withCookie(cookie(
            name: config('jwt.cookie'),
            value: $issued['token'],
            minutes: $minutes,
            path: '/',
            domain: null,
            secure: $secure,
            httpOnly: true,
            sameSite: 'lax'
        ));
    }

    private function provider(): Google
    {
        $cfg = config('services.google');
        $options = [
            'clientId' => $cfg['client_id'] ?? '',
            'clientSecret' => $cfg['client_secret'] ?? '',
            'redirectUri' => $cfg['redirect'] ?? url('/auth/google/callback'),
            // 'hostedDomain' => $cfg['hosted_domain'] ?? null, // restrict to GSuite domain if needed
        ];

        // In local/dev on Windows, cURL may lack a CA bundle which breaks TLS verification.
        // Provide a Guzzle client with verify=false ONLY in local to allow development.
        $collaborators = [];
        if (app()->isLocal()) {
            $collaborators['httpClient'] = new GuzzleClient([
                'verify' => false,
                'timeout' => 10,
            ]);
        }

        return new Google($options, $collaborators);
    }
}
