@extends('layouts.app')

@section('title', 'ReCircle – Forgot Password')
@section('meta_description', 'Reset your ReCircle password securely in a few simple steps.')

@section('content')
<main class="auth-section">
    <div class="container">
        <div class="auth-wrapper">
            <div class="auth-info">
                <div class="auth-recycle" aria-hidden="true">
                    <i class="fa-solid fa-key"></i>
                </div>
                <h1>Forgot your password?</h1>
                <p>We’ll help you reset it securely. Enter your email, verify the code, and set a new password.</p>
                <ul class="auth-benefits">
                    <li><i class="fa-solid fa-shield"></i> Secure, step-by-step process</li>
                    <li><i class="fa-solid fa-envelope"></i> One-time code sent to your email</li>
                    <li><i class="fa-solid fa-lock"></i> Strong password guidance</li>
                </ul>
            </div>

            <div class="auth-card" aria-live="polite">
                <ol class="steps small">
                    <li class="step current" data-step="1">Email</li>
                    <li class="step" data-step="2">Verify</li>
                    <li class="step" data-step="3">New Password</li>
                    <li class="step" data-step="4">Done</li>
                </ol>

                <!-- Step 1: Request reset -->
                <form id="fp-step1" class="auth-form" novalidate>
                    <div class="form-group">
                        <label for="fp-email">Email</label>
                        <input type="email" id="fp-email" name="email" placeholder="you@example.com" autocomplete="email" required>
                        <small class="field-error" aria-live="assertive"></small>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Send reset code</button>
                    <p class="switch-text"><a href="{{ route('auth') }}">Back to sign in</a></p>
                </form>

                <!-- Step 2: Verify code -->
                <form id="fp-step2" class="auth-form hidden" novalidate>
                    <div class="form-group">
                        <label for="fp-code">Verification code</label>
                        <input type="text" id="fp-code" name="code" inputmode="numeric" placeholder="6-digit code" required>
                        <small class="field-error" aria-live="assertive"></small>
                    </div>
                    <div class="form-row" style="justify-content: space-between; gap: .75rem;">
                        <button type="button" id="fp-resend" class="btn btn-secondary" style="flex:1;">Resend code</button>
                        <button type="submit" class="btn btn-primary" style="flex:1;">Verify</button>
                    </div>
                    <p class="switch-text">Code sent to <span id="fp-email-echo"></span></p>
                </form>

                <!-- Step 3: Set new password -->
                <form id="fp-step3" class="auth-form hidden" novalidate>
                    <div class="form-group password-group">
                        <label for="fp-pass">New password</label>
                        <div class="password-input">
                            <input type="password" id="fp-pass" name="password" placeholder="At least 8 characters" autocomplete="new-password" required>
                            <button type="button" class="toggle-password" aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
                        </div>
                        <div class="password-strength" aria-hidden="true">
                            <div class="strength-bar"></div>
                            <span class="strength-label">Weak</span>
                        </div>
                        <small class="field-error" aria-live="assertive"></small>
                    </div>
                    <div class="form-group">
                        <label for="fp-confirm">Confirm password</label>
                        <input type="password" id="fp-confirm" name="confirm" placeholder="Re-enter password" autocomplete="new-password" required>
                        <small class="field-error" aria-live="assertive"></small>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Update password</button>
                </form>

                <!-- Step 4: Done -->
                <div id="fp-step4" class="auth-form hidden" aria-live="polite">
                    <div class="success-box">
                        <i class="fa-solid fa-circle-check" style="color:#16a34a;font-size:2rem;margin-bottom:.5rem;"></i>
                        <h3>Password updated</h3>
                        <p>Your password has been reset successfully. You can now sign in with your new password.</p>
                        <a href="{{ route('auth') }}" class="btn btn-primary w-full">Return to sign in</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
    @vite(['resources/js/forgot-password.js'])
@endpush
