@extends('layouts.admin')

@section('title','Listings')

@section('admin-content')
<div class="admin-topbar">
  <div>
    <h1>Listings</h1>
    <div class="tb-sub">Manage all generator waste listings</div>
  </div>
  <div class="tb-right"></div>
</div>

<div class="filters-row" style="margin:1rem 0;display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
  <select id="al-condition" class="a-select" style="min-width:140px">
    <option value="">All Conditions</option>
    <option value="good">Good</option>
    <option value="fixable">Fixable</option>
    <option value="scrap">Scrap</option>
  </select>
  <select id="al-sort" class="a-select" style="min-width:140px">
    <option value="newest">Newest</option>
    <option value="oldest">Oldest</option>
    <option value="title_asc">Title A→Z</option>
    <option value="title_desc">Title Z→A</option>
  </select>
  <div class="tb-search"><input id="al-search" type="search" placeholder="Search title..." aria-label="Search listings by title" /><button type="button" class="clear-search" id="al-search-clear" aria-label="Clear search">&times;</button></div>
  <div style="margin-left:auto;font-size:.875rem;color:#64748b" id="al-meta"></div>
</div>

<div class="a-card wide">
  <div class="a-title"><i class="fa-solid fa-recycle"></i> Listings</div>
  <table class="a-table" id="al-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Condition</th>
        <th>Generator</th>
        <th>Images</th>
        <th>Created</th>
        <th></th>
      </tr>
    </thead>
    <tbody id="al-tbody">
      @foreach($items as $w)
        <tr data-id="{{ $w->id }}">
          <td>{{ $w->id }}</td>
          <td>{{ $w->title }}</td>
          <td><span class="badge cond-{{ $w->condition }}">{{ $w->condition }}</span></td>
          <td>{{ $w->generator->name ?? '—' }}</td>
          <td>{{ $w->photos->count() }}</td>
          <td>{{ $w->created_at?->diffForHumans() }}</td>
          <td class="actions">
            <div class="action-group">
              <button class="icon-btn view is-blue" data-action="view" data-view data-tooltip="View" aria-label="View listing">
                <i class="fa-solid fa-eye"></i>
                <span class="sr-only">View</span>
              </button>
              <button class="icon-btn edit is-green" data-action="edit" data-edit data-tooltip="Edit" aria-label="Edit listing">
                <i class="fa-solid fa-pen"></i>
                <span class="sr-only">Edit</span>
              </button>
              <button class="icon-btn delete is-red" data-action="delete" data-delete data-tooltip="Delete" aria-label="Delete listing">
                <i class="fa-solid fa-trash"></i>
                <span class="sr-only">Delete</span>
              </button>
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
  <div id="al-pagination" class="pagination" style="padding:.75rem 1rem;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
    <button id="al-prev" class="pg-btn" disabled>Prev</button>
    <div id="al-pageinfo" style="font-size:.75rem;letter-spacing:.5px;text-transform:uppercase"></div>
    <button id="al-next" class="pg-btn" disabled>Next</button>
  </div>
</div>

