// Ported from temp/forgot-password.js

document.addEventListener('DOMContentLoaded', () => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const minPassLen = 8;

  const step1 = document.getElementById('fp-step1');
  const step2 = document.getElementById('fp-step2');
  const step3 = document.getElementById('fp-step3');
  const step4 = document.getElementById('fp-step4');
  const emailInput = document.getElementById('fp-email');
  const codeInput = document.getElementById('fp-code');
  const emailEcho = document.getElementById('fp-email-echo');
  const resendBtn = document.getElementById('fp-resend');
  const pass = document.getElementById('fp-pass');
  const confirm = document.getElementById('fp-confirm');

  const steps = Array.from(document.querySelectorAll('.steps .step'));
  const strengthWrap = document.querySelector('.password-strength');
  const strengthBar = document.querySelector('.strength-bar');
  const strengthLabel = document.querySelector('.strength-label');

  function setError(input, msg) {
    const small = input.closest('.form-group')?.querySelector('.field-error');
    if (small) small.textContent = msg || '';
    input.classList.toggle('input-error', Boolean(msg));
  }

  function setStep(n) {
    steps.forEach((el) => {
      const me = Number(el.getAttribute('data-step'));
      el.classList.toggle('current', me === n);
      el.classList.toggle('done', me < n);
    });
    [step1, step2, step3, step4].forEach((form, idx) => {
      if (!form) return;
      form.classList.toggle('hidden', idx !== n - 1);
    });
  }

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
    let color = '#dc2626';
    if (s >= 4) { label = 'Strong'; color = '#16a34a'; }
    else if (s === 3) { label = 'Good'; color = '#f59e0b'; }
    else if (s === 2) { label = 'Fair'; color = '#f97316'; }
    strengthBar.style.backgroundColor = color;
    strengthLabel.textContent = label;
  }

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

  // Step 1: Send code
  step1?.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = emailInput.value.trim();
    if (!emailRegex.test(email)) { setError(emailInput, 'Enter a valid email.'); return; }
    setError(emailInput, '');

    const btn = step1.querySelector('button[type="submit"]');
    const original = btn.textContent;
    btn.textContent = 'Sending...';
    btn.disabled = true;
    setTimeout(() => {
      btn.textContent = original;
      btn.disabled = false;
      if (emailEcho) emailEcho.textContent = email;
      setStep(2);
    }, 800);
  });

  // Step 2: Verify code (demo)
  step2?.addEventListener('submit', (e) => {
    e.preventDefault();
    const code = codeInput.value.trim();
    if (!/^\d{6}$/.test(code)) { setError(codeInput, 'Enter the 6-digit code.'); return; }
    setError(codeInput, '');

    const btn = step2.querySelector('button[type="submit"]');
    const original = btn.textContent;
    btn.textContent = 'Verifying...';
    btn.disabled = true;
    setTimeout(() => {
      btn.textContent = original;
      btn.disabled = false;
      setStep(3);
    }, 700);
  });

  // Resend
  resendBtn?.addEventListener('click', () => {
    resendBtn.disabled = true;
    const original = resendBtn.textContent;
    resendBtn.textContent = 'Resending...';
    setTimeout(() => {
      resendBtn.textContent = original;
      resendBtn.disabled = false;
      alert('A new code was sent (demo).');
    }, 800);
  });

  // Step 3: Update password
  pass?.addEventListener('input', (e) => updateStrength(e.target.value));
  step3?.addEventListener('submit', (e) => {
    e.preventDefault();
    let ok = true;
    if (pass.value.length < minPassLen) { setError(pass, `Use at least ${minPassLen} characters.`); ok = false; } else setError(pass, '');
    if (confirm.value !== pass.value) { setError(confirm, 'Passwords do not match.'); ok = false; } else setError(confirm, '');
    if (!ok) return;

    const btn = step3.querySelector('button[type="submit"]');
    const original = btn.textContent;
    btn.textContent = 'Updating...';
    btn.disabled = true;
    setTimeout(() => {
      btn.textContent = original;
      btn.disabled = false;
      setStep(4);
    }, 900);
  });
});
