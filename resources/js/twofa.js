// Ported from temp/twofa.js

document.addEventListener('DOMContentLoaded', () => {
  const codeInput = document.getElementById('twofa-code');
  const form = document.getElementById('twofa-form');
  const success = document.getElementById('twofa-success');
  const resend = document.getElementById('twofa-resend');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const methodRadios = Array.from(document.querySelectorAll('input[name="twofa-method"]'));
  const labelEl = document.getElementById('twofa-label');
  const alertEl = document.getElementById('twofa-alert');

  function setError(input, msg) {
    const small = input.closest('.form-group')?.querySelector('.field-error');
    if (small) small.textContent = msg || '';
    input.classList.toggle('input-error', Boolean(msg));
  }

  methodRadios.forEach(r => r.addEventListener('change', () => {
    const method = methodRadios.find(m => m.checked)?.value || 'totp';
    if (method === 'recovery') {
      labelEl.textContent = 'Enter a recovery code';
      codeInput.setAttribute('placeholder', 'XXXXXXXXXX');
      codeInput.setAttribute('inputmode', 'text');
    } else {
      labelEl.textContent = 'Enter 6‑digit code';
      codeInput.setAttribute('placeholder', '123456');
      codeInput.setAttribute('inputmode', 'numeric');
    }
  }));

  function showAlert(msg, type='error') {
    if (!alertEl) return;
    alertEl.classList.remove('hidden');
    alertEl.classList.toggle('alert-error', type==='error');
    alertEl.classList.toggle('alert-success', type==='success');
    alertEl.textContent = msg;
  }

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const method = methodRadios.find(m => m.checked)?.value || 'totp';
    const raw = codeInput.value.trim();
    const code = method === 'recovery' ? raw.toUpperCase() : raw;
    if (method === 'totp' && !/^\d{6}$/.test(code)) { setError(codeInput, 'Enter the 6-digit code.'); return; }
    if (method === 'recovery' && !/^[A-Z0-9]{8,}$/.test(code)) { setError(codeInput, 'Enter a valid recovery code.'); return; }
    setError(codeInput, '');

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
        body: JSON.stringify(method === 'totp' ? { email, password, twofa_code: code } : { email, password, recovery_code: code })
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        if (res.status === 422 && (data?.invalid_twofa || data?.message)) {
          showAlert(data?.message || 'Invalid two-factor code.', 'error');
          setError(codeInput, 'Check your code and try again.');
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

  resend?.addEventListener('click', () => {
    resend.disabled = true;
    const original = resend.textContent;
    resend.textContent = 'Resending...';
    setTimeout(() => {
      resend.textContent = original;
      resend.disabled = false;
      showAlert('If configured, a new code was sent.', 'success');
    }, 800);
  });
});
