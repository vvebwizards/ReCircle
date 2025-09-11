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

  // Password strength
  const strengthWrap = document.querySelector('.password-strength');
  const strengthBar = document.querySelector('.strength-bar');
  const strengthLabel = document.querySelector('.strength-label');
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
    if (s >= 4) { label = 'Strong'; color = '#16a34a'; } // green-600
    else if (s === 3) { label = 'Good'; color = '#f59e0b'; } // amber-500
    else if (s === 2) { label = 'Fair'; color = '#f97316'; } // orange-500
    strengthBar.style.backgroundColor = color;
    strengthLabel.textContent = label;
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

  // Sign Up submit
  signupForm?.addEventListener('submit', (e) => {
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
    if (!terms.checked) { alert('Please accept Terms and Privacy.'); ok = false; }
    if (!ok) return;

    const btn = signupForm.querySelector('button[type="submit"]');
    const original = btn.textContent;
    btn.textContent = 'Creating account...';
    btn.disabled = true;
    setTimeout(() => {
      btn.textContent = original;
      btn.disabled = false;
      alert('Account created (demo). Please verify your email to continue.');
      showForm('signin');
    }, 1100);
  });
});
