@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('admin-content')
  <div class="admin-topbar">
    <div>
      <h1>Admin Dashboard</h1>
      <div class="tb-sub">System status and recent activity</div>
    </div>
    <div class="tb-right">
      <div class="tb-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" placeholder="Search..." /></div>
      <button class="tb-btn" data-signout title="Sign Out"><i class="fa-solid fa-right-from-bracket"></i></button>
    </div>
  </div>

  <section class="admin-stats">
    <div class="a-card stat" style="--accent:#16a34a">
      <div class="a-icon"><i class="fa-solid fa-cloud"></i></div>
      <div>
        <div class="a-value" id="a-co2">0</div>
        <div class="a-label">Kg CO₂ Saved</div>
      </div>
    </div>
    <div class="a-card stat" style="--accent:#2563eb">
      <div class="a-icon"><i class="fa-regular fa-user"></i></div>
      <div>
        <div class="a-value" id="a-users">0</div>
        <div class="a-label">Users</div>
      </div>
    </div>
    <div class="a-card stat" style="--accent:#f59e0b">
      <div class="a-icon"><i class="fa-solid fa-list"></i></div>
      <div>
        <div class="a-value" id="a-listings">0</div>
        <div class="a-label">Listings</div>
      </div>
    </div>
    <div class="a-card stat" style="--accent:#dc2626">
      <div class="a-icon"><i class="fa-solid fa-flag"></i></div>
      <div>
        <div class="a-value" id="a-flags">0</div>
        <div class="a-label">Flags</div>
      </div>
    </div>
  </section>

  <section class="admin-grid">
    <div class="a-card wide">
      <div class="a-title"><i class="fa-solid fa-users"></i> Recent Users</div>
      <table class="a-table">
        <thead><tr><th>Name</th><th>Role</th><th>Joined</th><th>Status</th></tr></thead>
        <tbody id="a-users-body"></tbody>
      </table>
    </div>
  </section>
@endsection
