<!doctype html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('subject', 'ReCircle')</title>
  <style>
    /* Base email reset */
    body { margin:0; padding:0; background:#FAFAF9; color:#4A5568; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; }
    a { color:#2F855A; text-decoration:none; }
    .wrapper { width:100%; background:#FAFAF9; padding:24px 0; }
    .container { width:100%; max-width: 600px; margin:0 auto; background:#ffffff; border-radius: 14px; box-shadow: 0 6px 30px rgba(0,0,0,0.08); overflow:hidden; border:1px solid #F6E6C5; }
    .header { background:#1C4532; padding:18px 24px; color:#FAFAF9; }
  .brand { font-size:20px; font-weight:800; margin:0; display:flex; align-items:center; gap:8px; }
  .recycle-icon { display:inline-block; font-size:18px; color:#ECC94B; }
  /* Some clients (Apple Mail) support animations; others will just show the icon static */
  @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
  .recycle-icon.spin { animation: spin 6s linear infinite; }
  @media (prefers-reduced-motion: reduce) { .recycle-icon.spin { animation: none !important; } }
    .content { padding: 24px; }
    .footer { padding: 18px 24px; color:#64748b; font-size:12px; text-align:center; }
    .btn { display:inline-block; padding:12px 18px; background:#2F855A; color:#fff; border-radius: 999px; font-weight:700; box-shadow: 0 4px 14px rgba(47,133,90,0.25); }
    .btn:hover { background:#1C4532; }
    .muted { color:#64748b; }
    .divider { height:1px; background:#F1F5F9; margin: 18px 0; }
  </style>
</head>
<body>
  <div class="wrapper">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
      <tr>
        <td align="center">
          <div class="container">
            <div class="header">
              <p class="brand"><span class="recycle-icon spin" aria-hidden="true">♻</span> ReCircle</p>
            </div>
            <div class="content">
              @yield('content')
            </div>
            <div class="footer">
              <p class="muted">&copy; {{ date('Y') }} ReCircle. All rights reserved.</p>
              <p class="muted">You received this email because you signed up for ReCircle.</p>
            </div>
          </div>
        </td>
      </tr>
    </table>
  </div>
</body>
</html>
