{{-- resources/views/maker/products/show.blade.php --}}
@extends('layouts.app')
@push('head')
@vite(['resources/css/create-product.css','resources/css/product_details.css'])
@endpush

@section('content')
<div class="product-container">
    <div class="product-details-container">
        <div class="product-header">
            <div class="breadcrumb">
                <a href="{{ route('maker.products') }}">Products</a> 
                <span>/</span>
                <span>{{ $product->name }}</span>
            </div>
            
            <div class="header-actions">
                <a href="{{ route('maker.products.edit', $product->id) }}" class="btn-primary">
                    Edit Product
                </a>
                @if($product->status !== \App\Enums\ProductStatus::PUBLISHED && $product->stock > 0)
                <form action="{{ route('maker.products.publish', $product->id) }}" method="POST" class="inline-form">
                    @csrf
                    <button type="submit" class="btn-success">Publish Now</button>
                </form>
                @endif
            </div>
        </div>

        @if($product->status === \App\Enums\ProductStatus::DRAFT)
        <div class="status-alert draft">
            <strong>Draft:</strong> This product is not visible to customers.
        </div>
        @elseif($product->status === \App\Enums\ProductStatus::SOLD_OUT)
        <div class="status-alert sold-out">
            <strong>Sold Out:</strong> This product is visible but out of stock.
        </div>
        @endif

        <div class="product-content">
            <div class="product-gallery">
                @if($product->images && $product->images->count() > 0)
                    <div class="main-image-container">
                        <img src="{{ asset($product->images->first()->image_path) }}" 
                             alt="{{ $product->name }}" 
                             id="mainImage"
                             class="main-image">
                        
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
                            <div class="image-counter" id="imagePosition">
                                1/{{ $product->images->count() }}
                            </div>
                        @endif
                    </div>
                    
                    @if($product->images->count() > 1)
                    <div class="image-thumbnails">
                        @foreach($product->images as $index => $image)
                        <div class="thumbnail {{ $loop->first ? 'active' : '' }}"
                             data-image-index="{{ $index }}"
                             onclick="changeMainImage('{{ asset($image->image_path) }}', {{ $index }}, this)">
                            <img src="{{ asset($image->image_path) }}" alt="{{ $product->name }}">
                        </div>
                        @endforeach
                    </div>
                    @endif
                @else
                <div class="no-image">
                    <div class="no-image-placeholder">
                        <i class="fas fa-camera"></i>
                        <p>No images available</p>
                    </div>
                </div>
                @endif
            </div>

            <div class="product-info">
                <div class="product-meta">
                    <span class="sku">SKU: {{ $product->sku }}</span>
                    <span class="status-badge status-{{ strtolower($product->status->name) }}">
                        {{ $product->status->name }}
                    </span>
                </div>

                <h1 class="product-title">{{ $product->name }}</h1>
                
                <div class="price-section">
                    <span class="price">€{{ number_format($product->price, 2) }}</span>
                    <span class="stock {{ $product->stock == 0 ? 'out-of-stock' : 'in-stock' }}">
                        {{ $product->stock }} in stock
                    </span>
                </div>

                <div class="product-description">
                    <h3>Description</h3>
                    <p>{{ $product->description }}</p>
                </div>

                <div class="quick-stats">
                    <div class="stat-item">
                        <div class="stat-value">{{ $product->stock }}</div>
                        <div class="stat-label">Available</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">€{{ number_format($product->price, 2) }}</div>
                        <div class="stat-label">Price</div>
                    </div>
                    @if($product->weight)
                    <div class="stat-item">
                        <div class="stat-value">{{ $product->weight }}kg</div>
                        <div class="stat-label">Weight</div>
                    </div>
                    @endif
                </div>

                <div class="quick-actions">
                    <form action="{{ route('maker.products.update-stock', $product->id) }}" method="POST" class="stock-form">
                        @csrf
                        @method('PATCH')
                        <div class="form-group">
                            <label for="quick_stock">Update Stock</label>
                            <div class="stock-input-group">
                                <input type="number" name="stock" id="quick_stock" value="{{ $product->stock }}" min="0" class="stock-input">
                                <button type="submit" class="btn-outline">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="product-details-grid">
            <div class="detail-card">
                <h3>Material Information</h3>
                <div class="detail-content">
                    @if($product->material)
                    <div class="detail-item">
                        <strong>Material:</strong> {{ $product->material->name }}
                    </div>
                    <div class="detail-item">
                        <strong>Category:</strong> {{ $product->material->category }}
                    </div>
                    <div class="detail-item">
                        <strong>Quantity Used:</strong> {{ $product->material->quantity }} {{ strtoupper($product->material->unit) }}
                    </div>
                    @if($product->material->wasteItem)
                    <div class="detail-item">
                        <strong>Source:</strong> {{ $product->material->wasteItem->name }}
                    </div>
                    @endif
                    @else
                    <div class="detail-item">
                        No material information available
                    </div>
                    @endif
                </div>
            </div>

            <div class="detail-card">
                <h3>Product Specifications</h3>
                <div class="detail-content">
                    <div class="detail-item">
                        <strong>SKU:</strong> {{ $product->sku }}
                    </div>
                    @if($product->weight)
                    <div class="detail-item">
                        <strong>Weight:</strong> {{ $product->weight }} kg
                    </div>
                    @endif
                    @if($product->warranty_months)
                    <div class="detail-item">
                        <strong>Warranty:</strong> {{ $product->warranty_months }} months
                    </div>
                    @endif
                    <div class="detail-item">
                        <strong>Created:</strong> {{ $product->created_at->format('M j, Y') }}
                    </div>
                    <div class="detail-item">
                        <strong>Last Updated:</strong> {{ $product->updated_at->format('M j, Y') }}
                    </div>
                </div>
            </div>

            @if($product->workOrder)
            <div class="detail-card">
                <h3>Work Order</h3>
                <div class="detail-content">
                    <div class="detail-item">
                        <strong>Work Order #:</strong> {{ $product->workOrder->id }}
                    </div>
                    <div class="detail-item">
                        <strong>Status:</strong> {{ $product->workOrder->status }}
                    </div>
                    <div class="detail-item">
                        <strong>Completed:</strong> {{ $product->workOrder->updated_at->format('M j, Y') }}
                    </div>
                    @if($product->workOrder->match && $product->workOrder->match->listing)
                    <div class="detail-item">
                        <strong>Source Listing:</strong> {{ $product->workOrder->match->listing->wasteItem->name }}
                    </div>
                    @endif
                </div>
            </div>
            @endif

            
            @if($product->care_instructions)
            <div class="detail-card full-width">
                <h3>Care Instructions</h3>
                <div class="detail-content">
                    <p>{{ $product->care_instructions }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentImagePosition = 0;
const productImages = [
    @foreach($product->images as $image)
    {
        path: "{{ asset($image->image_path) }}",
        id: {{ $image->id }},
        order: {{ $image->order }}
    },
    @endforeach
];

function nextProductImage(productId, totalImages) {
    currentImagePosition = (currentImagePosition + 1) % totalImages;
    updateMainImage();
}

function prevProductImage(productId, totalImages) {
    currentImagePosition = (currentImagePosition - 1 + totalImages) % totalImages;
    updateMainImage();
}

function changeMainImage(imageSrc, imageIndex, element) {
    currentImagePosition = imageIndex;
    updateMainImage();
    
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    element.classList.add('active');
}

function updateMainImage() {
    const mainImage = document.getElementById('mainImage');
    const positionElement = document.getElementById('imagePosition');
    
    if (mainImage && productImages[currentImagePosition]) {
        mainImage.src = productImages[currentImagePosition].path;
    }
    
    if (positionElement) {
        positionElement.textContent = `${currentImagePosition + 1}/${productImages.length}`;
    }
    
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
        if (parseInt(thumb.dataset.imageIndex) === currentImagePosition) {
            thumb.classList.add('active');
        }
    });
}

document.addEventListener('keydown', function(e) {
    if (productImages.length <= 1) return;
    
    if (e.key === 'ArrowLeft') {
        prevProductImage({{ $product->id }}, {{ $product->images->count() }});
    } else if (e.key === 'ArrowRight') {
        nextProductImage({{ $product->id }}, {{ $product->images->count() }});
    }
});

let touchStartX = 0;
let touchEndX = 0;

document.addEventListener('touchstart', e => {
    touchStartX = e.changedTouches[0].screenX;
});

document.addEventListener('touchend', e => {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
});

function handleSwipe() {
    if (productImages.length <= 1) return;
    
    const swipeThreshold = 50;
    const diff = touchStartX - touchEndX;
    
    if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
            nextProductImage({{ $product->id }}, {{ $product->images->count() }});
        } else {
            prevProductImage({{ $product->id }}, {{ $product->images->count() }});
        }
    }
}
</script>
@endpush