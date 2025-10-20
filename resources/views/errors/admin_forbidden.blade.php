<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Access denied</title>
  <style>
    /* Minimal, auth-style friendly styles reusing class names used across the app */
    :root{--muted:#6b7280;--green:#1f7a4a;--card-bg:#ffffff;--page-bg:#f8fafc}
    html,body{height:100%;margin:0;font-family:Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; background:var(--page-bg);color:#0f1724}
    .center-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:48px 18px}
    .dash-card{width:100%;max-width:840px;background:var(--card-bg);border-radius:12px;box-shadow:0 8px 30px rgba(2,6,23,0.06);overflow:hidden;display:flex;gap:0;align-items:stretch}
    .card-stack{display:flex;flex-direction:column;gap:.75rem;padding:28px}
    .card-icon{width:56px;height:56px;border-radius:999px;background:#eaf5ef;color:var(--green);display:flex;align-items:center;justify-content:center;font-size:20px}
    .content{flex:1;padding:28px}
    h1{margin:0 0 6px;font-size:20px;color:#0b1220}
    .card-desc{color:var(--muted);margin:0 0 12px}
    .card-actions{display:flex;gap:12px;margin-top:12px}
    .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:10px;color:white;text-decoration:none;font-weight:600;border:0;cursor:pointer}
    .btn-primary{background:var(--green);box-shadow:0 6px 18px rgba(31,122,74,0.12)}
    .btn-secondary{background:transparent;border:1px solid rgba(15,23,36,0.06);color:#0f1724}
    .muted{color:var(--muted)}

    /* small hero column for visual emphasis */
    .hero{flex:0 0 220px;display:flex;align-items:center;justify-content:center;padding:22px;background:linear-gradient(180deg, rgba(31,122,74,0.03), rgba(31,122,74,0.01));position:relative}
    .badge{width:120px;height:120px;display:inline-grid;place-items:center;border-radius:999px;background:linear-gradient(180deg, rgba(31,122,74,0.06), rgba(31,122,74,0.02));}
    .pulse{position:absolute;inset:-22px;border-radius:50%;background:radial-gradient(circle at center, rgba(31,122,74,0.12), transparent 30%);animation:pulse 2.4s infinite ease-out}
    @keyframes pulse{0%{transform:scale(.95);opacity:.8}50%{transform:scale(1.15);opacity:.2}100%{transform:scale(1.5);opacity:0}}

    /* subtle lift animation like auth cards */
    .dash-card{transform:translateY(10px);opacity:0;animation:liftUp 420ms cubic-bezier(.2,.9,.2,1) forwards}
    @keyframes liftUp{to{transform:none;opacity:1}}

    /* respect reduced motion */
    @media (prefers-reduced-motion: reduce){
      .pulse,.dash-card{animation:none}
      .lock-svg .shackle{animation:none}
    }

    @media (max-width:760px){.dash-card{flex-direction:column}.hero{flex:0 0 auto;padding:18px}.content{padding:18px}}
  </style>
</head>
<body>
  <main class="center-wrap" role="main">
    <section class="dash-card" aria-labelledby="access-title">
      <div class="hero" aria-hidden="true">
        <div class="pulse" aria-hidden="true"></div>
        <div class="badge" role="img" aria-label="Access denied">
          <!-- lock icon matching auth visuals -->
          <svg class="lock-svg" width="72" height="72" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <rect x="3" y="9" width="18" height="11" rx="2" fill="#fff" stroke="rgba(15,23,36,0.06)" stroke-width="1"/>
            <path class="shackle" d="M7 9V6a5 5 0 0 1 10 0v3" stroke="#1f7a4a" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none" />
            <circle cx="12" cy="14.5" r="1.6" fill="#1f7a4a"/>
          </svg>
        </div>
      </div>

      <div class="content">
        <div class="card-stack">
          <div class="card-icon"><i class="fa-solid fa-shield-halved"></i></div>
          <h1 id="access-title">Access denied</h1>
          <p class="card-desc">You don't have permission to view this page. If you believe this is an error, contact your administrator or request the proper role.</p>

          <div class="card-actions" role="group" aria-label="Actions">
            <a class="btn btn-primary" href="{{ route('home') }}">Return home</a>
            @if(auth()->check())
              <a class="btn btn-secondary" href="{{ route('dashboard') }}">Go to my dashboard</a>
            @endif
          </div>
        </div>
      </div>
    </section>
  </main>
</body>
</html>
