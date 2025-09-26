@extends('layouts.app')

@push('head')
@vite(['resources/css/materials.css'])
@endpush

@section('content')
<div class="materials-container">
    <div class="container">
        <div class="materials-header">
            <div>
                <h1 class="materials-title">
                    <i class="fa-solid fa-trash-can"></i> My Waste Items
                </h1>
                <p>Manage waste items you've generated</p>
            </div>
            <a href="{{ route('generator.waste-items.create') }}" class="btn-create">
                <i class="fa-solid fa-plus"></i> New Waste Item
            </a>
        </div>

        <div class="filters-section">
            <form action="{{ route('generator.waste-items.index') }}" method="GET" id="filterForm">
                <div class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label" for="search">Search</label>
                        <input type="text" name="search" id="search" class="filter-input" placeholder="Search waste items..." value="{{ request('search') }}">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="condition">Condition</label>
                        <select name="condition" id="condition" class="filter-select">
                            <option value="">All</option>
                            @foreach(['good','fixable','scrap'] as $c)
                                <option value="{{ $c }}" {{ request('condition') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="sort">Sort By</label>
                        <select name="sort" id="sort" class="filter-select">
                            <option value="newest" {{ request('sort')=='newest' ? 'selected' : '' }}>Newest</option>
                            <option value="oldest" {{ request('sort')=='oldest' ? 'selected' : '' }}>Oldest</option>
                            <option value="title_asc" {{ request('sort')=='title_asc' ? 'selected' : '' }}>Title A-Z</option>
                            <option value="title_desc" {{ request('sort')=='title_desc' ? 'selected' : '' }}>Title Z-A</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-filter">
                            <i class="fa-solid fa-filter"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3 class="stat-number">{{ $total }}</h3>
                <p class="stat-label">Total Items</p>
            </div>
            <div class="stat-card">
                <h3 class="stat-number">{{ number_format($avgWeight,2) }}</h3>
                <p class="stat-label">Avg Weight</p>
            </div>
            <div class="stat-card">
                <h3 class="stat-number">{{ $conditionsCount->count() }}</h3>
                <p class="stat-label">Conditions</p>
            </div>
        </div>

        <div class="materials-grid">
            @forelse($wasteItems as $item)
                <div class="material-card">
                    <div class="material-content" style="padding-top:0.8rem;">
                        <div class="material-header">
                            <h3 class="material-name">{{ $item->title }}</h3>
                            <span class="material-badge">{{ ucfirst($item->condition) }}</span>
                        </div>
                        <div class="material-meta">
                            <div class="meta-item">
                                <i class="fa-solid fa-weight-hanging meta-icon"></i>
                                <span>{{ $item->estimated_weight ?? '—' }} kg</span>
                            </div>
                            <div class="meta-item">
                                <i class="fa-solid fa-calendar meta-icon"></i>
                                <span>{{ $item->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        <p class="material-description">{{ Str::limit($item->notes, 100) }}</p>
                        <div class="material-actions">
                            <a href="#" class="btn-action btn-view"><i class="fa-solid fa-eye"></i> View</a>
                            <a href="#" class="btn-action btn-edit"><i class="fa-solid fa-edit"></i> Edit</a>
                            <form action="#" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete" onclick="return confirm('Delete this waste item?')"><i class="fa-solid fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon"><i class="fa-solid fa-trash"></i></div>
                    <h3 class="empty-text">No waste items found</h3>
                    <p>Create your first waste item.</p>
                    <a href="#" class="btn-create" style="display:inline-flex;margin-top:1rem;"><i class="fa-solid fa-plus"></i> Create Waste Item</a>
                </div>
            @endforelse
        </div>

        @if($wasteItems->hasPages())
            <div class="pagination">
                @if($wasteItems->onFirstPage())
                    <span class="page-link disabled">&laquo; Previous</span>
                @else
                    <a href="{{ $wasteItems->previousPageUrl() }}" class="page-link">&laquo; Previous</a>
                @endif
                @foreach($wasteItems->getUrlRange(1, $wasteItems->lastPage()) as $page => $url)
                    @if($page == $wasteItems->currentPage())
                        <span class="page-link active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                    @endif
                @endforeach
                @if($wasteItems->hasMorePages())
                    <a href="{{ $wasteItems->nextPageUrl() }}" class="page-link">Next &raquo;</a>
                @else
                    <span class="page-link disabled">Next &raquo;</span>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
