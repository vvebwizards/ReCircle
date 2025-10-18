@extends('layouts.admin')

@section('title','Materials')

@section('admin-content')
<div class="admin-topbar">
  <div>
    <h1>Materials</h1>
    <div class="tb-sub">Manage all recycled materials</div>
  </div>
  <div class="tb-right"></div>
</div>

<div class="filters-row" style="margin:1rem 0;display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
  <select id="am-category" class="a-select" style="min-width:140px">
    <option value="">All Categories</option>
    <option value="wood">Wood</option>
    <option value="metal">Metal</option>
    <option value="plastic">Plastic</option>
    <option value="textile">Textile</option>
    <option value="electronic">Electronic</option>
    <option value="glass">Glass</option>
    <option value="paper">Paper</option>
  </select>
  <select id="am-unit" class="a-select" style="min-width:120px">
    <option value="">All Units</option>
    <option value="kg">kg</option>
    <option value="pcs">pcs</option>
    <option value="m2">m²</option>
    <option value="l">l</option>
  </select>
  <select id="am-sort" class="a-select" style="min-width:140px">
    <option value="newest">Newest</option>
    <option value="oldest">Oldest</option>
    <option value="name_asc">Name A→Z</option>
    <option value="name_desc">Name Z→A</option>
    <option value="quantity_desc">Quantity (High)</option>
    <option value="quantity_asc">Quantity (Low)</option>
  </select>
  <div class="tb-search"><input id="am-search" type="search" placeholder="Search name..." aria-label="Search materials by name" /><button type="button" class="clear-search" id="am-search-clear" aria-label="Clear search">&times;</button></div>
  <div style="margin-left:auto;font-size:.875rem;color:#64748b" id="am-meta"></div>
</div>

<div class="a-card wide">
  <div class="a-title"><i class="fa-solid fa-recycle"></i> Materials</div>
  <table class="a-table" id="am-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Category</th>
        <th>Quantity</th>
        <th>Unit</th>
        <th>Recyclability</th>
        <th>Maker</th>
        <th>Products</th>
        <th>Created</th>
        <th></th>
      </tr>
    </thead>
    <tbody id="am-tbody">
      @foreach($materials as $material)
        <tr data-id="{{ $material->id }}">
          <td>{{ $material->id }}</td>
          <td>{{ $material->name }}</td>
          <td><span class="badge cat-{{ $material->category }}">{{ ucfirst($material->category) }}</span></td>
          <td>{{ $material->quantity }}</td>
          <td>{{ $material->unit }}</td>
          <td>
            <div class="score-bar">
              <div class="score-fill" style="width: {{ $material->recyclability_score }}%"></div>
              <span class="score-text">{{ $material->recyclability_score }}%</span>
            </div>
          </td>
          <td>{{ $material->maker->name ?? '—' }}</td>
          <td>{{ $material->products->count() }}</td>
          <td>{{ $material->created_at?->diffForHumans() }}</td>
          <td class="actions">
            <div class="action-group">
              <button class="icon-btn view is-blue" data-action="view" data-view data-tooltip="View" aria-label="View material">
                <i class="fa-solid fa-eye"></i>
                <span class="sr-only">View</span>
              </button>
              <button class="icon-btn edit is-green" data-action="edit" data-edit data-tooltip="Edit" aria-label="Edit material">
                <i class="fa-solid fa-pen"></i>
                <span class="sr-only">Edit</span>
              </button>
              <button class="icon-btn delete is-red" data-action="delete" data-delete data-tooltip="Delete" aria-label="Delete material">
                <i class="fa-solid fa-trash"></i>
                <span class="sr-only">Delete</span>
              </button>
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
  <div id="am-pagination" class="pagination" style="padding:.75rem 1rem;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
    <button id="am-prev" class="pg-btn" disabled>Prev</button>
    <div id="am-pageinfo" style="font-size:.75rem;letter-spacing:.5px;text-transform:uppercase"></div>
    <button id="am-next" class="pg-btn" disabled>Next</button>
  </div>
</div>

