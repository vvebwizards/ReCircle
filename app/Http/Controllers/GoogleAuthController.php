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
        $authUrl = $provider->getAuthorizationUrl([
            'scope' => ['openid', 'email', 'profile'],

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

            return redirect()->route('auth')->with('verify_message', 'Session expired or invalid. Please try Google sign-in again.');
        }
        $request->session()->forget('google_oauth_state');

        $code = (string) $request->query('code', '');
        if ($code === '') {
            abort(400, 'Missing code');
        }

        $provider = $this->provider();
        try {
            $tokenIface = $provider->getAccessToken('authorization_code', ['code' => $code]);
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

        $user = User::where('google_id', $googleId)->first();
        if (! $user && $email !== '') {
            $user = User::where('email', $email)->first();
        }

        if (! $user) {
            $user = new User;
            $user->name = $name !== '' ? $name : 'Google User';
            $user->email = $email !== '' ? $email : (strtolower(Str::random(8)).'@example.invalid');
            $user->password = Str::random(32);
        }

        if (! $user->google_id) {
            $user->google_id = $googleId;
        }
        if ($email !== '' && ! $user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
        }
        $user->save();
        $issued = $jwt->issue($user);

        $minutes = (int) config('jwt.ttl');
        $secure = app()->environment('production');

        switch ($user->role) {
            case 'admin':
                $redirectTo = route('admin.dashboard');
                break;
            case 'maker':
                $redirectTo = route('maker.dashboard');
                break;
            case 'generator':
                $redirectTo = route('dashboard');
                break;
            default:
                $redirectTo = route('choose-role.show');
                break;
        }

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
        ];
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
