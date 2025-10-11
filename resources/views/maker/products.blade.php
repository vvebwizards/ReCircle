@extends('layouts.app')

@push('head')
@vite(['resources/css/products.css'])
@endpush

@section('content')
<div class="products-container">
    <div class="container">
        <div class="products-header">
            <div>
                <h1 class="products-title">
                    <i class="fa-solid fa-cubes"></i> My Products
                </h1>
                <p>Manage your upcycled and repaired products</p>
            </div>
            <a href="{{ route('maker.products.create') }}" class="btn-create">
                <i class="fa-solid fa-plus"></i> New Product
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fa-solid fa-cube"></i>
                </div>
                <div class="stat-info">
                    <h3 class="stat-number">{{ $stats['total'] }}</h3>
                    <p class="stat-label">Total Products</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon published">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div class="stat-info">
                    <h3 class="stat-number">{{ $stats['published'] }}</h3>
                    <p class="stat-label">Published</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon draft">
                    <i class="fa-solid fa-pencil"></i>
                </div>
                <div class="stat-info">
                    <h3 class="stat-number">{{ $stats['draft'] }}</h3>
                    <p class="stat-label">Drafts</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon sold-out">
                    <i class="fa-solid fa-tag"></i>
                </div>
                <div class="stat-info">
                    <h3 class="stat-number">{{ $stats['sold_out'] }}</h3>
                    <p class="stat-label">Sold Out</p>
                </div>
            </div>
        </div>

        <div class="filters-section">
            <form action="{{ route('maker.products') }}" method="GET" id="filterForm">
                <div class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label" for="search">Search</label>
                        <input type="text" 
                               name="search" 
                               id="search" 
                               class="filter-input" 
                               placeholder="Search products..."
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="status">Status</label>
                        <select name="status" id="status" class="filter-select">
                            <option value="">All Statuses</option>
                            @foreach(\App\Enums\ProductStatus::cases() as $status)
                                <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
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
                    
                    <div class="filter-group">
                        <label class="filter-label" for="sort">Sort By</label>
                        <select name="sort" id="sort" class="filter-select">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Highest Price</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Lowest Price</option>
                            <option value="stock_high" {{ request('sort') == 'stock_high' ? 'selected' : '' }}>Highest Stock</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn-filter">
                            <i class="fa-solid fa-filter"></i> Apply Filters
                        </button>
                        <a href="{{ route('maker.products') }}" class="btn-clear">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="products-grid">
            @forelse($products as $product)
                <div class="product-card">
                    <div class="product-image-container">
                        @if($product->images->count() > 0)
                            <div class="image-wrapper">
                                <img src="{{ asset($product->images->first()->image_path) }}" 
                                     alt="{{ $product->name }}" 
                                     class="product-image"
                                     id="current-product-image-{{ $product->id }}">
                                
                                @if($product->images->count() > 1)
                                    <button type="button" 
                                            class="image-nav-btn prev-btn"
                                            onclick="prevProductImage({{ $product->id }}, {{ $product->images->count() }})">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </button>
                                    <button type="button" 
                                            class="image-nav-btn next-btn"
                                            onclick="nextProductImage({{ $product->id }}, {{ $product->images->count() }})">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </button>
                                    <div class="image-counter" id="product-position-{{ $product->id }}">
                                        1/{{ $product->images->count() }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="product-image-placeholder">
                                <i class="fa-solid fa-cube"></i>
                            </div>
                        @endif
                        
                        <div class="status-badge status-{{ $product->status->value }}">
                            {{ $product->status->name }}
                        </div>
                        
                        @if($product->is_featured)
                            <div class="featured-badge">
                                <i class="fa-solid fa-star"></i> Featured
                            </div>
                        @endif
                    </div>
                    
                    <div class="product-content">
                        <div class="product-header">
                            <h3 class="product-name">{{ $product->name }}</h3>
                            <span class="price-tag">{{ $product->price }} £</span>
                        </div>
                        
                        @if($product->material)
                            <div class="material-info">
                                <span class="material-badge">{{ ucfirst($product->material->category) }}</span>
                                <span class="material-name">{{ Str::limit($product->material->name, 20) }}</span>
                            </div>
                        @endif
                        
                        <p class="product-description">{{ Str::limit($product->description, 100) }}</p>
                        
                        <div class="product-meta">
                            <div class="meta-item">
                                <i class="fa-solid fa-box"></i>
                                <span>{{ $product->stock }} in stock</span>
                            </div>
                            <div class="meta-item">
                                <i class="fa-solid fa-calendar"></i>
                                <span>{{ $product->created_at->format('M d, Y') }}</span>
                            </div>
                            @if($product->sku)
                            <div class="meta-item">
                                <i class="fa-solid fa-barcode"></i>
                                <span>{{ $product->sku }}</span>
                            </div>
                            @endif
                        </div>
                        
                        <div class="stock-indicator">
                            <div class="stock-bar">
                                <div class="stock-fill 
                                    @if($product->stock_status === 'out_of_stock') out-of-stock
                                    @elseif($product->stock_status === 'low_stock') low-stock
                                    @else in-stock
                                    @endif"
                                    style="width: {{ min(100, ($product->stock / 50) * 100) }}%">
                                </div>
                            </div>
                            <div class="stock-text">
                                @if($product->stock_status === 'out_of_stock')
                                    <i class="fa-solid fa-times"></i> Out of Stock
                                @elseif($product->stock_status === 'low_stock')
                                    <i class="fa-solid fa-exclamation-triangle"></i> Low Stock
                                @else
                                    <i class="fa-solid fa-check"></i> In Stock
                                @endif
                            </div>
                        </div>
                        
                        <div class="product-actions">
                            <a href="{{ route('maker.products.show', $product->id) }}" class="btn-action btn-view">
                                <i class="fa-solid fa-eye"></i> View
                            </a>
                            <a href="{{ route('maker.products.edit', $product->id) }}" class="btn-action btn-edit">
                                <i class="fa-solid fa-edit"></i> Edit
                            </a>
                            
                            @if($product->status === \App\Enums\ProductStatus::DRAFT)
                                <form action="{{ route('maker.products.publish', $product->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn-action btn-publish">
                                        <i class="fa-solid fa-upload"></i> Publish
                                    </button>
                                </form>
                            @endif
                            
                            <form action="{{ route('maker.products.destroy', $product->id) }}" 
                                method="POST" 
                                class="delete-product-form">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="btn-action btn-delete delete-product-btn"
                                        data-action="delete"
                                        data-product-name="{{ $product->name }}"
                                        data-product-id="{{ $product->id }}"
                                        title="Delete product">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fa-solid fa-cube"></i>
                    </div>
                    <h3 class="empty-text">No products found</h3>
                    <p>Start by creating your first upcycled product</p>
                    <a href="{{ route('maker.products.create') }}" class="btn-create" style="display: inline-flex; margin-top: 1rem;">
                        <i class="fa-solid fa-plus"></i> Create Product
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>

@include('components.confirm-modal')
@endsection

@push('scripts')
<script>
const productImagePositions = {};

function nextProductImage(productId, totalImages) {
    if (!productImagePositions[productId]) {
        productImagePositions[productId] = 0;
    }
    
    productImagePositions[productId] = (productImagePositions[productId] + 1) % totalImages;
    updateProductImage(productId, totalImages);
}

function prevProductImage(productId, totalImages) {
    if (!productImagePositions[productId]) {
        productImagePositions[productId] = 0;
    }
    
    productImagePositions[productId] = (productImagePositions[productId] - 1 + totalImages) % totalImages;
    updateProductImage(productId, totalImages);
}

function updateProductImage(productId, totalImages) {
    const position = productImagePositions[productId] || 0;
    const imageElement = document.getElementById(`current-product-image-${productId}`);
    const positionElement = document.getElementById(`product-position-${productId}`);
    
    if (imageElement && productImages[productId] && productImages[productId][position]) {
        imageElement.src = productImages[productId][position].path;
    }
    
    if (positionElement) {
        positionElement.textContent = `${position + 1}/${totalImages}`;
    }
}

const productImages = {};

function preloadAllProductImages() {
    @foreach($products as $product)
        @if($product->images->count() > 0)
            productImages[{{ $product->id }}] = [
                @foreach($product->images as $image)
                {
                    id: {{ $image->id }},
                    path: "{{ asset($image->image_path) }}",
                    order: {{ $image->order }}
                },
                @endforeach
            ];
        @endif
    @endforeach
}

document.addEventListener('DOMContentLoaded', function() {
    preloadAllProductImages();
    
    @foreach($products as $product)
        @if($product->images->count() > 1)
            productImagePositions[{{ $product->id }}] = 0;
        @endif
    @endforeach

    const deleteBtns = document.querySelectorAll('.delete-product-btn');
    
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productName = this.dataset.productName;
            const form = this.closest('.delete-product-form');
            
            openDeleteConfirmation(productName, form);
        });
    });

    function openDeleteConfirmation(productName, form) {
        const popup = document.getElementById('genericConfirmPopup');
        const messageEl = popup.querySelector('.popup-message');
        const confirmBtn = popup.querySelector('.btn-confirm');
        const cancelBtn = popup.querySelector('.btn-cancel');

        messageEl.textContent = `Are you sure you want to delete "${productName}"? This action cannot be undone.`;
        popup.classList.remove('hidden');

        const newConfirmBtn = confirmBtn.cloneNode(true);
        const newCancelBtn = cancelBtn.cloneNode(true);
        
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

        newConfirmBtn.addEventListener('click', () => {
            if (form) form.submit();
            popup.classList.add('hidden');
        });

        newCancelBtn.addEventListener('click', () => {
            popup.classList.add('hidden');
        });
    }

    const statusSelect = document.getElementById('status');
    const categorySelect = document.getElementById('category');
    const sortSelect = document.getElementById('sort');
    
    [statusSelect, categorySelect, sortSelect].forEach(select => {
        if (select) {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
    });
});
</script>
@endpush