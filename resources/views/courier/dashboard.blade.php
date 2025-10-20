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
          <span class="hello-badge"><i class="fa-solid fa-truck"></i> Courier</span>
          <h1>Your Courier Hub</h1>
          <p class="dash-sub">Quick access to pickups and deliveries.</p>
        </div>
        <div class="dash-art" aria-hidden="true">
          <div class="blob b1"></div>
          <div class="blob b2"></div>
        </div>
      </div>
    </header>

    <section class="dash-grid">
      <div class="dash-card wide">
        <h3 class="dash-card-title"><i class="fa-solid fa-truck-fast"></i> Pickups</h3>
        <div class="qa-grid">
          <a href="{{ route('pickups.index') }}" class="qa-card qa-pickups" aria-label="View pickups">
            <span class="qa-icon"><i class="fa-solid fa-truck-fast"></i></span>
            <span class="qa-title">Pickups</span>
            <span class="qa-sub">View and claim pickups</span>
          </a>
        </div>
      </div>
    </section>
  </div>
</main>
@endsection
