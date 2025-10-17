<aside class="sidebar" aria-label="Admin sidebar navigation">
    <div class="side-brand">
        <a href="{{ route('admin.dashboard') }}" class="brand" aria-label="Admin home">
            <span class="brand-icon"><i class="fa-solid fa-recycle"></i></span>
            <span class="brand-text">ReCircle Admin</span>
        </a>
    </div>
    <nav class="side-nav" role="navigation">
    <a href="{{ route('admin.dashboard') }}" class="side-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="fa-solid fa-gauge"></i><span>Overview</span></a>
    <a href="{{ route('admin.users') }}" class="side-link {{ request()->routeIs('admin.users') ? 'active' : '' }}"><i class="fa-solid fa-users"></i><span>Users</span></a>
    <a href="{{ route('admin.notifications.index') }}" class="side-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
        <i class="fa-solid fa-bell"></i>
        <span>Notifications</span>
        <span id="notification-badge" class="notif-pill" aria-label="Unread notifications" style="display:none"></span>
    </a>
    <a href="{{ route('admin.reclamations.index') }}" class="side-link {{ request()->routeIs('admin.reclamations.*') ? 'active' : '' }}">
        <i class="fa-solid fa-flag"></i>
        <span>Reclamations</span>
        <span id="reclamation-badge" class="notif-pill" aria-label="Pending reclamations" style="display:none"></span>
    </a>
    <a href="{{ route('admin.audit-logs.index') }}" class="side-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}"><i class="fa-solid fa-clipboard-list"></i><span>Audit Logs</span></a>
    <a href="{{ route('admin.listings.index') }}" class="side-link {{ request()->routeIs('admin.listings.*') ? 'active' : '' }}"><i class="fa-solid fa-list"></i><span>Listings</span></a>
    <a href="{{ route('admin.carts.index') }}" class="side-link {{ request()->routeIs('admin.carts.*') ? 'active' : '' }}"><i class="fa-solid fa-shopping-cart"></i><span>Carts</span></a>
    <a href="#" class="side-link"><i class="fa-solid fa-gavel"></i><span>Bids</span></a>
    <a href="#" class="side-link"><i class="fa-solid fa-chart-line"></i><span>Reports</span></a>
        <div class="side-sep"></div>
    <a href="#" class="side-link"><i class="fa-solid fa-gears"></i><span>Settings</span></a>
    <a href="#" class="side-link danger" data-signout><i class="fa-solid fa-right-from-bracket"></i><span>Sign Out</span></a>
    </nav>
    <div class="side-footer">
        <div class="side-user">
            <span id="admin-avatar" class="avatar sm">AD</span>
            <div class="side-user-meta">
                <strong id="admin-name">Admin</strong>
                <small id="admin-email">admin@example.com</small>
            </div>
        </div>
    </div>
</aside>

<script>
// Update notification badge
function updateNotificationBadge() {
    const tryFetch = (url) => fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(r => r.ok ? r.json() : Promise.reject(r.status))
        .catch(() => null);

    tryFetch('/admin/notifications/unread-count')
        .then(data => data || tryFetch('/admin/notifications/api/unread-count'))
        .then(data => {
            if (!data) return;
            const badge = document.getElementById('notification-badge');
            if (data.count > 0) {
                badge.textContent = data.count > 99 ? '99+' : data.count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error fetching notification count:', error));
}

// Update reclamation badge
function updateReclamationBadge() {
    fetch('/admin/reclamations/pending-count', { 
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, 
        credentials: 'same-origin' 
    })
    .then(r => r.ok ? r.json() : Promise.reject(r.status))
    .then(data => {
        const badge = document.getElementById('reclamation-badge');
        if (data.count > 0) {
            badge.textContent = data.count > 99 ? '99+' : data.count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    })
    .catch(error => console.error('Error fetching reclamation count:', error));
}

// Update badges on page load
document.addEventListener('DOMContentLoaded', () => {
    updateNotificationBadge();
    updateReclamationBadge();
});

// Update badges every 30 seconds
setInterval(() => {
    updateNotificationBadge();
    updateReclamationBadge();
}, 30000);
</script>