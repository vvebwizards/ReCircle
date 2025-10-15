@extends('layouts.app')

@push('head')
@vite(['resources/css/marketplace-details.css'])
@endpush

@section('content')
<div class="product-container" style="padding-top: 100px; ">
    <div class="product-details-container" >
        <div class="product-header">
            <div class="breadcrumb">
                <a href="{{ route('buyer.marketplace.index', ['type' => 'materials']) }}">Marketplace</a> 
                <span>/</span>
                <span>Raw Materials</span>
                <span>/</span>
                <span>{{ $item->name }}</span>
            </div>
            
            <div class="header-actions">
                @if($item->quantity > 0)
                <button class="btn-success" onclick="addMaterialToCartDetail({{ $item->id }})">
                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                </button>
                @endif
            </div>
        </div>

        @if($item->quantity == 0)
        <div class="status-alert sold-out">
            <i class="fa-solid fa-times"></i>
            <strong>Out of Stock:</strong> This material is currently unavailable.
        </div>
        @endif

        <div class="details-grid">
            <div class="left-column">
                <div class="image-section">
                    @if($item->images->count() > 0)
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
                            <i class="fas fa-cube"></i>
                            <p>No images available</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="right-column">
                <div class="product-header-card">
                    <div class="product-meta">
                        <span class="sku">Category: {{ ucfirst($item->category) }}</span>
                        <span class="maker-info">
                            <i class="fa-solid fa-user"></i> By {{ $item->maker->name ?? 'Unknown Maker' }}
                        </span>
                    </div>
                    <h1 class="product-title">{{ $item->name }}</h1>
                    <div class="price-section">
                        <span class="price">{{ $item->quantity }} {{ strtoupper($item->unit) }}</span>
                        <span class="stock {{ $item->quantity == 0 ? 'out-of-stock' : 'in-stock' }}">
                            <i class="fa-solid {{ $item->quantity == 0 ? 'fa-times' : 'fa-check' }}"></i>
                            {{ $item->quantity == 0 ? 'Out of Stock' : 'Available' }}
                        </span>
                    </div>
                </div>

                @if($item->quantity > 0)
                <div class="detail-card">
                    <h3><i class="fa-solid fa-cart-plus"></i> Add to Cart</h3>
                    <div class="detail-content">
                        <div class="quantity-selector-large">
                            <div class="quantity-controls-large">
                                <button type="button" class="quantity-btn-large" onclick="decreaseMaterialQuantityDetail({{ $item->id }}, {{ $item->quantity }})">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <input type="number" 
                                       id="quantity-material-detail-{{ $item->id }}" 
                                       class="quantity-input-large" 
                                       value="1" 
                                       min="1" 
                                       max="{{ $item->quantity }}"
                                       onchange="validateMaterialQuantityDetail({{ $item->id }}, {{ $item->quantity }})">
                                <button type="button" class="quantity-btn-large" onclick="increaseMaterialQuantityDetail({{ $item->id }}, {{ $item->quantity }})">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                            <button class="btn-add-to-cart-large" onclick="addMaterialToCartDetail({{ $item->id }})">
                                <i class="fa-solid fa-cart-plus"></i> Add to Cart - 
                                <span id="quantity-display-{{ $item->id }}">1</span> {{ strtoupper($item->unit) }}
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <div class="detail-card">
                    <h3><i class="fa-solid fa-recycle"></i> Recyclability Score</h3>
                    <div class="detail-content">
                        <div class="score-display">
                            <div class="score-circle">
                                <div class="score-value {{ $item->recyclability_score >= 80 ? 'high' : ($item->recyclability_score >= 60 ? 'medium' : 'low') }}">
                                    {{ $item->recyclability_score }}%
                                </div>
                            </div>
                            <div class="score-info">
                                <h4>Material Recyclability</h4>
                                <p>This material has a {{ $item->recyclability_score }}% recyclability score, 
                                @if($item->recyclability_score >= 80)
                                    making it highly suitable for circular economy applications.
                                @elseif($item->recyclability_score >= 60)
                                    indicating good potential for reuse and recycling.
                                @else
                                    suggesting it may require special handling for recycling.
                                @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-card">
                    <h3><i class="fa-solid fa-file-lines"></i> Description</h3>
                    <div class="detail-content">
                        <p>{{ $item->description }}</p>
                    </div>
                </div>

                <div class="detail-card">
                    <h3><i class="fa-solid fa-leaf"></i> Environmental Impact</h3>
                    <div class="detail-content">
                        <div class="impact-grid">
                            @if($item->co2_kg_saved !== null)
                            <div class="impact-item">
                                <div class="impact-icon co2-saved">
                                    <i class="fa-solid fa-cloud"></i>
                                </div>
                                <div class="impact-info">
                                    <strong>{{ number_format($item->co2_kg_saved, 1) }} kg</strong>
                                    <span>CO₂ Saved</span>
                                </div>
                            </div>
                            @endif
                            
                            @if($item->landfill_kg_avoided !== null)
                            <div class="impact-item">
                                <div class="impact-icon landfill-avoided">
                                    <i class="fa-solid fa-mountain"></i>
                                </div>
                                <div class="impact-info">
                                    <strong>{{ number_format($item->landfill_kg_avoided, 1) }} kg</strong>
                                    <span>Landfill Avoided</span>
                                </div>
                            </div>
                            @endif
                           
                        </div>
                    </div>
                </div>

                <div class="detail-card">
                    <h3><i class="fa-solid fa-list"></i> Material Specifications</h3>
                    <div class="detail-content">
                        <div class="spec-grid">
                            <div class="spec-item">
                                <strong>Category:</strong>
                                <span>{{ ucfirst($item->category) }}</span>
                            </div>
                            <div class="spec-item">
                                <strong>Unit:</strong>
                                <span>{{ strtoupper($item->unit) }}</span>
                            </div>
                            <div class="spec-item">
                                <strong>Available Quantity:</strong>
                                <span>{{ $item->quantity }} {{ strtoupper($item->unit) }}</span>
                            </div>
                            <div class="spec-item">
                                <strong>Recyclability Score:</strong>
                                <span>{{ $item->recyclability_score }}%</span>
                            </div>
                            <div class="spec-item">
                                <strong>Created:</strong>
                                <span>{{ $item->created_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($item->wasteItem)
                <div class="detail-card">
                    <h3><i class="fa-solid fa-trash-arrow-up"></i> Source Information</h3>
                    <div class="detail-content">
                        <div class="source-item">
                            <div class="source-header">
                                <strong>{{ $item->wasteItem->title }}</strong>
                                @if($item->wasteItem->received_date)
                                <span class="source-date">{{ $item->wasteItem->received_date->format('M j, Y') }}</span>
                                @endif
                            </div>
                            @if($item->wasteItem->description)
                            <div class="source-description">
                                <p>{{ $item->wasteItem->description }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($relatedItems->count() > 0)
        <div class="related-section">
            <div class="section-header">
                <h2><i class="fa-solid fa-cubes"></i> Similar Materials</h2>
                <span class="section-badge">{{ $relatedItems->count() }} items</span>
            </div>
            <div class="related-grid">
                @foreach($relatedItems as $relatedMaterial)
                <a href="{{ route('buyer.marketplace.show', ['type' => 'material', 'id' => $relatedMaterial->id]) }}" class="related-card">
                    <div class="related-image">
                        @if($relatedMaterial->images->count() > 0)
                            <img src="{{ asset($relatedMaterial->images->first()->image_path) }}" 
                                 alt="{{ $relatedMaterial->name }}">
                        @else
                            <div class="related-no-image">
                                <i class="fa-solid fa-cube"></i>
                            </div>
                        @endif
                        <div class="related-category">{{ ucfirst($relatedMaterial->category) }}</div>
                    </div>
                    <div class="related-content">
                        <h4>{{ $relatedMaterial->name }}</h4>
                        <div class="related-meta">
                            <span class="related-quantity">{{ $relatedMaterial->quantity }}{{ strtoupper($relatedMaterial->unit) }}</span>
                            <div class="related-score">
                                <div class="score-mini-bar">
                                    <div class="score-mini-fill 
                                        @if($relatedMaterial->recyclability_score >= 80) high-score
                                        @elseif($relatedMaterial->recyclability_score >= 60) medium-score
                                        @else low-score
                                        @endif"
                                        style="width: {{ $relatedMaterial->recyclability_score }}%">
                                    </div>
                                </div>
                                <span class="score-text">{{ $relatedMaterial->recyclability_score }}%</span>
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
const materialImages = [
    @foreach($item->images as $image)
    {
        path: "{{ asset($image->image_path) }}",
        id: {{ $image->id }},
        order: {{ $image->order }}
    },
    @endforeach
];

function nextImage(materialId, totalImages) {
    currentImagePosition = (currentImagePosition + 1) % totalImages;
    updateMainImage();
}

function prevImage(materialId, totalImages) {
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
    
    if (mainImage && materialImages[currentImagePosition]) {
        mainImage.src = materialImages[currentImagePosition].path;
    }
    
    if (positionElement) {
        positionElement.textContent = `${currentImagePosition + 1}/${materialImages.length}`;
    }
    
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
        if (parseInt(thumb.dataset.imageIndex) === currentImagePosition) {
            thumb.classList.add('active');
        }
    });
}

function increaseMaterialQuantityDetail(materialId, maxQuantity) {
    const input = document.getElementById(`quantity-material-detail-${materialId}`);
    let value = parseInt(input.value) || 1;
    if (value < maxQuantity) {
        input.value = value + 1;
        updateMaterialQuantityDisplay(materialId, value + 1);
    }
}

function decreaseMaterialQuantityDetail(materialId, maxQuantity) {
    const input = document.getElementById(`quantity-material-detail-${materialId}`);
    let value = parseInt(input.value) || 1;
    if (value > 1) {
        input.value = value - 1;
        updateMaterialQuantityDisplay(materialId, value - 1);
    }
}

function validateMaterialQuantityDetail(materialId, maxQuantity) {
    const input = document.getElementById(`quantity-material-detail-${materialId}`);
    let value = parseInt(input.value) || 1;
    
    if (value < 1) {
        input.value = 1;
    } else if (value > maxQuantity) {
        input.value = maxQuantity;
    }
    updateMaterialQuantityDisplay(materialId, input.value);
}

function updateMaterialQuantityDisplay(materialId, quantity) {
    const displayElement = document.getElementById(`quantity-display-${materialId}`);
    if (displayElement) {
        displayElement.textContent = quantity;
    }
}

function addMaterialToCartDetail(materialId) {
    const quantityInput = document.getElementById(`quantity-material-detail-${materialId}`);
    const quantity = parseInt(quantityInput.value) || 1;
    
    console.log('Add material to cart from detail:', materialId, 'Quantity:', quantity);
    alert(`Added ${quantity} {{ strtoupper($item->unit) }} of material to cart!`);
    

}

document.addEventListener('DOMContentLoaded', function() {
    updateMaterialQuantityDisplay({{ $item->id }}, 1);
});
</script>
@endpush