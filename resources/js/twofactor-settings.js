// 2FA Settings page logic: orchestrates setup, enable and disable flows via /api/auth/2fa/* endpoints
(function(){
  const byId = (id)=>document.getElementById(id);
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  async function authedGet(path){
    const r = await fetch(path, { headers: { 'Accept': 'application/json' }, credentials: 'include' });
    if (!r.ok) throw new Error('HTTP '+r.status);
    return r.json();
  }
  async function authedPost(path, body){
    const r = await fetch(path, { method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, credentials: 'include', body: JSON.stringify(body||{}) });
    if (!r.ok) throw new Error('HTTP '+r.status);
    return r.json().catch(()=>({}));
  }

  function setHtml(el, html){ if (!el) return; el.innerHTML = html; }
  function show(el){ if(!el)return; el.hidden = false; }
  function hide(el){ if(!el)return; el.hidden = true; }
  function text(el, t){ if(!el)return; el.textContent = t; }
  function parseSecretFromOtpauth(uri){
    try {
      const u = new URL(uri);
      const params = new URLSearchParams(u.search);
      return params.get('secret') || '';
    } catch { return ''; }
  }
  async function getQR(){
    try { const mod = await import('qrcode'); return mod?.default || mod; } catch { return null; }
  }

  async function renderQR(qrContainer, manualWrap, secretEl, svg, otpauth){
    if (svg) { setHtml(qrContainer, svg); hide(manualWrap); return; }
    if (!otpauth) return;
    const QR = await getQR();
    if (QR && typeof QR.toCanvas === 'function') {
      const canvas = document.createElement('canvas');
      return new Promise((resolve)=>{
        QR.toCanvas(canvas, otpauth, { width: 220 }, (err) => {
          if (err) {
            const secret = parseSecretFromOtpauth(otpauth);
            if (secret) { text(secretEl, secret); show(manualWrap); }
            resolve(); return;
          }
          qrContainer.innerHTML = ''; qrContainer.appendChild(canvas); hide(manualWrap); resolve();
        });
      });
    } else {
      const secret = parseSecretFromOtpauth(otpauth);
      if (secret) { text(secretEl, secret); show(manualWrap); }
    }
  }

  async function init(){
  const beginBtn = byId('twofa-begin-setup');
  const modal = byId('twofa-modal');
  const modalCloseEls = Array.from(document.querySelectorAll('[data-modal-close]'));
    let twofaEnabled = false;
    const modalTitle = byId('twofa-modal-title');
    function setModalTitle(text){ if (modalTitle) modalTitle.innerHTML = `<i class="fa-solid fa-shield-halved"></i> ${text}`; }
    const pwdOpen = byId('password-open');
    const pwdModal = byId('password-modal');
    const notifOpen = byId('notifications-open');
    const notifModal = byId('notifications-modal');
    const qr = byId('twofa-qr');
    const manualWrap = byId('twofa-manual');
    const secretEl = byId('twofa-secret');
    const codesList = byId('twofa-codes');
  const step1 = byId('twofa-step-1');
  const step2 = byId('twofa-step-2');
  const step3 = byId('twofa-step-3');
  const step4 = byId('twofa-step-4');
    const next1 = byId('twofa-next-1');
    const back2 = byId('twofa-back-2');
    const finish = byId('twofa-finish');
  const statusChip = byId('twofa-status');
  const openDisable = byId('twofa-disable-open');
  const disableBtn = byId('twofa-disable');
  const back4 = byId('twofa-back-4');
  const disableInput = byId('twofa-disable-code');
  const disableError = byId('twofa-disable-error');

    function showOnly(stepEl){
      [step1, step2, step3, step4].forEach(el=>{ if(!el)return; el.classList.add('hidden'); });
      if (stepEl) stepEl.classList.remove('hidden');
    }

    // Ensure user is authenticated first
    try {
      const meRes = await fetch('/api/auth/me', { headers: { 'Accept': 'application/json' }, credentials: 'include' });
      if (!meRes.ok) throw new Error('unauthorized');
      await meRes.json();
    } catch {
      window.location.replace((window.appRoutes?.auth)||'/auth');
      return;
    }

    // Fetch current 2FA status to toggle chip and disable flow visibility
    try {
      const st = await authedGet('/api/auth/2fa/status');
      const enabled = !!st?.enabled;
      twofaEnabled = enabled;
      if (enabled) {
        statusChip?.removeAttribute('hidden');
        openDisable?.removeAttribute('hidden');
      }
    } catch {}

    // 2) Begin setup: generate secret and show QR + code input + recovery actions
    function setModalOpenState(open){
      const rootEls = [document.documentElement, document.body];
      rootEls.forEach(el=> open ? el.classList.add('modal-open') : el.classList.remove('modal-open'));
    }
    function openModal(m){
      if(!m) return;
      m.classList.remove('hidden');
      m.removeAttribute('aria-hidden');
      // Ensure inner panel isn't accidentally hidden from a previous close
      const panel = m.querySelector('.modal');
      if (panel) panel.classList.remove('hidden');
      setModalOpenState(true);
    }
    function closeModal(m){ if(!m)return; m.classList.add('hidden'); m.setAttribute('aria-hidden','true');
      // If no other modals visible, remove lock
      const anyOpen = document.querySelector('.modal-overlay:not(.hidden)');
      if (!anyOpen) setModalOpenState(false);
    }
    modalCloseEls.forEach(el => el.addEventListener('click', (e)=>{
      // Close the overlay container to avoid leaving the panel hidden between opens
      const overlay = e.target.closest('.modal-overlay');
      closeModal(overlay);
    }));
  document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') [modal,pwdModal,notifModal].forEach(closeModal); });

    beginBtn?.addEventListener('click', async ()=>{
      // Refresh status to ensure latest
      try { const st = await authedGet('/api/auth/2fa/status'); twofaEnabled = !!st?.enabled; } catch {}

      openModal(modal);
      if (twofaEnabled) {
        // Already enabled: go straight to Disable flow
        setModalTitle('Manage two‑factor authentication');
        showOnly(step4);
        if (disableError) disableError.textContent = '';
        disableInput?.focus();
        return;
      }

      // Not enabled: proceed with setup
      setModalTitle('Set up two‑factor authentication');
      showOnly(step1);
      try {
        const data = await authedGet('/api/auth/2fa/setup');
        const svg = data?.qr_svg || '';
        const otpauth = data?.otpauth_uri || '';
        await renderQR(qr, manualWrap, secretEl, svg, otpauth);
        const codes = Array.isArray(data?.recovery_codes) ? data.recovery_codes : [];
        setHtml(codesList, codes.map(c=>`<li class="code">${c}</li>`).join(''));
      } catch {
        alert('Could not start setup.');
      }
    });

    // Step navigation
    next1?.addEventListener('click', ()=>{
      showOnly(step2);
      byId('twofa-code')?.focus();
    });
    back2?.addEventListener('click', ()=>{
      showOnly(step1);
      setCodeError('');
    });

  // Close when clicking outside the panel
  modal?.addEventListener('click', (e)=>{ if (e.target === modal) closeModal(modal); });
  pwdModal?.addEventListener('click', (e)=>{ if (e.target === pwdModal) closeModal(pwdModal); });
  notifModal?.addEventListener('click', (e)=>{ if (e.target === notifModal) closeModal(notifModal); });

  // Open other modals
    pwdOpen?.addEventListener('click', ()=> openModal(pwdModal));
    notifOpen?.addEventListener('click', ()=> openModal(notifModal));

    // Enable 2FA (verify code)
    const codeInput = byId('twofa-code');
    const codeError = byId('twofa-code-error');
    function setCodeError(msg){ if(codeError){ codeError.textContent = msg||''; } }
    codeInput?.addEventListener('input', ()=>{
      // Allow only digits and cap at 6 characters
      if (!codeInput) return;
      const digits = (codeInput.value || '').replace(/\D+/g, '').slice(0,6);
      if (codeInput.value !== digits) codeInput.value = digits;
      const isSix = /^\d{6}$/.test(digits);
      codeInput.classList.toggle('valid', isSix);
      codeInput.setAttribute('aria-invalid', String(!isSix));
      if (isSix) setCodeError('');
      else if (digits.length === 6) setCodeError('Enter a valid 6‑digit code.');
      else setCodeError('');
    });
    byId('twofa-enable')?.addEventListener('click', async ()=>{
      const code = (codeInput?.value||'').trim();
      if (!/^\d{6}$/.test(code)) { setCodeError('Enter a valid 6‑digit code.'); codeInput?.focus(); return; }
      try {
        await authedPost('/api/auth/2fa/enable', { code });
        // Move to step 3 (show recovery codes already preloaded from setup)
        showOnly(step3);
        setCodeError('');
      } catch {
        setCodeError('Invalid or expired code. Please try again.');
        codeInput?.focus();
      }
    });

    finish?.addEventListener('click', ()=>{
      closeModal(modal);
      // Optional: reload to refresh any UI state about 2FA enabled
      window.location.reload();
    });

    // Open disable step
    openDisable?.addEventListener('click', ()=>{
      showOnly(step4);
      disableInput?.focus();
      if (disableError) disableError.textContent = '';
    });
    back4?.addEventListener('click', ()=>{
      if (twofaEnabled) {
        // When we came directly to disable (already enabled), Back should close
        const overlay = modal; // close the whole modal
        if (overlay) {
          overlay.classList.add('hidden');
          overlay.setAttribute('aria-hidden','true');
          const anyOpen = document.querySelector('.modal-overlay:not(.hidden)');
          if (!anyOpen) { document.documentElement.classList.remove('modal-open'); document.body.classList.remove('modal-open'); }
        }
      } else {
        showOnly(step3);
      }
      if (disableError) disableError.textContent = '';
    });
    // Disable 2FA with code verification
    function setDisableError(msg){ if (disableError) disableError.textContent = msg||''; }
    disableInput?.addEventListener('input', ()=>{
      const digits = (disableInput.value||'').replace(/\D+/g,'').slice(0,6);
      if (disableInput.value !== digits) disableInput.value = digits;
      if (/^\d{6}$/.test(digits)) setDisableError('');
    });
    disableBtn?.addEventListener('click', async ()=>{
      const code = (disableInput?.value||'').trim();
      if (!/^\d{6}$/.test(code)) { setDisableError('Enter a valid 6‑digit code.'); disableInput?.focus(); return; }
      try {
        await authedPost('/api/auth/2fa/disable', { code });
        window.location.reload();
      } catch {
        setDisableError('Invalid or expired code. Please try again.');
        disableInput?.focus();
      }
    });

    // Copy/Download recovery codes
    byId('twofa-copy')?.addEventListener('click', ()=>{
      const first = document.querySelector('#twofa-codes .code');
      const code = first ? first.textContent.trim() : '';
      if (!code) return;
      navigator.clipboard.writeText(code).then(()=>{
        const btn = byId('twofa-copy');
        const t = btn.textContent; btn.textContent = 'Copied one'; setTimeout(()=>btn.textContent=t, 1500);
      });
    });

    byId('twofa-download')?.addEventListener('click', ()=>{
      const codes = Array.from(document.querySelectorAll('#twofa-codes .code')).map(li=>li.textContent.trim()).filter(Boolean);
      if (!codes.length) return;
      const blob = new Blob([codes.join('\n')], { type: 'text/plain' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = 'recovery-codes.txt';
      document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
