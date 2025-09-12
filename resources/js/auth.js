// Ported from temp/auth.js

document.addEventListener('DOMContentLoaded', () => {
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

  // Sign In submit
  signinForm?.addEventListener('submit', (e) => {
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
    setTimeout(() => {
      btn.textContent = original;
      btn.disabled = false;
      try {
        const nameGuess = (email.value.split('@')[0] || 'user').replace(/\W+/g, ' ');
        const user = { name: nameGuess.trim(), email: email.value.trim() };
        localStorage.setItem('rc_user', JSON.stringify(user));
      } catch {}
      const to = (window.appRoutes && window.appRoutes.twofa) || '/twofa';
      window.location.href = to;
    }, 900);
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
});
