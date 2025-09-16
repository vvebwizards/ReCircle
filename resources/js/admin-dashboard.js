// Admin dashboard interactions (demo) adapted to Laravel routes
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
  ensureAuth().then((user) => {
    if (!user) return;
    // Avatar initials & user info
    const init = (u) => {
      if (!u) return 'AD';
      const guess = (u.name || u.email || 'Admin').toString();
      const parts = guess.split(/[\s._-]+/).filter(Boolean);
      return ((parts[0]?.[0] || 'A') + (parts[1]?.[0] || 'D')).toUpperCase();
    };
    const av = document.getElementById('admin-avatar');
    const nm = document.getElementById('admin-name');
    const em = document.getElementById('admin-email');
    if (av) av.textContent = init(user);
    if (nm && user?.name) nm.textContent = user.name;
    if (em && user?.email) em.textContent = user.email;
  });

  // Sidebar is always pinned now (toggle removed)

  // Delegated sign out
  document.addEventListener('click', async (e) => {
    const so = e.target.closest('[data-signout]');
    if (!so) return;
    e.preventDefault();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    try {
      const r = await fetch('/api/auth/logout', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, credentials: 'include' });
      console.debug('Admin logout status', r.status);
    } catch (e) { console.warn('Admin logout error', e); }
    window.location.replace(authUrl);
  });

  // (localStorage avatar code replaced by ensureAuth above)

  // Stats animation
  const animate = (el, target, duration = 1000) => {
    if (!el) return;
    let start = 0; const step = Math.max(1, Math.floor(target / (duration / 16)));
    const t = setInterval(() => {
      start += step; if (start >= target) { el.textContent = target.toLocaleString(); clearInterval(t); }
      else { el.textContent = start.toLocaleString(); }
    }, 16);
  };
  animate(document.getElementById('a-co2'), 18250, 1200);
  animate(document.getElementById('a-users'), 4920, 1000);
  animate(document.getElementById('a-listings'), 1280, 1000);
  animate(document.getElementById('a-flags'), 7, 800);

  // Table data
  const rows = [
    ['Samira Khan', 'Maker', 'Today', 'Active'],
    ['Luis Ortega', 'Generator', 'Today', 'Pending'],
    ['Akua Mensah', 'Buyer', 'Yesterday', 'Active'],
    ['Ethan Li', 'Courier', '2 days ago', 'Active'],
  ];
  const body = document.getElementById('a-users-body');
  if (body) body.innerHTML = rows.map(r => `<tr><td>${r[0]}</td><td>${r[1]}</td><td>${r[2]}</td><td>${r[3]}</td></tr>`).join('');
});
