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
                <p>Enter the 6-digit code from your authenticator app or the code we sent to your email/SMS.</p>
                <ul class="auth-benefits">
                    <li><i class="fa-solid fa-lock"></i> Adds an extra layer of security</li>
                    <li><i class="fa-solid fa-mobile-screen"></i> Works with authenticator apps</li>
                    <li><i class="fa-solid fa-envelope"></i> Or get a one-time email/SMS code</li>
                </ul>
            </div>

            <div class="auth-card" aria-live="polite">
                <form id="twofa-form" class="auth-form" novalidate>
                    <div class="form-group">
                        <label for="twofa-code">Authentication code</label>
                        <input type="text" id="twofa-code" name="code" inputmode="numeric" placeholder="6-digit code" required>
                        <small class="field-error" aria-live="assertive"></small>
                    </div>
                    <div class="form-row" style="justify-content: space-between; gap: .75rem;">
                        <button type="button" id="twofa-resend" class="btn btn-secondary" style="flex:1;">Resend code</button>
                        <button type="submit" class="btn btn-primary" style="flex:1;">Verify</button>
                    </div>
                    <p class="switch-text"><a href="{{ route('auth') }}">Use a different account</a></p>
                </form>

                <div id="twofa-success" class="auth-form hidden" aria-live="polite">
                    <div class="success-box">
                        <i class="fa-solid fa-circle-check" style="color:#16a34a;font-size:2rem;margin-bottom:.5rem;"></i>
                        <h3>Verified</h3>
                        <p>2FA code accepted. You're securely signed in.</p>
                        <a href="{{ route('home') }}" class="btn btn-primary w-full">Go to dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
    @vite(['resources/js/twofa.js'])
@endpush
