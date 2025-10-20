@extends('layouts.app')

@push('head')
@vite(['resources/js/dashboard.js'])
@endpush

@section('content')
<main class="dashboard">
  <div class="container">
    <header class="dash-header">
      <div class="dash-hero">
        <div class="dash-hello">
          <span class="hello-badge"><i class="fa-solid fa-wand-magic-sparkles"></i> Welcome back</span>
          <h1>Your Buyer Hub</h1>
          <p class="dash-sub">Explore listings and manage pickups.</p>
          <div class="dash-chips">
            <span class="chip"><i class="fa-solid fa-heart"></i> Favorites</span>
            <span class="chip"><i class="fa-solid fa-truck"></i> Upcoming pickups</span>
          </div>
        </div>
        <div class="dash-art" aria-hidden="true">
          <div class="blob b1"></div>
          <div class="blob b2"></div>
          <div class="blob b3"></div>
        </div>
      </div>
    </header>

    <section class="dash-grid">
      <div class="dash-card stat">
        <div class="stat-icon" style="--accent: var(--color-sunflower);"><i class="fa-solid fa-dollar-sign"></i></div>
        <div>
          <div class="stat-value" id="stat-spend">0</div>
          <div class="stat-label">Spent this month</div>
        </div>
      </div>

      <div class="dash-card stat">
        <div class="stat-icon" style="--accent: var(--color-emerald);"><i class="fa-solid fa-box-open"></i></div>
        <div>
          <div class="stat-value" id="stat-orders">0</div>
          <div class="stat-label">Orders</div>
        </div>
      </div>

      <div class="dash-card stat">
        <div class="stat-icon" style="--accent: var(--color-sky);"><i class="fa-solid fa-store"></i></div>
        <div>
          <div class="stat-value" id="stat-saved">0</div>
          <div class="stat-label">Saved items</div>
        </div>
      </div>

      <div class="dash-card stat">
        <div class="stat-icon" style="--accent: var(--color-purple);"><i class="fa-solid fa-truck-fast"></i></div>
        <div>
          <div class="stat-value" id="stat-pickups">0</div>
          <div class="stat-label">Scheduled pickups</div>
        </div>
      </div>

      <div class="dash-card wide">
        <h3 class="dash-card-title"><i class="fa-solid fa-bolt"></i> Quick Actions</h3>
        <div class="qa-grid">
          <a href="{{ route('buyer.marketplace.index') }}" class="qa-card qa-market" aria-label="Open marketplace">
            <span class="qa-icon"><i class="fa-solid fa-store"></i></span>
            <span class="qa-title">Marketplace</span>
            <span class="qa-sub">Browse materials & products</span>
          </a>

          <a href="{{ route('pickups.index') }}" class="qa-card qa-pickups" aria-label="View pickups">
            <span class="qa-icon"><i class="fa-solid fa-truck-fast"></i></span>
            <span class="qa-title">Pickups</span>
            <span class="qa-sub">Manage scheduled pickups</span>
          </a>

          <!-- Removed search and favorites per spec -->
        </div>
      </div>

      <div class="dash-card wide">
        <h3 class="dash-card-title"><i class="fa-solid fa-clock-rotate-left"></i> Recent Activity</h3>
        <ul class="activity-list" id="activity-list">
          <li>No recent activity</li>
        </ul>
      </div>
    </section>
  </div>
</main>
@endsection
