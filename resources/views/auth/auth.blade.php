@extends('layouts.app')

@section('title', 'ReCircle – Sign In / Sign Up')
@section('meta_description', 'Sign in or create your ReCircle account to join the circular economy marketplace.')

@push('head')
    <link rel="icon" href="{{ Vite::asset('resources/images/vite.svg') }}" />
@endpush

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="auth-wrapper">
            <div class="auth-info">
                <div class="auth-recycle" aria-hidden="true">
                    <i class="fa-solid fa-recycle"></i>
                </div>
                <h1>Join the Circular Movement</h1>
                <p>Sign in or create an account to list waste, bid on materials, or shop upcycled products with real impact.</p>
                <ul class="auth-benefits">
                    <li><i class="fa-solid fa-leaf"></i> Track CO₂ savings and landfill diversion</li>
                    <li><i class="fa-solid fa-recycle"></i> Transparent material passports</li>
                    <li><i class="fa-solid fa-handshake-angle"></i> Connect with local makers</li>
                </ul>
            </div>
            <div class="auth-card" aria-live="polite">
                @if(session('verify_message'))
                    <div class="notice verify-notice" role="alert">
                        <div class="notice-content">
                            <i class="fa-solid fa-envelope-circle-check" aria-hidden="true"></i>
                            <span>{{ session('verify_message') }}</span>
                        </div>
                        <button type="button" class="notice-close" aria-label="Dismiss notice">&times;</button>
                        <div class="notice-progress" aria-hidden="true">
                            <div class="notice-progress-bar"></div>
                        </div>
                    </div>
                @endif
                <div class="auth-tabs" role="tablist">
                    <button class="auth-tab active" role="tab" aria-selected="true" aria-controls="signin-panel" id="signin-tab">Sign In</button>
                    <button class="auth-tab" role="tab" aria-selected="false" aria-controls="signup-panel" id="signup-tab">Create Account</button>
                </div>

                <!-- Sign In Form -->
                <form id="signin-form" class="auth-form" aria-labelledby="signin-tab" novalidate>
                    <div class="form-group">
                        <label for="signin-email">Email</label>
                        <input type="email" id="signin-email" name="email" placeholder="you@example.com" autocomplete="email" required>
                        <small class="field-error" aria-live="assertive"></small>
                    </div>
                    <div class="form-group password-group">
                        <label for="signin-password">Password</label>
                        <div class="password-input">
                            <input type="password" id="signin-password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                            <button type="button" class="toggle-password" aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
                        </div>
                        <small class="field-error" aria-live="assertive"></small>
                    </div>
                    <div class="form-row">
                        <label class="checkbox">
                            <input type="checkbox" id="remember-me" name="remember"> Remember me
                        </label>
                        <a href="{{ route('forgot-password') }}" class="forgot-link">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Sign In</button>
                    <div class="divider"><span>or</span></div>
                    <button type="button" class="btn btn-secondary w-full social-btn"><i class="fa-brands fa-google"></i> Continue with Google</button>
                </form>

                <!-- Sign Up Form -->
                <form id="signup-form" class="auth-form hidden" aria-labelledby="signup-tab" method="POST" action="{{ route('register.store') }}" novalidate>
                    @csrf
                    <div class="form-group">
                        <label for="signup-name">Full Name</label>
                        <input type="text" id="signup-name" name="name" value="{{ old('name') }}" placeholder="Jane Doe" autocomplete="name" required class="@error('name') input-error @enderror" @error('name') aria-invalid="true" aria-describedby="error-signup-name" @enderror>
                        <small id="error-signup-name" class="field-error" aria-live="assertive">@error('name'){{ $message }}@enderror</small>
                    </div>
                    <div class="form-group">
                        <label for="signup-email">Email</label>
                        <input type="email" id="signup-email" name="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required class="@error('email') input-error @enderror" @error('email') aria-invalid="true" aria-describedby="error-signup-email" @enderror>
                        <small id="error-signup-email" class="field-error" aria-live="assertive">@error('email'){{ $message }}@enderror</small>
                    </div>
                    <div class="form-group">
                        <label for="signup-role">I am a</label>
                        <select id="signup-role" name="role" required class="@error('role') input-error @enderror" @error('role') aria-invalid="true" aria-describedby="error-signup-role" @enderror>
                            <option value="" @selected(old('role')==='')>Select your role</option>
                            <option value="generator" @selected(old('role')==='generator')>Generator (List Waste)</option>
                            <option value="maker" @selected(old('role')==='maker')>Maker / Repairer</option>
                            <option value="buyer" @selected(old('role')==='buyer')>Buyer</option>
                            <option value="courier" @selected(old('role')==='courier')>Courier Partner</option>
                        </select>
                        <small id="error-signup-role" class="field-error" aria-live="assertive">@error('role'){{ $message }}@enderror</small>
                    </div>
                    <div class="form-group password-group">
                        <label for="signup-password">Password</label>
                        <div class="password-input">
                            <input type="password" id="signup-password" name="password" placeholder="At least 8 characters" autocomplete="new-password" required class="@error('password') input-error @enderror" @error('password') aria-invalid="true" aria-describedby="error-signup-password" @enderror>
                            <button type="button" class="toggle-password" aria-label="Show password"><i class="fa-regular fa-eye"></i></button>
                        </div>
                        <div class="password-strength" aria-hidden="true">
                            <div class="strength-bar"></div>
                            <div class="strength-meta">
                                <i class="strength-icon fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                                <span class="strength-label">Weak</span>
                            </div>
                        </div>
                        <small id="error-signup-password" class="field-error" aria-live="assertive">@error('password'){{ $message }}@enderror</small>
                    </div>
                    <div class="form-group">
                        <label for="signup-confirm">Confirm Password</label>
                        <input type="password" id="signup-confirm" name="password_confirmation" placeholder="Re-enter password" autocomplete="new-password" required>
                        <small id="error-signup-confirm" class="field-error" aria-live="assertive"></small>
                    </div>
                    <label class="checkbox">
                        <input type="checkbox" id="terms" name="terms" {{ old('terms') ? 'checked' : '' }} required> I agree to the <a href="#">Terms</a> and <a href="#">Privacy</a>
                    </label>
                    <small id="error-terms" class="field-error" aria-live="assertive">@error('terms'){{ $message }}@enderror</small>
                    <button type="submit" class="btn btn-primary w-full">Create Account</button>
                    <p class="switch-text">Already have an account? <a href="#" id="go-signin">Sign in</a></p>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    @vite(['resources/js/auth.js'])
@endpush
