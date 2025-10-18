@extends('layouts.app')

@push('head')
@vite([
    'resources/css/materials.css',
    'resources/css/material-create.css',
    'resources/css/waste-items.css',
    'resources/js/waste-item-create.js',
    'resources/js/waste-items.js',
    'resources/js/waste-items-filters.js'
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

        <div class="filters-section modern">
            <form action="{{ route('generator.waste-items.index') }}" method="GET" id="filterForm" class="filter-toolbar" novalidate data-ajax="true">
                <div class="ft-row">
                    <div class="ft-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" id="search" placeholder="Search waste items..." value="{{ request('search') }}" autocomplete="off" />
                        @if(request('search'))
                            <button type="button" class="clear-search" aria-label="Clear search" title="Clear search">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        @endif
                    </div>
                    <div class="ft-select">
                        <label for="tag" class="sr-only">Filter by Tag</label>
                        <i class="fa-solid fa-tag"></i>
                        <select name="tag" id="tag" class="modern-select">
                            <option value="">All Tags</option>
                            @foreach(\App\Models\Tag::orderBy('display_name')->get() as $tag)
                                <option value="{{ $tag->name }}" {{ request('tag') == $tag->name ? 'selected' : '' }}>#{{ $tag->display_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ft-select">
                        <label for="condition" class="sr-only">Filter by Condition</label>
                        <i class="fa-solid fa-filter-circle-xmark"></i>
                        <select name="condition" id="condition" class="modern-select">
                            <option value="">All Conditions</option>
                            <option value="good" {{ request('condition') === 'good' ? 'selected' : '' }} data-icon="check-circle">
                                Good
                            </option>
                            <option value="fixable" {{ request('condition') === 'fixable' ? 'selected' : '' }} data-icon="wrench">
                                Fixable
                            </option>
                            <option value="scrap" {{ request('condition') === 'scrap' ? 'selected' : '' }} data-icon="recycle">
                                Scrap
                            </option>
                        </select>
                    </div>
                    <div class="ft-select">
                        <label for="sort" class="sr-only">Sort Items</label>
                        <i class="fa-solid fa-arrow-up-wide-short"></i>
                        <select name="sort" id="sort" class="modern-select">
                            <option value="newest" {{ request('sort')=='newest' ? 'selected' : '' }} data-icon="clock">
                                Newest First
                            </option>
                            <option value="oldest" {{ request('sort')=='oldest' ? 'selected' : '' }} data-icon="clock-rotate-left">
                                Oldest First
                            </option>
                            <option value="title_asc" {{ request('sort')=='title_asc' ? 'selected' : '' }} data-icon="arrow-down-a-z">
                                Title A-Z
                            </option>
                            <option value="title_desc" {{ request('sort')=='title_desc' ? 'selected' : '' }} data-icon="arrow-down-z-a">
                                Title Z-A
                            </option>
                        </select>
                    </div>
                    <div class="ft-actions">
                        <button type="reset" id="filtersReset" class="ft-btn ghost" title="Reset all filters">
                            <i class="fa-solid fa-rotate"></i>
                            <span class="txt">Reset Filters</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-box-archive"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-number">{{ $total }}</h3>
                    <p class="stat-label">Total Items</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-weight-scale"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-number">{{ number_format($avgWeight,2) }}</h3>
                    <p class="stat-label">Avg Weight (kg)</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-number">{{ $conditionsCount }}</h3>
                    <p class="stat-label">Conditions</p>
                </div>
            </div>
        </div>

        @include('generator.partials.waste_items_grid')

        @if(session('object_detection_debug'))
            <div class="debug-panel">
                <h3>Object Detection Debug</h3>
                <pre style="white-space:pre-wrap;background:#111;color:#0f0;padding:1rem;border-radius:6px;">{{ json_encode(session('object_detection_debug'), JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif

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
                            <input type="text" name="title" class="w-full mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="e.g., Mixed Plastic Batch">
                            <small class="error-text inline" data-error-for="title" style="display:none;"></small>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Condition *</label>
                            <select name="condition" class="w-full mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="good">Good</option>
                                <option value="fixable">Fixable</option>
                                <option value="scrap">Scrap</option>
                            </select>
                            <small class="error-text inline" data-error-for="condition" style="display:none;"></small>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Weight (kg) *</label>
                            <input type="number" step="0.01" min="0" name="estimated_weight" class="w-full mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="0.00">
                            <small class="error-text inline" data-error-for="estimated_weight" style="display:none;"></small>
                        </div>
                        <div class="full">
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Location (Lat / Lng) *</label>
                            <div style="display:flex;gap:.5rem;">
                                <input type="number" step="0.000001" name="location[lat]" placeholder="Latitude" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                <input type="number" step="0.000001" name="location[lng]" placeholder="Longitude" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                            </div>
                            <small class="error-text inline" data-error-for="location.lat" style="display:none;"></small>
                            <small class="error-text inline" data-error-for="location.lng" style="display:none;"></small>
                        </div>
                        <div class="full">
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Notes</label>
                            <textarea name="notes" rows="3" class="w-full mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Additional details..."></textarea>
                            <small class="error-text inline" data-error-for="notes" style="display:none;"></small>
                        </div>
                        <div class="full">
                            <label class="block text-xs font-semibold tracking-wide uppercase text-gray-600">Images *</label>
                            <div id="imageDropzone" class="image-dropzone" tabindex="0" role="button" aria-label="Upload images">
                                <p class="dz-instructions"><i class="fa-solid fa-cloud-arrow-up"></i> Drag & drop images here or <span class="link">browse</span><br><small>Up to 10 images, max 2MB each</small></p>
                                <input type="file" id="images" name="images[]" multiple accept="image/*" hidden>
                            </div>
                            <div id="imagePreviewList" class="image-preview-list"></div>
                            <small class="error-text inline" data-error-for="images" style="display:none;"></small>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" data-close="createModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="createSubmitBtn"><i class="fa-solid fa-save"></i> Create</button>
                    </div>
                </form>
            </div>
        </div>

        @include('generator.partials.waste_item_view_modal')
    @include('generator.partials.waste_item_photos_lightbox')

        <!-- EDIT MODAL -->
        <div class="modal hidden" id="editModal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
            <div class="modal-header minimal" style="justify-content:flex-end;">
                <h3 id="editModalTitle" class="sr-only">Edit Waste Item</h3>
                <button class="modal-close" data-close="editModal" aria-label="Close edit"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body edit-modal-body">
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
                    <div class="modal-actions edit-actions">
                        <button type="button" class="btn btn-secondary" data-close="editModal">Cancel</button>
                        <button type="submit" class="btn btn-primary edit-primary" id="editSubmitBtn"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
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
