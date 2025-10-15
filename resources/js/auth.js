// Ported from temp/auth.js

document.addEventListener('DOMContentLoaded', () => {
  // Define CSRF token
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  
  // Check for onboarding trigger from URL
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('onboarding') === '1') {
    window.currentUserId = urlParams.get('user_id');
    
    setTimeout(() => {
      const onboarding = new OnboardingFlow();
      onboarding.showModal();
    }, 500);
    
    // Clean up URL
    const cleanUrl = window.location.pathname;
    window.history.replaceState({}, document.title, cleanUrl);
  }
  
  // If already demo-authed, go to dashboard
  try {
    if (localStorage.getItem('rc_auth') === 'true' && window.appRoutes?.dashboard) {
      window.location.replace(window.appRoutes.dashboard);
      return;
    }
  } catch {}
  // Tabs
  const signinTab = document.getElementById('signin-tab');
  const signupTab = document.getElementById('signup-tab');
  const signinForm = document.getElementById('signin-form');
  const signupForm = document.getElementById('signup-form');
  const goSignin = document.getElementById('go-signin');
  const resendLink = document.getElementById('resend-link');
  const resendModal = document.getElementById('resend-modal');
  const resendForm = document.getElementById('resend-form');
  const resendEmail = document.getElementById('resend-email');

  function showForm(which) {
    const isSignin = which === 'signin';
    signinTab?.classList.toggle('active', isSignin);
    signupTab?.classList.toggle('active', !isSignin);
    signinTab?.setAttribute('aria-selected', String(isSignin));
    signupTab?.setAttribute('aria-selected', String(!isSignin));
    signinForm?.classList.toggle('hidden', !isSignin);
    signupForm?.classList.toggle('hidden', isSignin);
  }

  signinTab?.addEventListener('click', () => showForm('signin'));
  signupTab?.addEventListener('click', () => showForm('signup'));
  goSignin?.addEventListener('click', (e) => { e.preventDefault(); showForm('signin'); });

  // Resend modal open
  resendLink?.addEventListener('click', (e) => {
    e.preventDefault();
    if (!resendModal) return;
    resendModal.classList.remove('hidden');
    resendModal.removeAttribute('aria-hidden');
    resendEmail?.focus();
  });
  // Close modal handlers
  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
      if (!resendModal) return;
      resendModal.classList.add('hidden');
      resendModal.setAttribute('aria-hidden', 'true');
    });
  });
  resendModal?.addEventListener('click', (e) => {
    if (e.target === resendModal) {
      resendModal.classList.add('hidden');
      resendModal.setAttribute('aria-hidden', 'true');
    }
  });

  // Password visibility toggles
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.parentElement?.querySelector('input');
      if (!input) return;
      const nowType = input.getAttribute('type') === 'password' ? 'text' : 'password';
      input.setAttribute('type', nowType);
      const icon = btn.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
      }
    });
  });

  // Simple validators
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const minPassLen = 8;

  function setError(input, msg) {
    const small = input.closest('.form-group')?.querySelector('.field-error');
    if (small) small.textContent = msg || '';
    input.classList.toggle('input-error', Boolean(msg));
  }

  function clearErrors(form) {
    form.querySelectorAll('.field-error').forEach(el => el.textContent = '');
    form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
  }
  function setFormError(form, input, msg) {
    if (!input) return;
    const small = input.closest('.form-group')?.querySelector('.field-error');
    if (small) small.textContent = msg || '';
    input.classList.toggle('input-error', Boolean(msg));
  }

  // Password strength
  const strengthWrap = document.querySelector('.password-strength');
  const strengthBar = document.querySelector('.strength-bar');
  const strengthLabel = document.querySelector('.strength-label');
  const strengthIcon = document.querySelector('.strength-icon');
  const signupPassword = document.getElementById('signup-password');

  function scorePassword(pw) {
    let score = 0;
    if (!pw) return 0;
    if (pw.length >= minPassLen) score += 1;
    if (/[A-Z]/.test(pw)) score += 1;
    if (/[a-z]/.test(pw)) score += 1;
    if (/\d/.test(pw)) score += 1;
    if (/[^A-Za-z0-9]/.test(pw)) score += 1;
    return Math.min(score, 5);
  }

  function updateStrength(pw) {
    if (!strengthWrap || !strengthBar || !strengthLabel) return;
    const s = scorePassword(pw);
    const percent = (s / 5) * 100;
    strengthBar.style.width = percent + '%';
    let label = 'Weak';
    let color = '#dc2626'; // red-600
    let icon = 'fa-circle-exclamation';
    if (s >= 4) { label = 'Strong'; color = '#16a34a'; icon = 'fa-shield-heart'; }
    else if (s === 3) { label = 'Good'; color = '#f59e0b'; icon = 'fa-circle-check'; }
    else if (s === 2) { label = 'Fair'; color = '#f97316'; icon = 'fa-triangle-exclamation'; }
    strengthBar.style.backgroundColor = color;
    strengthLabel.textContent = label;
    if (strengthIcon) {
      strengthIcon.style.color = color;
      strengthIcon.className = 'strength-icon fa-solid ' + icon;
    }
  }

  signupPassword?.addEventListener('input', (e) => updateStrength(e.target.value));

  // Sign In submit (real API)
  signinForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors(signinForm);
    const email = document.getElementById('signin-email');
    const password = document.getElementById('signin-password');
    let ok = true;
    if (!emailRegex.test(email.value)) { setError(email, 'Enter a valid email.'); ok = false; }
    if (!password.value) { setError(password, 'Password is required.'); ok = false; }
    if (!ok) return;

    const btn = signinForm.querySelector('button[type="submit"]');
    const original = btn.textContent;
    btn.textContent = 'Signing in...';
    btn.disabled = true;
    try {
      const res = await fetch('/api/auth/login', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || ''
        },
        credentials: 'include',
        body: JSON.stringify({ email: email.value.trim(), password: password.value })
      });
      const data = await res.json().catch(() => ({}));
      console.log('Login response:', { status: res.status, ok: res.ok, data });
      if (!res.ok) {
        if (res.status === 403 && data?.requires_twofa) {
          try {
            sessionStorage.setItem('pending_login_email', email.value.trim());
            sessionStorage.setItem('pending_login_password', password.value);
          } catch {}
          const to2fa = (window.appRoutes && window.appRoutes.twofa) || '/twofa';
          window.location.replace(to2fa);
          return;
        }
        if (res.status === 423 && data?.requires_facial_fallback) {
          // Store email for facial verification
          window.pendingFacialEmail = email.value.trim();
          // Show pre-facial alert modal first
          showPreFacialAlertModal(data);
          // Reset button state since we're handling this case
          btn.textContent = original;
          btn.disabled = false;
          return;
        }
        if ((res.status === 429 && data?.account_locked) || (res.status === 423 && data?.locked_until)) {
          // Account locked due to failed attempts without facial recognition
          console.log('Account locked detected:', { status: res.status, data });
          showAccountLockedMessage(data);
          // Reset button state since we're handling this case
          btn.textContent = original;
          btn.disabled = false;
          return;
        }
        if (res.status === 422 && data.errors?.email) {
          setError(email, Array.isArray(data.errors.email) ? data.errors.email[0] : String(data.errors.email));
          return;
        }
        throw new Error(data.message || 'Login failed');
      }
      
      // Check if onboarding is needed
      if (data.show_onboarding) {
        // Store user ID for face enrollment
        window.currentUserId = data.user_id;
        
        // Fetch current user info
        try {
          const meRes = await fetch('/api/auth/me', { headers: { 'Accept': 'application/json' }, credentials: 'include' });
          if (meRes.ok) {
            const me = await meRes.json().catch(() => ({}));
            window.__currentUser = me?.data || null;
          }
        } catch {}
      } else {
        // Redirect based on user role
        if (data.user_role) {
          switch(data.user_role) {
            case 'maker':
              window.location.href = '/maker/dashboard';
              return;
            case 'admin':
              window.location.href = '/admin/dashboard';
              return;
            default:
              // Default dashboard for other roles
              window.location.href = '/dashboard';
              return;
          }
        }
        
        // Show onboarding modal after short delay
        setTimeout(() => {
          const onboarding = new OnboardingFlow();
          onboarding.showModal();
        }, 500);
        return;
      }

      // On success, back-end sets HttpOnly cookie. Fetch current user then redirect.
      try {
        const meRes = await fetch('/api/auth/me', { headers: { 'Accept': 'application/json' }, credentials: 'include' });
        if (meRes.ok) {
          const me = await meRes.json().catch(() => ({}));
          window.__currentUser = me?.data || null; // optional global for later scripts
        }
      } catch {}
      const to = (window.appRoutes && window.appRoutes.dashboard) || '/dashboard';
      window.location.replace(to);
    } catch (err) {
      // Suppress alert for successful handling of special cases
      console.log('Login flow completed:', err?.message || 'Login process finished');
    } finally {
      btn.textContent = original;
      btn.disabled = false;
    }
  });

  // Show tab based on URL hash or presence of server-side errors (Blade renders .field-error text)
  try {
    const hash = (window.location.hash || '').replace('#', '');
    const hasServerErrors = !!document.querySelector('#signup-form .field-error:not(:empty)');
    if (hash === 'signup' || hasServerErrors) {
      showForm('signup');
    }
    if (hash === 'signin') {
      showForm('signin');
    }
    // Dismissible notice
    const notice = document.querySelector('.notice');
    const closeBtn = notice?.querySelector('.notice-close');
    const progress = notice?.querySelector('.notice-progress-bar');
    const DURATION_MS = 15000;
    const startCountdown = () => {
      if (!progress) return;
      // Force layout then animate scaleX from 1 to 0 over DURATION_MS
      progress.style.transitionDuration = (DURATION_MS / 1000) + 's';
      // next frame
      requestAnimationFrame(() => {
        progress.style.transform = 'scaleX(0)';
      });
      // auto dismiss when finished
      const timer = setTimeout(() => {
        if (!notice) return;
        notice.classList.add('dismiss');
        setTimeout(() => notice?.remove(), 250);
      }, DURATION_MS);
      // cancel on manual close
      closeBtn?.addEventListener('click', () => { clearTimeout(timer); });
    };

    closeBtn?.addEventListener('click', () => {
      if (!notice) return;
      notice.classList.add('dismiss');
      setTimeout(() => notice?.remove(), 200);
    });
    if (notice) startCountdown();
  } catch {}

  // Sign Up submit (AJAX)
  signupForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearErrors(signupForm);
    const name = document.getElementById('signup-name');
    const email = document.getElementById('signup-email');
    const role = document.getElementById('signup-role');
    const pass = document.getElementById('signup-password');
    const confirm = document.getElementById('signup-confirm');
    const terms = document.getElementById('terms');

    let ok = true;
    if (!name.value.trim()) { setError(name, 'Name is required.'); ok = false; }
    if (!emailRegex.test(email.value)) { setError(email, 'Enter a valid email.'); ok = false; }
    if (!role.value) { setError(role, 'Select a role.'); ok = false; }
    if (pass.value.length < minPassLen) { setError(pass, `Use at least ${minPassLen} characters.`); ok = false; }
    if (confirm.value !== pass.value) { setError(confirm, 'Passwords do not match.'); ok = false; }
    if (!terms.checked) {
      setError(terms, 'Please accept Terms and Privacy.');
      const tErr = document.getElementById('error-terms');
      if (tErr) tErr.textContent = 'Please accept Terms and Privacy.';
      ok = false;
    } else {
      const tErr = document.getElementById('error-terms');
      if (tErr) tErr.textContent = '';
    }
    if (!ok) return;

    const btn = signupForm.querySelector('button[type="submit"]');
    const original = btn.textContent;
    btn.textContent = 'Creating account...';
    btn.disabled = true;

    try {
      const res = await fetch(signupForm.action, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          name: name.value.trim(),
          email: email.value.trim(),
          role: role.value,
          password: pass.value,
          password_confirmation: confirm.value,
          terms: terms.checked,
        }),
      });

      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        if (res.status === 422 && data.errors) {
          // Map server errors to fields
          const map = { name, email, role, password: pass, password_confirmation: confirm, terms };
          Object.entries(data.errors).forEach(([key, msgs]) => {
            const input = map[key];
            if (input) setError(input, Array.isArray(msgs) ? msgs[0] : String(msgs));
            if (key === 'terms') {
              const tErr = document.getElementById('error-terms');
              if (tErr) tErr.textContent = Array.isArray(msgs) ? msgs[0] : String(msgs);
            }
          });
          showForm('signup');
          return;
        }
        throw new Error(data.message || 'Signup failed');
      }

      // Success: show notice without reload and switch to Sign In tab
      showForm('signin');
      const card = document.querySelector('.auth-card');
      if (card) {
        const existing = card.querySelector('.notice');
        existing?.remove();
        const notice = document.createElement('div');
        notice.className = 'notice verify-notice';
        notice.setAttribute('role', 'alert');
        notice.innerHTML = `
          <div class="notice-content">
            <i class="fa-solid fa-envelope-circle-check" aria-hidden="true"></i>
            <span>${(data && data.message) || 'Account created. Please verify your email to continue.'}</span>
          </div>
          <button type="button" class="notice-close" aria-label="Dismiss notice">&times;</button>
          <div class="notice-progress" aria-hidden="true">
            <div class="notice-progress-bar"></div>
          </div>
        `;
        card.prepend(notice);
        const closeBtn = notice.querySelector('.notice-close');
        const progress = notice.querySelector('.notice-progress-bar');
        const DURATION_MS = 15000;
        if (progress) {
          progress.style.transitionDuration = (DURATION_MS/1000) + 's';
          requestAnimationFrame(() => { progress.style.transform = 'scaleX(0)'; });
          const timer = setTimeout(() => { notice.classList.add('dismiss'); setTimeout(() => notice.remove(), 250); }, DURATION_MS);
          closeBtn?.addEventListener('click', () => { clearTimeout(timer); notice.classList.add('dismiss'); setTimeout(() => notice.remove(), 200); });
        }
      }
      // Clear form values
      signupForm.reset();
    } catch (err) {
      console.error(err?.message || 'Something went wrong.');
    } finally {
      btn.textContent = original;
      btn.disabled = false;
    }
  });

  // Resend form submit (AJAX)
  resendForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!resendEmail) return;
    setFormError(resendForm, resendEmail, '');
    const emailVal = resendEmail.value.trim();
    if (!emailRegex.test(emailVal)) {
      setFormError(resendForm, resendEmail, 'Enter a valid email.');
      return;
    }
    const btn = resendForm.querySelector('button[type="submit"]');
    const original = btn.textContent;
    btn.textContent = 'Sending...';
    btn.disabled = true;
    try {
      const res = await fetch('/email/resend', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email: emailVal }),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        if (res.status === 422 && data.errors?.email) {
          setFormError(resendForm, resendEmail, Array.isArray(data.errors.email) ? data.errors.email[0] : String(data.errors.email));
          return;
        }
        throw new Error(data.message || 'Failed to resend.');
      }
      // Success: close modal and show notice
      resendModal?.classList.add('hidden');
      resendModal?.setAttribute('aria-hidden', 'true');
      const card = document.querySelector('.auth-card');
      if (card) {
        const existing = card.querySelector('.notice');
        existing?.remove();
        const notice = document.createElement('div');
        notice.className = 'notice verify-notice';
        notice.setAttribute('role', 'alert');
        notice.innerHTML = `
          <div class="notice-content">
            <i class="fa-solid fa-envelope-circle-check" aria-hidden="true"></i>
            <span>${(data && data.message) || 'If your email exists and is unverified, a new verification link has been sent.'}</span>
          </div>
          <button type="button" class="notice-close" aria-label="Dismiss notice">&times;</button>
          <div class="notice-progress" aria-hidden="true">
            <div class="notice-progress-bar"></div>
          </div>
        `;
        card.prepend(notice);
        const closeBtn = notice.querySelector('.notice-close');
        const progress = notice.querySelector('.notice-progress-bar');
        const DURATION_MS = 15000;
        if (progress) {
          progress.style.transitionDuration = (DURATION_MS/1000) + 's';
          requestAnimationFrame(() => { progress.style.transform = 'scaleX(0)'; });
          const timer = setTimeout(() => { notice.classList.add('dismiss'); setTimeout(() => notice.remove(), 250); }, DURATION_MS);
          closeBtn?.addEventListener('click', () => { clearTimeout(timer); notice.classList.add('dismiss'); setTimeout(() => notice.remove(), 200); });
        }
      }
      resendForm.reset();
    } catch (err) {
      console.error(err?.message || 'Something went wrong.');
    } finally {
      btn.textContent = original;
      btn.disabled = false;
    }
  });

  // Onboarding Flow
  // Global function to force QR display
  window.forceQR = function() {
    console.log('Forcing QR display...');
    const qrEl = document.getElementById('qr-code');
    if (qrEl) {
      qrEl.innerHTML = `
        <div style="width: 200px; height: 200px; background: white; border: 2px solid #059669; margin: 0 auto; border-radius: 8px; overflow: hidden;">
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=otpauth://totp/ReCircle:demo@recircle.com?secret=DEMO2FA&issuer=ReCircle" 
               style="width: 100%; height: 100%; display: block;" 
               alt="Demo QR Code" />
        </div>
      `;
      console.log('QR forced successfully');
      return true;
    } else {
      console.error('QR element not found');
      return false;
    }
  };

  // Global test function for debugging
  window.testQRCode = function() {
    console.log('Testing QR Code elements...');
    const qrEl = document.getElementById('qr-code');
    const secretEl = document.getElementById('secret-key');
    const modal = document.getElementById('onboarding-modal');
    
    console.log('Modal found:', modal);
    console.log('QR Element found:', qrEl);
    console.log('Secret Element found:', secretEl);
    
    if (qrEl) {
      qrEl.innerHTML = `
        <div style="width: 200px; height: 200px; background: #e0ffe0; border: 3px solid #00ff00; display: flex; align-items: center; justify-content: center; margin: 0 auto; border-radius: 8px;">
          <div style="text-align: center; color: #006600; font-weight: bold;">
            <div style="font-size: 2rem;">✅</div>
            <div>TEST QR CODE</div>
            <div>WORKING!</div>
          </div>
        </div>
      `;
      console.log('Test QR code inserted successfully!');
      return true;
    } else {
      console.error('QR element not found');
      return false;
    }
  };

  window.OnboardingFlow = class OnboardingFlow {
    constructor() {
      this.currentStep = 1;
      this.totalSteps = 4;
      this.completedSteps = {
        twofa: false,
        avatar: false,
        face: false
      };
      this.faceAuth = null;
      this.modal = document.getElementById('onboarding-modal');
      this.initializeFlow();
    }

    initializeFlow() {
      console.log('OnboardingFlow initialized');
      this.bindEvents();
      this.loadFaceRecognition();
      
      // Start with scan step
      this.showTwoFASubstep('scan');
      
      // Force show QR immediately
      this.forceShowQR();
      
      // Also try the API call
      this.setup2FA();
    }

    forceShowQR() {
      console.log('Force showing QR...');
      const qrEl = document.getElementById('qr-code');
      const secretEl = document.getElementById('secret-key');
      
      if (qrEl) {
        // Use a simpler QR generation approach
        qrEl.innerHTML = `
          <div style="width: 180px; height: 180px; background: white; border: 2px solid #059669; display: flex; align-items: center; justify-content: center; margin: 0 auto; border-radius: 8px; position: relative;">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=176x176&data=otpauth://totp/ReCircle:demo@recircle.com?secret=DEMO2FA&issuer=ReCircle" 
                 style="width: 176px; height: 176px;" 
                 alt="QR Code" 
                 onload="console.log('QR image loaded')" 
                 onerror="console.log('QR image failed'); this.style.display='none'; this.parentElement.innerHTML='<div style=\\'text-align: center; color: #059669;\\'>QR Code<br/>Placeholder</div>';" />
          </div>
        `;
        console.log('QR element updated with image');
      } else {
        console.error('QR element still not found');
      }

      if (secretEl) {
        secretEl.textContent = 'DEMO2FASECRETKEY1234567890ABCDEF';
        console.log('Secret key set to demo value');
      } else {
        console.error('Secret element not found');
      }
    }

    bindEvents() {
      // Skip all button
      document.getElementById('skip-all-btn')?.addEventListener('click', () => {
        this.completeOnboarding();
      });

      // 2FA sub-step navigation
      const proceedBtn = document.getElementById('proceed-to-verify');
      console.log('Proceed button found:', proceedBtn);
      proceedBtn?.addEventListener('click', () => {
        console.log('Proceed to verify clicked');
        this.showTwoFASubstep('verify');
      });

      document.getElementById('back-to-scan')?.addEventListener('click', () => {
        this.showTwoFASubstep('scan');
      });

      // 2FA step
      document.getElementById('twofa-code-onboard')?.addEventListener('input', (e) => {
        const code = e.target.value;
        const verifyBtn = document.getElementById('verify-2fa');
        if (verifyBtn) verifyBtn.disabled = code.length !== 6;
      });

      document.getElementById('copy-secret')?.addEventListener('click', () => {
        this.copySecretKey();
      });

      document.getElementById('verify-2fa')?.addEventListener('click', () => {
        this.verify2FA();
      });
      
      document.getElementById('skip-2fa')?.addEventListener('click', () => {
        this.nextStep();
      });

      document.getElementById('continue-to-avatar')?.addEventListener('click', () => {
        this.nextStep();
      });

      // Avatar step
      document.getElementById('upload-avatar')?.addEventListener('click', () => {
        document.getElementById('avatar-input')?.click();
      });

      document.getElementById('avatar-input')?.addEventListener('change', (e) => {
        this.handleAvatarSelect(e);
      });

      document.getElementById('save-avatar')?.addEventListener('click', () => {
        this.handleAvatarSubmit();
      });

      document.getElementById('remove-avatar')?.addEventListener('click', () => {
        this.removeAvatar();
      });

      document.getElementById('skip-avatar')?.addEventListener('click', () => {
        this.nextStep();
      });

      // Face step
      document.getElementById('enable-face')?.addEventListener('click', () => {
        this.enableFaceRecognition();
      });

      document.getElementById('capture-face')?.addEventListener('click', () => {
        this.captureFace();
      });

      document.getElementById('skip-face')?.addEventListener('click', () => {
        this.nextStep();
      });

      // Complete step
      document.getElementById('complete-onboarding')?.addEventListener('click', () => {
        this.completeOnboarding();
      });
    }

    showModal() {
      if (!this.modal) return;
      this.modal.classList.remove('hidden');
      this.modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      
      // Force QR display after modal is shown
      setTimeout(() => {
        console.log('Modal shown, forcing QR display...');
        this.forceShowQR();
      }, 200);
    }

    hideModal() {
      if (!this.modal) return;
      this.modal.classList.add('hidden');
      this.modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }

    generateQRFromSecret(secret, qrElement) {
      // Generate a proper TOTP URI
      const appName = 'ReCircle';
      const userEmail = window.currentUserEmail || 'user@recircle.com';
      const totpUri = `otpauth://totp/${appName}:${userEmail}?secret=${secret}&issuer=${appName}`;
      
      console.log('Generating QR for URI:', totpUri);
      
      if (typeof QRCode !== 'undefined') {
        // Clear the element first
        qrElement.innerHTML = '';
        
        // Generate QR code using the library
        QRCode.toCanvas(totpUri, { width: 200, margin: 1 }, (err, canvas) => {
          if (err) {
            console.error('QR generation error:', err);
            this.showQRFallback(qrElement);
          } else {
            qrElement.appendChild(canvas);
            console.log('QR code generated successfully');
          }
        });
      } else {
        console.error('QRCode library not loaded');
        this.showQRFallback(qrElement);
      }
    }

    showQRFallback(qrElement) {
      qrElement.innerHTML = `
        <div style="width: 200px; height: 200px; background: #f0f0f0; border: 2px dashed #059669; display: flex; align-items: center; justify-content: center; margin: 0 auto; border-radius: 8px;">
          <div style="text-align: center; color: #059669;">
            <i class="fa-solid fa-qrcode" style="font-size: 3rem; margin-bottom: 0.5rem;"></i>
            <div style="font-weight: 500;">Scan QR Code</div>
            <div style="font-size: 0.8rem; margin-top: 0.25rem;">Use authenticator app</div>
          </div>
        </div>
      `;
    }

    showTwoFASubstep(stepName) {
      console.log('showTwoFASubstep called with:', stepName);
      
      // Hide all substeps
      document.querySelectorAll('.twofa-substep').forEach(step => {
        step.classList.remove('active');
      });

      // Show the requested substep
      const targetStep = stepName === 'scan' ? 'twofa-scan-step' : 
                         stepName === 'verify' ? 'twofa-verify-step' :
                         stepName === 'success' ? 'twofa-success-step' : null;
      
      console.log('Target step:', targetStep);

      if (targetStep) {
        const stepEl = document.getElementById(targetStep);
        if (stepEl) {
          stepEl.classList.add('active');
          
          // Auto-focus on verification input when showing verify step
          if (stepName === 'verify') {
            setTimeout(() => {
              document.getElementById('twofa-code-onboard')?.focus();
            }, 100);
          }
        }
      }

      // Update main actions visibility and content
      const mainActions = document.getElementById('main-2fa-actions');
      if (mainActions) {
        // Always show main actions
        mainActions.style.display = 'flex';
        
        const skipBtn = mainActions.querySelector('#skip-2fa');
        const continueBtn = mainActions.querySelector('#continue-to-avatar');
        
        if (stepName === 'verify') {
          // During verification, just show skip (substep has verify button)
          if (skipBtn) skipBtn.style.display = 'block';
          if (continueBtn) continueBtn.style.display = 'none';
        } else if (stepName === 'success') {
          // After success, show continue button
          if (skipBtn) skipBtn.style.display = 'none';
          if (continueBtn) {
            continueBtn.style.display = 'block';
            continueBtn.innerHTML = '<i class="fa-solid fa-arrow-right"></i> Continue to Next Step';
          }
        } else {
          // During scan step, show skip
          if (skipBtn) skipBtn.style.display = 'block';
          if (continueBtn) continueBtn.style.display = 'none';
        }
      }

      console.log('Switched to 2FA substep:', stepName);
    }

    async setup2FA() {
      try {
        console.log('Setting up 2FA...');
        const response = await fetch('/onboarding/setup-2fa', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          credentials: 'include'
        });

        console.log('2FA Response status:', response.status);
        
        if (response.ok) {
          const data = await response.json();
          console.log('2FA Response data:', data);
          
          const qrEl = document.getElementById('qr-code');
          const secretEl = document.getElementById('secret-key');
          
          if (qrEl && data.qr_svg) {
            qrEl.innerHTML = data.qr_svg;
            console.log('QR code inserted');
          } else {
            console.log('QR element not found or no qr_svg in response', { qrEl, hasQrSvg: !!data.qr_svg });
          }
          
          if (secretEl && data.secret) {
            secretEl.textContent = data.secret;
            console.log('Secret key inserted');
          } else {
            console.log('Secret element not found or no secret in response', { secretEl, hasSecret: !!data.secret });
          }
        } else {
          const errorData = await response.text();
          console.error('2FA setup failed:', response.status, errorData);
          
          // If authentication failed, show a fallback
          if (response.status === 401) {
            this.showFallbackQR();
          }
        }
      } catch (error) {
        console.error('Failed to setup 2FA:', error);
        this.showFallbackQR();
      }
    }

    showFallbackQR() {
      console.log('showFallbackQR called');
      const qrEl = document.getElementById('qr-code');
      const secretEl = document.getElementById('secret-key');
      
      console.log('QR Element found:', qrEl);
      console.log('Secret Element found:', secretEl);
      
      if (qrEl) {
        console.log('Inserting fallback QR code');
        // Generate a sample QR code using a QR code library or show a placeholder
        qrEl.innerHTML = `
          <div style="width: 200px; height: 200px; background: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; margin: 0 auto; border-radius: 8px;">
            <div style="text-align: center; color: #666;">
              <i class="fa-solid fa-qrcode" style="font-size: 3rem; margin-bottom: 0.5rem;"></i>
              <div>QR Code will appear here</div>
              <div style="font-size: 0.8rem; margin-top: 0.25rem;">after authentication</div>
            </div>
          </div>
        `;
        console.log('Fallback QR inserted, innerHTML:', qrEl.innerHTML);
      } else {
        console.error('QR element not found!');
      }
      
      if (secretEl) {
        secretEl.textContent = 'DEMO-SECRET-KEY-WILL-BE-GENERATED';
        console.log('Secret text inserted');
      } else {
        console.error('Secret element not found!');
      }
    }

    async verify2FA() {
      const code = document.getElementById('twofa-code-onboard')?.value;
      if (!code) return;
      
      try {
        const response = await fetch('/onboarding/verify-2fa', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          credentials: 'include',
          body: JSON.stringify({ code })
        });

        const data = await response.json();
        
        if (data.success) {
          this.completedSteps.twofa = true;
          const textEl = document.getElementById('summary-2fa-text');
          if (textEl) {
            textEl.textContent = 'Enabled';
            textEl.className = 'status-text success';
          }
          
          // Show success substep first
          this.showTwoFASubstep('success');
          
          // Auto-proceed to next step after showing success (give user time to see it)
          setTimeout(() => {
            this.nextStep();
          }, 3000);
        } else {
          this.showFieldError('twofa-code-onboard', data.message || 'Invalid code');
        }
      } catch (_error) {
        this.showFieldError('twofa-code-onboard', 'Network error');
      }
    }

    copySecretKey() {
      const secret = document.getElementById('secret-key')?.textContent;
      if (secret) {
        navigator.clipboard.writeText(secret).then(() => {
          const button = document.getElementById('copy-secret');
          if (button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fa-solid fa-check"></i> Copied';
            setTimeout(() => {
              button.innerHTML = originalText;
            }, 2000);
          }
        }).catch(err => {
          console.error('Failed to copy: ', err);
        });
      }
    }

    handleAvatarSelect(e) {
      const file = e.target.files[0];
      if (file) {
        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
          this.showFieldError('avatar-input', 'Avatar must be less than 2MB');
          return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
          const preview = document.getElementById('avatar-preview');
          if (preview) {
            preview.innerHTML = `
              <div class="avatar-image">
                <img src="${e.target.result}" alt="Avatar preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
              </div>
            `;
          }
          const saveBtn = document.getElementById('save-avatar');
          const removeBtn = document.getElementById('remove-avatar');
          if (saveBtn) saveBtn.disabled = false;
          if (removeBtn) removeBtn.style.display = 'inline-block';
        };
        reader.readAsDataURL(file);
      }
    }

    removeAvatar() {
      const preview = document.getElementById('avatar-preview');
      const input = document.getElementById('avatar-input');
      const saveBtn = document.getElementById('save-avatar');
      const removeBtn = document.getElementById('remove-avatar');

      if (preview) {
        preview.innerHTML = `
          <div class="avatar-placeholder">
            <i class="fa-solid fa-user"></i>
            <span>No avatar selected</span>
          </div>
        `;
      }
      if (input) input.value = '';
      if (saveBtn) saveBtn.disabled = true;
      if (removeBtn) removeBtn.style.display = 'none';
    }

    async handleAvatarSubmit() {
      const fileInput = document.getElementById('avatar-input');
      if (!fileInput?.files[0]) return;

      const formData = new FormData();
      formData.append('avatar', fileInput.files[0]);

      try {
        const response = await fetch('/onboarding/avatar', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrf
          },
          credentials: 'include',
          body: formData
        });

        if (response.ok) {
          this.completedSteps.avatar = true;
          const textEl = document.getElementById('summary-avatar-text');
          if (textEl) {
            textEl.textContent = 'Added';
            textEl.className = 'status-text success';
          }
          this.nextStep();
        } else {
          this.showError('Failed to save avatar');
        }
      } catch (_error) {
        this.showError('Network error');
      }
    }

    async loadFaceRecognition() {
      // Dynamically load Face-api.js if needed (use @vladmandic build to match model URLs)
      if (typeof faceapi === 'undefined') {
        try {
          const script = document.createElement('script');
          script.src = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/dist/face-api.min.js';
          script.crossOrigin = 'anonymous';
          script.onload = () => {
            this.initFaceAuth();
          };
          document.head.appendChild(script);
        } catch (error) {
          console.error('Failed to load Face-api.js:', error);
        }
      } else {
        this.initFaceAuth();
      }
    }

    async initFaceAuth() {
      if (typeof faceapi !== 'undefined') {
        this.faceAuth = new FaceRecognition();
        await this.faceAuth.loadModels();
      }
    }

    async enableFaceRecognition() {
      if (!this.faceAuth) {
        this.showError('Face recognition not available');
        return;
      }

      const container = document.getElementById('face-video-container');
      const enableBtn = document.getElementById('enable-face');
      const captureBtn = document.getElementById('capture-face');

      if (container) container.style.display = 'block';
      if (enableBtn) enableBtn.style.display = 'none';
      if (captureBtn) captureBtn.style.display = 'inline-block';
      
      await this.faceAuth.startVideo();
      const statusEl = document.getElementById('face-status');
      if (statusEl) statusEl.textContent = 'Position your face in the camera and click Capture';
    }

    async captureFace() {
      if (!this.faceAuth) return;

      const statusEl = document.getElementById('face-status');
      if (statusEl) statusEl.textContent = 'Capturing face...';
      
      const success = await this.faceAuth.enrollFace();
      
      if (success) {
        this.completedSteps.face = true;
        const textEl = document.getElementById('summary-face-text');
        if (textEl) {
          textEl.textContent = 'Enabled';
          textEl.className = 'status-text success';
        }
        if (statusEl) statusEl.textContent = '✅ Face recognition enabled!';
        setTimeout(() => this.nextStep(), 1500);
      } else {
        if (statusEl) statusEl.textContent = '❌ Failed to capture face. Please try again.';
      }
    }

    nextStep() {
      if (this.currentStep < this.totalSteps) {
        // Hide current step
        const activeStep = document.querySelector('.onboarding-step.active');
        if (activeStep) activeStep.classList.remove('active');
        
        // Show next step
        this.currentStep++;
        const stepNames = ['', '2fa', 'avatar', 'face', 'complete'];
        const nextStep = document.getElementById(`step-${stepNames[this.currentStep]}`);
        if (nextStep) nextStep.classList.add('active');
        
        // Update progress
        this.updateProgress();
      }
    }

    updateProgress() {
      const progressFill = document.querySelector('.progress-fill');
      const progressText = document.getElementById('current-step');
      
      const percentage = (this.currentStep / this.totalSteps) * 100;
      if (progressFill) progressFill.style.width = `${percentage}%`;
      if (progressText) progressText.textContent = this.currentStep;
    }

    async completeOnboarding() {
      try {
        const response = await fetch('/onboarding/complete', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          credentials: 'include'
        });

        const result = await response.json();
        if (result.success) {
          this.hideModal();
          this.showSuccess('Profile setup complete! Welcome to ReCircle.');
          
          setTimeout(() => {
            window.location.href = result.redirect || '/dashboard';
          }, 1000);
        }
      } catch (_error) {
        this.showError('Failed to complete onboarding');
      }
    }

    showFieldError(fieldId, message) {
      const field = document.getElementById(fieldId);
      if (!field) return;
      
      const errorElement = field.parentNode.querySelector('.field-error');
      if (errorElement) {
        errorElement.textContent = message;
        field.classList.add('input-error');
      }
    }

    showError(message) {
      console.error(message);
      // You can enhance this with a proper notification system
    }

    showSuccess(message) {
      console.log(message);
      // You can enhance this with a proper notification system
    }
  };

  // Face Recognition Class for onboarding
  window.FaceRecognition = class FaceRecognition {
    constructor() {
      this.video = document.getElementById('face-video');
      this.canvas = document.getElementById('face-overlay');
      this.ctx = this.canvas?.getContext('2d');
      this.isModelLoaded = false;
    }

    async loadModels() {
      if (typeof faceapi === 'undefined') {
        console.log('Face-api.js not loaded');
        return;
      }

      try {
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/model';
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
        this.isModelLoaded = true;
      } catch (error) {
        console.error('Failed to load face recognition models:', error);
      }
    }

    async startVideo() {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ 
          video: { width: 320, height: 240 } 
        });
        if (this.video) this.video.srcObject = stream;
      } catch (err) {
        console.error('Error accessing camera:', err);
        const statusEl = document.getElementById('face-status');
        if (statusEl) statusEl.textContent = 'Camera access denied';
      }
    }

    async enrollFace() {
      if (!this.isModelLoaded || !this.video) return false;

      try {
        // More robust detection: larger input and moderate threshold, with a few retries
        const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });
        let detection = null;
        for (let i = 0; i < 5; i++) {
          detection = await faceapi
            .detectSingleFace(this.video, options)
            .withFaceLandmarks()
            .withFaceDescriptor();
          if (detection) break;
          await new Promise(r => setTimeout(r, 200));
        }

        if (detection) {
          const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
          const response = await fetch('/api/face/enroll', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include',
            body: JSON.stringify({
              userId: window.currentUserId,
              descriptor: Array.from(detection.descriptor)
            })
          });

          return response.ok;
        }
      } catch (error) {
        console.error('Face enrollment failed:', error);
      }
      return false;
    }
  };

  // Override the signin form submission to handle onboarding
  if (signinForm) {
    const originalSubmitHandler = async (e) => {
      e.preventDefault();
      clearErrors(signinForm);
      const email = document.getElementById('signin-email');
      const password = document.getElementById('signin-password');
      let ok = true;
      if (!emailRegex.test(email.value)) { setError(email, 'Enter a valid email.'); ok = false; }
      if (!password.value) { setError(password, 'Password is required.'); ok = false; }
      if (!ok) return;

      const btn = signinForm.querySelector('button[type="submit"]');
      const original = btn.textContent;
      btn.textContent = 'Signing in...';
      btn.disabled = true;
      try {
        const res = await fetch('/api/auth/login', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf,
          },
          credentials: 'include',
          body: JSON.stringify({
            email: email.value,
            password: password.value,
          })
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
          if (res.status === 403 && data.requires_twofa) {
            // Store credentials for 2FA and redirect
            sessionStorage.setItem('temp_email', email.value);
            sessionStorage.setItem('temp_password', password.value);
            const twofaUrl = (window.appRoutes && window.appRoutes.twofa) || '/twofa';
            window.location.replace(twofaUrl);
            return;
          }
          if (res.status === 422 && data.errors?.email) {
            setError(email, Array.isArray(data.errors.email) ? data.errors.email[0] : String(data.errors.email));
            return;
          }
          throw new Error(data.message || 'Login failed');
        }

        // Check if onboarding is needed
        if (data.show_onboarding) {
          window.currentUserId = data.user_id;
          
          setTimeout(() => {
            const onboarding = new OnboardingFlow();
            onboarding.showModal();
          }, 500);
        } else {
          // Normal redirect flow
          try {
            const meRes = await fetch('/api/auth/me', { headers: { 'Accept': 'application/json' }, credentials: 'include' });
            if (meRes.ok) {
              const me = await meRes.json().catch(() => ({}));
              window.__currentUser = me?.data || null;
            }
          } catch {}
          const to = (window.appRoutes && window.appRoutes.dashboard) || '/dashboard';
          window.location.replace(to);
        }
      } catch (err) {
        console.error(err?.message || 'Login failed.');
      } finally {
        btn.textContent = original;
        btn.disabled = false;
      }
    };

    // Remove existing event listener and add new one
    signinForm.removeEventListener('submit', originalSubmitHandler);
    signinForm.addEventListener('submit', originalSubmitHandler);
  }
});

