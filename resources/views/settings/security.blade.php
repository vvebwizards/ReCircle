@extends('layouts.app')

@push('head')
@unless (app()->environment('testing'))
@vite(['resources/js/twofactor-settings.js'])
@endunless
@endpush

@section('content')
<main class="settings">
  <div class="container">
    <header class="dash-header">
      <div class="dash-hello">
        <h1>Settings</h1>
        <p class="dash-sub">Take Control , Optimize Your Account Security Features.</p>
      </div>
    </header>

    <div class="settings-stack">
    <section class="dash-card" id="twofa-card">
      <div class="card-stack">
        <div class="card-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <span id="twofa-status" class="chip" hidden><i class="fa-solid fa-circle-check"></i> Enabled</span>
        <h3 class="card-title">Two‑Factor Authentication (TOTP)</h3>
        <p class="card-desc">Protect your account with an authenticator app and recovery codes.</p>
        <div class="card-actions" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">

          <button id="twofa-begin-setup" class="btn btn-primary"><i class="fa-solid fa-gear"></i> Manage 2FA</button>
        </div>
      </div>
    </section>

    <!-- Change Password card: title, description, open button -->
    <section class="dash-card" id="password-card">
      <div class="card-stack">
        <div class="card-icon"><i class="fa-solid fa-key"></i></div>
        <h3 class="card-title">Change Password</h3>
        <p class="card-desc">Update your password regularly to keep your account secure.</p>
        <div class="card-actions">
          <button id="password-open" class="btn btn-primary"><i class="fa-solid fa-gear"></i> Open</button>
        </div>
      </div>
    </section>

    <!-- Notifications card: title, description, open button -->
    <section class="dash-card" id="notifications-card">
      <div class="card-stack">
        <div class="card-icon"><i class="fa-solid fa-bell"></i></div>
        <h3 class="card-title">Notifications</h3>
        <p class="card-desc">Choose how you want to hear from us.</p>
        <div class="card-actions">
          <button id="notifications-open" class="btn btn-primary"><i class="fa-solid fa-gear"></i> Open</button>
        </div>
      </div>
    </section>
    <!-- Future cards go here as additional <section class="dash-card"> ... </section> -->
    </div>

    <!-- 2FA Modal (auth-style) -->
  <div id="twofa-modal" class="modal-overlay hidden" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="twofa-modal-title">
      <div class="modal" role="document">
        <div class="modal-header">
          <h3 id="twofa-modal-title"><i class="fa-solid fa-shield-halved"></i> Set up two‑factor authentication</h3>
          <button class="modal-close" aria-label="Close" data-modal-close>&times;</button>
        </div>
        <div class="modal-body">
          <!-- Step 1: Scan QR -->
          <section id="twofa-step-1" class="twofa-step">
            <div class="twofa-content">
              <div id="twofa-qr" class="qr-box" aria-label="Authenticator QR code"></div>
              <p class="muted">Scan this QR with Google Authenticator or any TOTP app.</p>
              <div class="mt-2" id="twofa-manual" hidden>
                <h5 class="text-sm font-semibold">Manual setup</h5>
                <p class="text-sm">If you can't scan the QR, enter this key in your authenticator app:</p>
                <code id="twofa-secret" class="block p-2 bg-gray-100 rounded text-xs break-all"></code>
              </div>
              <p class="muted">After scanning the code, click Continue to enter the 6‑digit code from your app.</p>
            </div>
            <div class="twofa-actions">
              <button id="twofa-next-1" class="btn btn-primary"><i class="fa-solid fa-arrow-right"></i> Continue</button>
            </div>
          </section>

          <!-- Step 2: Enter 6-digit code -->
          <section id="twofa-step-2" class="twofa-step hidden">
            <div class="twofa-content">
              <h4>Verify code</h4>
              <p class="text-sm">Open your authenticator app and enter the current 6‑digit code.</p>
                <label for="twofa-code" class="form-label">Enter 6‑digit code</label>
                <small id="twofa-code-error" class="field-error" aria-live="assertive"></small>
                <input id="twofa-code" class="input twofa-code-input" inputmode="numeric" autocomplete="one-time-code" placeholder="123456" maxlength="6" pattern="[0-9]{6}" aria-describedby="twofa-code-error" aria-invalid="false" />
            </div>
            <div class="twofa-actions">
              <button id="twofa-back-2" class="btn btn-secondary">Back</button>
              <button id="twofa-enable" class="btn btn-primary"><i class="fa-solid fa-lock"></i> Enable 2FA</button>
            </div>
          </section>

          <!-- Step 3: Recovery codes (also used when already enabled) -->
          <section id="twofa-step-3" class="twofa-step hidden">
            <div class="mt-3">
              <h4>Recovery Codes</h4>
              <p class="text-sm">Save these in a safe place. They will not be shown again.</p>
              <ul id="twofa-codes" class="codes" hidden></ul>
              <div class="mt-2 twofa-actions">
                <button id="twofa-copy" class="btn btn-secondary social-btn"><i class="fa-regular fa-copy"></i> Copy one code</button>
                <button id="twofa-download" class="btn btn-secondary social-btn"><i class="fa-solid fa-download"></i> Download all</button>
                <button id="twofa-finish" class="btn btn-primary"><i class="fa-solid fa-check"></i> Finish</button>
                <button id="twofa-disable-open" class="btn btn-danger" hidden><i class="fa-solid fa-unlock"></i> Disable 2FA</button>
              </div>
            </div>
          </section>

          <!-- Step 4: Disable confirmation (ask for 6-digit code) -->
          <section id="twofa-step-4" class="twofa-step hidden">
              <div class="twofa-content">
                <h4>Disable 2FA</h4>
                <p class="text-sm">Enter a current 6‑digit code from your authenticator app to disable two‑factor authentication.</p>
                <label for="twofa-disable-code" class="form-label">Enter 6‑digit code</label>
                <small id="twofa-disable-error" class="field-error" aria-live="assertive"></small>
                <input id="twofa-disable-code" class="input twofa-code-input" inputmode="numeric" placeholder="123456" maxlength="6" pattern="[0-9]{6}" />
              </div>
              <div class="twofa-actions">
                <button id="twofa-disable" class="btn btn-warning"><i class="fa-solid fa-unlock"></i> Disable 2FA</button>
              </div>
          </section>
        </div>
      </div>
    </div>

    <!-- Change Password Modal (UI only, auth-style) -->
  <div id="password-modal" class="modal-overlay hidden" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="password-modal-title">
      <div class="modal" role="document">
        <div class="modal-header">
          <h3 id="password-modal-title"><i class="fa-solid fa-key"></i> Change Password</h3>
          <button class="modal-close" aria-label="Close" data-modal-close>&times;</button>
        </div>
        <div class="modal-body">
          <form class="form-grid" onsubmit="return false;">
            <div class="form-group">
              <label for="current-password">Current password</label>
              <input type="password" id="current-password" class="input" placeholder="••••••••" autocomplete="current-password">
            </div>
            <div class="form-group">
              <label for="new-password">New password</label>
              <input type="password" id="new-password" class="input" placeholder="At least 8 characters" autocomplete="new-password">
            </div>
            <div class="form-group">
              <label for="confirm-password">Confirm new password</label>
              <input type="password" id="confirm-password" class="input" placeholder="Repeat new password" autocomplete="new-password">
            </div>
            <div>
              <button class="btn btn-primary" type="button" disabled title="No backend yet">Save password</button>
              <button class="btn" data-modal-close>Close</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Notifications Modal (UI only, auth-style) -->
  <div id="notifications-modal" class="modal-overlay hidden" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="notifications-modal-title">
      <div class="modal" role="document">
        <div class="modal-header">
          <h3 id="notifications-modal-title"><i class="fa-solid fa-bell"></i> Notifications</h3>
          <button class="modal-close" aria-label="Close" data-modal-close>&times;</button>
        </div>
        <div class="modal-body">
          <form class="form-grid" onsubmit="return false;">
            <label class="switch">
              <input type="checkbox" id="notif-system" checked>
              <span>System alerts</span>
            </label>
            <label class="switch">
              <input type="checkbox" id="notif-marketing">
              <span>Product updates & tips</span>
            </label>
            <label class="switch">
              <input type="checkbox" id="notif-bids" checked>
              <span>Marketplace bids</span>
            </label>
            <div>
              <button class="btn btn-primary" type="button" disabled title="No backend yet">Save preferences</button>
              <button class="btn" data-modal-close>Close</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection

