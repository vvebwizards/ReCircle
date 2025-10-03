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
      alert(err?.message || 'Login failed.');
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
      alert(err?.message || 'Something went wrong.');
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
      alert(err?.message || 'Something went wrong.');
    } finally {
      btn.textContent = original;
      btn.disabled = false;
    }
  });

  // Onboarding Flow
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
      this.bindEvents();
      this.loadFaceRecognition();
      this.setup2FA();
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
      if (!this.modal) return;
      this.modal.classList.remove('hidden');
      this.modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    hideModal() {
      if (!this.modal) return;
      this.modal.classList.add('hidden');
      this.modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }

    async setup2FA() {
      try {
        const response = await fetch('/onboarding/setup-2fa', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          credentials: 'include'
        });

        if (response.ok) {
          const data = await response.json();
          const qrEl = document.getElementById('qr-code');
          const secretEl = document.getElementById('secret-key');
          
          if (qrEl && data.qr_svg) {
            qrEl.innerHTML = data.qr_svg;
          }
          if (secretEl && data.secret) {
            secretEl.textContent = data.secret;
          }
        }
      } catch (error) {
        console.error('Failed to setup 2FA:', error);
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
          this.nextStep();
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
      // Dynamically load Face-api.js if needed
      if (typeof faceapi === 'undefined') {
        try {
          const script = document.createElement('script');
          script.src = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js';
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
      /* eslint-disable no-undef */
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
      /* eslint-enable no-undef */
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
        /* eslint-disable no-undef */
        const detection = await faceapi
          .detectSingleFace(this.video, new faceapi.TinyFaceDetectorOptions())
          .withFaceLandmarks()
          .withFaceDescriptor();
        /* eslint-enable no-undef */

        if (detection) {
          const response = await fetch('/api/face/enroll', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrf
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
        alert(err?.message || 'Login failed.');
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
      const response = await fetch('/onboarding/setup-2fa', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });

      if (response.ok) {
        const data = await response.json();
        const qrCodeEl = document.getElementById('qr-code');
        const secretKeyEl = document.getElementById('secret-key');
        
        if (qrCodeEl && data.qr_svg) {
          qrCodeEl.innerHTML = data.qr_svg;
        }
        if (secretKeyEl && data.secret) {
          secretKeyEl.textContent = data.secret;
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
    alert(message); // You can replace with a better notification system
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
      /* eslint-disable no-undef */
      const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.13/model';
      await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
      await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
      await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
      /* eslint-enable no-undef */
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
      /* eslint-disable no-undef */
      const detection = await faceapi
        .detectSingleFace(this.video, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor();
      /* eslint-enable no-undef */

      if (detection) {
        const response = await fetch('/api/face/enroll', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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
