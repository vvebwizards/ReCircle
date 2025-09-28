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
                    <i class="fa-solid fa-cubes"></i> My Materials
                </h1>
                <p>Manage your recycled materials inventory</p>
            </div>
            <a href="{{ route('materials.create') }}" class="btn-create">
                <i class="fa-solid fa-plus"></i> New Material
            </a>
        </div>

        <div class="filters-section">
            <form action="{{ route('maker.materials.index') }}" method="GET" id="filterForm">
                <div class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label" for="search">Search</label>
                        <input type="text" 
                               name="search" 
                               id="search" 
                               class="filter-input" 
                               placeholder="Search materials..."
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="category">Category</label>
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
                            <option value="score_high" {{ request('sort') == 'score_high' ? 'selected' : '' }}>Highest Score</option>
                            <option value="score_low" {{ request('sort') == 'score_low' ? 'selected' : '' }}>Lowest Score</option>
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
                <h3 class="stat-number">{{ $materials->total() }}</h3>
                <p class="stat-label">Total Materials</p>
            </div>
            <div class="stat-card">
                <h3 class="stat-number">{{ number_format($averageScore, 1) }}%</h3>
                <p class="stat-label">Avg Recyclability</p>
            </div>
            <div class="stat-card">
                <h3 class="stat-number">{{ $categoriesCount }}</h3>
                <p class="stat-label">Categories</p>
            </div>
        </div>

        <div class="materials-grid">
            @forelse($materials as $material)
                <div class="material-card">
                    <div class="material-image-container" id="image-container-{{ $material->id }}">
                        @if($material->images->count() > 0)
                            <img src="{{ asset($material->images->first()->image_path) }}" 
                                 alt="{{ $material->name }}" 
                                 class="material-image"
                                 id="current-image-{{ $material->id }}">
                            
                            @if($material->images->count() > 1)
                                <button class="image-nav-button image-nav-prev" 
                                        onclick="prevImage({{ $material->id }}, {{ $material->images->count() }})">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <button class="image-nav-button image-nav-next" 
                                        onclick="nextImage({{ $material->id }}, {{ $material->images->count() }})">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                                
                                <div class="image-count-badge">
                                    <i class="fa-solid fa-images"></i> {{ $material->images->count() }}
                                </div>
                                
                                <div class="image-position" id="position-{{ $material->id }}">
                                    1/{{ $material->images->count() }}
                                </div>
                            @endif
                        @else
                            <div class="material-image-placeholder">
                                <i class="fa-solid fa-cube"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="material-content">
                        <div class="material-header">
                            <h3 class="material-name">{{ $material->name }}</h3>
                            <span class="material-badge">{{ ucfirst($material->category) }}</span>
                        </div>
                        
                        <div class="material-meta">
                            <div class="meta-item">
                                <i class="fa-solid fa-scale-balanced meta-icon"></i>
                                <span>{{ $material->quantity }} {{ strtoupper($material->unit) }}</span>
                            </div>
                            <div class="meta-item">
                                <i class="fa-solid fa-calendar meta-icon"></i>
                                <span>created at {{ $material->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        
                        <p class="material-description">{{ Str::limit($material->description, 100) }}</p>
                        
                        <div class="score-container">
                            <div class="score-bar">
                                <div class="score-fill" 
                                     style="width: {{ $material->recyclability_score }}%;
                                            background: 
                                            @if($material->recyclability_score >= 80) #27ae60
                                            @elseif($material->recyclability_score >= 60) #f39c12
                                            @else #e74c3c
                                            @endif;">
                                </div>
                            </div>
                            <div class="score-text">
                                Recyclability: {{ $material->recyclability_score }}%
                            </div>
                        </div>
                        
                        <div class="material-actions">
                            <a href="#" class="btn-action btn-view">
                                <i class="fa-solid fa-eye"></i> View
                            </a>
                            <a href="{{ route('maker.materials.edit', $material->id) }}" class="btn-action btn-edit">
                                <i class="fa-solid fa-edit"></i> Edit
                            </a>
                            
                            <form action="{{ route('maker.materials.destroy', $material->id) }}" 
                                method="POST" 
                                class="delete-material-form">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="confirm" value="1">
                                <button type="button"
                                        class="btn-action btn-delete delete-material-btn"
                                        data-action="delete"
                                        data-material-name="{{ $material->name }}"
                                        data-material-id="{{ $material->id }}"
                                        title="Delete material">
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
                    <h3 class="empty-text">No materials found</h3>
                    <p>Start by creating your first recycled material</p>
                    <a href="{{ route('materials.create') }}" class="btn-create" style="display: inline-flex; margin-top: 1rem;">
                        <i class="fa-solid fa-plus"></i> Create Material
                    </a>
                </div>
            @endforelse
        </div>

        @if($materials->hasPages())
            <div class="pagination">
                @if($materials->onFirstPage())
                    <span class="page-link disabled">&laquo; Previous</span>
                @else
                    <a href="{{ $materials->previousPageUrl() }}" class="page-link">&laquo; Previous</a>
                @endif

                @foreach($materials->getUrlRange(1, $materials->lastPage()) as $page => $url)
                    @if($page == $materials->currentPage())
                        <span class="page-link active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                    @endif
                @endforeach

                @if($materials->hasMorePages())
                    <a href="{{ $materials->nextPageUrl() }}" class="page-link">Next &raquo;</a>
                @else
                    <span class="page-link disabled">Next &raquo;</span>
                @endif
            </div>
        @endif
    </div>
</div>
@include('components.confirm-modal')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const popup = document.getElementById('genericConfirmPopup');
    const messageEl = popup.querySelector('.popup-message');
    const confirmBtn = popup.querySelector('.btn-confirm');
    const cancelBtn = popup.querySelector('.btn-cancel');

    let currentForm = null;
    let currentMaterialName = null;

    function openDeleteConfirmation(materialName, form) {
        currentForm = form;
        currentMaterialName = materialName;
        
        messageEl.textContent = `Are you sure you want to delete "${materialName}"? This action cannot be undone.`;
        popup.classList.remove('hidden');
    }

    confirmBtn.addEventListener('click', () => {
        if (currentForm) {
            currentForm.submit();
        }
        popup.classList.add('hidden');
        currentForm = null;
        currentMaterialName = null;
    });

    cancelBtn.addEventListener('click', () => {
        popup.classList.add('hidden');
        currentForm = null;
        currentMaterialName = null;
    });

    document.querySelectorAll('.delete-material-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const form = btn.closest('.delete-material-form');
            const materialName = btn.dataset.materialName;
            
            openDeleteConfirmation(materialName, form);
        });
    });

    popup.addEventListener('click', (e) => {
        if (e.target === popup) {
            popup.classList.add('hidden');
            currentForm = null;
            currentMaterialName = null;
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !popup.classList.contains('hidden')) {
            popup.classList.add('hidden');
            currentForm = null;
            currentMaterialName = null;
        }
    });

    const imagePositions = {};

    window.nextImage = function(materialId, totalImages) {
        if (!imagePositions[materialId]) {
            imagePositions[materialId] = 0;
        }
        
        imagePositions[materialId] = (imagePositions[materialId] + 1) % totalImages;
        updateImageSimple(materialId, totalImages);
    }

    window.prevImage = function(materialId, totalImages) {
        if (!imagePositions[materialId]) {
            imagePositions[materialId] = 0;
        }
        
        imagePositions[materialId] = (imagePositions[materialId] - 1 + totalImages) % totalImages;
        updateImageSimple(materialId, totalImages);
    }

    function updateImageSimple(materialId, totalImages) {
        const position = imagePositions[materialId] || 0;
        const imageElement = document.getElementById(`current-image-${materialId}`);
        const positionElement = document.getElementById(`position-${materialId}`);
        
        if (imageElement && materialImages[materialId] && materialImages[materialId][position]) {
            imageElement.src = materialImages[materialId][position].path;
        }
        
        if (positionElement) {
            positionElement.textContent = `${position + 1}/${totalImages}`;
        }
    }

    function preloadAllImages() {
        @foreach($materials as $material)
            @if($material->images->count() > 0)
                materialImages[{{ $material->id }}] = [
                    @foreach($material->images as $image)
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

    preloadAllImages();
    
    @foreach($materials as $material)
        @if($material->images->count() > 1)
            imagePositions[{{ $material->id }}] = 0;
        @endif
    @endforeach

    const categorySelect = document.getElementById('category');
    const sortSelect = document.getElementById('sort');
    
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }
});

const materialImages = {};
</script>
@endpush