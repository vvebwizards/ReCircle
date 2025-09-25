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
          <h1>Your circular hub</h1>
          <p class="dash-sub">Here’s a quick snapshot of your impact and activity. Keep the loop going.</p>
          <div class="dash-chips">
            <span class="chip"><i class="fa-solid fa-fire"></i> 7‑day streak</span>
            <span class="chip"><i class="fa-solid fa-seedling"></i> Goal: 15% ↑ this week</span>
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
        <div class="stat-icon" style="--accent: var(--color-sunflower);"><i class="fa-solid fa-cloud"></i></div>
        <div>
          <div class="stat-value" id="stat-co2">0</div>
          <div class="stat-label">Kg CO₂ Saved</div>
        </div>
      </div>

      <div class="dash-card stat">
        <div class="stat-icon" style="--accent: var(--color-emerald);"><i class="fa-solid fa-recycle"></i></div>
        <div>
          <div class="stat-value" id="stat-landfill">0</div>
          <div class="stat-label">Kg Landfill Diverted</div>
        </div>
      </div>

      <div class="dash-card stat">
        <div class="stat-icon" style="--accent: var(--color-sky);"><i class="fa-solid fa-list"></i></div>
        <div>
          <div class="stat-value" id="stat-listings">0</div>
          <div class="stat-label">Active Listings</div>
        </div>
      </div>

      <div class="dash-card wide">
        <h3 class="dash-card-title"><i class="fa-solid fa-bolt"></i> Quick Actions</h3>
        <div class="qa-grid">
          <a href="#" class="qa-card qa-new" aria-label="Create new waste listing">
            <span class="qa-icon"><i class="fa-solid fa-plus"></i></span>
            <span class="qa-title">New Listing</span>
            <span class="qa-sub">List waste with AI assist</span>
          </a>
          <a href="#" class="qa-card qa-bids" aria-label="View and manage bids">
            <span class="qa-icon"><i class="fa-solid fa-gavel"></i></span>
            <span class="qa-title">View Bids</span>
            <span class="qa-sub">Compare offers quickly</span>
          </a>
          <a href="#" class="qa-card qa-market" aria-label="Browse marketplace">
            <span class="qa-icon"><i class="fa-solid fa-store"></i></span>
            <span class="qa-title">Marketplace</span>
            <span class="qa-sub">Discover upcycled goods</span>
          </a>
          <a href="#" class="qa-card qa-report" aria-label="Open impact report">
            <span class="qa-icon"><i class="fa-solid fa-chart-line"></i></span>
            <span class="qa-title">Impact Report</span>
            <span class="qa-sub">Weekly performance</span>
          </a>
        </div>
      </div>

      <div class="dash-card wide">
        <h3 class="dash-card-title"><i class="fa-solid fa-clock-rotate-left"></i> Recent Activity</h3>
        <ul class="activity-list" id="activity-list"></ul>
      </div>
    </section>
  </div>
</main>
@endsection