@push('scripts')
<script>
  // Lightweight modal styles
  document.head.insertAdjacentHTML('beforeend', `<style>
  /* Settings page layout (mirror Dashboard spacing) */
  .settings{padding:8rem 0 4rem;background:var(--color-off-white);min-height:100vh}
  /* Horizontal cards: responsive grid */
  .settings-stack{display:grid;gap:1rem;grid-template-columns:repeat(3,minmax(280px,1fr));align-items:stretch}
  @media (max-width:1100px){.settings-stack{grid-template-columns:repeat(2,minmax(260px,1fr))}}
  @media (max-width:700px){.settings-stack{grid-template-columns:1fr}}
  .settings-stack .dash-card{height:100%}
  /* Explicit gap after the cards stack */
  .settings .settings-stack{margin-bottom:4rem}
  @media (min-width: 1024px){.settings .settings-stack{margin-bottom:6rem}}
  /* Add breathing room before footer */
  .settings .container{padding-bottom:6rem}
  @media (min-width: 1024px){.settings .container{padding-bottom:8rem}}
  
  /* Card polish */
  .settings .dash-card{transition:box-shadow .2s ease, transform .12s ease}
  .settings .dash-card:hover{box-shadow:0 10px 24px rgba(0,0,0,.08); transform:translateY(-1px)}
  .settings .dash-card:active{transform:translateY(0)}
  .settings .dash-card .card-stack{display:flex;flex-direction:column;gap:.75rem;padding:1rem}
  .settings .dash-card .card-icon{width:40px;height:40px;border-radius:999px;background:#eaf5ef;color:#1f7a4a;display:flex;align-items:center;justify-content:center;font-size:1.1rem;transition:transform .2s ease, box-shadow .2s ease}
  .settings .dash-card:hover .card-icon{transform:scale(1.08) rotate(-6deg);box-shadow:0 6px 14px rgba(31,122,74,.15)}
  .settings .dash-card .card-title{margin:0;font-size:1.15rem;color:#184a2f}
  .settings .dash-card .card-desc{margin:0;color:#4b5563;line-height:1.45}
  .settings .dash-card .card-actions{margin-top:.5rem}
  
  /* Better primary buttons */
  .settings .btn.btn-primary{background:#1f7a4a;border:none;color:#fff;border-radius:999px;padding:.7rem 1.1rem;box-shadow:0 4px 14px rgba(31,122,74,.22);transition:transform .08s ease, box-shadow .15s ease, background .15s ease}
  .settings .btn.btn-primary:hover{background:#19643e;box-shadow:0 6px 18px rgba(31,122,74,.28);transform:translateY(-1px)}
  .settings .btn.btn-primary:active{transform:translateY(0);box-shadow:0 3px 10px rgba(31,122,74,.2)}
  .settings .btn.btn-primary:focus{outline:2px solid #a7f3d0; outline-offset:2px}
  
  /* Secondary buttons */
  .settings .btn.btn-secondary{background:#eef2f7;color:#184a2f;border:none}
  .settings .btn.btn-secondary:hover{background:#e5ecf5}
  
  /* Danger buttons */
  .settings .btn.btn-danger{background:#b42318;border:none}
  .settings .btn.btn-danger:hover{background:#9f1f15}
  /* Warning (yellow) button */
  .settings .btn.btn-warning{background:var(--color-sunflower);border:none;color:#184a2f}
  .settings .btn.btn-warning:hover{background:#f0c14a}
  /* Auth-style modal overlay */
  .modal-overlay.hidden{display:none}
  .modal-overlay{position:fixed;inset:0;z-index:60;background:rgba(0,0,0,.45);backdrop-filter:saturate(120%) blur(2px);display:flex;align-items:center;justify-content:center;padding:4vh 1rem;box-sizing:border-box}
  .modal{position:relative;background:#fff;border-radius:16px;width:min(90vw, 860px);max-height:90vh;overflow:auto;box-shadow:0 18px 60px rgba(0,0,0,.35)}
  .modal-header{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #e6e7eb}
  .modal-body{padding:1rem 1.25rem}
  .modal-close{background:#f3f4f6;border:0;color:#6b7280;width:32px;height:32px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;line-height:1;cursor:pointer}
  .modal-close:hover{background:#e5e7eb;color:#374151}
  .modal-header h3{display:flex;align-items:center;gap:.6rem;margin:0;font-size:1.15rem}
  .modal-header h3 i{color:#16a34a;background:#eaf5ef;border-radius:999px;padding:.35rem}
  .twofa-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
  @media (max-width: 900px){.twofa-grid{grid-template-columns:1fr}}
  .twofa-content{display:flex;flex-direction:column;align-items:center;text-align:center;gap:.5rem;padding:0 .25rem}
  .twofa-actions{display:flex;gap:.5rem;justify-content:center;margin-top:1rem}
  @media (min-width: 700px){.twofa-actions{justify-content:flex-end}}
  .qr-box{min-width:240px;min-height:240px;display:flex;align-items:center;justify-content:center;background:#f3f4f6;border-radius:10px;color:#6b7280;box-shadow:inset 0 0 0 6px #eef2f7}
  .qr-box canvas, .qr-box img{max-width:100%;height:auto}
  .modal code{word-break:break-word}
  /* Lock scroll when any modal is open */
  html.modal-open, body.modal-open{overflow:hidden;height:100%}
  /* Steps */
  .twofa-step{animation:fadeIn .15s ease}
  @keyframes fadeIn{from{opacity:0;transform:translateY(2px)}to{opacity:1;transform:none}}
  .chip{display:inline-flex;align-items:center;gap:.4rem;padding:.25rem .6rem;border-radius:999px;background:#eef3ee;color:#1f5f3e;font-weight:600}
  .chip .fa-circle-check{color:#16a34a}
  .chip .fa-circle{color:#f59e0b}
  .form-grid{display:grid;gap:1rem;grid-template-columns:1fr 1fr}
  @media (max-width: 768px){.form-grid{grid-template-columns:1fr}}
  .form-group{display:flex;flex-direction:column;gap:.35rem}
  .switch{display:flex;align-items:center;gap:.6rem}
  /* Inputs and buttons inside modal */
  /* Match contact form label/input styling */
  .modal .form-label{display:block;margin-bottom:.5rem;color: var(--color-deep-green);font-weight:600;font-size:1rem}
  .modal .input{width:100%;border:2px solid var(--color-warm-sand);border-radius:10px;height:48px;padding:1rem 1rem;font-size:1rem;color:#111827;background:#fff}
  /* Default focus like contact form */
  .modal .input:focus{outline:none;border-color: var(--color-emerald)}
  /* Specific 2FA code input focus: yellow ring */
  .twofa-code-input:focus{outline:2px solid var(--color-sunflower);outline-offset:2px;border-color:var(--color-sunflower)}
  /* Green valid ring for the 6-digit code */
  .twofa-code-input.valid{outline:2px solid var(--color-emerald)!important;outline-offset:2px;border-color:var(--color-emerald)!important}
  /* Use global base btn styles; tweak only specifics */
  .modal .btn.btn-primary{border:none}
  .modal .btn.btn-danger{border:none}
  /* In modal, align secondary buttons with Google social style (outlined sky blue -> filled on hover) */
  .settings .modal .btn.btn-secondary{background:transparent;color:var(--color-sky);border:2px solid var(--color-sky);border-radius:50px}
  .settings .modal .btn.btn-secondary:hover{background:var(--color-sky);color:var(--color-off-white)}
  /* Back button subtle style */
  #twofa-back-2{border-color:#d1d5db;color:#374151}
  #twofa-back-2:hover{background:#f3f4f6}
  .twofa-code-input{max-width:360px}
  /* Keep card-level secondary buttons as soft-gray pills */
  .settings .dash-card .btn.btn-secondary{background:#eef2f7;color:#184a2f;border:none;border-radius:999px}
  .settings .dash-card .btn.btn-secondary:hover{background:#e5ecf5}
  /* Recovery codes actions layout */
  .twofa-actions{display:grid;grid-template-columns:repeat(2,minmax(180px,auto));justify-content:center;gap:1rem 1.25rem}
  .twofa-actions #twofa-finish{grid-column:1/-1;justify-self:center;margin-top:.5rem}
  @media (max-width:520px){.twofa-actions{grid-template-columns:1fr}}
  .twofa-grid .mt-2{margin-top:.75rem}
  /* Section divider above Recovery Codes (step 3) */
  #twofa-step-3 .mt-3{border-top:1px solid #e6e7eb;padding-top:1rem;margin-top:1.25rem}
  </style>`);
</script>
@endpush
