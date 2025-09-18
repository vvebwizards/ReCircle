@extends('layouts.app')

@section('title', 'ReCircle – Two-Factor Authentication')
@section('meta_description', 'Secure your account with two-factor authentication (2FA).')

@section('content')
<main class="auth-section">
    <div class="container">
        <div class="auth-wrapper">
            <div class="auth-info">
                <div class="auth-recycle" aria-hidden="true">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h1>Two-Factor Authentication</h1>
                <p>Enter a code from your authenticator app, or request a one‑time code by email.</p>
                <ul class="auth-benefits">
                    <li><i class="fa-solid fa-lock"></i> Adds an extra layer of security</li>
                    <li><i class="fa-solid fa-mobile-screen"></i> Works with authenticator apps</li>
                    <li><i class="fa-solid fa-envelope"></i> Or get a one-time email/SMS code</li>
                </ul>
            </div>

            <div class="auth-card" aria-live="polite">
                <div id="twofa-alert" class="alert hidden" role="alert"></div>

                <div class="auth-tabs" role="tablist">
                    <button class="auth-tab active" role="tab" aria-selected="true" aria-controls="tab-totp" id="tabbtn-totp"><i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i> Authenticator</button>
                    <button class="auth-tab" role="tab" aria-selected="false" aria-controls="tab-email" id="tabbtn-email"><i class="fa-solid fa-envelope" aria-hidden="true"></i> Email code</button>
                    <button class="auth-tab" role="tab" aria-selected="false" aria-controls="tab-recovery" id="tabbtn-recovery"><i class="fa-solid fa-key" aria-hidden="true"></i> Recovery</button>
                </div>

                <form id="twofa-form" class="auth-form" novalidate>
                    <input type="hidden" id="twofa-method" name="twofa-method" value="totp" />
                    <div id="tab-totp" role="tabpanel" aria-labelledby="tabbtn-totp">
                        <div class="form-group">
                            <label for="twofa-code" id="twofa-label">Enter 6‑digit code</label>
                            <input type="text" id="twofa-code" name="code" inputmode="numeric" placeholder="123456" required>
                            <small class="field-error" aria-live="assertive"></small>
                        </div>
                    </div>
                    <div id="tab-email" class="hidden" role="tabpanel" aria-labelledby="tabbtn-email">
                        <div class="form-group">
                            <label for="twofa-code-email">Enter the 6‑digit email code</label>
                            <input type="text" id="twofa-code-email" name="code-email" inputmode="numeric" placeholder="123456">
                            <small class="field-error" aria-live="assertive"></small>
                        </div>
                        <div class="form-row" style="justify-content:flex-end; gap:.75rem;">
                            <button type="button" id="twofa-resend" class="btn btn-secondary">Send code</button>
                        </div>
                    </div>
                    <div id="tab-recovery" class="hidden" role="tabpanel" aria-labelledby="tabbtn-recovery">
                        <div class="form-group">
                            <label for="twofa-code-recovery">Enter a recovery code</label>
                            <input type="text" id="twofa-code-recovery" name="code-recovery" inputmode="text" placeholder="XXXXXXXXXX">
                            <small class="field-error" aria-live="assertive"></small>
                        </div>
                    </div>

                    <div class="form-row" style="justify-content: flex-end; gap: .75rem;">
                        <button type="submit" class="btn btn-primary" style="flex:1;">Verify</button>
                    </div>
                    <p class="switch-text"><a href="{{ route('auth') }}">Use a different account</a></p>
                </form>

                <div id="twofa-success" class="auth-form hidden" aria-live="polite">
                    <div class="success-box">
                        <i class="fa-solid fa-circle-check" style="color:#16a34a;font-size:2rem;margin-bottom:.5rem;"></i>
                        <h3>Verified</h3>
                        <p>2FA code accepted. You're securely signed in.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
@unless (app()->environment('testing'))
    @vite(['resources/js/twofa.js'])
@endunless
@endpush