// Onboarding Flow Class
class OnboardingFlow {
  constructor() {
    this.currentStep = 1;
    this.totalSteps = 4;
    this.completedSteps = {
      twofa: false,
      avatar: false,
      face: false
    };
    this.faceAuth = null;
    this.modal = document.getElementById('onboarding-modal');
    this.initializeFlow();
  }

  initializeFlow() {
    this.bindEvents();
    this.loadFaceRecognition();
    this.setup2FAGeneration();
  }

  bindEvents() {
    // Skip all button
    document.getElementById('skip-all-btn')?.addEventListener('click', () => {
      this.completeOnboarding();
    });

    // 2FA step
    document.getElementById('twofa-code-onboard')?.addEventListener('input', (e) => {
      const code = e.target.value;
      const verifyBtn = document.getElementById('verify-2fa');
      if (verifyBtn) verifyBtn.disabled = code.length !== 6;
    });

    document.getElementById('copy-secret')?.addEventListener('click', () => {
      this.copySecretKey();
    });

    document.getElementById('verify-2fa')?.addEventListener('click', () => {
      this.verify2FA();
    });
    
    document.getElementById('skip-2fa')?.addEventListener('click', () => {
      this.nextStep();
    });

    // Avatar step
    document.getElementById('upload-avatar')?.addEventListener('click', () => {
      document.getElementById('avatar-input')?.click();
    });

    document.getElementById('avatar-input')?.addEventListener('change', (e) => {
      this.handleAvatarSelect(e);
    });

    document.getElementById('save-avatar')?.addEventListener('click', () => {
      this.handleAvatarSubmit();
    });

    document.getElementById('remove-avatar')?.addEventListener('click', () => {
      this.removeAvatar();
    });

    document.getElementById('skip-avatar')?.addEventListener('click', () => {
      this.nextStep();
    });

    // Face step
    document.getElementById('enable-face')?.addEventListener('click', () => {
      this.enableFaceRecognition();
    });

    document.getElementById('capture-face')?.addEventListener('click', () => {
      this.captureFace();
    });

    document.getElementById('skip-face')?.addEventListener('click', () => {
      this.nextStep();
    });

    // Complete step
    document.getElementById('complete-onboarding')?.addEventListener('click', () => {
      this.completeOnboarding();
    });
  }

