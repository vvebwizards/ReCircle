<!-- resources/views/marketplace.blade.php -->
@extends('layouts.app')

@push('head')
@vite([
    'resources/css/materials.css',
    'resources/css/material-create.css',
    'resources/css/waste-items.css',
    'resources/css/market-place.css',
    'resources/js/waste-item-create.js',
    'resources/js/marketplace.js'
])
@endpush

@section('content')
<main class="marketplace">
    <div class="container">
        <header class="dash-header">
            <div class="dash-hello">
                <h1>Marketplace</h1>
                <p class="dash-sub">Discover upcycled goods and sustainable materials.</p>
            </div>
        </header>

        <div class="filters-section modern">
            <form action="{{ route('generator.waste-items.index') }}" method="GET" id="filterForm" class="filter-toolbar" novalidate>
                <div class="ft-row">
                    <div class="ft-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" id="search" placeholder="Search waste items..." value="{{ request('search') }}" autocomplete="off" />
                        @if(request('search'))
                            <button type="button" class="clear-search" aria-label="Clear search" title="Clear search">&times;</button>
                        @endif
                    </div>
                    <div class="ft-select">
                        <label for="condition" class="sr-only">Condition</label>
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        <select name="condition" id="condition">
                            <option value="">Condition: All</option>
                            @foreach(['good','fixable','scrap'] as $c)
                                <option value="{{ $c }}" {{ request('condition') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ft-select">
                        <label for="sort" class="sr-only">Sort</label>
                        <i class="fa-solid fa-arrow-down-wide-short"></i>
                        <select name="sort" id="sort">
                            <option value="newest" {{ request('sort')=='newest' ? 'selected' : '' }}>Newest</option>
                            <option value="oldest" {{ request('sort')=='oldest' ? 'selected' : '' }}>Oldest</option>
                            <option value="title_asc" {{ request('sort')=='title_asc' ? 'selected' : '' }}>Title A-Z</option>
                            <option value="title_desc" {{ request('sort')=='title_desc' ? 'selected' : '' }}>Title Z-A</option>
                        </select>
                    </div>
                    <div class="ft-actions">
                        <button type="reset" id="filtersReset" class="ft-btn ghost" title="Reset filters" aria-label="Reset filters"><i class="fa-solid fa-rotate"></i><span class="txt">Reset</span></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="marketplace-stack">
            <!-- Sample Listing Card 1 -->
            <section class="dash-card" id="listing-card-1">
                <div class="card-stack">
                    <div class="card-icon"><i class="fa-solid fa-box"></i></div>
                    <h3 class="card-title">Recycled Plastic Pellets</h3>
                    <p class="card-desc">High-quality recycled HDPE pellets for manufacturing.</p>
                    <div class="card-actions" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
                        <button class="btn btn-primary" data-listing-id="1" data-modal-open="order-modal"><i class="fa-solid fa-cart-shopping"></i> Order</button>
                        <span class="chip"><i class="fa-solid fa-tag"></i> $5/kg</span>
                    </div>
                </div>
            </section>

            <!-- Sample Listing Card 2 -->
            <section class="dash-card" id="listing-card-2">
                <div class="card-stack">
                    <div class="card-icon"><i class="fa-solid fa-gear"></i></div>
                    <h3 class="card-title">Upcycled Wood Scraps</h3>
                    <p class="card-desc">Perfect for crafting or small-scale construction.</p>
                    <div class="card-actions" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
                        <button class="btn btn-primary" data-listing-id="2" data-modal-open="order-modal"><i class="fa-solid fa-cart-shopping"></i> Order</button>
                        <span class="chip"><i class="fa-solid fa-tag"></i> $10/kg</span>
                    </div>
                </div>
            </section>

            <!-- Sample Listing Card 3 -->
            <section class="dash-card" id="listing-card-3">
                <div class="card-stack">
                    <div class="card-icon"><i class="fa-solid fa-recycle"></i></div>
                    <h3 class="card-title">Textile Waste</h3>
                    <p class="card-desc">Assorted fabric scraps for creative reuse.</p>
                    <div class="card-actions" style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
                        <button class="btn btn-primary" data-listing-id="3" data-modal-open="order-modal"><i class="fa-solid fa-cart-shopping"></i> Order</button>
                        <span class="chip"><i class="fa-solid fa-tag"></i> $3/kg</span>
                    </div>
                </div>
            </section>
            <!-- Additional listing cards can be dynamically added here -->
        </div>

        <!-- Order Modal -->
        <div id="order-modal" class="modal-overlay hidden" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="order-modal-title">
            <div class="modal" role="document">
                <div class="modal-header">
                    <h3 id="order-modal-title"><i class="fa-solid fa-cart-shopping"></i> Place Order</h3>
                    <button class="modal-close" aria-label="Close" data-modal-close>&times;</button>
                </div>
                <div class="modal-body">
                    <div class="order-content">
                        <h4 id="order-title">Loading...</h4>
                        <p id="order-description" class="text-sm">Description will load here.</p>
                        <div class="order-details">
                            <p><strong>Price per kg:</strong> <span id="order-price"></span></p>
                            <p><strong>Available Quantity:</strong> <span id="order-quantity"></span></p>
                            <p><strong>Location:</strong> <span id="order-location"></span></p>
                        </div>
                        <div class="order-form">
                            <label for="order-amount" class="block text-sm font-medium">Quantity (kg):</label>
                            <input type="number" id="order-amount" name="quantity" min="1" class="mt-1 block w-full border rounded-md p-2" placeholder="Enter quantity in kg" required>
                        </div>
                        <div class="order-actions mt-4">
                            <button class="btn btn-primary" id="order-confirm"><i class="fa-solid fa-check"></i> Confirm</button>
                            <button class="btn btn-secondary" data-modal-close><i class="fa-solid fa-times"></i> Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection