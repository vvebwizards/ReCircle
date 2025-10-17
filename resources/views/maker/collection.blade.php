@extends('layouts.app')

@push('head')
<style>
  /* Toolbar */
  .filters { 
    padding: 1.2rem; 
    display: flex; 
    gap: .85rem; 
    align-items: center; 
    flex-wrap: wrap; 
    position: relative; 
    background: linear-gradient(to right, rgba(16, 185, 129, 0.07), rgba(59, 130, 246, 0.07));
    border-radius: 1rem;
    box-shadow: inset 0 0 0 1px rgba(16, 185, 129, 0.15);
  }
  .filters input[type="text"], .filters select {
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: .6rem; 
    padding: .65rem .95rem; 
    outline: none;
    background: #ffffff; 
    transition: all .2s ease;
    min-width: 140px;
    font-size: 0.95rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
    color: #1f2937;
  }
  .filters input[type="text"] { 
    min-width: 200px; 
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='18' height='18' stroke='%2310b981' stroke-width='2' fill='none' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 10px center;
    padding-left: 35px;
  }
  .filters select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='18' height='18' stroke='%2310b981' stroke-width='2' fill='none' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 35px;
  }
  .filters input[type="text"]:hover, .filters select:hover {
    border-color: rgba(16, 185, 129, 0.5);
    background-color: rgba(255, 255, 255, 0.95);
  }
  .filters input[type="text"]:focus, .filters select:focus { 
    box-shadow: 0 0 0 3px rgba(16, 185, 129, .25); 
    border-color: #10b981; 
    background-color: white;
  }
  
  /* Filter section title */
  .filters::before {
    content: "Filter Collection";
    position: absolute;
    top: -10px;
    left: 18px;
    background: linear-gradient(to right, #10b981, #3b82f6);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0 8px;
    letter-spacing: 0.5px;
  }
  
  /* Loading indicator */
  .filters.loading::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 1rem;
  }
  .filters.loading::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 24px;
    height: 24px;
    margin-top: -12px;
    margin-left: -12px;
    border: 3px solid rgba(16, 185, 129, 0.3);
    border-radius: 50%;
    border-top-color: #10b981;
    z-index: 10;
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
    animation: loading-spinner 0.8s linear infinite;
  }
  @keyframes loading-spinner {
    to { transform: rotate(360deg); }
  }
  .btn.primary { 
    background: linear-gradient(to right, #10b981, #0ea371); 
    color: #fff; 
    border: 0; 
    padding: .65rem 1rem; 
    border-radius: .6rem; 
    font-weight: 500;
    transition: all .2s ease;
    box-shadow: 0 2px 5px rgba(16, 185, 129, 0.25);
  }
  .btn.primary:hover { 
    background: linear-gradient(to right, #0ea371, #0c8c61);
    box-shadow: 0 3px 7px rgba(16, 185, 129, 0.35);
    transform: translateY(-1px);
  }
  .btn.secondary { 
    background: #ffffff; 
    color: #10b981; 
    padding: .65rem 1rem; 
    border-radius: .6rem;
    border: 1px solid rgba(16, 185, 129, 0.3);
    font-weight: 500;
    transition: all .2s ease;
  }
  .btn.secondary:hover { 
    background: rgba(16, 185, 129, 0.07);
    border-color: rgba(16, 185, 129, 0.5);
  }

  /* Utility classes */
  .mb-4 { margin-bottom: 1.5rem; }
  
  /* Filter section */
  .filter-section {
    margin-bottom: 1.5rem;
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(16, 185, 129, 0.1);
    overflow: hidden;
    border: 1px solid rgba(16, 185, 129, 0.08);
    transition: box-shadow 0.3s ease;
  }
  
  .filter-section:hover {
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08), 0 2px 5px rgba(16, 185, 129, 0.15);
  }
  
  /* Cards grid */
  .grid-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem; }
  .card { border-radius: 1rem; overflow: hidden; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,.05); transition: transform .18s ease, box-shadow .18s ease; }
  .card:hover { transform: translateY(-3px); box-shadow: 0 8px 18px rgba(0,0,0,.12); }
  .card-media { position: relative; aspect-ratio: 16/10; background: #f3f4f6; cursor: pointer; }
  .card-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .card-badge { position: absolute; top: .6rem; left: .6rem; background: rgba(17,24,39,.7); color: #fff; font-size: .75rem; padding: .25rem .5rem; border-radius: .375rem; backdrop-filter: blur(4px); }
  .gallery-icon { 
    position: absolute; 
    bottom: .6rem; 
    right: .6rem; 
    background: rgba(16,185,129,.85); 
    color: #fff; 
    width: 36px; 
    height: 36px; 
    border-radius: 50%; 
    display: flex; 
    align-items: center; 
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,.2);
    transition: transform .2s ease, background-color .2s ease;
    backdrop-filter: blur(4px);
    cursor: pointer;
    z-index: 5; /* Ensure icon is above other elements */
  }
  .gallery-icon i {
    font-size: 16px;
  }
  .card-media:hover .gallery-icon {
    transform: scale(1.1);
    background: rgba(16,185,129,1);
    box-shadow: 0 2px 12px rgba(16,185,129,.4);
  }
  .gallery-icon:hover {
    transform: scale(1.15) !important;
    background: rgba(16,185,129,1) !important;
    box-shadow: 0 3px 14px rgba(16,185,129,.5) !important;
  }
  .card-body { padding: 1rem; display: flex; flex-direction: column; gap: .5rem; }
  .card-title { font-size: 1.05rem; margin: 0 0 .4rem; line-height: 1.3; color: #111827; }
  .card-meta { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: .5rem; }
  .pill { display: inline-flex; align-items: center; gap: .35rem; font-size: .75rem; padding: .25rem .55rem; border-radius: 999px; background: #f3f4f6; color: #111827; }
  .badge { display: inline-flex; align-items: center; gap: .35rem; font-size: .72rem; padding: .22rem .5rem; border-radius: .4rem; font-weight: 500; }
  .badge-good { background: rgba(16,185,129,.12); color: #047857; }
  .badge-fixable { background: rgba(245,158,11,.15); color: #b45309; }
  .badge-scrap { background: rgba(239,68,68,.15); color: #b91c1c; }
  .badge-weight { background: rgba(59,130,246,.12); color: #1d4ed8; }
  .card-text { color: #4b5563; font-size: .9rem; }
  .empty-state { text-align: center; padding: 3rem 1rem; color: #6b7280; }
  .card-actions { display: flex; gap: .5rem; margin-top: .25rem; }
  .card-actions.centered { justify-content: center; }
  .btn.link { padding: .4rem .6rem; border-radius: .5rem; background: #111827; color: #fff; font-size: .85rem; }
  .btn.link:hover { background: #0f172a; }
  .btn.ghost { padding: .4rem .6rem; border-radius: .5rem; background: #f3f4f6; color: #111827; font-size: .85rem; }
  .btn.ghost:hover { background: #e5e7eb; }
  .pagination-wrap { margin-top: 1rem; }

  /* Lightbox */
  .lightbox { 
    position: fixed; 
    inset: 0; 
    background: rgba(0,0,0,.92); 
    display: none; 
    align-items: center; 
    justify-content: center; 
    z-index: 1050; /* Even higher z-index */
    backdrop-filter: blur(5px);
    transition: opacity 0.3s ease;
    opacity: 0;
    padding: 15px; /* Slightly reduced padding */
  }
  .lightbox.open { 
    display: flex; 
    animation: fadeIn 0.3s ease forwards;
  }
  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  .lightbox-dialog { 
    background: rgba(17, 24, 39, 0.97); 
    color: #fff; 
    max-width: min(850px, 82vw); /* Even more reduced width */
    width: 100%; 
    border-radius: 0.8rem; 
    overflow: hidden; 
    box-shadow: 0 8px 30px rgba(0,0,0,.5);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    transform: translateY(0);
    transition: transform 0.3s ease;
    margin: 1rem auto; /* Less space from top */
    max-height: calc(100vh - 2rem); /* More compact, less space from viewport edges */
    display: flex;
    flex-direction: column;
    position: relative; /* Enable positioning context */
  }
  .lightbox-header { 
    display: flex; 
    align-items: center; 
    justify-content: space-between; 
    padding: 0.7rem 1.1rem; /* Even more reduced padding */
    background: rgba(15, 23, 42, 0.98); 
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    flex-shrink: 0; /* Prevent header from shrinking */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    position: relative;
    z-index: 5;
  }
  .lightbox-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: rgba(255, 255, 255, 0.9);
  }
  .lightbox-header .btn {
    transition: all 0.2s ease;
    font-size: 0.85rem;
    padding: 0.4rem 0.75rem;
  }
  .lightbox-body { 
    padding: 0.8rem 1rem 3rem; /* Less padding at bottom */
    background: rgba(11, 18, 32, 0.8);
    position: relative;
    overflow: hidden;
    overflow-y: auto; /* Allow scrolling if needed */
    flex-grow: 1; /* Allow body to expand */
  }
  
  /* Gallery grid view */
  .gallery-container {
    position: relative;
    padding-top: 0.25rem; /* Reduced top space */
    padding-bottom: 3.5rem; /* Reduced bottom space */
    height: 100%;
    width: 100%;
  }
  .gallery-view-toggle {
    position: absolute;
    top: -40px; /* Better position */
    right: 70px;
    display: flex;
    gap: 0.4rem;
    z-index: 10;
  }
  .gallery-view-toggle button {
    background: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.7);
    border: none;
    width: 28px; /* Even smaller */
    height: 28px; /* Even smaller */
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.75rem; /* Smaller icon */
  }
  .gallery-view-toggle button.active,
  .gallery-view-toggle button:hover {
    background: rgba(16, 185, 129, 0.35);
    color: white;
  }
  
  /* Grid view */
  .gallery { 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); 
    gap: 0.75rem;
    opacity: 1;
    transition: opacity 0.2s ease;
    max-height: 350px;
    overflow-y: auto;
    scrollbar-width: thin;
    padding: 0.25rem;
  }
  .gallery.hidden {
    opacity: 0;
    pointer-events: none;
    position: absolute;
  }
  .gallery-item {
    position: relative;
    cursor: pointer;
    overflow: hidden;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  }
  .gallery-item:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
  }
  .gallery-item.active {
    box-shadow: 0 0 0 3px #10b981, 0 8px 20px rgba(0, 0, 0, 0.3);
  }
  .gallery img { 
    width: 100%; 
    height: 180px; 
    object-fit: cover; 
    border-radius: 0.75rem; 
    transition: transform 0.3s ease;
  }
  .gallery-item:hover img {
    transform: scale(1.05);
  }
  
  /* Slideshow view */
  .slideshow {
    position: relative;
    height: 380px; /* Even more reduced height */
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    position: absolute;
    width: 100%;
    top: 0;
    left: 0;
    transition: opacity 0.3s ease;
    padding: 0.5rem 0;
  }
  .slideshow.visible {
    opacity: 1;
    pointer-events: all;
    position: relative;
  }
  .slideshow-image {
    max-width: 95%;
    max-height: 350px; /* Even more reduced height */
    border-radius: 0.5rem;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
    object-fit: contain; /* Ensures image fits properly */
  }
  .slideshow-nav {
    position: absolute;
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 1rem;
    z-index: 5;
    pointer-events: none; /* Don't block the image click */
  }
  .slideshow-nav button {
    width: 42px; /* Even smaller */
    height: 42px; /* Even smaller */
    border-radius: 50%;
    border: none;
    background: rgba(16, 185, 129, 0.4);
    color: white;
    font-size: 1rem; /* Smaller icon */
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    backdrop-filter: blur(5px);
    pointer-events: auto; /* Make buttons clickable */
    margin: 0 10px; /* Add some margin */
  }
  .slideshow-nav button:hover {
    background: rgba(16, 185, 129, 0.7);
    transform: scale(1.1);
  }
  .slideshow-counter {
    position: absolute;
    bottom: -20px; /* Moved further up */
    left: 50%;
    transform: translateX(-50%);
    background: rgba(16, 185, 129, 0.4);
    padding: 0.3rem 0.8rem;
    border-radius: 999px;
    font-size: 0.85rem;
    backdrop-filter: blur(5px);
    color: white;
  }
  .slideshow-thumbnails {
    position: absolute;
    bottom: -48px; /* Moved even higher */
    left: 0;
    right: 0;
    display: flex;
    gap: 0.4rem;
    overflow-x: auto;
    padding: 0.25rem 0.5rem;
    scroll-behavior: smooth;
    scrollbar-width: thin;
    justify-content: center; /* Center the thumbnails */
    max-width: 90%;
    margin: 0 auto;
  }
  .slideshow-thumbnail {
    width: 50px; /* Even smaller */
    height: 38px; /* Even smaller */
    border-radius: 0.3rem;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
    opacity: 0.6;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    margin-right: 1px;
  }
  .slideshow-thumbnail:hover {
    opacity: 0.9;
    transform: translateY(-2px);
  }
  .slideshow-thumbnail.active {
    opacity: 1;
    box-shadow: 0 0 0 2px #10b981, 0 2px 8px rgba(0, 0, 0, 0.3);
  }
  .slideshow-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  /* Custom scrollbar for thumbnails */
  .slideshow-thumbnails::-webkit-scrollbar {
    height: 6px;
  }
  .slideshow-thumbnails::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 10px;
  }
  .slideshow-thumbnails::-webkit-scrollbar-thumb {
    background: rgba(16, 185, 129, 0.4);
    border-radius: 10px;
  }
  .slideshow-thumbnails::-webkit-scrollbar-thumb:hover {
    background: rgba(16, 185, 129, 0.6);
  }
</style>
@endpush

@section('content')
<main class="dashboard">
  <div class="container">
    <header class="dash-header">
      <div class="dash-hello">
        <span class="hello-badge"><i class="fa-solid fa-boxes-stacked"></i> Collection</span>
        <h1>My Collection</h1>
        <p class="dash-sub">Waste items you've been assigned after bid acceptance.</p>
      </div>
    </header>

    <section class="dash-card wide filter-section">
      <form method="GET" class="filters" action="{{ route('maker.collection') }}" id="filterForm">
        <select name="search_by" aria-label="Search field" class="dynamic-filter">
          <option value="title" @selected(request('search_by', 'title')==='title')>Search by title</option>
          <option value="description" @selected(request('search_by')==='description')>Search by description</option>
          <option value="generator" @selected(request('search_by')==='generator')>Search by generator</option>
        </select>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Enter search term" aria-label="Search term" class="dynamic-filter" />
        <select name="condition" aria-label="Filter by condition" class="dynamic-filter">
          <option value="">All conditions</option>
          @foreach(\App\Models\WasteItem::distinct()->pluck('condition')->filter() as $c)
            <option value="{{ $c }}" @selected(request('condition')===$c)>{{ ucfirst($c) }}</option>
          @endforeach
        </select>
        <button type="button" id="resetBtn" class="btn secondary">
          <i class="fa-solid fa-rotate"></i> Reset
        </button>
      </form>
    </section>

    <!-- Added spacing between filter and cards -->
    <div class="mb-4"></div>
    
    <section class="grid-cards">
      @forelse($items as $item)
      <article class="card" aria-label="Waste item {{ $item->title }}">
        <div class="card-media" data-gallery data-item="{{ $item->id }}">
          <img src="{{ $item->primary_image_url }}" alt="{{ $item->title }}" />
          <span class="card-badge"><i class="fa-solid fa-user-check"></i> Assigned</span>
          <span class="gallery-icon" title="View all photos"><i class="fa-solid fa-images"></i></span>
        </div>
        <div class="card-body">
          <h3 class="card-title">{{ $item->title }}</h3>
          <div class="card-meta">
            @php
              $condClass = match($item->condition){
                'good' => 'badge-good', 'fixable' => 'badge-fixable', 'scrap' => 'badge-scrap', default => 'badge-good'
              };
            @endphp
            <span class="badge {{ $condClass }}"><i class="fa-solid fa-screwdriver-wrench"></i> {{ ucfirst($item->condition) }}</span>
            @if($item->estimated_weight)
              <span class="badge badge-weight"><i class="fa-solid fa-weight-hanging"></i> ~ {{ number_format($item->estimated_weight,2) }} kg</span>
            @endif
          </div>
          <p class="card-text"><i class="fa-solid fa-industry"></i> From: {{ $item->generator?->name ?? 'Unknown' }}</p>
          <div class="card-actions centered">
            <a class="btn link" href="{{ route('materials.create', ['from' => $item->id]) }}"><i class="fa-solid fa-hammer"></i> Create Material</a>
          </div>
        </div>
      </article>
      @empty
      <div class="empty-state">
        <h3>No items yet</h3>
        <p>When your bids are accepted, those waste items will show up here.</p>
      </div>
      @endforelse
    </section>

    <div class="pagination-wrap">
      {{ $items->links() }}
    </div>
  </div>
  </main>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Dynamic filtering
  const filterForm = document.getElementById('filterForm');
  const dynamicFilters = document.querySelectorAll('.dynamic-filter');
  const resetBtn = document.getElementById('resetBtn');
  
  // Add debounce function to prevent excessive form submissions
  function debounce(func, delay = 400) {
    let timer;
    return function(...args) {
      clearTimeout(timer);
      timer = setTimeout(() => {
        func.apply(this, args);
      }, delay);
    };
  }
  
  // Submit form when any filter changes
  const submitForm = debounce(() => {
    filterForm.classList.add('loading');
    filterForm.submit();
  }, 500);
  
  // Add change event listeners to all dynamic filters
  dynamicFilters.forEach(filter => {
    filter.addEventListener('change', submitForm);
    
    // For text input, also listen for keyup events with debounce
    if (filter.tagName === 'INPUT') {
      filter.addEventListener('keyup', submitForm);
    }
  });
  
  // Reset button redirects to the page without query parameters
  resetBtn.addEventListener('click', () => {
    window.location.href = '{{ route('maker.collection') }}';
  });
  
  // Gallery lightbox
  const modal = createLightbox();
  document.body.appendChild(modal.root);
  
  // Function to open gallery modal
  const openGallery = async (itemId, element) => {
    try {
      if (element) element.style.opacity = '0.7'; // Visual feedback that click was registered
      
      const res = await fetch(`{{ url('/maker/collection') }}/${itemId}/images`, { 
        headers: { 'Accept': 'application/json' }, 
        credentials: 'include' 
      });
      
      if (!res.ok) throw new Error('Failed to load images');
      
      const data = await res.json();
      const imgs = data.data?.images ?? [];
      modal.open(imgs.map(i => i.url));
      
      if (element) element.style.opacity = ''; // Reset opacity
    } catch (e) {
      console.error('Gallery load failed', e);
      if (element) element.style.opacity = ''; // Reset opacity on error too
    }
  };
  
  // Add click event to card-media elements
  document.querySelectorAll('.card-media[data-gallery]').forEach(mediaEl => {
    mediaEl.addEventListener('click', (e) => {
      const itemId = mediaEl.getAttribute('data-item');
      openGallery(itemId, mediaEl);
    });
  });
  
  // Add specific click handler for the gallery icon to prevent event bubbling issues
  document.querySelectorAll('.gallery-icon').forEach(icon => {
    icon.addEventListener('click', (e) => {
      e.stopPropagation(); // Stop event from bubbling up
      const mediaEl = icon.closest('.card-media');
      const itemId = mediaEl.getAttribute('data-item');
      openGallery(itemId, mediaEl);
    });
  });

  function createLightbox(){
    const root = document.createElement('div');
    root.className = 'lightbox';
    root.innerHTML = `
      <div class="lightbox-dialog" role="dialog" aria-modal="true" aria-label="Photos">
        <div class="lightbox-header">
          <h3><i class="fa-solid fa-images"></i> <span class="modal-title">Item Photos</span></h3>
          <div class="gallery-view-toggle">
            <button type="button" class="grid-view active" title="Grid View"><i class="fa-solid fa-table-cells"></i></button>
            <button type="button" class="slideshow-view" title="Slideshow"><i class="fa-solid fa-image"></i></button>
          </div>
          <button type="button" class="btn secondary" data-close><i class="fa-solid fa-xmark"></i> Close</button>
        </div>
        <div class="lightbox-body">
          <div class="gallery-container">
            <div class="gallery"></div>
            <div class="slideshow">
              <div class="slideshow-nav">
                <button class="prev-btn" title="Previous Image"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="next-btn" title="Next Image"><i class="fa-solid fa-chevron-right"></i></button>
              </div>
              <img class="slideshow-image" src="" alt="Waste item photo" />
              <div class="slideshow-counter">1 of 3</div>
              <div class="slideshow-thumbnails"></div>
            </div>
          </div>
        </div>
      </div>
    `;
    
    const gallery = root.querySelector('.gallery');
    const slideshow = root.querySelector('.slideshow');
    const slideshowImage = root.querySelector('.slideshow-image');
    const slideshowCounter = root.querySelector('.slideshow-counter');
    const slideshowThumbnails = root.querySelector('.slideshow-thumbnails');
    const prevBtn = root.querySelector('.prev-btn');
    const nextBtn = root.querySelector('.next-btn');
    const gridViewBtn = root.querySelector('.grid-view');
    const slideshowViewBtn = root.querySelector('.slideshow-view');
    const closeBtn = root.querySelector('[data-close]');
    
    let urls = [];
    let currentIndex = 0;
    
    // View toggle handlers
    gridViewBtn.addEventListener('click', () => {
      gridViewBtn.classList.add('active');
      slideshowViewBtn.classList.remove('active');
      gallery.classList.remove('hidden');
      slideshow.classList.remove('visible');
    });
    
    slideshowViewBtn.addEventListener('click', () => {
      slideshowViewBtn.classList.add('active');
      gridViewBtn.classList.remove('active');
      gallery.classList.add('hidden');
      slideshow.classList.add('visible');
    });
    
    // Slideshow navigation
    function updateSlideshow() {
      if (urls.length === 0) return;
      
      slideshowImage.src = urls[currentIndex];
      slideshowCounter.textContent = `${currentIndex + 1} of ${urls.length}`;
      
      // Update thumbnails active state
      const thumbnails = slideshowThumbnails.querySelectorAll('.slideshow-thumbnail');
      thumbnails.forEach((thumb, i) => {
        if (i === currentIndex) {
          thumb.classList.add('active');
          // Scroll into view if needed
          thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        } else {
          thumb.classList.remove('active');
        }
      });
      
      // Update grid view active state
      const galleryItems = gallery.querySelectorAll('.gallery-item');
      galleryItems.forEach((item, i) => {
        item.classList.toggle('active', i === currentIndex);
      });
    }
    
    prevBtn.addEventListener('click', () => {
      currentIndex = (currentIndex - 1 + urls.length) % urls.length;
      updateSlideshow();
    });
    
    nextBtn.addEventListener('click', () => {
      currentIndex = (currentIndex + 1) % urls.length;
      updateSlideshow();
    });
    
    // Keyboard navigation
    root.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') {
        prevBtn.click();
      } else if (e.key === 'ArrowRight') {
        nextBtn.click();
      } else if (e.key === 'Escape') {
        closeBtn.click();
      }
    });
    
    // Close modal
    closeBtn.addEventListener('click', () => {
      root.classList.remove('open');
      setTimeout(() => {
        slideshowImage.src = '';
      }, 300);
    });
    
    root.addEventListener('click', (e) => { 
      if (e.target === root) closeBtn.click();
    });
    
    // Handle slideshow swipe for touch devices
    let touchStartX = 0;
    let touchEndX = 0;
    
    slideshowImage.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    
    slideshowImage.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].screenX;
      handleSwipe();
    }, { passive: true });
    
    function handleSwipe() {
      const swipeThreshold = 50;
      if (touchEndX < touchStartX - swipeThreshold) {
        // Swipe left, go to next
        nextBtn.click();
      } else if (touchEndX > touchStartX + swipeThreshold) {
        // Swipe right, go to previous
        prevBtn.click();
      }
    }
    
    return {
      root,
      open(imageUrls) {
        urls = imageUrls;
        currentIndex = 0;
        
        // Reset views
        gridViewBtn.classList.add('active');
        slideshowViewBtn.classList.remove('active');
        gallery.classList.remove('hidden');
        slideshow.classList.remove('visible');
        
        // Populate grid view
        gallery.innerHTML = '';
        urls.forEach((url, index) => {
          const item = document.createElement('div');
          item.className = 'gallery-item';
          item.dataset.index = index;
          
          const img = document.createElement('img');
          img.src = url;
          img.alt = `Image ${index + 1}`;
          
          item.appendChild(img);
          gallery.appendChild(item);
          
          // Click handler for grid items
          item.addEventListener('click', () => {
            currentIndex = index;
            slideshowViewBtn.click();
            updateSlideshow();
          });
        });
        
        // Populate thumbnails for slideshow
        slideshowThumbnails.innerHTML = '';
        urls.forEach((url, index) => {
          const thumb = document.createElement('div');
          thumb.className = 'slideshow-thumbnail';
          if (index === currentIndex) thumb.classList.add('active');
          
          const img = document.createElement('img');
          img.src = url;
          img.alt = `Thumbnail ${index + 1}`;
          
          thumb.appendChild(img);
          slideshowThumbnails.appendChild(thumb);
          
          // Click handler for thumbnails
          thumb.addEventListener('click', () => {
            currentIndex = index;
            updateSlideshow();
          });
        });
        
        updateSlideshow();
        root.classList.add('open');
        root.focus(); // Enable keyboard navigation
      }
    }
  }
});
</script>
@endpush
@endsection