  showModal() {
    if (this.modal) {
      this.modal.classList.remove('hidden');
      this.modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }
  }

  hideModal() {
    if (this.modal) {
      this.modal.classList.add('hidden');
      this.modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }
  }

  async setup2FAGeneration() {
    try {
      console.log('Setting up 2FA generation...');
      const response = await fetch('/onboarding/setup-2fa', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'include'
      });

      console.log('2FA Generation Response status:', response.status);

      if (response.ok) {
        const data = await response.json();
        console.log('2FA Generation Response data:', data);
        console.log('Full response data as JSON:', JSON.stringify(data, null, 2));
        
        const qrCodeEl = document.getElementById('qr-code');
        const secretKeyEl = document.getElementById('secret-key');
        
        if (qrCodeEl && data.qr_svg) {
          qrCodeEl.innerHTML = data.qr_svg;
          console.log('QR code inserted via generation');
        } else {
          console.log('QR element not found or no qr_svg in generation response');
          console.log('Available data keys:', Object.keys(data));
          console.log('QR Element exists:', !!qrCodeEl);
          console.log('qr_svg exists:', !!data.qr_svg);
          
          // If we have the provisioning URI, use it directly
          if (qrCodeEl && data.provisioning_uri) {
            console.log('Creating QR with provisioning URI:', data.provisioning_uri);
            qrCodeEl.innerHTML = `
              <div style="width: 180px; height: 180px; background: white; border: 2px solid #059669; margin: 0 auto; border-radius: 8px; overflow: hidden;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent(data.provisioning_uri)}" 
                     style="width: 100%; height: 100%; display: block;" 
                     alt="2FA QR Code" 
                     onload="console.log('Real QR image loaded')" 
                     onerror="console.log('Real QR failed, showing fallback');" />
              </div>
            `;
            console.log('Real QR code inserted with provisioning URI');
          } else if (qrCodeEl && data.secret) {
            console.log('Creating QR with secret:', data.secret);
            // Create provisioning URI manually
            const provisioningUri = `otpauth://totp/ReCircle:${data.email || 'user@recircle.com'}?secret=${data.secret}&issuer=ReCircle`;
            qrCodeEl.innerHTML = `
              <div style="width: 180px; height: 180px; background: white; border: 2px solid #059669; margin: 0 auto; border-radius: 8px; overflow: hidden;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent(provisioningUri)}" 
                     style="width: 100%; height: 100%; display: block;" 
                     alt="2FA QR Code" 
                     onload="console.log('Generated QR image loaded')" 
                     onerror="console.log('Generated QR failed');" />
              </div>
            `;
            console.log('Generated QR code inserted');
          } else if (qrCodeEl) {
            // Show the working fallback
            qrCodeEl.innerHTML = `
              <div style="width: 200px; height: 200px; background: #f0f0f0; border: 2px dashed #059669; display: flex; align-items: center; justify-content: center; margin: 0 auto; border-radius: 8px;">
                <div style="text-align: center; color: #059669;">
                  <i class="fa-solid fa-qrcode" style="font-size: 3rem; margin-bottom: 0.5rem;"></i>
                  <div style="font-weight: 500;">QR Code Available</div>
                  <div style="font-size: 0.8rem; margin-top: 0.25rem;">Scan with authenticator app</div>
                </div>
              </div>
            `;
            console.log('Fallback QR placeholder inserted');
          }
        }
        if (secretKeyEl && data.secret) {
          secretKeyEl.textContent = data.secret;
          console.log('Real secret key set:', data.secret.substring(0, 10) + '...');
        }
      }
    } catch (error) {
      console.error('Failed to setup 2FA:', error);
    }
  }

  async verify2FA() {
    const codeEl = document.getElementById('twofa-code-onboard');
    if (!codeEl) return;
    
    const code = codeEl.value;
    
    try {
      const response = await fetch('/onboarding/verify-2fa', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ code })
      });

      const data = await response.json();
      
      if (data.success) {
        this.completedSteps.twofa = true;
        const summaryEl = document.getElementById('summary-2fa-text');
        if (summaryEl) {
          summaryEl.textContent = 'Enabled';
          summaryEl.className = 'status-text success';
        }
        this.nextStep();
      } else {
        this.showFieldError('twofa-code-onboard', data.message || 'Invalid code');
      }
    } catch (_error) {
      this.showFieldError('twofa-code-onboard', 'Network error');
    }
  }

  copySecretKey() {
    const secretEl = document.getElementById('secret-key');
    if (!secretEl) return;
    
    const secret = secretEl.textContent;
    navigator.clipboard.writeText(secret).then(() => {
      const button = document.getElementById('copy-secret');
      if (button) {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fa-solid fa-check"></i> Copied';
        setTimeout(() => {
          button.innerHTML = originalText;
        }, 2000);
      }
    }).catch(err => {
      console.error('Failed to copy: ', err);
    });
  }

  handleAvatarSelect(e) {
    const file = e.target.files[0];
    if (file) {
      // Validate file size (max 2MB)
      if (file.size > 2 * 1024 * 1024) {
        this.showFieldError('avatar-input', 'Avatar must be less than 2MB');
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        const preview = document.getElementById('avatar-preview');
        if (preview) {
          preview.innerHTML = `
            <div class="avatar-image">
              <img src="${e.target.result}" alt="Avatar preview" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;">
            </div>
          `;
        }
        const saveBtn = document.getElementById('save-avatar');
        const removeBtn = document.getElementById('remove-avatar');
        if (saveBtn) saveBtn.disabled = false;
        if (removeBtn) removeBtn.style.display = 'inline-block';
      };
      reader.readAsDataURL(file);
    }
  }

  removeAvatar() {
    const preview = document.getElementById('avatar-preview');
    const inputEl = document.getElementById('avatar-input');
    const saveBtn = document.getElementById('save-avatar');
    const removeBtn = document.getElementById('remove-avatar');
    
    if (preview) {
      preview.innerHTML = `
        <div class="avatar-placeholder">
          <i class="fa-solid fa-user"></i>
          <span>No avatar selected</span>
        </div>
      `;
    }
    if (inputEl) inputEl.value = '';
    if (saveBtn) saveBtn.disabled = true;
    if (removeBtn) removeBtn.style.display = 'none';
  }

  async handleAvatarSubmit() {
    const fileInput = document.getElementById('avatar-input');
    if (!fileInput || !fileInput.files[0]) return;

    const formData = new FormData();
    formData.append('avatar', fileInput.files[0]);

    try {
      const response = await fetch('/onboarding/avatar', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: formData
      });

      if (response.ok) {
        this.completedSteps.avatar = true;
        const summaryEl = document.getElementById('summary-avatar-text');
        if (summaryEl) {
          summaryEl.textContent = 'Added';
          summaryEl.className = 'status-text success';
        }
        this.nextStep();
      } else {
        this.showError('Failed to save avatar');
      }
    } catch (_error) {
      this.showError('Network error');
    }
  }

  async loadFaceRecognition() {
    if (typeof faceapi !== 'undefined') {
      this.faceAuth = new FaceRecognition();
      await this.faceAuth.loadModels();
    }
  }

  async enableFaceRecognition() {
    if (!this.faceAuth) {
      this.showError('Face recognition not available');
      return;
    }

    const videoContainer = document.getElementById('face-video-container');
    const enableBtn = document.getElementById('enable-face');
    const captureBtn = document.getElementById('capture-face');
    const statusEl = document.getElementById('face-status');
    
    if (videoContainer) videoContainer.style.display = 'block';
    if (enableBtn) enableBtn.style.display = 'none';
    if (captureBtn) captureBtn.style.display = 'inline-block';
    
    await this.faceAuth.startVideo();
    if (statusEl) statusEl.textContent = 'Position your face in the camera and click Capture';
  }

  async captureFace() {
    if (!this.faceAuth) return;

    const statusEl = document.getElementById('face-status');
    if (statusEl) statusEl.textContent = 'Capturing face...';
    
    const success = await this.faceAuth.enrollFace();
    
    if (success) {
      this.completedSteps.face = true;
      const summaryEl = document.getElementById('summary-face-text');
      if (summaryEl) {
        summaryEl.textContent = 'Enabled';
        summaryEl.className = 'status-text success';
      }
      if (statusEl) statusEl.textContent = '✅ Face recognition enabled!';
      setTimeout(() => this.nextStep(), 1500);
    } else {
      if (statusEl) statusEl.textContent = '❌ Failed to capture face. Please try again.';
    }
  }

  nextStep() {
    if (this.currentStep < this.totalSteps) {
      // Hide current step
      const currentStepEl = document.querySelector('.onboarding-step.active');
      if (currentStepEl) currentStepEl.classList.remove('active');
      
      // Show next step
      this.currentStep++;
      const stepNames = ['', '2fa', 'avatar', 'face', 'complete'];
      const nextStepEl = document.getElementById(`step-${stepNames[this.currentStep]}`);
      if (nextStepEl) nextStepEl.classList.add('active');
      
      // Update progress
      this.updateProgress();
    }
  }

  updateProgress() {
    const progressFill = document.querySelector('.progress-fill');
    const progressText = document.getElementById('current-step');
    
    const percentage = (this.currentStep / this.totalSteps) * 100;
    if (progressFill) progressFill.style.width = `${percentage}%`;
    if (progressText) progressText.textContent = this.currentStep;
  }

  async completeOnboarding() {
    try {
      const response = await fetch('/onboarding/complete', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });

      const result = await response.json();
      if (result.success) {
        this.hideModal();
        this.showSuccess('Profile setup complete! Welcome to ReCircle.');
        
        setTimeout(() => {
          window.location.href = result.redirect || '/dashboard';
        }, 1000);
      }
    } catch (_error) {
      this.showError('Failed to complete onboarding');
    }
  }

  showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (field) {
      const errorElement = field.parentNode.querySelector('.field-error');
      if (errorElement) {
        errorElement.textContent = message;
        field.classList.add('input-error');
      }
    }
  }

  showError(message) {
    console.error(message);
    // Alert removed to prevent browser popup interference
  }

  showSuccess(message) {
    console.log(message);
    // You can replace with a better notification system
  }
}

