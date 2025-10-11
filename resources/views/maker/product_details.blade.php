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
                @if($product->status !== \App\Enums\ProductStatus::PUBLISHED && $product->stock > 0)
                <form action="{{ route('maker.products.publish', $product->id) }}" method="POST" class="inline-form">
                    @csrf
                    <button type="submit" class="btn-success">
                        <i class="fa-solid fa-upload"></i> Publish Now
                    </button>
                </form>
                @endif
                <a href="{{ route('maker.products.edit', $product->id) }}" class="btn-primary">
                    <i class="fa-solid fa-edit"></i> Edit Product
                </a>
                <form action="{{ route('maker.products.destroy', $product->id) }}" method="POST" class="inline-form delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn-danger delete-product-btn" data-product-name="{{ $product->name }}">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>

        @if($product->status === \App\Enums\ProductStatus::DRAFT)
        <div class="status-alert draft">
            <i class="fa-solid fa-pencil"></i>
            <strong>Draft:</strong> This product is not visible to customers.
        </div>
        @elseif($product->status === \App\Enums\ProductStatus::SOLD_OUT)
        <div class="status-alert sold-out">
            <i class="fa-solid fa-tag"></i>
            <strong>Sold Out:</strong> This product is visible but out of stock.
        </div>
        @endif

        <div class="details-grid">
            <div class="left-column">
                <div class="image-section">
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

            </div>

            <div class="right-column">
                <div class="product-header-card">
                    <div class="product-meta">
                        <span class="sku">SKU: {{ $product->sku }}</span>
                        <span class="status-badge status-{{ strtolower($product->status->name) }}">
                            <i class="fa-solid fa-circle"></i> {{ $product->status->name }}
                        </span>
                    </div>
                    <h1 class="product-title">{{ $product->name }}</h1>
                    <div class="price-section">
                        <span class="price">€{{ number_format($product->price, 2) }}</span>
                        <span class="stock {{ $product->stock == 0 ? 'out-of-stock' : 'in-stock' }}">
                            <i class="fa-solid {{ $product->stock == 0 ? 'fa-times' : 'fa-check' }}"></i>
                            {{ $product->stock }} in stock
                        </span>
                    </div>
                </div>

                <div class="detail-card">
                    <h3><i class="fa-solid fa-file-lines"></i> Description</h3>
                    <div class="detail-content">
                        <p>{{ $product->description }}</p>
                    </div>
                </div>

                <div class="detail-card">
                    <h3><i class="fa-solid fa-cubes"></i> Materials Used</h3>
                    <div class="detail-content">
                        @if($product->materials && $product->materials->count() > 0)
                            @foreach($product->materials as $material)
                            <div class="material-item">
                                <div class="material-header">
                                    <div class="material-info">
                                        <strong>{{ $material->name }}</strong>
                                        <div class="material-meta">from
                                            <span class="material-category"> {{ $material->category }} </span>
                                            @if($material->wasteItem)
                                            <span class="material-source"> {{ $material->wasteItem->name }}</span>
                                            @endif
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
                                <span>{{ $product->sku }}</span>
                            </div>
                            @if($product->weight)
                            <div class="spec-item">
                                <strong>Weight:</strong>
                                <span>{{ $product->weight }} kg</span>
                            </div>
                            @endif
                            @if($product->warranty_months)
                            <div class="spec-item">
                                <strong>Warranty:</strong>
                                <span>{{ $product->warranty_months }} months</span>
                            </div>
                            @endif
                            <div class="spec-item">
                                <strong>Created:</strong>
                                <span>{{ $product->created_at->format('M j, Y') }}</span>
                            </div>
                            <div class="spec-item">
                                <strong>Last Updated:</strong>
                                <span>{{ $product->updated_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($product->workOrder)
                <div class="detail-card">
                    <h3><i class="fa-solid fa-clipboard-list"></i> Work Order</h3>
                    <div class="detail-content">
                        <div class="spec-grid">
                            <div class="spec-item">
                                <strong>Work Order #:</strong>
                                <span>{{ $product->workOrder->id }}</span>
                            </div>
                            <div class="spec-item">
                                <strong>Status:</strong>
                                <span class="status-badge">{{ $product->workOrder->status }}</span>
                            </div>
                            <div class="spec-item">
                                <strong>Completed:</strong>
                                <span>{{ $product->workOrder->updated_at->format('M j, Y') }}</span>
                            </div>
                            @if($product->workOrder->match && $product->workOrder->match->listing)
                            <div class="spec-item">
                                <strong>Source Listing:</strong>
                                <span>{{ $product->workOrder->match->listing->wasteItem->name }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                @if($product->care_instructions)
                <div class="detail-card">
                    <h3><i class="fa-solid fa-heart"></i> Care Instructions</h3>
                    <div class="detail-content">
                        <p>{{ $product->care_instructions }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('components.confirm-modal')
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

document.addEventListener('DOMContentLoaded', function() {
    const deleteBtn = document.querySelector('.delete-product-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const productName = this.dataset.productName;
            const form = this.closest('.delete-form');
            
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
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const popup = document.getElementById('genericConfirmPopup');
    
    popup.addEventListener('click', function(e) {
        if (e.target === popup) {
            popup.classList.add('hidden');
        }
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !popup.classList.contains('hidden')) {
            popup.classList.add('hidden');
        }
    });
});
</script>
@endpush