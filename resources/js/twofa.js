// Ported from temp/twofa.js

document.addEventListener('DOMContentLoaded', () => {
  const codeInput = document.getElementById('twofa-code');
  const form = document.getElementById('twofa-form');
  const success = document.getElementById('twofa-success');
  const resend = document.getElementById('twofa-resend');

  function setError(input, msg) {
    const small = input.closest('.form-group')?.querySelector('.field-error');
    if (small) small.textContent = msg || '';
    input.classList.toggle('input-error', Boolean(msg));
  }

  form?.addEventListener('submit', (e) => {
    e.preventDefault();
    const code = codeInput.value.trim();
    if (!/^\d{6}$/.test(code)) { setError(codeInput, 'Enter the 6-digit code.'); return; }
    setError(codeInput, '');

    const btn = form.querySelector('button[type="submit"]');
    const original = btn.textContent;
    btn.textContent = 'Verifying...';
    btn.disabled = true;
    // Placeholder: In a real flow we'd verify the code server-side then redirect.
    setTimeout(() => {
      btn.textContent = original;
      btn.disabled = false;
      form.classList.add('hidden');
      success.classList.remove('hidden');
      const dash = (window.appRoutes && window.appRoutes.dashboard) || '/dashboard';
      setTimeout(() => { window.location.href = dash; }, 700);
    }, 900);
  });

  resend?.addEventListener('click', () => {
    resend.disabled = true;
    const original = resend.textContent;
    resend.textContent = 'Resending...';
    setTimeout(() => {
      resend.textContent = original;
      resend.disabled = false;
      alert('A new code was sent (demo).');
    }, 800);
  });
});