// Face Recognition Class for onboarding
class FaceRecognition {
  constructor() {
    this.video = document.getElementById('face-video');
    this.canvas = document.getElementById('face-overlay');
    this.ctx = this.canvas?.getContext('2d');
    this.isModelLoaded = false;
  }

  async loadModels() {
    if (typeof faceapi === 'undefined') {
      console.log('Face-api.js not loaded');
      return;
    }

    try {
      const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/model';
      await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
      await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
      await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
      this.isModelLoaded = true;
    } catch (_error) {
      console.error('Failed to load face recognition models:', _error);
    }
  }

  async startVideo() {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ 
        video: { width: 320, height: 240 } 
      });
      if (this.video) this.video.srcObject = stream;
    } catch (err) {
      console.error('Error accessing camera:', err);
      const statusEl = document.getElementById('face-status');
      if (statusEl) statusEl.textContent = 'Camera access denied';
    }
  }

  async enrollFace() {
    if (!this.isModelLoaded || !this.video) return false;

    try {
      const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });
      let detection = null;
      for (let i = 0; i < 5; i++) {
        detection = await faceapi
          .detectSingleFace(this.video, options)
          .withFaceLandmarks()
          .withFaceDescriptor();
        if (detection) break;
        await new Promise(r => setTimeout(r, 200));
      }

      if (detection) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const response = await fetch('/api/face/enroll', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({
            userId: window.currentUserId,
            descriptor: Array.from(detection.descriptor)
          })
        });

        return response.ok;
      }
    } catch (error) {
      console.error('Face enrollment failed:', error);
    }
    return false;
  }
}

