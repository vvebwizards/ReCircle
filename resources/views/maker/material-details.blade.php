@extends('layouts.app')

@push('head')
@vite(['resources/css/material-details.css'])
@endpush

@section('content')
<div class="material-details-container">
    <div class="container">
        {{-- In the header-section --}}
<div class="header-section">
   
    <div class="header-content">
        <h1 class="material-title">{{ $material->name }}</h1>
        <div class="header-meta">
            <span class="category-tag">{{ ucfirst($material->category) }}</span>
            <span class="score-tag 
                @if($material->recyclability_score >= 80) score-high
                @elseif($material->recyclability_score >= 60) score-medium
                @else score-low
                @endif">
                <i class="fa-solid fa-recycle"></i>
                {{ $material->recyclability_score }}% Recyclable
            </span>
        </div>
    </div>
</div>

        <div class="main-content-grid">
            <div class="left-column">
                <div class="card description-card">
                    <h3 class="card-title">
                        <i class="fa-solid fa-file-lines"></i>
                        Description
                    </h3>
                    <p class="description-text">{{ $material->description }}</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value">{{ $material->quantity }}</span>
                            <span class="stat-label">{{ strtoupper($material->unit) }} Available</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fa-solid fa-hammer"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value">{{ $usageCount }}</span>
                            <span class="stat-label">Times Used</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fa-solid fa-calendar"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-value">{{ $material->created_at->format('M d') }}</span>
                            <span class="stat-label">Date Added</span>
                        </div>
                    </div>
                </div>

                @if($material->wasteItem)
                <div class="card source-card">
                    <h3 class="card-title">
                        <i class="fa-solid fa-trash-arrow-up"></i>
                        Source Material
                    </h3>
                    <div class="source-content">
                       
                        <div class="source-info">
                            <strong>{{ $material->wasteItem->title }}</strong>
                        </div>
                    </div>
                </div>
                @endif

                <div class="action-card">
                    <a href="{{ route('maker.materials.edit', $material->id) }}" class="action-btn edit-btn">
                        <i class="fa-solid fa-pencil"></i>
                        Edit Material
                    </a>
                    <button class="action-btn delete-btn delete-material-btn" data-material-name="{{ $material->name }}">
                        <i class="fa-solid fa-trash"></i>
                        Delete
                    </button>
                    <form action="{{ route('maker.materials.destroy', $material->id) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>

            <div class="right-column">
                <div class="image-card">
                    @if($material->images->count() > 0)
                        <img src="{{ asset($material->images->first()->image_path) }}" 
                             alt="{{ $material->name }}" 
                             class="featured-image"
                             id="featured-image">
                    @else
                        <div class="no-image">
                            <i class="fa-solid fa-image"></i>
                            <span>No Images</span>
                        </div>
                    @endif
                </div>

                @if($material->images->count() > 1)
                <div class="thumbnails-row">
                    @foreach($material->images as $image)
                        <div class="thumb-item {{ $loop->first ? 'active' : '' }}" 
                             data-image="{{ asset($image->image_path) }}">
                            <img src="{{ asset($image->image_path) }}" 
                                 alt="Thumbnail {{ $loop->iteration }}"
                                 class="thumb-img">
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        @if($material->processSteps->count() > 0)
        <div class="usage-section">
            <div class="section-header">
                <h2>Usage in Projects</h2>
                <span class="badge">{{ $material->processSteps->count() }} process steps</span>
            </div>
            <div class="process-list">
                @foreach($material->processSteps as $step)
                <div class="process-item">
                    <div class="process-icon">
                        <i class="fa-solid fa-gear"></i>
                    </div>
                    <div class="process-details">
                        <h4>{{ $step->step_name }}</h4>
                        <p>{{ $step->description }}</p>
                        <div class="process-meta">
                            <span class="work-order">Work Order #{{ $step->workOrder->id }}</span>
                            <span class="time">
                                <i class="fa-solid fa-clock"></i>
                                {{ $step->actual_hours ?? $step->estimated_hours }}h
                            </span>
                            @if($step->qc_pass !== null)
                            <span class="qc-status {{ $step->qc_pass ? 'passed' : 'failed' }}">
                                {{ $step->qc_pass ? '✓ Passed' : '✗ Failed' }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($relatedMaterials->count() > 0)
        <div class="related-section">
            <div class="section-header">
                <h2>Similar Materials</h2>
                <span class="badge">{{ $relatedMaterials->count() }} items</span>
            </div>
            <div class="related-grid">
                @foreach($relatedMaterials as $relatedMaterial)
                <a href="{{ route('maker.materials.show', $relatedMaterial->id) }}" class="related-item">
                    <div class="related-image">
                        @if($relatedMaterial->images->count() > 0)
                            <img src="{{ asset($relatedMaterial->images->first()->image_path) }}" 
                                 alt="{{ $relatedMaterial->name }}">
                        @else
                            <div class="related-no-image">
                                <i class="fa-solid fa-cube"></i>
                            </div>
                        @endif
                    </div>
                    <div class="related-info">
                        <h4>{{ $relatedMaterial->name }}</h4>
                        <div class="related-meta">
                            <span>{{ $relatedMaterial->quantity }}{{ strtoupper($relatedMaterial->unit) }}</span>
                            <div class="score-mini">
                                <div class="mini-bar">
                                    <div class="mini-fill" style="width: {{ $relatedMaterial->recyclability_score }}%"></div>
                                </div>
                                <span>{{ $relatedMaterial->recyclability_score }}%</span>
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
document.addEventListener('DOMContentLoaded', function() {
    const featuredImage = document.getElementById('featured-image');
    const thumbItems = document.querySelectorAll('.thumb-item');
    
    thumbItems.forEach(thumb => {
        thumb.addEventListener('click', function() {
            thumbItems.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            if (featuredImage) {
                featuredImage.src = this.dataset.image;
            }
        });
    });

    const deleteBtn = document.querySelector('.delete-material-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const materialName = this.dataset.materialName;
            const form = this.closest('.action-card').querySelector('.delete-form');
            
            const popup = document.getElementById('genericConfirmPopup');
            const messageEl = popup.querySelector('.popup-message');
            const confirmBtn = popup.querySelector('.btn-confirm');
            const cancelBtn = popup.querySelector('.btn-cancel');

            messageEl.textContent = `Delete "${materialName}"? This action cannot be undone.`;
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