@push('admin-modals')
<div class="modal-overlay hidden" id="am-modal-overlay" aria-hidden="true">
  <div class="modal hidden" id="am-view-modal" role="dialog" aria-modal="true" aria-labelledby="am-view-title">
    <div class="modal-header minimal">
      <h3 class="modal-title" id="am-view-title"><i class="fa-solid fa-eye"></i> <span>Material</span></h3>
      <button class="modal-close" data-close aria-label="Close view"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="am-view-body">
      <div id="am-view-loading" class="modal-skeleton">
        <div class="sk-header-line" style="width:55%;height:14px;"></div>
        <div class="sk-pills" style="display:flex;gap:.4rem;margin:.6rem 0 1rem;">
          <div class="sk-pill" style="width:70px;height:20px;"></div>
          <div class="sk-pill" style="width:55px;height:20px;"></div>
          <div class="sk-pill" style="width:40px;height:20px;"></div>
        </div>
        <div class="sk-panels" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.9rem;">
          <div class="sk-box" style="height:140px;"></div>
          <div class="sk-box" style="height:140px;"></div>
        </div>
      </div>
      <div id="am-view-error" class="hidden" style="text-align:center;padding:2rem 1rem;">
        <p style="margin:0 0 .75rem;font-size:.8rem;color:var(--wi-gray-700);"><i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;margin-right:.4rem;"></i>Failed to load material.</p>
        <button type="button" class="btn btn-secondary" data-close>Close</button>
      </div>
      <div id="am-view-content" class="hidden am-view-wrap">
        <div class="am-view-head">
          <h2 id="am-vh-name" class="am-view-title">—</h2>
          <div id="am-vh-meta" class="am-pill-row"></div>
        </div>
        
        <div class="am-section am-images-block">
          <div class="am-section-head">
            <span class="am-icon-badge"><i class="fa-solid fa-image"></i></span>
            <span class="am-section-label">Images</span>
            <div class="am-section-spacer"></div>
            <span id="am-view-img-count" class="am-count-pill">0</span>
            <button type="button" id="am-view-open-lightbox" class="am-btn-ghost-sm" style="display:none"><i class="fa-solid fa-images"></i><span>See all</span></button>
          </div>
          <div id="am-view-images" class="am-image-strip"></div>
        </div>

        <div class="am-panels-grid">
          <div class="am-panel">
            <div class="am-panel-head"><span class="am-icon-badge"><i class="fa-solid fa-circle-info"></i></span><span class="am-panel-title">Details</span></div>
            <dl id="am-view-details" class="am-dl"></dl>
          </div>
          
          <div class="am-panel">
            <div class="am-panel-head"><span class="am-icon-badge"><i class="fa-solid fa-chart-line"></i></span><span class="am-panel-title">Environmental Impact</span></div>
            <dl id="am-view-impact" class="am-dl"></dl>
          </div>
          
          <div class="am-panel">
            <div class="am-panel-head"><span class="am-icon-badge"><i class="fa-solid fa-cube"></i></span><span class="am-panel-title">Used in Products</span></div>
            <div id="am-view-products" class="am-products-list">—</div>
          </div>
          
          <div class="am-panel">
            <div class="am-panel-head"><span class="am-icon-badge"><i class="fa-solid fa-note-sticky"></i></span><span class="am-panel-title">Description</span></div>
            <div id="am-view-description" class="am-notes">—</div>
          </div>
        </div>
        
        <div class="am-actions-row">
          <button class="am-btn ghost" data-close type="button">Close</button>
          <button class="am-btn primary" id="am-open-edit" type="button"><i class="fa-solid fa-pen-to-square"></i><span>Edit</span></button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal hidden" id="am-edit-modal" role="dialog" aria-modal="true" aria-labelledby="am-edit-title-h">
    <div class="modal-header minimal">
      <h3 class="modal-title" id="am-edit-title-h"><i class="fa-solid fa-pen-to-square"></i> <span>Edit Material</span></h3>
      <button class="modal-close" data-close aria-label="Close edit"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body am-edit-body">
      <form id="am-edit-form" enctype="multipart/form-data" class="am-edit-form">
        <input type="hidden" name="keep_images" id="am-edit-keep" />
        <input type="hidden" name="remove_images" id="am-edit-remove" />
        
        <div class="am-edit-grid">
          <div class="am-field">
            <label for="am-edit-name" class="am-field-label">Name *</label>
            <input type="text" name="name" id="am-edit-name" required class="am-input" />
          </div>
          
          <div class="am-field">
            <label for="am-edit-category" class="am-field-label">Category *</label>
            <select name="category" id="am-edit-category" required class="am-input">
              <option value="wood">Wood</option>
              <option value="metal">Metal</option>
              <option value="plastic">Plastic</option>
              <option value="textile">Textile</option>
              <option value="electronic">Electronic</option>
              <option value="glass">Glass</option>
              <option value="paper">Paper</option>
            </select>
          </div>
          
          <div class="am-field">
            <label for="am-edit-quantity" class="am-field-label">Quantity *</label>
            <input type="number" step="0.01" min="0" name="quantity" id="am-edit-quantity" required class="am-input" />
          </div>
          
          <div class="am-field">
            <label for="am-edit-unit" class="am-field-label">Unit *</label>
            <select name="unit" id="am-edit-unit" required class="am-input">
              <option value="kg">kg</option>
              <option value="pcs">pcs</option>
              <option value="m2">m²</option>
              <option value="l">l</option>
            </select>
          </div>
          
          <div class="am-field">
            <label for="am-edit-recyclability" class="am-field-label">Recyclability Score (%)</label>
            <input type="number" min="0" max="100" name="recyclability_score" id="am-edit-recyclability" class="am-input" />
            <div class="score-slider-container">
              <input type="range" min="0" max="100" value="0" class="score-slider" id="am-edit-recyclability-slider">
              <div class="score-labels">
                <span>0%</span>
                <span>100%</span>
              </div>
            </div>
          </div>
          
          <div class="am-field">
            <label for="am-edit-maker" class="am-field-label">Maker</label>
            <select name="maker_id" id="am-edit-maker" class="am-input">
              <option value="">Select Maker</option>
              @foreach($makers as $maker)
                <option value="{{ $maker->id }}">{{ $maker->name }}</option>
              @endforeach
            </select>
          </div>
          
          <div class="am-field am-description-field">
            <label for="am-edit-description" class="am-field-label">Description</label>
            <textarea name="description" id="am-edit-description" rows="4" class="am-input am-textarea"></textarea>
          </div>
        </div>
        
        <div class="am-edit-images">
          <div class="am-section-head" style="margin-bottom:.55rem;">
            <span class="am-icon-badge"><i class="fa-solid fa-images"></i></span>
            <span class="am-section-label">Images</span>
          </div>
          <div id="am-edit-existing" class="am-edit-existing"></div>
          <div class="am-edit-upload-row">
            <label class="upload-tile am-upload-tile">
              <input type="file" name="new_images[]" id="am-edit-new" multiple hidden accept="image/*" />
              <span><i class="fa-solid fa-upload"></i> Add Images</span>
            </label>
            <div id="am-edit-new-previews" class="am-edit-new-previews"></div>
          </div>
        </div>
        
        <div class="am-actions-row">
          <button type="button" class="am-btn ghost" data-close>Cancel</button>
          <button type="submit" class="am-btn primary"><i class="fa-solid fa-floppy-disk"></i><span>Save Changes</span></button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal hidden confirm-box" id="am-delete-modal" role="alertdialog" aria-modal="true" aria-labelledby="am-delete-title">
    <div class="modal-header">
      <h3 class="modal-title" id="am-delete-title"><i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;"></i> <span>Delete Material</span></h3>
      <button class="modal-close" data-close aria-label="Close delete"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <p id="am-delete-text">Are you sure you want to delete this material? This action cannot be undone.</p>
      <div class="modal-actions">
        <button class="btn btn-secondary" data-close type="button">Cancel</button>
        <button class="btn btn-danger" id="am-delete-confirm" type="button"><i class="fa-solid fa-trash"></i> Delete</button>
      </div>
    </div>
  </div>

  <div class="modal hidden" id="am-photos-modal" role="dialog" aria-modal="true" aria-labelledby="am-photos-title" style="max-width:900px;">
    <div class="modal-header minimal">
      <h3 class="modal-title" id="am-photos-title"><i class="fa-solid fa-images"></i> <span>Material Photos</span></h3>
      <button class="modal-close" data-close aria-label="Close photos"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div id="am-photos-loader" class="photos-loader" style="text-align:center;padding:2rem 0;">
        <div class="spinner" style="width:32px;height:32px;border:4px solid var(--wi-gray-300);border-top-color:var(--wi-accent-600);border-radius:50%;margin:0 auto;animation:spin .9s linear infinite"></div>
        <p style="font-size:.7rem;color:var(--wi-gray-600);margin-top:.75rem;letter-spacing:.05em;">Loading photos…</p>
      </div>
      <div id="am-photos-error" class="hidden" style="text-align:center;padding:2rem 0;">
        <p style="font-size:.75rem;color:var(--wi-gray-700);"><i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;margin-right:.35rem;"></i><span>Failed to load photos.</span></p>
      </div>
      <div id="am-photos-main-wrap" class="hidden lb-main-wrap">
        <div class="lb-image-area" style="position:relative;">
          <button type="button" class="lb-nav prev" aria-label="Previous image">&#10094;</button>
          <img id="am-photos-main-image" src="" alt="Material photo" class="hidden" style="max-height:420px;object-fit:contain;width:100%;background:#fff;border:1px solid var(--wi-gray-200);border-radius:10px;" />
          <button type="button" class="lb-nav next" aria-label="Next image">&#10095;</button>
        </div>
        <div id="am-photos-caption" class="lb-caption" style="font-size:.65rem;text-align:center;margin-top:.55rem;color:var(--wi-gray-700);"></div>
        <div id="am-photos-thumbs" class="lb-thumbs" style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.7rem;max-height:140px;overflow:auto;"></div>
      </div>
    </div>
  </div>
</div>
@endpush

@push('admin-head')
  @vite(['resources/css/admin.css','resources/css/admin-listings.css','resources/css/waste-items.css','resources/css/admin-materials.css'])
@endpush
@push('admin-scripts')
  @vite(['resources/js/admin-materials.js'])
@endpush
@stack('admin-modals')
@endsection