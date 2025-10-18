@extends('layouts.app')

@push('head')
@vite(['resources/css/buyerMarketplace.css'])
@endpush

@section('content')
<div class="marketplace-container">
    <div class="container">
        <div class="marketplace-header">
            <div>
                <h1 class="marketplace-title">
                    <i class="fa-solid fa-store"></i> Sustainable Marketplace
                </h1>
                <p>Discover eco-friendly {{ $type === 'products' ? 'products' : 'raw materials' }} made from recycled resources</p>
            </div>
            <div class="header-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ number_format($stats['total_items']) }}</span>
                    <span class="stat-label">Total {{ $type === 'products' ? 'Products' : 'Materials' }}</span>
                </div>
                @if($type === 'products')
                <div class="stat-item">
                    <span class="stat-number">1</span>
                    <span class="stat-label">Makers</span>
                </div>
                @else
                <div class="stat-item">
                    <span class="stat-number">{{ number_format($stats['average_score'], 1) }}%</span>
                    <span class="stat-label">Avg Recyclability</span>
                </div>
                @endif
                <div class="stat-item">
                    <span class="stat-number">{{ $stats['categories_count'] }}</span>
                    <span class="stat-label">Categories</span>
                </div>
            </div>
        </div>

<div class="marketplace-tabs">
    <a href="{{ route('buyer.marketplace.index', ['type' => 'products'] + request()->except('type')) }}" 
       class="tab-item {{ $type === 'products' ? 'active' : '' }}">
        <i class="fa-solid fa-cube"></i> Finished Products
    </a>
    <a href="{{ route('buyer.marketplace.index', ['type' => 'materials'] + request()->except('type')) }}" 
       class="tab-item {{ $type === 'materials' ? 'active' : '' }}">
        <i class="fa-solid fa-cubes"></i> Raw Materials
    </a>
