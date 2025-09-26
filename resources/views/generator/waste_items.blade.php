@extends('layouts.app')

@push('head')
@vite([
    'resources/css/materials.css',
    'resources/css/material-create.css',
    'resources/css/waste-items.css',
    'resources/js/waste-item-create.js',
    'resources/js/waste-items.js'
])
@endpush

@section('content')
<div class="materials-container">
    <div class="container">
        <div class="materials-header">
            <div>
                <h1 class="materials-title">
                    <i class="fa-solid fa-trash-can"></i> My Waste Items
                </h1>
                <p>Manage waste items you've generated</p>
            </div>
            <a href="{{ route('generator.waste-items.create') }}" class="btn-create open-create-modal">
                <i class="fa-solid fa-plus"></i> New Waste Item
            </a>
        </div>

        <div class="filters-section">
            <form action="{{ route('generator.waste-items.index') }}" method="GET" id="filterForm">
                <div class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label" for="search">Search</label>
                        <input type="text" name="search" id="search" class="filter-input" placeholder="Search waste items..." value="{{ request('search') }}">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="condition">Condition</label>
                        <select name="condition" id="condition" class="filter-select">
                            <option value="">All</option>
                            @foreach(['good','fixable','scrap'] as $c)
                                <option value="{{ $c }}" {{ request('condition') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label" for="sort">Sort By</label>
                        <select name="sort" id="sort" class="filter-select">
                            <option value="newest" {{ request('sort')=='newest' ? 'selected' : '' }}>Newest</option>
                            <option value="oldest" {{ request('sort')=='oldest' ? 'selected' : '' }}>Oldest</option>
                            <option value="title_asc" {{ request('sort')=='title_asc' ? 'selected' : '' }}>Title A-Z</option>
                            <option value="title_desc" {{ request('sort')=='title_desc' ? 'selected' : '' }}>Title Z-A</option>
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
                <h3 class="stat-number">{{ $total }}</h3>
                <p class="stat-label">Total Items</p>
            </div>
            <div class="stat-card">
                <h3 class="stat-number">{{ number_format($avgWeight,2) }}</h3>
                <p class="stat-label">Avg Weight</p>
            </div>
            <div class="stat-card">
                <h3 class="stat-number">{{ $conditionsCount->count() }}</h3>
                <p class="stat-label">Conditions</p>
            </div>
        </div>

        <div class="materials-grid">
            @forelse($wasteItems as $item)
                <div class="material-card" data-id="{{ $item->id }}">
                    @php 
                        $primary = $item->primary_image_url ?? null;
                        $src = $primary; 
                    @endphp
                    <div class="material-image-wrapper" style="height:140px;overflow:hidden;position:relative;border-radius:4px 4px 0 0;background:#f5f5f5;display:flex;align-items:center;justify-content:center;">
                        @php $photosCount = $item->photos->count(); @endphp
                        <div class="card-img-count" style="position:absolute;top:4px;left:4px;background:rgba(0,0,0,.55);color:#fff;font-size:10px;padding:2px 4px;border-radius:3px;z-index:2;">imgs: <span class="count-val">{{ $photosCount }}</span></div>
                        @if($src)
                            <img src="{{ $src }}" alt="{{ $item->title }}" class="card-primary-img" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='https://via.placeholder.com/400x240?text=Image';">
                        @else
                            <div class="card-primary-fallback" style="font-size:0.85rem;color:#888;display:flex;flex-direction:column;align-items:center;gap:0.25rem;">
                                <i class="fa-solid fa-image" style="font-size:1.4rem;"></i>
                                <span>No Image</span>
                            </div>
                        @endif
                    </div>
                    <div class="material-content" style="padding-top:0.8rem;">
                        <div class="material-header">
                            <h3 class="material-name">{{ $item->title }}</h3>
                            <span class="material-badge">{{ ucfirst($item->condition) }}</span>
                        </div>
                        <div class="material-meta">
                            <div class="meta-item">
                                <i class="fa-solid fa-weight-hanging meta-icon"></i>
                                <span>{{ $item->estimated_weight ?? '—' }} kg</span>
                            </div>
                            <div class="meta-item">
                                <i class="fa-solid fa-calendar meta-icon"></i>
                                <span>{{ $item->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        <p class="material-description">{{ Str::limit($item->notes, 100) }}</p>
                        <div class="material-actions" data-id="{{ $item->id }}">
                            <a href="#" class="btn-action btn-view" data-id="{{ $item->id }}"><i class="fa-solid fa-eye"></i> View</a>
                            <a href="#" class="btn-action btn-edit" data-id="{{ $item->id }}"><i class="fa-solid fa-edit"></i> Edit</a>
                            <form action="#" method="POST" style="display:inline;" class="delete-form" data-id="{{ $item->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn-action btn-delete" data-id="{{ $item->id }}"><i class="fa-solid fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon"><i class="fa-solid fa-trash"></i></div>
                    <h3 herf class="empty-text">No waste items found</h3>
                    <p>Create your first waste item.</p>
                    <a href="#" class="btn-create open-create-modal" style="display:inline-flex;margin-top:1rem;"><i class="fa-solid fa-plus"></i> Create Waste Item</a>
                </div>
            @endforelse
        </div>

        @if($wasteItems->hasPages())
            <div class="pagination">
                @if($wasteItems->onFirstPage())
                    <span class="page-link disabled">&laquo; Previous</span>
                @else
                    <a href="{{ $wasteItems->previousPageUrl() }}" class="page-link">&laquo; Previous</a>
                @endif
                @foreach($wasteItems->getUrlRange(1, $wasteItems->lastPage()) as $page => $url)
                    @if($page == $wasteItems->currentPage())
                        <span class="page-link active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                    @endif
                @endforeach
                @if($wasteItems->hasMorePages())
                    <a href="{{ $wasteItems->nextPageUrl() }}" class="page-link">Next &raquo;</a>
                @else
                    <span class="page-link disabled">Next &raquo;</span>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

    @push('modals')
    <div class="modal-overlay" id="modalOverlay" aria-hidden="true">
        <!-- CREATE MODAL -->
        <div class="modal hidden" id="createModal" role="dialog" aria-modal="true" aria-labelledby="createModalTitle">
            <div class="modal-header">
                <h3 class="modal-title" id="createModalTitle"><i class="fa-solid fa-circle-plus"></i> <span>Create Waste Item</span></h3>
                <button class="modal-close" data-close="createModal" aria-label="Close create"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="createWasteItemForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-grid">
                        <div class="full">
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Title *</label>
                            <input type="text" name="title" class="w-full mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required placeholder="e.g., Mixed Plastic Batch">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Condition *</label>
                            <select name="condition" class="w-full mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                                <option value="good">Good</option>
                                <option value="fixable">Fixable</option>
                                <option value="scrap">Scrap</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Weight (kg)</label>
                            <input type="number" step="0.01" min="0" name="estimated_weight" class="w-full mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="0.00">
                        </div>
                        <div class="full">
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Location (Lat / Lng)</label>
                            <div style="display:flex;gap:.5rem;">
                                <input type="number" step="0.000001" name="location[lat]" placeholder="Latitude" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                <input type="number" step="0.000001" name="location[lng]" placeholder="Longitude" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                            </div>
                        </div>
                        <div class="full">
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Notes</label>
                            <textarea name="notes" rows="3" class="w-full mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Additional details..."></textarea>
                        </div>
                        <div class="full">
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Images</label>
                            <div id="imageDropzone" class="image-dropzone" tabindex="0" role="button" aria-label="Upload images">
                                <p class="dz-instructions"><i class="fa-solid fa-cloud-arrow-up"></i> Drag & drop images here or <span class="link">browse</span><br><small>Up to 10 images, max 2MB each</small></p>
                                <input type="file" id="images" name="images[]" multiple accept="image/*" hidden>
                            </div>
                            <div id="imagePreviewList" class="image-preview-list"></div>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" data-close="createModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fa-solid fa-save"></i> Create</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- VIEW MODAL -->
        <div class="modal hidden" id="viewModal" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
            <div class="modal-header">
                <h3 class="modal-title" id="viewModalTitle"><i class="fa-solid fa-eye"></i> <span class="title-text">View Waste Item</span></h3>
                <button class="modal-close" data-close="viewModal" aria-label="Close view"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div id="viewLoading" class="loading-spinner hidden"></div>
                <div id="viewContent" class="hidden">
                    <div class="wi-header-block" style="display:flex;flex-direction:column;gap:.4rem;margin-bottom:.75rem;">
                        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
                            <span class="badge" id="viewCondition">—</span>
                            <span class="wi-pill" id="viewWeight"></span>
                            <span class="wi-pill subtle" id="viewId"></span>
                            <span class="wi-pill geo" id="viewLocation"></span>
                        </div>
                        <h2 id="viewTitle" style="font-size:1.05rem;font-weight:600;color:#111827;letter-spacing:.015em;">—</h2>
                    </div>

                    <div class="wi-meta-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.65rem;margin-bottom:1rem;">
                        <div class="wi-meta-box"><span class="label">Created</span><span class="value" id="viewCreated">—</span></div>
                        <div class="wi-meta-box"><span class="label">Updated</span><span class="value" id="viewUpdated">—</span></div>
                        <div class="wi-meta-box"><span class="label">Materials</span><span class="value" id="viewMaterials">0</span></div>
                    </div>

                    <div class="wi-section">
                        <h4 class="wi-section-title">Notes</h4>
                        <p class="wi-notes" id="viewNotes" style="white-space:pre-line">—</p>
                    </div>

                    <div class="wi-section">
                        <h4 class="wi-section-title">Images</h4>
                        <div id="viewImages" class="gallery-grid"></div>
                    </div>
                    <div class="modal-actions">
                        <button class="btn btn-secondary" data-close="viewModal">Close</button>
                        <button class="btn btn-primary" id="openEditFromView"><i class="fa-solid fa-edit"></i> Edit</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div class="modal hidden" id="editModal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
            <div class="modal-header">
                <h3 class="modal-title" id="editModalTitle"><i class="fa-solid fa-pen-to-square"></i> <span>Edit Waste Item</span></h3>
                <button class="modal-close" data-close="editModal" aria-label="Close edit"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <div class="form-grid">
                        <div class="full">
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Title</label>
                            <input type="text" name="title" id="editTitle" class="w-full mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Condition</label>
                            <select name="condition" id="editCondition" class="w-full mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                                <option value="good">Good</option>
                                <option value="fixable">Fixable</option>
                                <option value="scrap">Scrap</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Weight (kg)</label>
                            <input type="number" step="0.01" min="0" name="estimated_weight" id="editWeight" class="w-full mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div class="full">
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Location (Lat / Lng)</label>
                            <div style="display:flex;gap:.5rem;">
                                <input type="number" step="0.000001" name="location[lat]" id="editLocationLat" placeholder="Latitude" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                <input type="number" step="0.000001" name="location[lng]" id="editLocationLng" placeholder="Longitude" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                            </div>
                        </div>
                        <div class="full">
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Notes</label>
                            <textarea name="notes" id="editNotes" rows="4" class="w-full mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Notes..."></textarea>
                        </div>
                        <div class="full">
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Images</label>
                            <div id="editImagesExisting" class="image-preview-list" style="margin-bottom:.5rem;">
                                <!-- existing images injected by JS -->
                            </div>
                            <div class="edit-image-add">
                                <input type="file" id="editNewImages" name="new_images[]" multiple accept="image/*" hidden>
                                <button type="button" class="btn btn-secondary btn-sm" id="editAddImagesBtn"><i class="fa-solid fa-image"></i> Add Images</button>
                                <small class="block text-gray-500 mt-1">You can add up to 10 images total. Drag to reorder. First image is primary.</small>
                            </div>
                            <input type="hidden" name="keep_images" id="editKeepImages"> <!-- CSV of existing image IDs in order -->
                            <input type="hidden" name="remove_images" id="editRemoveImages"> <!-- CSV of removed image IDs -->
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" data-close="editModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="editSubmitBtn"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DELETE CONFIRM -->
        <div class="modal hidden confirm-box" id="deleteModal" role="alertdialog" aria-modal="true" aria-labelledby="deleteModalTitle">
            <div class="modal-header">
                <h3 class="modal-title" id="deleteModalTitle"><i class="fa-solid fa-triangle-exclamation text-red-600"></i> Confirm Delete</h3>
                <button class="modal-close" data-close="deleteModal" aria-label="Close delete"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <p class="text-sm text-gray-700" id="deleteMessage">Are you sure you want to delete this waste item?</p>
                <div class="modal-actions">
                    <button class="btn btn-secondary" data-close="deleteModal">Cancel</button>
                    <button class="btn btn-danger" id="confirmDeleteBtn"><i class="fa-solid fa-trash"></i> Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endpush

    @push('scripts')
    <script>
        window.wasteItemRoutes = {
            base: @json(url('/waste-items'))
        };
    </script>
    @endpush
