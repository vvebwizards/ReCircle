@extends('layouts.app')
@push('head')
@vite(['resources/css/material-create.css', 'resources/js/waste-item-create.js'])
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-sA+e2X0kqkzvGZsFh5gk8G8m7k6b0hKp3yZq+o0XwYQ=" crossorigin="" />
<style>
  /* small helpers for create page map */
  #locationMap { width: 100%; height: 300px; border-radius: 8px; border: 1px solid #e5e7eb; }
  .address-search { display:flex; gap:0.5rem; margin-bottom:0.5rem; }
  .address-search input { flex:1 }
</style>
@endpush

@section('content')
<main class="dashboard">
  <div class="container">
    <h1 class="dash-title"><i class="fa-solid fa-trash"></i> Create New Waste Item</h1>
    <p class="dash-sub">Register a newly generated waste item for potential recycling or conversion.</p>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="material-container">
      <div class="material-form-container">
  <form action="{{ route('generator.waste-items.store') }}" method="POST" class="material-form" id="wasteItemForm" novalidate enctype="multipart/form-data">
          @csrf
          @if ($errors->any())
            <div class="alert alert-danger" role="alert">
              <strong>Please fix the errors below:</strong>
              <ul style="margin:0.5rem 0 0; padding-left:1.2rem;">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          <div class="form-group full-width {{ $errors->has('title') ? 'has-error' : '' }}">
            <label for="title">Title *</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}" placeholder="e.g., Mixed Plastic Batch" class="{{ $errors->has('title') ? 'is-invalid' : '' }}">
            @error('title')<small class="error-text">{{ $message }}</small>@enderror
          </div>

          <div class="form-group {{ $errors->has('condition') ? 'has-error' : '' }}">
            <label for="condition">Condition *</label>
            <select name="condition" id="condition" class="{{ $errors->has('condition') ? 'is-invalid' : '' }}">
              <option value="">Select</option>
              @foreach(['good','fixable','scrap'] as $c)
                <option value="{{ $c }}" {{ old('condition') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
              @endforeach
            </select>
            @error('condition')<small class="error-text">{{ $message }}</small>@enderror
          </div>

          <div class="form-group {{ $errors->has('estimated_weight') ? 'has-error' : '' }}">
            <label for="estimated_weight">Estimated Weight (kg) *</label>
            <input type="number" step="0.01" min="0" name="estimated_weight" id="estimated_weight" value="{{ old('estimated_weight') }}" placeholder="0.00" class="{{ $errors->has('estimated_weight') ? 'is-invalid' : '' }}">
            @error('estimated_weight')<small class="error-text">{{ $message }}</small>@enderror
          </div>

          <div class="form-group full-width {{ $errors->has('images') || $errors->has('images.*') ? 'has-error' : '' }}">
            <label for="images">Images *</label>
            <div id="imageDropzone" class="image-dropzone" tabindex="0" role="button" aria-label="Upload images" data-required="true">
              <p class="dz-instructions"><i class="fa-solid fa-cloud-arrow-up"></i> Drag & drop images here or <span class="link">browse</span><br><small>Up to 10 images, max 2MB each (jpg, jpeg, png, gif, webp)</small></p>
              <input type="file" id="images" name="images[]" multiple accept="image/*" hidden>
            </div>
            <div id="imagePreviewList" class="image-preview-list"></div>
            @error('images')<small class="error-text">{{ $message }}</small>@enderror
            @error('images.*')<small class="error-text">{{ $message }}</small>@enderror
          </div>

          <div class="form-group full-width">
            <label>Location (pick an address or click on the map) *</label>
            <div class="address-search">
              <input type="search" id="addressSearch" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" placeholder="Search an address (e.g. 1600 Amphitheatre Pkwy, Mountain View)" aria-label="Search address">
              <button type="button" id="addressSearchBtn" class="btn btn-secondary">Search</button>
              <button type="button" id="useMyLocationBtn" class="btn btn-outline">Use my location</button>
            </div>
            <div id="locationMap" role="application" aria-label="Map for picking location"></div>

            <!-- will be filled by JS when user selects a point -->
            <input type="hidden" name="location[lat]" id="locationLat" value="{{ old('location.lat') }}">
            <input type="hidden" name="location[lng]" id="locationLng" value="{{ old('location.lng') }}">
            <input type="hidden" name="location[address]" id="locationAddress" value="{{ old('location.address') }}">

            <small class="muted">Tip: search for an address or click the map to drop a pin. You can also use your device location.</small>
            @error('location')<small class="error-text">{{ $message }}</small>@enderror
            @error('location.lat')<small class="error-text">{{ $message }}</small>@enderror
            @error('location.lng')<small class="error-text">{{ $message }}</small>@enderror
          </div>

          <div class="form-group full-width {{ $errors->has('notes') ? 'has-error' : '' }}">
            <label for="notes">Notes</label>
            <textarea name="notes" id="notes" rows="3" placeholder="Additional details..." class="{{ $errors->has('notes') ? 'is-invalid' : '' }}">{{ old('notes') }}</textarea>
            @error('notes')<small class="error-text">{{ $message }}</small>@enderror
          </div>

          <div class="form-group full-width">
            <button type="submit" class="btn btn-primary mt-1"><i class="fa-solid fa-save"></i> Create Waste Item</button>
          </div>
        </form>
      </div>

      <div class="instructions-sidebar">
        <div class="instructions-panel">
          <div class="instructions-header">
            <h3><i class="fa-solid fa-info-circle"></i> Tips</h3>
            <button class="instructions-toggle" aria-label="Toggle instructions"><i class="fa-solid fa-chevron-down"></i></button>
          </div>
          <div class="instructions-content">
            <ul class="instructions-list">
              <li>Use a clear, descriptive title (e.g., Sorted PET Plastic Bottles)</li>
              <li>Choose the most accurate condition for processing</li>
              <li>Include an estimated weight if known</li>
              <li>Upload clear, well-lit images (first image becomes the primary)</li>
              <li>Location helps logistics and buyers plan transport</li>
            </ul>
            <div class="instruction-warning">
              <strong><i class="fa-solid fa-exclamation-triangle"></i> Note:</strong> You can edit waste items later to refine details or manage images.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
@push('scripts')
<script type="module" src="{{ Vite::asset('resources/js/waste-item-create.js') }}"></script>
@endpush
@endsection
