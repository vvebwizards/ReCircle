@extends('layouts.app')

@push('head')
@vite(['resources/css/marketplace-details.css'])
@endpush

@section('content')
<div class="product-container">
    <div class="product-details-container">
        <div class="product-header">
            <div class="breadcrumb">
                <a href="{{ route('buyer.marketplace.index', ['type' => 'products']) }}">Marketplace</a> 
                <span>/</span>
                <span>Products</span>
                <span>/</span>
                <span>{{ $item->name }}</span>
            </div>
            
            <div class="header-actions">
                @if($item->stock > 0 && auth()->check() && auth()->user()->role === \App\Enums\UserRole::BUYER)
                <button class="btn-success" onclick="addProductToCart({{ $item->id }})">
                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                </button>
                @endif
            </div>

            @if(auth()->check() && auth()->user()->role === \App\Enums\UserRole::BUYER)
            <!-- Hidden form used for Add to Cart (submits to CartController@addToCart) -->
            <form id="add-to-cart-form" method="POST" action="{{ route('cart.add') }}" style="display:none;">
                @csrf
                <input type="hidden" name="product_id" id="form-product-id" value="{{ $item->id }}">
                <input type="hidden" name="quantity" id="form-quantity" value="1">
            </form>
            @endif
        </div>

        @if($item->status === \App\Enums\ProductStatus::SOLD_OUT)
        <div class="status-alert sold-out">
            <i class="fa-solid fa-tag"></i>
            <strong>Sold Out:</strong> This product is currently out of stock.
        </div>
        @endif

        <div class="details-grid">
            <div class="left-column">
                <div class="image-section">
                    @if($item->images && $item->images->count() > 0)
                        <div class="main-image-container">
                            <img src="{{ asset($item->images->first()->image_path) }}" 
                                 alt="{{ $item->name }}" 
                                 id="mainImage"
                                 class="main-image">
                            
                            @if($item->images->count() > 1)
                                <button type="button" 
                                        class="image-nav-btn prev-btn"
                                        onclick="prevImage({{ $item->id }}, {{ $item->images->count() }})">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <button type="button" 
                                        class="image-nav-btn next-btn"
                                        onclick="nextImage({{ $item->id }}, {{ $item->images->count() }})">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                                <div class="image-counter" id="imagePosition">
                                    1/{{ $item->images->count() }}
                                </div>
                            @endif
                        </div>
                        
                        @if($item->images->count() > 1)
                        <div class="image-thumbnails">
                            @foreach($item->images as $index => $image)
                            <div class="thumbnail {{ $loop->first ? 'active' : '' }}"
                                 data-image-index="{{ $index }}"
                                 onclick="changeMainImage('{{ asset($image->image_path) }}', {{ $index }}, this)">
                                <img src="{{ asset($image->image_path) }}" alt="{{ $item->name }}">
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
            </div>

            <div class="right-column">
                <div class="product-header-card">
                    <div class="product-meta">
                        <span class="sku">SKU: {{ $item->sku }}</span>
                        <span class="maker-info">
                            <i class="fa-solid fa-user"></i> By {{ $item->maker->name ?? 'Unknown Maker' }}
                        </span>
                    </div>
                    <h1 class="product-title">{{ $item->name }}</h1>
                    <div class="price-section">
                        <span class="price">${{ number_format($item->price, 2) }}</span>
                        <span class="stock {{ $item->stock == 0 ? 'out-of-stock' : 'in-stock' }}">
                            <i class="fa-solid {{ $item->stock == 0 ? 'fa-times' : 'fa-check' }}"></i>
                            {{ $item->stock }} in stock
                        </span>
                    </div>
                </div>

                @if($item->stock > 0)
                <div class="detail-card">
                    <h3><i class="fa-solid fa-cart-plus"></i> Add to Cart</h3>
                    <div class="detail-content">
                        <div class="quantity-selector-large">
                            <div class="quantity-controls-large">
                                <button type="button" class="quantity-btn-large" onclick="decreaseProductQuantityDetail({{ $item->id }}, {{ $item->stock }})">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <input type="number" 
                                       id="quantity-product-detail-{{ $item->id }}" 
                                       class="quantity-input-large" 
                                       value="1" 
                                       min="1" 
                                       max="{{ $item->stock }}"
                                       onchange="validateProductQuantityDetail({{ $item->id }}, {{ $item->stock }})">
                                <button type="button" class="quantity-btn-large" onclick="increaseProductQuantityDetail({{ $item->id }}, {{ $item->stock }})">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                            <button class="btn-add-to-cart-large" onclick="addProductToCartDetail({{ $item->id }})">
                                <i class="fa-solid fa-cart-plus"></i> Add to Cart - 
                                $<span id="total-price-{{ $item->id }}">{{ number_format($item->price, 2) }}</span>
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <div class="detail-card">
                    <h3><i class="fa-solid fa-file-lines"></i> Description</h3>
                    <div class="detail-content">
                        <p>{{ $item->description }}</p>
                    </div>
                </div>

                <div class="detail-card">
                    <h3><i class="fa-solid fa-leaf"></i> Environmental Impact</h3>
                    <div class="detail-content">
                        @php
                            $totalCO2 = $item->materials ? $item->materials->sum('co2_kg_saved') : 0;
                            $totalLandfill = $item->materials ? $item->materials->sum('landfill_kg_avoided') : 0;
                            $totalEnergy = $item->materials ? $item->materials->sum('energy_saved_kwh') : 0;
                        @endphp
                        
                        <div class="impact-grid">
                            @if($totalCO2 > 0)
                            <div class="impact-item">
                                <div class="impact-icon co2-saved">
                                    <i class="fa-solid fa-cloud"></i>
                                </div>
                                <div class="impact-info">
                                    <strong>{{ number_format($totalCO2, 1) }} kg</strong>
                                    <span>CO₂ Saved</span>
                                </div>
                            </div>
                            @endif
                            
                            @if($totalLandfill > 0)
                            <div class="impact-item">
                                <div class="impact-icon landfill-avoided">
                                    <i class="fa-solid fa-mountain"></i>
                                </div>
                                <div class="impact-info">
                                    <strong>{{ number_format($totalLandfill, 1) }} kg</strong>
                                    <span>Landfill Avoided</span>
                                </div>
                            </div>
                            @endif
                            
                            @if($totalEnergy > 0)
                            <div class="impact-item">
                                <div class="impact-icon energy-saved">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>
                                <div class="impact-info">
                                    <strong>{{ number_format($totalEnergy, 1) }} kWh</strong>
                                    <span>Energy Saved</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="detail-card">
                    <h3><i class="fa-solid fa-cubes"></i> Materials Used</h3>
                    <div class="detail-content">
                        @if($item->materials && $item->materials->count() > 0)
                            @foreach($item->materials as $material)
                            <div class="material-item">
                                <div class="material-header">
                                    <div class="material-info">
                                        <strong>{{ $material->name }}</strong>
                                        <div class="material-meta">
                                            <span class="material-category">{{ ucfirst($material->category) }}</span>
                                            <span class="material-score {{ $material->recyclability_score >= 80 ? 'high' : ($material->recyclability_score >= 60 ? 'medium' : 'low') }}">
                                                {{ $material->recyclability_score }}% Recyclable
                                            </span>
                                        </div>
                                    </div>
                                    <span class="material-quantity">
                                        {{ $material->pivot->quantity_used }} {{ strtoupper($material->pivot->unit) }}
                                    </span>
                                </div>
                            </div>
                            @if(!$loop->last)
                            <hr class="material-divider">
                            @endif
                            @endforeach
                        @else
                        <div class="empty-state">
                            <i class="fa-solid fa-cube"></i>
                            <p>No materials information available</p>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="detail-card">
                    <h3><i class="fa-solid fa-list"></i> Product Specifications</h3>
                    <div class="detail-content">
                        <div class="spec-grid">
                            <div class="spec-item">
                                <strong>SKU:</strong>
                                <span>{{ $item->sku }}</span>
                            </div>
                            @if($item->weight)
                            <div class="spec-item">
                                <strong>Weight:</strong>
                                <span>{{ $item->weight }} kg</span>
                            </div>
                            @endif
                            @if($item->warranty_months)
                            <div class="spec-item">
                                <strong>Warranty:</strong>
                                <span>{{ $item->warranty_months }} months</span>
                            </div>
                            @endif
                            @if($item->care_instructions)
                            <div class="spec-item">
                                <strong>Care Instructions:</strong>
                                <span>{{ $item->care_instructions }}</span>
                            </div>
                            @endif
                            <div class="spec-item">
                                <strong>Created:</strong>
                                <span>{{ $item->created_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($item->material_passport)
                <div class="detail-card">
                    <h3><i class="fa-solid fa-passport"></i> Material Passport</h3>
                    <div class="detail-content">
                        <div class="passport-info">
                            <p>This product comes with a digital material passport that tracks its sustainable journey from waste to product.</p>
                            <div class="passport-badge">
                                <i class="fa-solid fa-certificate"></i>
                                <span>Verified Sustainable Product</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($relatedItems->count() > 0)
        <div class="related-section">
            <div class="section-header">
                <h2><i class="fa-solid fa-cube"></i> Similar Products</h2>
                <span class="section-badge">{{ $relatedItems->count() }} items</span>
            </div>
            <div class="related-grid">
                @foreach($relatedItems as $relatedProduct)
                <a href="{{ route('buyer.marketplace.show', ['type' => 'product', 'id' => $relatedProduct->id]) }}" class="related-card">
                    <div class="related-image">
                        @if($relatedProduct->images->count() > 0)
                            <img src="{{ asset($relatedProduct->images->first()->image_path) }}" 
                                 alt="{{ $relatedProduct->name }}">
                        @else
                            <div class="related-no-image">
                                <i class="fa-solid fa-cube"></i>
                            </div>
                        @endif
                        @if($relatedProduct->is_featured)
                        <div class="featured-badge">
                            <i class="fa-solid fa-star"></i> Featured
                        </div>
                        @endif
                    </div>
                    <div class="related-content">
                        <h4>{{ $relatedProduct->name }}</h4>
                        <div class="related-meta">
                            <span class="related-price">${{ number_format($relatedProduct->price, 2) }}</span>
                            <div class="related-stock {{ $relatedProduct->stock > 0 ? 'in-stock' : 'out-of-stock' }}">
                                {{ $relatedProduct->stock }} left
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentImagePosition = 0;
const productImages = [
    @foreach($item->images as $image)
    {
        path: "{{ asset($image->image_path) }}",
        id: {{ $image->id }},
        order: {{ $image->order }}
    },
    @endforeach
];

function nextImage(productId, totalImages) {
    currentImagePosition = (currentImagePosition + 1) % totalImages;
    updateMainImage();
}

function prevImage(productId, totalImages) {
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

function increaseProductQuantityDetail(productId, maxStock) {
    const input = document.getElementById(`quantity-product-detail-${productId}`);
    let value = parseInt(input.value) || 1;
    if (value < maxStock) {
        input.value = value + 1;
        updateTotalPrice(productId, {{ $item->price }}, value + 1);
    }
}

function decreaseProductQuantityDetail(productId, maxStock) {
    const input = document.getElementById(`quantity-product-detail-${productId}`);
    let value = parseInt(input.value) || 1;
    if (value > 1) {
        input.value = value - 1;
        updateTotalPrice(productId, {{ $item->price }}, value - 1);
    }
}

function validateProductQuantityDetail(productId, maxStock) {
    const input = document.getElementById(`quantity-product-detail-${productId}`);
    let value = parseInt(input.value) || 1;
    
    if (value < 1) {
        input.value = 1;
    } else if (value > maxStock) {
        input.value = maxStock;
    }
    updateTotalPrice(productId, {{ $item->price }}, input.value);
}

function updateTotalPrice(productId, unitPrice, quantity) {
    const totalPriceElement = document.getElementById(`total-price-${productId}`);
    const totalPrice = (unitPrice * quantity).toFixed(2);
    totalPriceElement.textContent = totalPrice;
}

function addProductToCartDetail(productId) {
    const quantityInput = document.getElementById(`quantity-product-detail-${productId}`);
    const quantity = parseInt(quantityInput.value) || 1;
    
    // If the hidden form exists (user is a Buyer), submit it with the selected quantity
    const form = document.getElementById('add-to-cart-form');
    if (form) {
        document.getElementById('form-product-id').value = productId;
        document.getElementById('form-quantity').value = quantity;
        form.submit();
        return;
    }

    // Fallback for unauthenticated or non-buyer users
    console.log('Add product to cart from detail (fallback):', productId, 'Quantity:', quantity);
    alert(`Added ${quantity} product(s) to cart!`);
 
}

function addProductToCart(productId) {
    // Header quick-add button uses quantity = 1
    const form = document.getElementById('add-to-cart-form');
    if (form) {
        document.getElementById('form-product-id').value = productId;
        document.getElementById('form-quantity').value = 1;
        form.submit();
        return;
    }

    alert('Please log in as a Buyer to add items to the cart.');
}


document.addEventListener('DOMContentLoaded', function() {
    updateTotalPrice({{ $item->id }}, {{ $item->price }}, 1);
});
</script>
@endpush