@push('admin-modals')
<div class="modal-overlay" id="al-modal-overlay" aria-hidden="true">
  <!-- View Modal (generator style parity with skeleton & error states) -->
  <div class="modal hidden" id="al-view-modal" role="dialog" aria-modal="true" aria-labelledby="al-view-title">
    <div class="modal-header minimal">
      <h3 class="modal-title" id="al-view-title"><i class="fa-solid fa-eye"></i> <span>Listing</span></h3>
      <button class="modal-close" data-close aria-label="Close view"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="al-view-body">
      <!-- Loading skeleton -->
      <div id="al-view-loading" class="modal-skeleton">
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
      <!-- Error -->
      <div id="al-view-error" class="hidden" style="text-align:center;padding:2rem 1rem;">
        <p style="margin:0 0 .75rem;font-size:.8rem;color:var(--wi-gray-700);"><i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;margin-right:.4rem;"></i>Failed to load listing.</p>
        <button type="button" class="btn btn-secondary" data-close>Close</button>
      </div>
      <!-- Content -->
      <div id="al-view-content" class="hidden al-view-wrap">
        <div class="al-view-head">
          <h2 id="al-vh-title" class="al-view-title">—</h2>
          <div id="al-vh-meta" class="al-pill-row"></div>
        </div>
        <div class="al-section al-images-block">
          <div class="al-section-head">
            <span class="al-icon-badge"><i class="fa-solid fa-image"></i></span>
            <span class="al-section-label">Images</span>
            <div class="al-section-spacer"></div>
            <span id="al-view-img-count" class="al-count-pill">0</span>
            <button type="button" id="al-view-open-lightbox" class="al-btn-ghost-sm" style="display:none"><i class="fa-solid fa-images"></i><span>See all</span></button>
          </div>
          <div id="al-view-images" class="al-image-strip"></div>
        </div>
        <div class="al-panels-grid">
          <div class="al-panel">
            <div class="al-panel-head"><span class="al-icon-badge"><i class="fa-solid fa-circle-info"></i></span><span class="al-panel-title">Summary</span></div>
            <dl id="al-view-summary" class="al-dl"></dl>
          </div>
          <div class="al-panel">
            <div class="al-panel-head"><span class="al-icon-badge"><i class="fa-solid fa-note-sticky"></i></span><span class="al-panel-title">Notes</span></div>
            <div id="al-view-notes" class="al-notes">—</div>
          </div>
        </div>
        <div class="al-actions-row">
          <button class="al-btn ghost" data-close type="button">Close</button>
          <button class="al-btn primary" id="al-open-edit" type="button"><i class="fa-solid fa-pen-to-square"></i><span>Edit</span></button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal (modernized) -->
  <div class="modal hidden" id="al-edit-modal" role="dialog" aria-modal="true" aria-labelledby="al-edit-title-h">
    <div class="modal-header minimal"><h3 class="modal-title" id="al-edit-title-h"><i class="fa-solid fa-pen-to-square"></i> <span>Edit Listing</span></h3><button class="modal-close" data-close aria-label="Close edit"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="modal-body al-edit-body">
      <form id="al-edit-form" enctype="multipart/form-data" class="al-edit-form">
        <input type="hidden" name="keep_images" id="al-edit-keep" />
        <input type="hidden" name="remove_images" id="al-edit-remove" />
        <div class="al-edit-grid">
          <div class="al-field">
            <label for="al-edit-title" class="al-field-label">Title</label>
            <input type="text" name="title" id="al-edit-title" required class="al-input" />
          </div>
          <div class="al-field">
            <label for="al-edit-condition" class="al-field-label">Condition</label>
            <select name="condition" id="al-edit-condition" required class="al-input">
              <option value="good">Good</option>
              <option value="fixable">Fixable</option>
              <option value="scrap">Scrap</option>
            </select>
          </div>
          <div class="al-field">
            <label for="al-edit-weight" class="al-field-label">Est. Weight (kg)</label>
            <input type="number" step="0.01" min="0" name="estimated_weight" id="al-edit-weight" class="al-input" />
          </div>
          <div class="al-field">
            <label for="al-edit-lat" class="al-field-label">Latitude</label>
            <input type="number" step="0.000001" name="location[lat]" id="al-edit-lat" class="al-input" />
          </div>
          <div class="al-field">
            <label for="al-edit-lng" class="al-field-label">Longitude</label>
            <input type="number" step="0.000001" name="location[lng]" id="al-edit-lng" class="al-input" />
          </div>
          <div class="al-field al-notes-field">
            <label for="al-edit-notes" class="al-field-label">Notes</label>
            <textarea name="notes" id="al-edit-notes" rows="3" class="al-input al-textarea"></textarea>
          </div>
        </div>
        <div class="al-edit-images">
          <div class="al-section-head" style="margin-bottom:.55rem;">
            <span class="al-icon-badge"><i class="fa-solid fa-images"></i></span>
            <span class="al-section-label">Images</span>
          </div>
          <div id="al-edit-existing" class="al-edit-existing"></div>
          <div class="al-edit-upload-row">
            <label class="upload-tile al-upload-tile">
              <input type="file" name="new_images[]" id="al-edit-new" multiple hidden accept="image/*" />
              <span><i class="fa-solid fa-upload"></i> Add Images</span>
            </label>
            <div id="al-edit-new-previews" class="al-edit-new-previews"></div>
          </div>
        </div>
        <div class="al-actions-row">
          <button type="button" class="al-btn ghost" data-close>Cancel</button>
            <button type="submit" class="al-btn primary"><i class="fa-solid fa-floppy-disk"></i><span>Save Changes</span></button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Modal -->
  <div class="modal hidden confirm-box" id="al-delete-modal" role="alertdialog" aria-modal="true" aria-labelledby="al-delete-title">
    <div class="modal-header"><h3 class="modal-title" id="al-delete-title"><i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;"></i> <span>Delete Listing</span></h3><button class="modal-close" data-close aria-label="Close delete"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="modal-body">
      <p id="al-delete-text">Are you sure?</p>
      <div class="modal-actions">
        <button class="btn btn-secondary" data-close type="button">Cancel</button>
        <button class="btn btn-danger" id="al-delete-confirm" type="button"><i class="fa-solid fa-trash"></i> Delete</button>
      </div>
    </div>
  </div>

  <!-- Photos Lightbox Modal (shared styling with generator) -->
  <div class="modal hidden" id="al-photos-modal" role="dialog" aria-modal="true" aria-labelledby="al-photos-title" style="max-width:900px;">
    <div class="modal-header minimal">
      <h3 class="modal-title" id="al-photos-title"><i class="fa-solid fa-images"></i> <span>Listing Photos</span></h3>
      <button class="modal-close" data-close aria-label="Close photos"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div id="al-photos-loader" class="photos-loader" style="text-align:center;padding:2rem 0;">
        <div class="spinner" style="width:32px;height:32px;border:4px solid var(--wi-gray-300);border-top-color:var(--wi-accent-600);border-radius:50%;margin:0 auto;animation:spin .9s linear infinite"></div>
        <p style="font-size:.7rem;color:var(--wi-gray-600);margin-top:.75rem;letter-spacing:.05em;">Loading photos…</p>
      </div>
      <div id="al-photos-error" class="hidden" style="text-align:center;padding:2rem 0;">
        <p style="font-size:.75rem;color:var(--wi-gray-700);"><i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;margin-right:.35rem;"></i><span>Failed to load photos.</span></p>
      </div>
      <div id="al-photos-main-wrap" class="hidden lb-main-wrap">
        <div class="lb-image-area" style="position:relative;">
          <button type="button" class="lb-nav prev" aria-label="Previous image">&#10094;</button>
          <img id="al-photos-main-image" src="" alt="Listing photo" class="hidden" style="max-height:420px;object-fit:contain;width:100%;background:#fff;border:1px solid var(--wi-gray-200);border-radius:10px;" />
          <button type="button" class="lb-nav next" aria-label="Next image">&#10095;</button>
        </div>
        <div id="al-photos-caption" class="lb-caption" style="font-size:.65rem;text-align:center;margin-top:.55rem;color:var(--wi-gray-700);"></div>
        <div id="al-photos-thumbs" class="lb-thumbs" style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.7rem;max-height:140px;overflow:auto;"></div>
      </div>
    </div>
  </div>
</div>
@endpush


@push('admin-head')
  @vite(['resources/css/admin.css','resources/css/waste-items.css','resources/css/admin-listings.css'])
@endpush
@push('admin-scripts')
  @vite(['resources/js/admin-listings.js'])
@endpush
@stack('admin-modals')
@endsection
