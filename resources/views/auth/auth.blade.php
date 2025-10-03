@extends('layouts.app')

@section('title', 'ReCircle – Sign In / Sign Up')
@section('meta_description', 'Sign in or create your ReCircle account to join the circular economy marketplace.')

@push('head')
    <link rel="icon" href="{{ Vite::asset('resources/images/vite.svg') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <style>
        /* Onboarding Modal Styles */
        .onboarding-modal {
            max-width: 600px;
            width: 90%;
        }
        
        .onboarding-progress {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1rem 0;
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
            padding: 2rem;
        }
        
        .onboarding-step.active {
            display: block;
        }
        
        .step-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #059669;
        }
        
        .step-icon.success {
            color: #16a34a;
        }
        
        .step-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .qr-code-container {
            display: flex;
            justify-content: center;
            margin: 1rem 0;
        }
        
        .secret-code {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
            margin: 1rem 0;
        }
        
        .secret-code code {
            background: #f5f5f5;
            padding: 0.5rem;
            border-radius: 4px;
            font-family: monospace;
        }
        
        .avatar-upload-container {
            text-align: center;
            margin: 2rem 0;
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
            margin: 2rem 0;
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
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 1rem 0;
            color: #666;
        }
        
        .completion-summary {
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
                    
                    <div class="twofa-setup-container">
                        <!-- QR Code Section -->
                        <div class="qr-section" id="qr-section">
                            <div class="qr-instructions">
                                <p><strong>Step 1:</strong> Scan this QR code with your authenticator app</p>
                                <div class="qr-code-container">
                                    <div id="qr-code"></div>
                                </div>
                                <div class="manual-entry">
                                    <p><strong>Or enter manually:</strong></p>
                                    <div class="secret-code">
                                        <code id="secret-key"></code>
                                        <button type="button" class="btn btn-text" id="copy-secret">
                                            <i class="fa-solid fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Verification Section -->
                        <div class="verification-section">
                            <p><strong>Step 2:</strong> Enter the 6-digit code from your app</p>
                            <div class="form-group">
                                <label for="twofa-code-onboard">Verification Code</label>
                                <input type="text" id="twofa-code-onboard" maxlength="6" placeholder="123456" autocomplete="one-time-code" pattern="[0-9]{6}">
                                <small class="field-error" aria-live="assertive"></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="step-actions">
                        <button type="button" class="btn btn-secondary" id="skip-2fa">Skip</button>
                        <button type="button" class="btn btn-primary" id="verify-2fa" disabled>Verify & Enable</button>
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
                        <div id="face-status" class="face-status">Click "Enable" to start camera setup</div>
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
@endpush

@push('scripts')
    @vite(['resources/js/auth.js'])
@endpush
