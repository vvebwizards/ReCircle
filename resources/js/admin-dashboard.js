// Admin dashboard interactions (demo) adapted to Laravel routes
document.addEventListener('DOMContentLoaded', () => {
  const routes = (window.appRoutes || {});
  const authUrl = routes.auth || '/auth';
  try { if (localStorage.getItem('rc_auth') !== 'true') { window.location.replace(authUrl); return; } } catch {}

  // Sidebar is always pinned now (toggle removed)

  // Delegated sign out
  document.addEventListener('click', (e) => {
    const so = e.target.closest('[data-signout]');
    if (!so) return;
    e.preventDefault();
    try { localStorage.removeItem('rc_auth'); localStorage.removeItem('rc_user'); } catch {}
    window.location.replace(authUrl);
  });

  // Avatar initials
  try {
    const u = JSON.parse(localStorage.getItem('rc_user') || 'null');
    const init = (u) => {
      if (!u) return 'AD';
      const guess = (u.name || u.email || 'Admin').toString();
      const parts = guess.split(/[\s._-]+/).filter(Boolean);
      return ((parts[0]?.[0] || 'A') + (parts[1]?.[0] || 'D')).toUpperCase();
    };
    const av = document.getElementById('admin-avatar');
    const nm = document.getElementById('admin-name');
    const em = document.getElementById('admin-email');
    if (av) av.textContent = init(u);
    if (nm && u?.name) nm.textContent = u.name;
    if (em && u?.email) em.textContent = u.email;
  } catch {}

  // Stats animation
  const animate = (el, target, duration = 1000) => {
    if (!el) return;
    let start = 0, step = Math.max(1, Math.floor(target / (duration / 16)));
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
