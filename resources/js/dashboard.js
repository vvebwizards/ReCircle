// Dashboard interactions adapted to Laravel routes
document.addEventListener('DOMContentLoaded', () => {
  const routes = (window.appRoutes || {});
  const authUrl = routes.auth || '/auth';

  const ensureAuth = async () => {
    try {
      const res = await fetch('/api/auth/me', { headers: { 'Accept': 'application/json' }, credentials: 'include' });
      if (!res.ok) { window.location.replace(authUrl); return null; }
      const data = await res.json().catch(() => ({}));
      window.__currentUser = data?.data || null;
      return window.__currentUser;
    } catch { window.location.replace(authUrl); return null; }
  };

  // Early auth check
  ensureAuth();

  // Delegated sign out
  document.addEventListener('click', async (e) => {
    const so = e.target.closest('[data-signout]');
    if (!so) return;
    e.preventDefault();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    try {
      const r = await fetch('/api/auth/logout', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, credentials: 'include' });
      console.debug('Dashboard logout status', r.status);
    } catch (e) { console.warn('Dashboard logout error', e); }
    window.location.replace(authUrl);
  });

  // Fake stats animation
  const animate = (el, target, duration = 1000) => {
    if (!el) return;
    let start = 0; const step = Math.max(1, Math.floor(target / (duration / 16)));
    const t = setInterval(() => {
      start += step;
      if (start >= target) { el.textContent = target.toLocaleString(); clearInterval(t); }
      else { el.textContent = start.toLocaleString(); }
    }, 16);
  };
  animate(document.getElementById('stat-co2'), 12500, 1400);
  animate(document.getElementById('stat-landfill'), 8750, 1400);
  animate(document.getElementById('stat-listings'), 24, 900);

  // Populate recent activity
  const activity = [
    { icon: 'fa-plus', text: 'You created a new listing: 20kg cardboard sheets', time: '2h ago' },
    { icon: 'fa-gavel', text: '3 new bids on: Mixed plastic offcuts', time: '5h ago' },
    { icon: 'fa-truck', text: 'Courier pickup scheduled for listing #1042', time: 'Yesterday' },
    { icon: 'fa-chart-line', text: 'Your weekly impact report is ready', time: '2d ago' },
  ];
  const list = document.getElementById('activity-list');
  if (list) list.innerHTML = activity.map(a => `
    <li class="act-row">
      <span class="act-icon"><i class="fa-solid ${a.icon}"></i></span>
      <span class="act-text">${a.text}</span>
      <span class="act-time">${a.time}</span>
    </li>`).join('');
});
