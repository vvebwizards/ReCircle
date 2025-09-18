@extends('layouts.minimal')

@section('title', 'Choose your role')

@section('content')
<section class="auth-section auth-compact">
  <div class="container">
    <div class="auth-wrapper">
      <div class="auth-info">
        <div class="auth-recycle" aria-hidden="true">
          <i class="fa-solid fa-user-gear"></i>
        </div>
        <h1>Pick how you’ll use ReCircle</h1>
        <p>Choose the role that best fits what you want to do. You can update it later in settings.</p>
      </div>
      <div class="auth-card">
        <form method="POST" action="{{ route('choose-role.store') }}" class="auth-form" novalidate>
          @csrf
          <fieldset>
            <legend class="sr-only">Select your role</legend>
            <div class="role-grid">
              <label class="role-card">
                <input type="radio" name="role" value="generator" required>
                <span class="role-title"><i class="fa-solid fa-dumpster"></i> Generator</span>
                <span class="role-sub">List waste and track impact</span>
              </label>
              <label class="role-card">
                <input type="radio" name="role" value="maker" required>
                <span class="role-title"><i class="fa-solid fa-screwdriver-wrench"></i> Maker</span>
                <span class="role-sub">Repair and upcycle materials</span>
              </label>
              <label class="role-card">
                <input type="radio" name="role" value="buyer" required>
                <span class="role-title"><i class="fa-solid fa-bag-shopping"></i> Buyer</span>
                <span class="role-sub">Shop upcycled products</span>
              </label>
              <label class="role-card">
                <input type="radio" name="role" value="courier" required>
                <span class="role-title"><i class="fa-solid fa-truck"></i> Courier</span>
                <span class="role-sub">Pickup and deliveries</span>
              </label>
            </div>
          </fieldset>
          @error('role')
          <small class="field-error" aria-live="assertive">{{ $message }}</small>
          @enderror
          <div class="social-center" style="margin-top:1rem;">
            <button type="submit" class="btn btn-primary">Continue</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection

@push('head')
<style>
  .role-grid { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: .75rem; }
  .role-card { display:flex; flex-direction:column; gap:.25rem; padding:1rem; border:1px solid #e7e5e4; border-radius:12px; cursor:pointer; }
  .role-card input { display:none; }
  .role-title { font-weight:700; color: var(--color-deep-green); display:flex; align-items:center; gap:.5rem; }
  .role-sub { color:#475569; font-size:.9rem; }
  .role-card:has(input:checked) { border-color: var(--color-emerald); box-shadow: 0 0 0 3px rgba(47,133,90,.12); }
    /* Icon hover animation */
    .role-title i { display:inline-block; transition: transform .22s ease, color .22s ease, text-shadow .22s ease; transform-origin:center; will-change: transform; }
    .role-card:hover .role-title i,
    .role-card:focus-within .role-title i { transform: translateY(-2px) scale(1.1) rotate(-3deg); color: var(--color-emerald); text-shadow: 0 4px 12px rgba(16,185,129,.25); }
    /* Keep selected state visually active */
    .role-card:has(input:checked) .role-title i { color: var(--color-emerald); }
    /* Subtle lift on the whole card for better affordance */
    .role-card { transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease; }
    .role-card:hover, .role-card:focus-within { transform: translateY(-2px); border-color: var(--color-emerald); box-shadow: 0 8px 28px rgba(2,6,23,.06); }
    /* Accessibility: reduce motion */
    @media (prefers-reduced-motion: reduce) {
      .role-title i { transition: none; }
      .role-card:hover .role-title i,
      .role-card:focus-within .role-title i { transform: none; }
      .role-card { transition: border-color .2s ease, box-shadow .2s ease; }
      .role-card:hover, .role-card:focus-within { transform: none; }
    }
  @media (max-width: 640px) { .role-grid { grid-template-columns: 1fr; } }
</style>
@endpush