</div>

        <div class="filters-section">
           <form action="{{ route('buyer.marketplace.index') }}" method="GET" id="filterForm">
                <input type="hidden" name="type" value="{{ $type }}">
                
                <div class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label" for="search">
                            Search {{ $type === 'products' ? 'Products' : 'Materials' }}
                        </label>
                        <input type="text" 
                               name="search" 
                               id="search" 
                               class="filter-input" 
                               placeholder="Search {{ $type === 'products' ? 'products...' : 'materials...' }}"
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="category">Material Category</label>
                        <select name="category" id="category" class="filter-select">
                            <option value="">All Categories</option>
                            @foreach(\App\Models\Material::CATEGORIES as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                    {{ ucfirst($category) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($type === 'products')
                    <div class="filter-group">
                        <label class="filter-label" for="min_price">Min Price ($)</label>
                        <input type="number" 
                               name="min_price" 
                               id="min_price" 
                               class="filter-input" 
                               placeholder="Min"
                               value="{{ request('min_price') }}">
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="max_price">Max Price ($)</label>
                        <input type="number" 
                               name="max_price" 
                               id="max_price" 
                               class="filter-input" 
                               placeholder="Max"
                               value="{{ request('max_price') }}">
                    </div>
                    @else
                    <div class="filter-group">
                        <label class="filter-label" for="min_score">Min Recyclability</label>
                        <select name="min_score" id="min_score" class="filter-select">
                            <option value="">Any Score</option>
                            <option value="80" {{ request('min_score') == '80' ? 'selected' : '' }}>80%+ (Excellent)</option>
                            <option value="60" {{ request('min_score') == '60' ? 'selected' : '' }}>60%+ (Good)</option>
                            <option value="40" {{ request('min_score') == '40' ? 'selected' : '' }}>40%+ (Fair)</option>
                        </select>
                    </div>
                    @endif
                    
                    <div class="filter-group">
                        <label class="filter-label" for="sort">Sort By</label>
                        <select name="sort" id="sort" class="filter-select">
                            @if($type === 'products')
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                                <option value="featured" {{ request('sort') == 'featured' ? 'selected' : '' }}>Featured</option>
                            @else
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="score_high" {{ request('sort') == 'score_high' ? 'selected' : '' }}>Highest Recyclability</option>
                                <option value="score_low" {{ request('sort') == 'score_low' ? 'selected' : '' }}>Lowest Recyclability</option>
                                <option value="quantity_high" {{ request('sort') == 'quantity_high' ? 'selected' : '' }}>Highest Quantity</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                            @endif
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn-filter">
                            <i class="fa-solid fa-filter"></i> Apply
                        </button>
                       <a href="{{ route('buyer.marketplace.index', ['type' => $type]) }}" class="btn-clear" style="display: inline-flex; margin-top: 1rem;">
                            <i class="fa-solid fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

<div class="items-grid">
    @forelse($items as $item)
        @if($type === 'products')
            {{-- THIS IS WHERE YOU INCLUDE THE PRODUCT CARD --}}
            @include('marketplace.partials.product-card', ['product' => $item])
        @else
            {{-- AND THIS IS FOR MATERIALS --}}
            @include('marketplace.partials.material-card', ['material' => $item])
        @endif
    @empty
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fa-solid fa-{{ $type === 'products' ? 'cube' : 'cubes' }}"></i>
            </div>
            <h3 class="empty-text">No {{ $type === 'products' ? 'products' : 'materials' }} found</h3>
            <p>Try adjusting your search filters</p>
            <a href="{{ route('buyer.marketplace.index', ['type' => $type]) }}" class="btn-clear" style="display: inline-flex; margin-top: 1rem;">
                <i class="fa-solid fa-times"></i> Clear Filters
            </a>
        </div>
    @endforelse
</div>

        @if($items->hasPages())
            <div class="pagination">
                {{ $items->appends(request()->except('page'))->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    const materialImagePositions = {};

function nextMaterialImage(materialId, totalImages) {
    if (!materialImagePositions[materialId]) {
        materialImagePositions[materialId] = 0;
    }
    
    materialImagePositions[materialId] = (materialImagePositions[materialId] + 1) % totalImages;
    updateMaterialImage(materialId, totalImages);
}

function prevMaterialImage(materialId, totalImages) {
    if (!materialImagePositions[materialId]) {
        materialImagePositions[materialId] = 0;
    }
    
    materialImagePositions[materialId] = (materialImagePositions[materialId] - 1 + totalImages) % totalImages;
    updateMaterialImage(materialId, totalImages);
}

function updateMaterialImage(materialId, totalImages) {
    const position = materialImagePositions[materialId] || 0;
    const imageElement = document.getElementById(`current-image-${materialId}`);
    const positionElement = document.getElementById(`position-${materialId}`);
    
    if (imageElement && window.materialImagesData && window.materialImagesData[materialId]) {
        const materialImages = window.materialImagesData[materialId];
        if (materialImages[position]) {
            imageElement.src = materialImages[position].path;
        }
    }
    
    if (positionElement) {
        positionElement.textContent = `${position + 1}/${totalImages}`;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    window.materialImagesData = {};
    
    @foreach($items as $item)
        @if($type === 'materials' && $item->images->count() > 0)
            window.materialImagesData[{{ $item->id }}] = [
                @foreach($item->images as $image)
                {
                    path: "{{ asset($image->image_path) }}",
                    id: {{ $image->id }},
                    order: {{ $image->order }}
                },
                @endforeach
            ];
        @endif
    @endforeach
});
document.addEventListener('DOMContentLoaded', function() {
    const autoSubmitElements = ['sort', 'category', 'min_score'];
    
    autoSubmitElements.forEach(elementId => {
        const element = document.getElementById(elementId);
        if (element) {
            element.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
    });
});

function addToCart(itemId, type) {
    if (type === 'product') {
        console.log('Add product to cart:', itemId);
    } else {
        console.log('Purchase material:', itemId);
    }
}


</script>
@endpush