// Initialize the onboarding flow when the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  console.log('DOM loaded, initializing OnboardingFlow');
  if (window.OnboardingFlow) {
    window.onboardingFlow = new window.OnboardingFlow();
  } else {
    console.error('OnboardingFlow class not found');
  }

  // ================================
  // Facial Recognition Fallback
  // ================================
  
  // Global variables for facial fallback
  window.facialFallbackActive = false;
  window.facialFallbackVideo = null;
  window.facialFallbackStream = null;

  function showPreFacialAlertModal(data) {
    const modal = document.getElementById('pre-facial-alert-modal');
    if (!modal) return;
    
    // Show modal with animation
    modal.classList.remove('hidden');
    modal.removeAttribute('aria-hidden');
    
    // Add entrance animation
    setTimeout(() => {
      const modalContent = modal.querySelector('.modal');
      if (modalContent) {
        modalContent.style.transform = 'scale(0.9) translateY(20px)';
        modalContent.style.opacity = '0';
        modalContent.style.transition = 'all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
        
        requestAnimationFrame(() => {
          modalContent.style.transform = 'scale(1) translateY(0)';
          modalContent.style.opacity = '1';
        });
      }
    }, 10);
    
    // Set up event listeners
    setupPreFacialListeners(data);
  }

  function setupPreFacialListeners(data) {
    const proceedBtn = document.getElementById('proceed-to-facial-verification');
    const cancelBtn = document.getElementById('cancel-pre-facial');
    
    // Remove existing listeners
    proceedBtn?.removeEventListener('click', handleProceedToFacial);
    cancelBtn?.removeEventListener('click', closePreFacialModal);
    
    // Add event listeners
    proceedBtn?.addEventListener('click', () => handleProceedToFacial(data));
    cancelBtn?.addEventListener('click', closePreFacialModal);
  }

  function handleProceedToFacial(data) {
    // Close pre-modal with animation
    const preModal = document.getElementById('pre-facial-alert-modal');
    if (preModal) {
      const modalContent = preModal.querySelector('.modal');
      if (modalContent) {
        modalContent.style.transition = 'all 0.3s ease-out';
        modalContent.style.transform = 'scale(0.95) translateY(-20px)';
        modalContent.style.opacity = '0';
        
        setTimeout(() => {
          preModal.classList.add('hidden');
          preModal.setAttribute('aria-hidden', 'true');
          
          // Show facial verification modal after a brief delay
          setTimeout(() => {
            showFacialFallbackModal(data);
          }, 200);
        }, 300);
      }
    }
  }

  function closePreFacialModal() {
    const modal = document.getElementById('pre-facial-alert-modal');
    if (modal) {
      const modalContent = modal.querySelector('.modal');
      if (modalContent) {
        modalContent.style.transition = 'all 0.3s ease-out';
        modalContent.style.transform = 'scale(0.95)';
        modalContent.style.opacity = '0';
        
        setTimeout(() => {
          modal.classList.add('hidden');
          modal.setAttribute('aria-hidden', 'true');
        }, 300);
      }
    }
    
    // Clean up
    window.pendingFacialEmail = null;
  }
  
  function showFacialFallbackModal(data) {
    const modal = document.getElementById('facial-fallback-modal');
    if (!modal) return;
    
    // Update attempt count
    const attemptCountEl = document.getElementById('attempt-count');
    if (attemptCountEl && data.failed_attempts && data.max_attempts) {
      attemptCountEl.textContent = `${data.failed_attempts}/${data.max_attempts}`;
    }
    
    // Show modal with animation
    modal.classList.remove('hidden');
    modal.removeAttribute('aria-hidden');
    
    // Add entrance animation
    setTimeout(() => {
      const modalContent = modal.querySelector('.modal');
      if (modalContent) {
        modalContent.style.transform = 'scale(0.95)';
        modalContent.style.opacity = '0';
        modalContent.style.transition = 'all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
        
        requestAnimationFrame(() => {
          modalContent.style.transform = 'scale(1)';
          modalContent.style.opacity = '1';
        });
      }
    }, 10);
    
    // Set up event listeners
    setupFacialFallbackListeners();
  }
  
  function setupFacialFallbackListeners() {
    const startBtn = document.getElementById('start-fallback-camera');
    const verifyBtn = document.getElementById('verify-fallback-face');
    const cancelBtn = document.getElementById('cancel-facial-fallback');
    const retryBtn = document.getElementById('retry-password-login');
    
    // Remove existing listeners to prevent duplicates
    startBtn?.removeEventListener('click', startFallbackCamera);
    verifyBtn?.removeEventListener('click', verifyFallbackIdentity);
    cancelBtn?.removeEventListener('click', closeFacialFallbackModal);
    retryBtn?.removeEventListener('click', retryPasswordLogin);
    
    // Add event listeners
    startBtn?.addEventListener('click', startFallbackCamera);
    verifyBtn?.addEventListener('click', verifyFallbackIdentity);
    cancelBtn?.addEventListener('click', closeFacialFallbackModal);
    retryBtn?.addEventListener('click', retryPasswordLogin);
  }
  
  async function startFallbackCamera() {
    try {
      const videoContainer = document.getElementById('fallback-video-container');
      const video = document.getElementById('fallback-video');
      const statusEl = document.getElementById('fallback-status');
      const startBtn = document.getElementById('start-fallback-camera');
      const verifyBtn = document.getElementById('verify-fallback-face');
      
      if (!video || !videoContainer) return;
      
      // Request camera access
      window.facialFallbackStream = await navigator.mediaDevices.getUserMedia({ 
        video: { width: 320, height: 240 } 
      });
      
      video.srcObject = window.facialFallbackStream;
      window.facialFallbackVideo = video;
      
      // Switch from initial centered view to camera active view
      const initialContent = document.getElementById('initial-content');
      const cameraActiveContent = document.getElementById('camera-active-content');
      
      if (initialContent) initialContent.classList.add('hidden');
      if (cameraActiveContent) cameraActiveContent.classList.remove('hidden');
      
      // Show video container
      videoContainer.style.display = 'block';
      
      // Show verify button in camera active state
      const verifyActiveBtn = document.getElementById('verify-fallback-face-active');
      if (verifyActiveBtn) {
        verifyActiveBtn.classList.remove('hidden');
        // Add event listener if not already added
        verifyActiveBtn.removeEventListener('click', verifyFallbackIdentity);
        verifyActiveBtn.addEventListener('click', verifyFallbackIdentity);
      }
      
      // Update original status for backward compatibility
      if (statusEl) {
        statusEl.innerHTML = `
          <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center justify-center mb-3">
              <div class="bg-green-100 rounded-full p-2 mr-3">
                <i class="fa-solid fa-video text-green-600"></i>
              </div>
              <h4 class="text-green-800 font-medium">Camera Active</h4>
            </div>
            <div class="text-center">
              <div class="inline-flex items-center bg-white bg-opacity-60 rounded-md px-3 py-2 mb-3">
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-2"></div>
                  <span class="text-green-700 text-sm font-medium">Live Camera Feed</span>
                </div>
              </div>
              <p class="text-green-700 text-sm mb-2">
                Great! Your camera is working properly.
              </p>
              <p class="text-gray-600 text-xs">
                Position your face clearly in the center and click "Verify My Identity" when ready
              </p>
            </div>
          </div>
        `;
      }
      
      startBtn?.classList.add('hidden');
      verifyBtn?.classList.remove('hidden');
      
      // Load face-api.js models if needed
      await loadFaceApiModels();
      
    } catch (error) {
      console.error('Error starting camera:', error);
      const statusEl = document.getElementById('fallback-status');
      if (statusEl) {
        statusEl.innerHTML = `
          <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center justify-center mb-3">
              <div class="bg-red-100 rounded-full p-2 mr-3">
                <i class="fa-solid fa-video-slash text-red-600"></i>
              </div>
              <h4 class="text-red-800 font-medium">Camera Access Failed</h4>
            </div>
            <div class="text-center">
              <p class="text-red-700 text-sm mb-3">
                Unable to access your camera for verification.
              </p>
              <div class="bg-white bg-opacity-60 rounded-md p-3 mb-3">
                <p class="text-xs text-red-600 font-mono">${error.message}</p>
              </div>
              <div class="text-left bg-red-25 rounded-md p-3">
                <p class="text-xs text-red-600 mb-1"><strong>Troubleshooting:</strong></p>
                <ul class="text-xs text-red-600 space-y-1">
                  <li>• Check camera permissions in your browser</li>
                  <li>• Ensure no other app is using the camera</li>
                  <li>• Try refreshing the page and try again</li>
                </ul>
              </div>
            </div>
          </div>
        `;
      }
    }
  }
  
  async function verifyFallbackIdentity() {
    try {
      const video = window.facialFallbackVideo;
      const statusEl = document.getElementById('fallback-status');
      const verifyBtn = document.getElementById('verify-fallback-face');
      
      if (!video || !window.pendingFacialEmail) return;
      
      // Update button state
      const _originalText = verifyBtn.textContent;
      verifyBtn.textContent = 'Verifying...';
      verifyBtn.disabled = true;
      
      // Update status with processing animation
      if (statusEl) {
        statusEl.innerHTML = `
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-center mb-3">
              <div class="bg-blue-100 rounded-full p-2 mr-3">
                <i class="fa-solid fa-brain text-blue-600 animate-pulse"></i>
              </div>
              <h4 class="text-blue-800 font-medium">Analyzing Facial Features</h4>
            </div>
            <div class="text-center">
              <div class="flex items-center justify-center mb-3">
                <div class="relative">
                  <div class="w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                </div>
              </div>
              <p class="text-blue-700 text-sm mb-2">
                Processing your facial biometrics securely...
              </p>
              <div class="bg-white bg-opacity-60 rounded-md p-2">
                <span class="text-xs text-blue-600">This may take a few seconds</span>
              </div>
            </div>
          </div>
        `;
      }
      
      // Get face descriptor from video
      const descriptor = await getFaceDescriptor(video);
      
      if (!descriptor) {
        throw new Error('No face detected. Please ensure your face is visible and try again.');
      }
      
      // Send verification request
      const res = await fetch('/api/auth/facial-fallback', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'include',
        body: JSON.stringify({
          email: window.pendingFacialEmail,
          descriptor: Array.from(descriptor)
        })
      });
      
      const data = await res.json().catch(() => ({}));
      
      if (res.ok) {
        // Success - handle like regular login
        handleFacialFallbackSuccess(data);
      } else {
        // Failed verification
        handleFacialFallbackFailure(data);
      }
      
    } catch (error) {
      console.error('Facial verification error:', error);
      const statusEl = document.getElementById('fallback-status');
      if (statusEl) {
        statusEl.innerHTML = `
          <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center justify-center mb-3">
              <div class="bg-red-100 rounded-full p-2 mr-3">
                <i class="fa-solid fa-exclamation-triangle text-red-600"></i>
              </div>
              <h4 class="text-red-800 font-medium">Verification Error</h4>
            </div>
            <div class="text-center">
              <p class="text-red-700 text-sm mb-3">
                We encountered an issue during verification.
              </p>
              <div class="bg-white bg-opacity-60 rounded-md p-3 mb-3">
                <p class="text-xs text-red-600">${error.message}</p>
              </div>
              <p class="text-gray-600 text-xs">
                Please ensure your face is clearly visible and try again, or use the "Try Password Again" option.
              </p>
            </div>
          </div>
        `;
      }
    } finally {
      const verifyBtn = document.getElementById('verify-fallback-face');
      if (verifyBtn) {
        verifyBtn.textContent = verifyBtn.textContent.replace('Verifying...', 'Verify Identity');
        verifyBtn.disabled = false;
      }
    }
  }
  
  async function getFaceDescriptor(video) {
    if (typeof faceapi === 'undefined') {
      throw new Error('Face API not loaded');
    }
    
    const detection = await faceapi
      .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor();
    
    return detection?.descriptor || null;
  }
  
  async function loadFaceApiModels() {
    if (typeof faceapi === 'undefined') {
      throw new Error('Face API not loaded');
    }
    
    // Check if models are already loaded
    if (faceapi.nets.tinyFaceDetector.isLoaded) {
      return;
    }
    
    const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/model';
    await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
  }
  
  function handleFacialFallbackSuccess(data) {
    const resultDiv = document.getElementById('facial-fallback-result');
    const contentDiv = document.getElementById('facial-fallback-content');
    
    if (resultDiv && contentDiv) {
      contentDiv.classList.add('hidden');
      resultDiv.classList.remove('hidden');
      resultDiv.innerHTML = `
        <div class="text-center py-8">
          <div class="relative inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
            <div class="absolute inset-0 bg-green-500 rounded-full animate-ping opacity-20"></div>
            <i class="fa-solid fa-check text-green-600 text-3xl relative z-10"></i>
          </div>
          
          <h3 class="text-xl font-semibold text-green-800 mb-2">
            Identity Verified Successfully!
          </h3>
          
          <p class="text-green-600 mb-4">
            Your facial biometrics have been matched. Welcome back!
          </p>
          
          <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
            <div class="flex items-center justify-center text-sm text-green-700">
              <i class="fa-solid fa-shield-check mr-2"></i>
              Account security verified • Login attempts reset
            </div>
          </div>
          
          <div class="flex items-center justify-center">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-green-600 mr-2"></div>
            <span class="text-sm text-gray-600">Signing you in securely...</span>
          </div>
        </div>
      `;
    }
    
    // Clean up
    stopFacialFallbackCamera();
    
    // Redirect after short delay
    setTimeout(() => {
      if (data.show_onboarding) {
        window.currentUserId = data.user_id;
        closeFacialFallbackModal();
        setTimeout(() => {
          const onboarding = new OnboardingFlow();
          onboarding.showModal();
        }, 500);
      } else {
        const to = (window.appRoutes && window.appRoutes.dashboard) || '/dashboard';
        window.location.replace(to);
      }
    }, 1500);
  }
  
  function handleFacialFallbackFailure(data) {
    const resultDiv = document.getElementById('facial-fallback-result');
    const contentDiv = document.getElementById('facial-fallback-content');
    const retryBtn = document.getElementById('retry-password-login');
    
    if (resultDiv && contentDiv) {
      contentDiv.classList.add('hidden');
      resultDiv.classList.remove('hidden');
      
      const isLocked = data.account_locked;
      const lockTime = data.locked_until ? new Date(data.locked_until).toLocaleString() : null;
      
      resultDiv.innerHTML = `
        <div class="text-center py-8">
          <div class="relative inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-full mb-4">
            <i class="fa-solid fa-${isLocked ? 'lock' : 'times'} text-red-600 text-3xl"></i>
          </div>
          
          <h3 class="text-xl font-semibold text-red-800 mb-2">
            ${isLocked ? 'Account Temporarily Locked' : 'Verification Failed'}
          </h3>
          
          <p class="text-red-600 mb-4">
            ${isLocked 
              ? 'Your account has been locked for security purposes due to multiple failed verification attempts.' 
              : 'The facial recognition system could not verify your identity.'}
          </p>
          
          ${isLocked ? `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
              <div class="text-sm text-red-700">
                <div class="flex items-center justify-center mb-2">
                  <i class="fa-solid fa-clock mr-2"></i>
                  <strong>Lockout Details</strong>
                </div>
                ${lockTime ? `<p class="mb-1">Locked until: <span class="font-mono">${lockTime}</span></p>` : ''}
                <p class="text-xs mt-2 text-red-600">
                  This is a temporary security measure to protect your account from unauthorized access.
                </p>
              </div>
            </div>
          ` : `
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
              <div class="text-sm text-amber-700">
                <p class="mb-2"><strong>Possible reasons:</strong></p>
                <ul class="text-left text-xs space-y-1">
                  <li>• Poor lighting conditions</li>
                  <li>• Face partially obscured</li>
                  <li>• Camera angle or distance issues</li>
                  <li>• Significant changes in appearance</li>
                </ul>
              </div>
            </div>
          `}
          
          ${data.message ? `
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4">
              <p class="text-xs text-gray-600">${data.message}</p>
            </div>
          ` : ''}
        </div>
      `;
    }
    
    // Show retry button if not locked
    if (!data.account_locked && retryBtn) {
      retryBtn.classList.remove('hidden');
    }
    
    // Clean up camera
    stopFacialFallbackCamera();
  }
  
  function closeFacialFallbackModal() {
    const modal = document.getElementById('facial-fallback-modal');
    if (modal) {
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden', 'true');
    }
    
    // Clean up
    stopFacialFallbackCamera();
    window.pendingFacialEmail = null;
    
    // Reset modal state
    resetFacialFallbackModal();
  }
  
  function stopFacialFallbackCamera() {
    if (window.facialFallbackStream) {
      window.facialFallbackStream.getTracks().forEach(track => track.stop());
      window.facialFallbackStream = null;
    }
    
    if (window.facialFallbackVideo) {
      window.facialFallbackVideo.srcObject = null;
      window.facialFallbackVideo = null;
    }
  }
  
  function resetFacialFallbackModal() {
    const videoContainer = document.getElementById('fallback-video-container');
    const resultDiv = document.getElementById('facial-fallback-result');
    const contentDiv = document.getElementById('facial-fallback-content');
    const initialContent = document.getElementById('initial-content');
    const cameraActiveContent = document.getElementById('camera-active-content');
    const startBtn = document.getElementById('start-fallback-camera');
    const verifyBtn = document.getElementById('verify-fallback-face');
    const retryBtn = document.getElementById('retry-password-login');
    const statusEl = document.getElementById('fallback-status');
    
    // Reset to initial centered view
    if (initialContent) initialContent.classList.remove('hidden');
    if (cameraActiveContent) cameraActiveContent.classList.add('hidden');
    
    if (videoContainer) videoContainer.style.display = 'none';
    if (resultDiv) resultDiv.classList.add('hidden');
    if (contentDiv) contentDiv.classList.remove('hidden');
    if (startBtn) startBtn.classList.remove('hidden');
    if (verifyBtn) verifyBtn.classList.add('hidden');
    if (retryBtn) retryBtn.classList.add('hidden');
    
    if (statusEl) {
      statusEl.innerHTML = '<p class="text-gray-600">Click "Start Camera" to begin verification</p>';
    }
  }
  
  function retryPasswordLogin() {
    closeFacialFallbackModal();
    // Focus on password field for retry
    const passwordField = document.getElementById('signin-password');
    if (passwordField) {
      passwordField.focus();
      passwordField.select();
    }
  }
  
  function showAccountLockedMessage(data) {
    console.log('showAccountLockedMessage called with:', data);
    const lockedUntil = data.locked_until ? new Date(data.locked_until).toLocaleString() : 'Unknown';
    const lockDuration = Math.round((new Date(data.locked_until) - new Date()) / (1000 * 60)); // minutes
    
    // Create a custom modal for account locked message
    const existingModal = document.getElementById('account-locked-modal');
    if (existingModal) {
      existingModal.remove();
    }
    
    const modalHTML = `
      <div id="account-locked-modal" class="modal-overlay" style="z-index: 9999;">
        <div class="modal" style="max-width: 480px; border-radius: 16px;">
          <div class="modal-header" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; border-radius: 16px 16px 0 0; padding: 1.5rem;">
            <div class="flex items-center justify-center">
              <div class="inline-flex items-center justify-center w-12 h-12 bg-white bg-opacity-20 rounded-full mr-3 border-2 border-white border-opacity-30">
                <i class="fa-solid fa-lock text-white" style="font-size: 1.25rem;"></i>
              </div>
              <h3 class="text-white text-xl font-semibold mb-0" style="color: white !important;">
                Account Temporarily Locked
              </h3>
            </div>
          </div>
          
          <div class="modal-body" style="padding: 2rem;">
            <div class="text-center mb-6">
              <p class="text-gray-700 mb-4">
                Your account has been temporarily locked due to multiple failed login attempts for security purposes.
              </p>
              
              <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <div class="text-sm">
                  <div class="flex items-center justify-center mb-2">
                    <i class="fa-solid fa-clock text-red-600 mr-2"></i>
                    <strong class="text-red-800">Lockout Information</strong>
                  </div>
                  <p class="text-red-700 mb-1">Locked until: <span class="font-mono">${lockedUntil}</span></p>
                  <p class="text-red-600 text-xs">
                    Approximately ${lockDuration > 0 ? lockDuration : 0} minutes remaining
                  </p>
                </div>
              </div>
              
              ${data.suggestion ? `
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                  <div class="flex items-start">
                    <i class="fa-solid fa-lightbulb text-blue-600 mr-2 mt-0.5"></i>
                    <div class="text-left">
                      <p class="text-sm text-blue-800 font-medium mb-1">Suggestion</p>
                      <p class="text-xs text-blue-700">${data.suggestion}</p>
                    </div>
                  </div>
                </div>
              ` : ''}
              
              <div class="text-xs text-gray-500 space-y-1">
                <p><strong>What you can do:</strong></p>
                <p>• Wait for the lockout period to expire</p>
                <p>• Contact support if you need immediate assistance</p>
                <p>• Set up facial recognition for future security recovery</p>
              </div>
            </div>
          </div>
          
          <div class="modal-footer" style="background-color: #f8fafc; border-radius: 0 0 16px 16px; padding: 1.5rem; text-align: center;">
            <button type="button" class="px-6 py-2 text-sm font-medium text-white rounded-lg transition-all duration-200" 
                    onclick="document.getElementById('account-locked-modal').remove()"
                    style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border: none;">
              <i class="fa-solid fa-check mr-2"></i>
              I Understand
            </button>
          </div>
        </div>
      </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
  }

  // Make functions globally available
  window.showPreFacialAlertModal = showPreFacialAlertModal;
  window.showFacialFallbackModal = showFacialFallbackModal;
  window.closeFacialFallbackModal = closeFacialFallbackModal;
  window.showAccountLockedMessage = showAccountLockedMessage;
});
