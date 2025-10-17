@extends('layouts.app')

@section('title', 'ReCircle – Sign In / Sign Up')
@section('meta_description', 'Sign in or create your ReCircle account to join the circular economy marketplace.')

@push('head')
    <link rel="icon" href="{{ Vite::asset('resources/images/vite.svg') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <style>
        /* Onboarding Modal Styles */
        
        /* Override modal overlay for onboarding - blur background */
        #onboarding-modal.modal-overlay {
            background: rgba(0, 0, 0, 0.2) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
        }
        
        .onboarding-modal {
            max-width: 700px;
            width: 98%;
            position: fixed;
            top: 60px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1100;
            box-shadow: 0 25px 80px rgba(0,0,0,0.25);
            max-height: calc(100vh - 80px);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            border: 2px solid transparent;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        /* Custom scrollbar styling */
        .onboarding-modal::-webkit-scrollbar {
            width: 8px;
        }
        
        .onboarding-modal::-webkit-scrollbar-track {
            background: rgba(241, 241, 241, 0.5);
            border-radius: 4px;
            margin: 4px 0;
        }
        
        .onboarding-modal::-webkit-scrollbar-thumb {
            background: #059669;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }
        
        .onboarding-modal::-webkit-scrollbar-thumb:hover {
            background: #047857;
        }
        
        .onboarding-modal::-webkit-scrollbar-thumb:active {
            background: #065f46;
        }
        
        .onboarding-modal:hover {
            border-color: #059669;
            box-shadow: 0 25px 80px rgba(0,0,0,0.2), 0 0 0 1px #059669;
        }
        
        .onboarding-progress {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.5rem 0;
        }
        
        .progress-bar {
            flex: 1;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: #059669;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            font-size: 0.9rem;
            color: #666;
        }
        
        .onboarding-step {
            display: none;
            text-align: center;
            padding: 1rem 1.25rem;
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }
        
        .onboarding-step.active {
            display: block;
        }
        
        .step-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #059669;
        }
        
        .step-icon.success {
            color: #16a34a;
        }
        
        .step-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 0.5rem;
            padding: 0.5rem 0;
            border-top: 1px solid #e0e0e0;
            flex-shrink: 0;
        }
        
        .qr-code-container {
            display: flex;
            justify-content: center;
            margin: 1rem 0;
        }
        
        .qr-code-container #qr-code {
            display: flex;
            justify-content: center;
        }
        
        .qr-code-container #qr-code > div {
            width: 180px !important;
            height: 180px !important;
        }
        
        .qr-code-container #qr-code img {
            width: 180px !important;
            height: 180px !important;
        }
        
        .secret-code {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            justify-content: center;
            margin: 0.5rem 0;
            flex-wrap: wrap;
        }
        
        .secret-code code {
            background: #f5f5f5;
            padding: 0.5rem;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.8rem;
            word-break: break-all;
            max-width: 280px;
            border: 1px solid #e0e0e0;
            min-height: 16px;
        }
        
        .avatar-upload-container {
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .avatar-preview {
            width: 120px;
            height: 120px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            border: 2px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .avatar-placeholder {
            text-align: center;
            color: #666;
        }
        
        .avatar-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin: 1rem 0;
        }
        
        .upload-info {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.5rem;
        }
        
        .face-setup-container {
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .video-container {
            display: inline-block;
            position: relative;
            border: 2px solid #059669;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        #face-overlay {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
        }
        
        .face-instructions {
            font-size: 0.9rem;
            color: #666;
            margin: 0.5rem 0;
        }
        
        .face-status {
            margin-top: 1rem;
            padding: 0.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        /* Cancel button hover effect */
        .cancel-facial-btn:hover {
            background: #3b82f6 !important;
            color: white !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4) !important;
        }

        /* Pre-facial cancel button hover effect */
        .cancel-pre-facial-btn:hover {
            background: #3b82f6 !important;
            color: white !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4) !important;
        }        .completion-summary {
            text-align: left;
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .summary-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1rem 0;
            font-size: 0.95rem;
        }
        
        .status-text {
            font-weight: 500;
        }
        
        .status-text.success {
            color: #16a34a;
        }
        
        .skip-all {
            margin-left: auto !important;
            font-size: 0.9rem !important;
        }
        
        .btn-text {
            background: none !important;
            border: none !important;
            color: #059669 !important;
            text-decoration: underline;
            padding: 0.5rem 1rem !important;
        }
        
        .btn-text:hover {
            color: #047857 !important;
            background: rgba(5, 150, 105, 0.1) !important;
        }
        
        /* 2FA Sub-steps */
        .twofa-substeps {
            margin: 1rem 0;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .twofa-substep {
            display: none;
            animation: fadeInSlide 0.3s ease-out;
        }
        
        .twofa-substep.active {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex: 1;
        }
        

        
        .substep-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e0e0e0;
            flex-shrink: 0;
        }
        
        .substep-header.success {
            border-bottom-color: #16a34a;
        }
        
        .substep-number {
            width: 28px;
            height: 28px;
            background: #059669;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        
        .substep-number.success {
            background: #16a34a;
            font-size: 1.2rem;
        }
        
        .substep-header h5 {
            margin: 0;
            font-size: 1rem;
            color: #1a1a1a;
        }
        
        .substep-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 0.75rem;
            flex-wrap: wrap;
            flex-shrink: 0;
        }
        
        .verification-input {
            max-width: 280px;
            margin: 1rem auto;
        }
        
        .verification-input input {
            text-align: center;
            font-size: 1.25rem;
            letter-spacing: 0.25rem;
            font-weight: 600;
        }
        
        .verification-help {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 0.75rem;
            margin: 0.75rem 0;
            text-align: center;
        }
        
        .verification-help p {
            margin: 0;
            color: #0369a1;
            font-size: 0.9rem;
        }
        
        .success-info {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 1rem;
            margin: 1rem 0;
        }
        
        .success-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0.75rem 0;
            color: #15803d;
            font-weight: 500;
        }
        
        .success-item i {
            color: #16a34a;
            font-size: 1.1rem;
        }
        
        @keyframes fadeInSlide {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Social buttons styling */
        .social-center {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        
        .social-btn {
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.875rem 1.5rem !important;
            text-decoration: none !important;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .social-btn i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        .social-btn span {
            flex: 1;
            text-align: center;
        }
        
        /* Responsive: side-by-side on larger screens */
        @media (min-width: 640px) {
            .social-center {
                flex-direction: row;
                gap: 1rem;
            }
            
            .social-btn {
                flex: 1;
            }
        }
    </style>
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
                    <p class="switch-text" style="font-size:.9rem; margin-top:.5rem;">
                        Didn't receive the verification email? <a href="{{ route('verification.notice') }}" id="resend-link">Resend</a>
                    </p>
                    <div class="divider"><span>or</span></div>
                    <div class="social-center">
                        <a href="{{ route('google.redirect') }}" class="btn btn-secondary social-btn" aria-label="Continue with Google">
                            <i class="fa-brands fa-google" aria-hidden="true"></i>
                            <span>Continue with Google</span>
                        </a>
                        <a href="{{ route('facial.login') }}" class="btn btn-secondary social-btn" aria-label="Sign in with facial recognition">
                            <i class="fa-solid fa-face-smile" aria-hidden="true"></i>
                            <span>Facial Recognition</span>
                        </a>
                    </div>
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

@push('modals')
    <!-- Resend Verification Modal -->
    <div class="modal-overlay hidden" id="resend-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="resend-title">
        <div class="modal">
            <div class="modal-header">
                <h3 id="resend-title"><i class="fa-solid fa-envelope-circle-check" aria-hidden="true"></i> Resend verification email</h3>
                <button type="button" class="modal-close" aria-label="Close" data-close-modal>&times;</button>
            </div>
            <div class="modal-body">
                <p class="modal-text">Enter your email and we'll send a new verification link.</p>
                <form id="resend-form" novalidate>
                    <div class="form-group">
                        <label for="resend-email"></label>
                        <input type="email" id="resend-email" name="email" placeholder="you@example.com" autocomplete="email" required>
                        <small class="field-error" aria-live="assertive"></small>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn" data-close-modal>Cancel</button>
                        <button type="submit" class="btn btn-primary">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Onboarding Modal -->
    <div class="modal-overlay hidden" id="onboarding-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="onboarding-title">
        <div class="modal onboarding-modal">
            <div class="modal-header">
                <h3 id="onboarding-title">
                    <i class="fa-solid fa-sparkles" aria-hidden="true"></i> 
                    Complete Your Profile
                </h3>
                <div class="onboarding-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 25%;"></div>
                    </div>
                    <span class="progress-text">Step <span id="current-step">1</span> of 4</span>
                </div>
                <button type="button" class="btn btn-text skip-all" id="skip-all-btn">Skip All & Continue</button>
            </div>
            
            <div class="modal-body onboarding-body">
                <!-- Step 1: App Authenticator 2FA -->
                <div class="onboarding-step active" id="step-2fa">
                    <div class="step-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h4>Enable Two-Factor Authentication</h4>
                    <p>Secure your account with an authenticator app like Google Authenticator or Authy.</p>
                    
                    <!-- Sub-steps within 2FA setup -->
                    <div class="twofa-substeps">
                        <!-- Sub-step 1: Scan QR Code -->
                        <div class="twofa-substep active" id="twofa-scan-step">
                            <div class="substep-header">
                                <div class="substep-number">1</div>
                                <h5>Scan QR Code</h5>
                            </div>
                            <p style="margin: 0.5rem 0;">Open your authenticator app and scan this QR code:</p>
                            
                            <div class="qr-code-container">
                                <div id="qr-code"></div>
                            </div>
                            
                            <div class="manual-entry">
                                <p style="margin: 0.5rem 0; font-size: 0.9rem;"><strong>Can't scan?</strong> Enter this code manually:</p>
                                <div class="secret-code">
                                    <code id="secret-key"></code>
                                    <button type="button" class="btn btn-text" id="copy-secret">
                                        <i class="fa-solid fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>
                            
                            <div class="substep-actions">
                                <button type="button" class="btn btn-primary" id="proceed-to-verify">
                                    <i class="fa-solid fa-arrow-right"></i> I've Scanned It
                                </button>
                            </div>
                        </div>

                        <!-- Sub-step 2: Enter Verification Code -->
                        <div class="twofa-substep" id="twofa-verify-step">
                            <div class="substep-header">
                                <div class="substep-number">2</div>
                                <h5>Verify Your Setup</h5>
                            </div>
                            <p>Enter the 6-digit code from your authenticator app to confirm the setup:</p>
                            
                            <div class="form-group verification-input">
                                <label for="twofa-code-onboard">Verification Code</label>
                                <input type="text" id="twofa-code-onboard" maxlength="6" placeholder="123456" autocomplete="one-time-code" pattern="[0-9]{6}">
                                <small class="field-error" aria-live="assertive"></small>
                            </div>
                            
                            <div class="verification-help">
                                <p><i class="fa-solid fa-info-circle"></i> Enter the current 6-digit code shown in your authenticator app</p>
                            </div>
                            
                            <div class="substep-actions">
                                <button type="button" class="btn btn-text" id="back-to-scan">
                                    <i class="fa-solid fa-arrow-left"></i> Back to QR Code
                                </button>
                                <button type="button" class="btn btn-primary" id="verify-2fa" disabled>
                                    <i class="fa-solid fa-check"></i> Verify & Enable
                                </button>
                            </div>
                        </div>

                        <!-- Success State -->
                        <div class="twofa-substep" id="twofa-success-step">
                            <div class="substep-header success">
                                <div class="substep-number success">✓</div>
                                <h5>2FA Enabled Successfully!</h5>
                            </div>
                            <p>Your account is now secured with two-factor authentication.</p>
                            
                            <div class="success-info">
                                <div class="success-item">
                                    <i class="fa-solid fa-shield-check"></i>
                                    <span>Enhanced Security Active</span>
                                </div>
                                <div class="success-item">
                                    <i class="fa-solid fa-mobile-screen"></i>
                                    <span>Authenticator App Configured</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="step-actions" id="main-2fa-actions">
                        <button type="button" class="btn btn-secondary" id="skip-2fa">Skip This Step</button>
                        <button type="button" class="btn btn-primary" id="continue-to-avatar" style="display: none;">Continue to Avatar</button>
                    </div>
                </div>

                <!-- Step 2: User Avatar -->
                <div class="onboarding-step" id="step-avatar">
                    <div class="step-icon">
                        <i class="fa-solid fa-user-circle"></i>
                    </div>
                    <h4>Add Your Avatar</h4>
                    <p>Upload an avatar to personalize your ReCircle profile and help others recognize you.</p>
                    
                    <div class="avatar-upload-container">
                        <div class="avatar-preview" id="avatar-preview">
                            <div class="avatar-placeholder">
                                <i class="fa-solid fa-user"></i>
                                <span>No avatar selected</span>
                            </div>
                        </div>
                        <input type="file" id="avatar-input" accept="image/jpeg,image/png,image/jpg,image/webp" style="display: none;">
                        <div class="avatar-actions">
                            <button type="button" class="btn btn-secondary" id="upload-avatar">
                                <i class="fa-solid fa-upload"></i> Choose Avatar
                            </button>
                            <button type="button" class="btn btn-text" id="remove-avatar" style="display: none;">
                                <i class="fa-solid fa-trash"></i> Remove
                            </button>
                        </div>
                        <p class="upload-info">JPG, PNG, or WebP. Max 2MB. Recommended: 400x400px</p>
                    </div>
                    
                    <div class="step-actions">
                        <button type="button" class="btn btn-secondary" id="skip-avatar">Skip</button>
                        <button type="button" class="btn btn-primary" id="save-avatar" disabled>Save Avatar</button>
                    </div>
                </div>

                <!-- Step 3: Facial Recognition -->
                <div class="onboarding-step" id="step-face">
                    <div class="step-icon">
                        <i class="fa-solid fa-face-smile"></i>
                    </div>
                    <h4>Setup Facial Recognition</h4>
                    <p>Enable quick and secure login with your face. Your biometric data stays private and secure on our servers.</p>
                    
                    <div class="face-setup-container">
                        <div class="video-container" id="face-video-container" style="display: none;">
                            <video id="face-video" width="320" height="240" autoplay muted></video>
                            <canvas id="face-overlay" width="320" height="240"></canvas>
                            <div class="face-instructions">
                                <p><i class="fa-solid fa-info-circle"></i> Position your face in the green box</p>
                            </div>
                        </div>
                        <div id="face-status" class="face-status">Click "Enable Face Login" to start the camera</div>
                    </div>
                    
                    <div class="step-actions">
                        <button type="button" class="btn btn-secondary" id="skip-face">Skip</button>
                        <button type="button" class="btn btn-primary" id="enable-face">
                            <i class="fa-solid fa-camera"></i> Enable Face Login
                        </button>
                        <button type="button" class="btn btn-primary" id="capture-face" style="display: none;">
                            <i class="fa-solid fa-user-check"></i> Capture Face
                        </button>
                    </div>
                </div>

                <!-- Step 4: Complete -->
                <div class="onboarding-step" id="step-complete">
                    <div class="step-icon success">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <h4>Welcome to ReCircle!</h4>
                    <p>Your profile setup is complete. You're ready to start making an environmental impact!</p>
                    
                    <div class="completion-summary">
                        <div class="summary-item" id="summary-2fa">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Two-Factor Auth: <span id="summary-2fa-text" class="status-text">Not enabled</span></span>
                        </div>
                        <div class="summary-item" id="summary-avatar">
                            <i class="fa-solid fa-user-circle"></i>
                            <span>User Avatar: <span id="summary-avatar-text" class="status-text">Not added</span></span>
                        </div>
                        <div class="summary-item" id="summary-face">
                            <i class="fa-solid fa-face-smile"></i>
                            <span>Facial Recognition: <span id="summary-face-text" class="status-text">Not setup</span></span>
                        </div>
                    </div>
                    
                    <div class="step-actions">
                        <button type="button" class="btn btn-primary w-full" id="complete-onboarding">
                            <i class="fa-solid fa-rocket"></i> Start Using ReCircle
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pre-Facial Verification Alert Modal -->
    <div id="pre-facial-alert-modal" class="modal-overlay hidden" aria-hidden="true" style="z-index: 9998;">
        <div class="modal" role="dialog" aria-labelledby="pre-facial-title" style="max-width: 450px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden;">
            <!-- Header with gradient -->
            <div style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 1.5rem; text-align: center;">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-20 rounded-full mb-3 border-2 border-white border-opacity-30">
                    <i class="fa-solid fa-triangle-exclamation text-white" style="font-size: 2.5rem; line-height: 1; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));"></i>
                </div>
                <h3 id="pre-facial-title" class="text-white text-xl font-semibold mb-2">
                    Security Alert
                </h3>
                <p class="text-red-100 text-sm">
                    Multiple failed login attempts detected
                </p>
            </div>

            <!-- Body -->
            <div class="modal-body" style="padding: 2rem; background: white;">
                <div class="text-center">
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                        <div class="flex items-center justify-center mb-3">
                            <div class="bg-red-100 rounded-full p-2 mr-3">
                                <i class="fa-solid fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <h4 class="text-red-800 font-semibold">Account Protection Activated</h4>
                        </div>
                        <p class="text-red-700 text-sm leading-relaxed">
                            Too many failed attempts. Please verify your identity using facial recognition to protect your account from unauthorized access.
                        </p>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                        <div class="flex items-start">
                            <i class="fa-solid fa-info-circle text-blue-600 mr-3 mt-1"></i>
                            <div class="text-left">
                                <h5 class="text-blue-800 font-medium text-sm mb-1">What happens next?</h5>
                                <ul class="text-blue-700 text-xs space-y-1">
                                    <li>• We'll open your camera for identity verification</li>
                                    <li>• Your face will be compared with your stored profile</li>
                                    <li>• Upon success, you'll be logged in securely</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-center text-xs text-gray-500 mb-4">
                        <i class="fa-solid fa-lock mr-1"></i>
                        Your biometric data is encrypted and never stored
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div style="background: linear-gradient(to right, #f8fafc, #f1f5f9); padding: 1.5rem; border-top: 1px solid #e2e8f0;">
                <div class="flex space-x-3">
                    <button type="button" class="flex-1 px-4 py-3 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" 
                            style="background: linear-gradient(135deg, #047857 0%, #065f46 100%); border: none;"
                            id="proceed-to-facial-verification">
                        <i class="fa-solid fa-shield-check mr-2"></i>
                        Verify Identity
                    </button>
                    <button type="button" class="px-6 py-3 border-2 text-sm font-medium rounded-xl transition-all duration-200 cancel-pre-facial-btn" 
                            style="border: 2px solid #3b82f6; color: #3b82f6; background: transparent; border-radius: 50px;"
                            id="cancel-pre-facial">
                        <i class="fa-solid fa-times mr-2"></i>
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

        <!-- Inline patch: ensure correct face-api build and robust enroll even if assets aren’t rebuilt -->
        <script>
            (function() {
                function ensureVladFaceApi(cb) {
                    var src = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/dist/face-api.min.js';
                    if (typeof window.faceapi === 'undefined' || !window.faceapi.nets || !window.faceapi.nets.tinyFaceDetector) {
                        var s = document.createElement('script');
                        s.src = src; s.async = true; s.crossOrigin = 'anonymous';
                        s.onload = function() { try { cb && cb(); } catch(e) { console.error(e); } };
                        document.head.appendChild(s);
                    } else {
                        try { cb && cb(); } catch(e) { console.error(e); }
                    }
                }

                        function patchFaceRecognition() {
                            var FR = window.FaceRecognition || (typeof FaceRecognition !== 'undefined' ? FaceRecognition : null);
                            if (!FR || FR.__patched) return;
                            // Ensure both globals reference the same constructor
                            window.FaceRecognition = FR;
                            try { window['FaceRecognition'] = FR; } catch(_) {}

                            FR.prototype.enrollFace = async function() {
                        if (!this.isModelLoaded || !this.video) return false;
                        try {
                                    var options = new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.4 });
                            var detection = null;
                            for (var i = 0; i < 5; i++) {
                                detection = await faceapi
                                    .detectSingleFace(this.video, options)
                                    .withFaceLandmarks()
                                    .withFaceDescriptor();
                                if (detection) break;
                                await new Promise(function(r){ setTimeout(r, 200); });
                            }
                            if (detection) {
                                var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                                        var res = await fetch('/api/face/enroll', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                                    credentials: 'include',
                                    body: JSON.stringify({ userId: window.currentUserId, descriptor: Array.from(detection.descriptor) })
                                });
                                        if (res.ok) return true;
                                        // Fallback: verify server-side flag in case response handling failed
                                        try {
                                            var meRes = await fetch('/api/auth/me', { headers: { 'Accept': 'application/json' }, credentials: 'include' });
                                            if (meRes.ok) {
                                                var me = await meRes.json().catch(function(){ return {}; });
                                                if ((me && me.data && me.data.is_facial_registered) || me.is_facial_registered) return true;
                                            }
                                        } catch(_) {}
                                        return false;
                            }
                        } catch (e) {
                            console.error('Face enrollment failed (inline):', e);
                        }
                        return false;
                    };
                            FR.__patched = true;
                }

                document.addEventListener('DOMContentLoaded', function() {
                    ensureVladFaceApi(function(){ patchFaceRecognition(); });
                });
            })();
        </script>

    <!-- Facial Verification Fallback Modal -->
    <div id="facial-fallback-modal" class="modal-overlay hidden" aria-hidden="true">
        <div class="modal" role="dialog" aria-labelledby="facial-fallback-title" style="max-width: 700px; width: 95vw; max-height: 90vh; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; display: flex; flex-direction: column;">
            <!-- Header with gradient -->
            <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 1.25rem; text-align: center; flex-shrink: 0;">
                <div class="inline-flex items-center justify-center w-14 h-14 bg-white bg-opacity-20 rounded-full mb-2 border-2 border-white border-opacity-30">
                    <i class="fa-solid fa-video text-white" style="font-size: 1.75rem; line-height: 1; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));"></i>
                </div>
                <h3 id="facial-fallback-title" class="text-white text-lg font-semibold mb-1">
                    Security Verification Required
                </h3>
                <p class="text-green-100 text-xs">
                    Multiple failed attempts detected. Please verify your identity for account security.
                </p>
            </div>
            
            <!-- Body -->
            <div class="modal-body" style="padding: 1.5rem; background: white; flex: 1;">
                <div id="facial-fallback-content" class="w-full h-full">
                    <!-- Initial centered content container -->
                    <div id="initial-content" class="h-full flex items-center justify-center">
                        <div class="text-center max-w-sm mx-auto">
                            <div id="fallback-status" class="mb-6">
                                <p class="text-gray-600 mb-4">Click "Start Camera" to begin verification</p>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="space-y-4">
                                <button type="button" class="w-full px-6 py-3 text-white font-medium rounded-lg transition-all duration-200" 
                                        id="start-fallback-camera" 
                                        style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border: none;">
                                    <i class="fa-solid fa-video mr-2"></i>
                                    Start Camera
                                </button>
                                
                                <button type="button" class="w-full px-6 py-3 text-white font-medium rounded-lg transition-all duration-200 hidden" 
                                        id="verify-fallback-face"
                                        style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                                    <i class="fa-solid fa-shield-check mr-2"></i>
                                    Verify My Identity
                                </button>
                                
                                <div class="flex items-center justify-center text-xs text-gray-500 mt-4">
                                    <i class="fa-solid fa-lock mr-1"></i>
                                    Data encrypted & secure
                                </div>
                                
                                <div class="mt-4">
                                    <button type="button" class="px-4 py-2 border-2 text-sm font-medium rounded-lg transition-all duration-200 cancel-facial-btn" 
                                            id="cancel-facial-fallback"
                                            style="border: 2px solid #3b82f6; color: #3b82f6; background: transparent;">
                                        <i class="fa-solid fa-times mr-1"></i>
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Camera active content - Side by side layout -->
                    <div id="camera-active-content" class="hidden h-full">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 h-full items-center">
                            <!-- Camera Container -->
                            <div class="text-center">
                                <div class="video-container-wrapper" id="fallback-video-container" style="display: none;">
                                    <div class="relative bg-green-50 border border-green-200 rounded-xl p-3 inline-block">
                                        <div class="relative" style="width: 280px; height: 200px;">
                                            <video id="fallback-video" width="280" height="200" autoplay muted 
                                                   class="rounded-lg border-2 border-green-300 shadow-md" 
                                                   style="object-fit: cover; background: #1f2937;"></video>
                                            <canvas id="fallback-overlay" width="280" height="200" 
                                                    class="absolute top-0 left-0 rounded-lg pointer-events-none"></canvas>
                                            
                                            <!-- Face detection overlay -->
                                            <div class="absolute inset-0 border-2 border-green-400 rounded-lg opacity-60 animate-pulse hidden" id="face-detection-frame"></div>
                                        </div>
                                        
                                        <div class="text-center mt-3">
                                            <div class="inline-flex items-center bg-blue-50 text-blue-700 px-3 py-1 rounded text-sm">
                                                <i class="fa-solid fa-camera text-blue-500 mr-2"></i>
                                                Position your face clearly
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status Messages & Controls -->
                            <div class="space-y-4">
                                <div id="camera-status">
                                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                                        <div class="flex items-center mb-3">
                                            <div class="bg-green-100 rounded-full p-2 mr-3">
                                                <i class="fa-solid fa-video text-green-600"></i>
                                            </div>
                                            <h4 class="text-green-800 font-medium">Camera Active</h4>
                                        </div>
                                        <div class="text-xs text-green-600 mb-2">● Live Camera Feed</div>
                                        <p class="text-green-700 text-sm mb-2">
                                            Great! Your camera is working properly.
                                        </p>
                                        <p class="text-green-600 text-xs">
                                            Position your face clearly in the center and click "Verify My Identity" when ready
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons for camera active state -->
                                <div class="space-y-3">
                                    <button type="button" class="w-full px-6 py-3 text-white font-medium rounded-lg transition-all duration-200" 
                                            id="verify-fallback-face-active"
                                            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                                        <i class="fa-solid fa-shield-check mr-2"></i>
                                        Verify My Identity
                                    </button>
                                    
                                    <div class="flex items-center justify-center text-xs text-gray-500">
                                        <i class="fa-solid fa-lock mr-1"></i>
                                        Your biometric data is encrypted and secure
                                    </div>
                                    
                                    <div class="text-center">
                                        <button type="button" class="px-4 py-2 border-2 text-sm font-medium rounded-lg transition-all duration-200 cancel-facial-btn" 
                                                style="border: 2px solid #3b82f6; color: #3b82f6; background: transparent;">
                                            <i class="fa-solid fa-times mr-1"></i>
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
                
                <div id="facial-fallback-result" class="hidden">
                    <!-- Success or failure result will be inserted here -->
                </div>
            </div>
            
            
            <!-- Footer -->
            <div style="background: linear-gradient(to right, #f8fafc, #f1f5f9); padding: 0.75rem; border-top: 1px solid #e2e8f0; flex-shrink: 0;">
                <div class="flex justify-center">
                    <button type="button" class="px-4 py-1.5 border-2 text-xs font-medium rounded-lg transition-all duration-200 hidden" 
                            id="retry-password-login"
                            style="border: 2px solid #059669; color: #059669; background: transparent;">
                        <i class="fa-solid fa-key mr-1"></i>
                        Try Password Again
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    @vite(['resources/js/auth.js'])
@endpush
