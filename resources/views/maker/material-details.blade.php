@extends('layouts.app')
@push('head')
@vite(['resources/css/create-product.css','resources/css/product_details.css'])
@endpush

@section('content')
<div class="product-container">
    <div class="product-details-container">
        <div class="product-header">
            <div class="breadcrumb">
                <a href="{{ route('maker.materials.index') }}">Materials</a> 
                <span>/</span>
                <span>{{ $material->name }}</span>
            </div>
            
            <div class="header-actions">
                <a href="{{ route('maker.materials.edit', $material->id) }}" class="btn-primary">
                    <i class="fa-solid fa-edit"></i> Edit Material
                </a>
                <form action="{{ route('maker.materials.destroy', $material->id) }}" method="POST" class="inline-form delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn-danger delete-material-btn" data-material-name="{{ $material->name }}">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="details-grid">
            <div class="left-column">
                <div class="image-section">
                    @if($material->images->count() > 0)
                        <div class="main-image-container">
                            <img src="{{ asset($material->images->first()->image_path) }}" 
                                 alt="{{ $material->name }}" 
                                 id="mainImage"
                                 class="main-image">
                            
                            @if($material->images->count() > 1)
                                <button type="button" 
                                        class="image-nav-btn prev-btn"
                                        onclick="prevMaterialImage({{ $material->id }}, {{ $material->images->count() }})">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <button type="button" 
                                        class="image-nav-btn next-btn"
                                        onclick="nextMaterialImage({{ $material->id }}, {{ $material->images->count() }})">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                                <div class="image-counter" id="imagePosition">
                                    1/{{ $material->images->count() }}
                                </div>
                            @endif
                        </div>
                        
                        @if($material->images->count() > 1)
                        <div class="image-thumbnails">
                            @foreach($material->images as $index => $image)
                            <div class="thumbnail {{ $loop->first ? 'active' : '' }}"
                                 data-image-index="{{ $index }}"
                                 onclick="changeMainImage('{{ asset($image->image_path) }}', {{ $index }}, this)">
                                <img src="{{ asset($image->image_path) }}" alt="{{ $material->name }}">
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
                        <span class="sku">Category: {{ ucfirst($material->category) }}</span>
                        <span class="status-badge score-{{ $material->recyclability_score >= 80 ? 'high' : ($material->recyclability_score >= 60 ? 'medium' : 'low') }}">
                            <i class="fa-solid fa-recycle"></i> {{ $material->recyclability_score }}% Recyclable
                        </span>
                    </div>
                    <h1 class="product-title">{{ $material->name }}</h1>
                    <div class="price-section">
                        <span class="price">{{ $material->quantity }} {{ strtoupper($material->unit) }}</span>
                        <span class="stock in-stock">
                            <i class="fa-solid fa-check"></i>
                            Available
                        </span>
                    </div>
                </div>

                <div class="detail-card">
                    <h3><i class="fa-solid fa-file-lines"></i> Description</h3>
                    <div class="detail-content">
                        <p>{{ $material->description }}</p>
                    </div>
                </div>

                @if($material->wasteItem)
                <div class="detail-card">
                    <h3><i class="fa-solid fa-trash-arrow-up"></i> Source Waste Item</h3>
                    <div class="detail-content">
                        <div class="source-item">
                            <div class="source-header">
                                <strong>{{ $material->wasteItem->title }}</strong>
                                @if($material->wasteItem->received_date)
                                <span class="source-date">{{ $material->wasteItem->received_date->format('M j, Y') }}</span>
                                @endif
                            </div>
                            @if($material->wasteItem->description)
                            <div class="source-description">
                                <small>{{ Str::limit($material->wasteItem->description, 150) }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Combined Specifications and Environmental Impact Card -->
                <div class="detail-card">
                    <h3><i class="fa-solid fa-list"></i> Material Details</h3>
                    <div class="detail-content">
                        <div class="spec-grid">
                            <div class="spec-item">
                                <strong>Category:</strong>
                                <span>{{ ucfirst($material->category) }}</span>
                            </div>
                            <div class="spec-item">
                                <strong>Unit:</strong>
                                <span>{{ strtoupper($material->unit) }}</span>
                            </div>
                            <div class="spec-item">
                                <strong>Quantity:</strong>
                                <span>{{ $material->quantity }} {{ strtoupper($material->unit) }}</span>
                            </div>
                            <div class="spec-item">
                                <strong>Recyclability Score:</strong>
                                <span>{{ $material->recyclability_score }}%</span>
                            </div>
                            <div class="spec-item">
                                <strong>Created:</strong>
                                <span>{{ $material->created_at->format('M j, Y') }}</span>
                            </div>
                            <div class="spec-item">
                                <strong>Last Updated:</strong>
                                <span>{{ $material->updated_at->format('M j, Y') }}</span>
                            </div>
                        </div>

                        @if($material->co2_kg_saved !== null || $material->landfill_kg_avoided !== null)
                        <div class="impact-section" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
                            <h4 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-leaf" style="color: #10b981;"></i>
                                Environmental Impact
                            </h4>
                            <div class="impact-grid">
                                @if($material->co2_kg_saved !== null)
                                <div class="impact-item">
                                    <div class="impact-info">
                                        <i class="fa-solid fa-cloud"></i>
                                        <strong>CO₂ Saved</strong>
                                        <span class="impact-value">{{ number_format($material->co2_kg_saved, 2) }} kg</span>
                                    </div>
                                </div>
                                @endif
                                
                                @if($material->landfill_kg_avoided !== null)
                                <div class="impact-item">
                                    <div class="impact-info">
                                        <i class="fa-solid fa-mountain"></i>
                                        <strong>Landfill Avoided</strong>
                                        <span class="impact-value">{{ number_format($material->landfill_kg_avoided, 2) }} kg</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($relatedMaterials->count() > 0)
        <div class="related-section">
            <div class="section-header">
                <h2><i class="fa-solid fa-cubes"></i> Similar Materials</h2>
                <span class="section-badge">{{ $relatedMaterials->count() }} items</span>
            </div>
            <div class="related-grid">
                @foreach($relatedMaterials as $relatedMaterial)
                <a href="{{ route('maker.materials.show', $relatedMaterial->id) }}" class="related-card">
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

@include('components.confirm-modal')
@endsection

@push('scripts')
<script>
let currentImagePosition = 0;
const materialImages = [
    @foreach($material->images as $image)
    {
        path: "{{ asset($image->image_path) }}",
        id: {{ $image->id }},
        order: {{ $image->order }}
    },
    @endforeach
];

function nextMaterialImage(materialId, totalImages) {
    currentImagePosition = (currentImagePosition + 1) % totalImages;
    updateMainImage();
}

function prevMaterialImage(materialId, totalImages) {
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

document.addEventListener('DOMContentLoaded', function() {
    const deleteBtn = document.querySelector('.delete-material-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const materialName = this.dataset.materialName;
            const form = document.querySelector('.delete-form');
            
            const popup = document.getElementById('genericConfirmPopup');
            const messageEl = popup.querySelector('.popup-message');
            const confirmBtn = popup.querySelector('.btn-confirm');
            const cancelBtn = popup.querySelector('.btn-cancel');

            messageEl.textContent = `Are you sure you want to delete "${materialName}"? This action cannot be undone.`;
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
</script>
@endpush