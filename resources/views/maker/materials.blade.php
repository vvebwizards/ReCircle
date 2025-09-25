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
                    <!-- Container d'image avec navigation -->
                    <div class="material-image-container" id="image-container-{{ $material->id }}">
                        @if($material->images->count() > 0)
                            <!-- Première image visible -->
                            <img src="{{ asset($material->images->first()->image_path) }}" 
                                 alt="{{ $material->name }}" 
                                 class="material-image"
                                 id="current-image-{{ $material->id }}">
                            
                            <!-- Boutons de navigation -->
                            @if($material->images->count() > 1)
                                <button class="image-nav-button image-nav-prev" 
                                        onclick="prevImage({{ $material->id }}, {{ $material->images->count() }})">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <button class="image-nav-button image-nav-next" 
                                        onclick="nextImage({{ $material->id }}, {{ $material->images->count() }})">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                                
                                <!-- Badge du nombre d'images -->
                                <div class="image-count-badge">
                                    <i class="fa-solid fa-images"></i> {{ $material->images->count() }}
                                </div>
                                
                                <!-- Indicateur de position -->
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
                            <a href="#" class="btn-action btn-edit">
                                <i class="fa-solid fa-edit"></i> Edit
                            </a>
                            <form action="#" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete" 
                                        onclick="return confirm('Delete this material?')">
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
@endsection

@push('scripts')
<script>
// Stockage des images par matériau
const materialImages = {};

// Charger les images d'un matériau
async function loadMaterialImages(materialId) {
    if (!materialImages[materialId]) {
        try {
            const response = await fetch(`/api/materials/${materialId}/images`);
            const data = await response.json();
            materialImages[materialId] = data.images;
        } catch (error) {
            console.error('Error loading images:', error);
            materialImages[materialId] = [];
        }
    }
    return materialImages[materialId];
}

// Navigation entre les images
async function nextImage(materialId, totalImages) {
    const images = await loadMaterialImages(materialId);
    if (!imagePositions[materialId]) {
        imagePositions[materialId] = 0;
    }
    
    imagePositions[materialId] = (imagePositions[materialId] + 1) % totalImages;
    await updateImage(materialId, totalImages, images);
}

async function prevImage(materialId, totalImages) {
    const images = await loadMaterialImages(materialId);
    if (!imagePositions[materialId]) {
        imagePositions[materialId] = 0;
    }
    
    imagePositions[materialId] = (imagePositions[materialId] - 1 + totalImages) % totalImages;
    await updateImage(materialId, totalImages, images);
}

async function updateImage(materialId, totalImages, images = null) {
    if (!images) {
        images = await loadMaterialImages(materialId);
    }
    
    const position = imagePositions[materialId] || 0;
    const imageElement = document.getElementById(`current-image-${materialId}`);
    const positionElement = document.getElementById(`position-${materialId}`);
    
    if (images[position] && imageElement) {
        imageElement.src = images[position].path;
        imageElement.alt = `Material image ${position + 1}`;
    }
    
    if (positionElement) {
        positionElement.textContent = `${position + 1}/${totalImages}`;
    }
}

// Alternative plus simple : Précharger toutes les images au chargement de la page
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

// Version simplifiée sans API call
function nextImageSimple(materialId, totalImages) {
    if (!imagePositions[materialId]) {
        imagePositions[materialId] = 0;
    }
    
    imagePositions[materialId] = (imagePositions[materialId] + 1) % totalImages;
    updateImageSimple(materialId, totalImages);
}

function prevImageSimple(materialId, totalImages) {
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
    
    // Les images sont déjà préchargées dans materialImages
    const images = materialImages[materialId];
    if (images && images[position] && imageElement) {
        imageElement.src = images[position].path;
    }
    
    if (positionElement) {
        positionElement.textContent = `${position + 1}/${totalImages}`;
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Précharger les images au chargement
    preloadAllImages();
    
    // Initialiser les positions
    @foreach($materials as $material)
        @if($material->images->count() > 1)
            imagePositions[{{ $material->id }}] = 0;
        @endif
    @endforeach
    
    // Filtrage automatique
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

const imagePositions = {};
</script>
@endpush