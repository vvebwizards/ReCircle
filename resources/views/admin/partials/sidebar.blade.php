<aside class="sidebar" aria-label="Admin sidebar navigation">
    <div class="side-brand">
        <a href="{{ route('admin.dashboard') }}" class="brand" aria-label="Admin home">
            <span class="brand-icon"><i class="fa-solid fa-recycle"></i></span>
            <span class="brand-text">ReCircle Admin</span>
        </a>
    </div>
    <nav class="side-nav" role="navigation">
    <a href="{{ route('admin.dashboard') }}" class="side-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="fa-solid fa-gauge"></i><span>Overview</span></a>
    <a href="{{ route('admin.users') }}" class="side-link"><i class="fa-solid fa-users {{ request()->routeIs('admin.users') ? 'active' : '' }}"></i><span>Users</span></a>
   
    <a href="{{ route('admin.pickups.index') }}"
   class="side-link {{ request()->routeIs('admin.pickups.*') ? 'active' : '' }}">
  <i class="fa-solid fa-truck"></i><span>Pickups</span>
</a>

<a href="{{ route('admin.deliveries.index') }}"
   class="side-link {{ request()->routeIs('admin.deliveries.*') ? 'active' : '' }}">
   <i class="fa-solid fa-truck-fast"></i><span>Deliveries</span>
</a>

    <a href="{{ route('admin.audit-logs.index') }}" class="side-link"><i class="fa-solid fa-clipboard-list"></i><span>Audit Logs</span></a>
    <a href="{{ route('admin.listings.index') }}" class="side-link {{ request()->routeIs('admin.listings.*') ? 'active' : '' }}"><i class="fa-solid fa-list"></i><span>Listings</span></a>
    <a href="#" class="side-link"><i class="fa-solid fa-gavel"></i><span>Bids</span></a>
    <a href="#" class="side-link" id="notificationsLink">
        <i class="fa-solid fa-bell"></i><span>Notifications</span>
        <div class="notification-badge" id="notificationBadge" style="display: flex;">
            <span class="notification-count" id="notificationCount">4</span>
        </div>
    </a>
    <a href="#" class="side-link"><i class="fa-solid fa-chart-line"></i><span>Reports</span></a>
    <a href="{{ route('chat.index') }}" class="side-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
        <i class="fa-solid fa-comments"></i><span>Chat</span>
    </a>
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
