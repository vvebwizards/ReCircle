// Ported from temp/twofa.js

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('twofa-form');
  const success = document.getElementById('twofa-success');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const alertEl = document.getElementById('twofa-alert');
  const methodHidden = document.getElementById('twofa-method');
  // Inputs per tab
  const inputTotp = document.getElementById('twofa-code');
  const inputEmail = document.getElementById('twofa-code-email');
  const inputRecovery = document.getElementById('twofa-code-recovery');
  const resend = document.getElementById('twofa-resend');
  // Tabs
  const tabBtns = {
    totp: document.getElementById('tabbtn-totp'),
    email: document.getElementById('tabbtn-email'),
    recovery: document.getElementById('tabbtn-recovery'),
  };
  const tabPanels = {
    totp: document.getElementById('tab-totp'),
    email: document.getElementById('tab-email'),
    recovery: document.getElementById('tab-recovery'),
  };

  function setError(input, msg) {
    const small = input.closest('.form-group')?.querySelector('.field-error');
    if (small) small.textContent = msg || '';
    input.classList.toggle('input-error', Boolean(msg));
  }

  function switchTab(which) {
    const keys = ['totp', 'email', 'recovery'];
    keys.forEach(k => {
      tabBtns[k]?.classList.toggle('active', k === which);
      tabBtns[k]?.setAttribute('aria-selected', String(k === which));
      tabPanels[k]?.classList.toggle('hidden', k !== which);
    });
    methodHidden.value = which;
    // Focus appropriate input
    setTimeout(() => {
      if (which === 'totp') inputTotp?.focus();
      else if (which === 'email') inputEmail?.focus();
      else inputRecovery?.focus();
    }, 0);
  }
  tabBtns.totp?.addEventListener('click', () => switchTab('totp'));
  tabBtns.email?.addEventListener('click', () => switchTab('email'));
  tabBtns.recovery?.addEventListener('click', () => switchTab('recovery'));

  function showAlert(msg, type='error') {
    if (!alertEl) return;
    alertEl.classList.remove('hidden');
    alertEl.classList.toggle('alert-error', type==='error');
    alertEl.classList.toggle('alert-success', type==='success');
    alertEl.textContent = msg;
  }

  function showNotice(message) {
    const card = document.querySelector('.auth-card');
    if (!card) return;
    const existing = card.querySelector('.notice');
    existing?.remove();
    const notice = document.createElement('div');
    notice.className = 'notice verify-notice';
    notice.setAttribute('role', 'alert');
    notice.innerHTML = `
      <div class="notice-content">
        <i class="fa-solid fa-envelope-circle-check" aria-hidden="true"></i>
        <span>${message}</span>
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

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const method = methodHidden.value || 'totp';
    let raw = '';
    if (method === 'totp') raw = inputTotp.value.trim();
    else if (method === 'email') raw = inputEmail.value.trim();
    else raw = inputRecovery.value.trim();
    const code = method === 'recovery' ? raw.toUpperCase() : raw;
    if (method === 'totp' && !/^\d{6}$/.test(code)) { setError(inputTotp, 'Enter the 6-digit code.'); return; }
    if (method === 'email' && !/^\d{6}$/.test(code)) { setError(inputEmail, 'Enter the 6-digit email code.'); return; }
    if (method === 'recovery' && !/^[A-Z0-9]{8,}$/.test(code)) { setError(inputRecovery, 'Enter a valid recovery code.'); return; }
  // Clear any previous field errors
  [inputTotp, inputEmail, inputRecovery].forEach(el => { if (el) setError(el, ''); });

    // Retrieve pending creds set during login 403
    let email = '', password = '';
    try {
      email = sessionStorage.getItem('pending_login_email') || '';
      password = sessionStorage.getItem('pending_login_password') || '';
    } catch {}
    if (!email || !password) {
      alert('Your session expired. Please sign in again.');
      const back = (window.appRoutes && window.appRoutes.auth) || '/auth';
      window.location.replace(back);
      return;
    }

    const btn = form.querySelector('button[type="submit"]');
    const original = btn.textContent;
    btn.textContent = 'Verifying...';
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
        body: JSON.stringify(method === 'totp'
          ? { email, password, twofa_code: code }
          : method === 'email'
            ? { email, password, email_code: code }
            : { email, password, recovery_code: code })
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        if (res.status === 422 && (data?.invalid_twofa || data?.message)) {
          showAlert(data?.message || 'Invalid two-factor code.', 'error');
          const errInput = method === 'totp' ? inputTotp : (method === 'email' ? inputEmail : inputRecovery);
          if (errInput) setError(errInput, 'Check your code and try again.');
          return;
        }
        if (res.status === 403 && data?.requires_twofa) {
          showAlert('Two-factor code is required. Please enter a code.', 'error');
          return;
        }
        throw new Error(data?.message || 'Verification failed');
      }
      // Clean up the stored creds
      try { sessionStorage.removeItem('pending_login_email'); sessionStorage.removeItem('pending_login_password'); } catch {}
      // Success: cookie set, redirect to dashboard
      form.classList.add('hidden');
      success.classList.remove('hidden');
      const dash = (window.appRoutes && window.appRoutes.dashboard) || '/dashboard';
      setTimeout(() => { window.location.replace(dash); }, 600);
    } catch (err) {
      showAlert(err?.message || 'Verification failed', 'error');
    } finally {
      btn.textContent = original;
      btn.disabled = false;
    }
  });

  // Initialize focus on default active tab
  switchTab(methodHidden.value || 'totp');

  resend?.addEventListener('click', async () => {
    const method = methodHidden.value || 'totp';
    if (method !== 'email') {
      showAlert('Switch to Email code to send a code to your inbox.', 'error');
      return;
    }
    // Need email/password from prior step
    let email = '', password = '';
    try {
      email = sessionStorage.getItem('pending_login_email') || '';
      password = sessionStorage.getItem('pending_login_password') || '';
    } catch {}
    if (!email || !password) {
      showAlert('Your session expired. Please sign in again.', 'error');
      const back = (window.appRoutes && window.appRoutes.auth) || '/auth';
      window.location.replace(back);
      return;
    }
    resend.disabled = true;
    const original = resend.textContent;
    resend.textContent = 'Sending...';
    try {
      const res = await fetch('/api/auth/2fa/email/send', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf,
        },
        credentials: 'include',
        body: JSON.stringify({ email, password })
      });
      const data = await res.json().catch(() => ({}));
  if (!res.ok) throw new Error(data?.message || 'Failed to send code');
  // Show a notice similar to resend verification on auth page
  showNotice((data && data.message) || 'We sent a 6‑digit code to your email.');
    } catch (err) {
      showAlert(err?.message || 'Failed to send code', 'error');
    } finally {
      resend.textContent = original;
      resend.disabled = false;
    }
  });
});
