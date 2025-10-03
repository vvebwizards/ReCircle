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
          <h1>Your Maker Hub</h1>
          <p class="dash-sub">Track your materials, products, and bids. Keep the circular loop going.</p>
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
          <div class="stat-value" id="stat-materials">0</div>
          <div class="stat-label">My Materials</div>
        </div>
      </div>

      <div class="dash-card stat">
        <div class="stat-icon" style="--accent: var(--color-purple);"><i class="fa-solid fa-cube"></i></div>
        <div>
          <div class="stat-value" id="stat-products">0</div>
          <div class="stat-label">My Products</div>
        </div>
      </div>

      <div class="dash-card wide">
        <h3 class="dash-card-title"><i class="fa-solid fa-bolt"></i> Quick Actions</h3>
        <div class="qa-grid">
          
          
          <a href="{{ route('materials.create') }}" class="qa-card qa-new" aria-label="Add new material">
            <span class="qa-icon"><i class="fa-solid fa-plus"></i></span>
            <span class="qa-title">Add Material</span>
            <span class="qa-sub">Link to received waste items</span>
          </a>
          
          <a href="{{ route('maker.materials.index') }}" class="qa-card qa-list-material" aria-label="View my materials">
            <div class="stat-icon" style="--accent: #d71616;"><i class="fa-solid fa-list"></i></div>
            <span class="qa-title">My Materials</span>
            <span class="qa-sub">View and manage your materials</span>
          </a>

          <a href="{{ route('maker.products.create') }}" class="qa-card qa-product" aria-label="Create new product">
            <span class="qa-icon"><i class="fa-solid fa-hammer"></i></span>
            <span class="qa-title">Create Product</span>
            <span class="qa-sub">Transform materials into products</span>
          </a>

          <a href="{{ route('maker.products') }}" class="qa-card qa-market" aria-label="View my products">
            <span class="qa-icon"><i class="fa-solid fa-store"></i></span>
            <span class="qa-title">My Products</span>
            <span class="qa-sub">Products ready for sale</span>
          </a>
          
          <a href="#" class="qa-card qa-bids" aria-label="View my bids">
            <span class="qa-icon"><i class="fa-solid fa-gavel"></i></span>
            <span class="qa-title">My Bids</span>
            <span class="qa-sub">Offers on waste items</span>
          </a>
          
          <a href="#" class="qa-card qa-report" aria-label="View impact report">
            <span class="qa-icon"><i class="fa-solid fa-chart-line"></i></span>
            <span class="qa-title">Impact Report</span>
            <span class="qa-sub">Track your circular contribution</span>
          </a>
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