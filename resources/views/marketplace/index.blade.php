@extends('layouts.app')

@push('head')
@vite([
    'resources/css/materials.css',
    'resources/css/waste-items.css',
    'resources/css/marketplace-lightbox.css',
    'resources/js/waste-items-filters.js',
    'resources/js/marketplace.js'
])
@endpush

@section('content')
<div class="materials-container">
    <div class="container">
        <div class="materials-header">
            <div>
                <h1 class="materials-title">
                    <i class="fa-solid fa-store"></i> Marketplace
                </h1>
                <p>Browse available waste items</p>
            </div>
        </div>
        <div class="filters-section modern">
            <form action="{{ route('marketplace.index') }}" method="GET" id="filterForm" class="filter-toolbar" novalidate data-ajax="true">
                <div class="ft-row">
                    <div class="ft-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" id="search" placeholder="Search waste items..." value="{{ request('search') }}" autocomplete="off" />
                        @if(request('search'))
                            <button type="button" class="clear-search" aria-label="Clear search" title="Clear search">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        @endif
                    </div>
                    <div class="ft-select">
                        <label for="condition" class="sr-only">Filter by Condition</label>
                        <i class="fa-solid fa-filter-circle-xmark"></i>
                        <select name="condition" id="condition" class="modern-select">
                            <option value="">All Conditions</option>
                            <option value="good" {{ request('condition') === 'good' ? 'selected' : '' }} data-icon="check-circle">
                                Good
                            </option>
                            <option value="fixable" {{ request('condition') === 'fixable' ? 'selected' : '' }} data-icon="wrench">
                                Fixable
                            </option>
                            <option value="scrap" {{ request('condition') === 'scrap' ? 'selected' : '' }} data-icon="recycle">
                                Scrap
                            </option>
                        </select>
                    </div>
                    <div class="ft-select">
                        <label for="sort" class="sr-only">Sort Items</label>
                        <i class="fa-solid fa-arrow-up-wide-short"></i>
                        <select name="sort" id="sort" class="modern-select">
                            <option value="newest" {{ request('sort')=='newest' ? 'selected' : '' }} data-icon="clock">
                                Newest First
                            </option>
                            <option value="oldest" {{ request('sort')=='oldest' ? 'selected' : '' }} data-icon="clock-rotate-left">
                                Oldest First
                            </option>
                            <option value="title_asc" {{ request('sort')=='title_asc' ? 'selected' : '' }} data-icon="arrow-down-a-z">
                                Title A-Z
                            </option>
                            <option value="title_desc" {{ request('sort')=='title_desc' ? 'selected' : '' }} data-icon="arrow-down-z-a">
                                Title Z-A
                            </option>
                        </select>
                    </div>
                    <div class="ft-actions">
                        <button type="reset" id="filtersReset" class="ft-btn ghost" title="Reset all filters">
                            <i class="fa-solid fa-rotate"></i>
                            <span class="txt">Reset Filters</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @include('marketplace.partials.grid')
        @include('marketplace.partials.pagination')
        @push('modals')
            <div class="modal-overlay" id="marketplaceModalOverlay" aria-hidden="true">
                @include('marketplace.partials.photos_lightbox')
            </div>
            <div class="modal-overlay" id="bidModalOverlay" aria-hidden="true">
                @include('marketplace.partials.bid_modal')
            </div>
        @endpush
    </div>
</div>
@endsection
