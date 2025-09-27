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

<div class="a-card" style="padding:0">
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
            <button class="tbl-btn" data-view title="View" aria-label="View listing"><i class="fa-regular fa-eye"></i></button>
            <button class="tbl-btn" data-edit title="Edit" aria-label="Edit listing"><i class="fa-regular fa-pen-to-square"></i></button>
            <button class="tbl-btn danger" data-delete title="Delete" aria-label="Delete listing"><i class="fa-regular fa-trash-can"></i></button>
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
  <!-- View Modal (generator style) -->
  <div class="modal hidden" id="al-view-modal" role="dialog" aria-modal="true" aria-labelledby="al-view-title">
    <div class="modal-header">
      <h3 class="modal-title" id="al-view-title"><i class="fa-solid fa-eye"></i> <span>View Listing</span></h3>
      <button class="modal-close" data-close aria-label="Close view"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="al-view-body">
      <div class="wi-header-block" style="display:flex;flex-direction:column;gap:.35rem;margin-bottom:.9rem;">
        <h2 id="al-vh-title" style="font-size:1.05rem;font-weight:600;color:#111827;letter-spacing:.01em;margin:0;">—</h2>
        <div id="al-vh-meta" style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;"></div>
      </div>
      <div class="wi-detail-layout" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.05rem;align-items:start;margin-bottom:1.1rem;">
        <div class="wi-panel" style="background:var(--wi-gray-50);border:1px solid var(--wi-gray-200);padding:.85rem .9rem;border-radius:14px;display:flex;flex-direction:column;gap:.65rem;">
          <div>
            <h4 style="margin:0 0 .55rem;font-size:.6rem;letter-spacing:.12em;font-weight:700;text-transform:uppercase;color:var(--wi-gray-600);">Summary</h4>
            <dl id="al-view-summary" style="margin:0;display:grid;grid-template-columns:auto 1fr;row-gap:.4rem;column-gap:.65rem;font-size:.68rem;color:var(--wi-gray-700);"></dl>
          </div>
          <div>
            <h4 style="margin:0 0 .55rem;font-size:.6rem;letter-spacing:.12em;font-weight:700;text-transform:uppercase;color:var(--wi-gray-600);">Notes</h4>
            <div id="al-view-notes" style="white-space:pre-line;font-size:.68rem;line-height:1.35;min-height:40px;color:var(--wi-gray-800);">—</div>
          </div>
        </div>
        <div class="wi-panel" style="background:var(--wi-gray-50);border:1px solid var(--wi-gray-200);padding:.85rem .9rem;border-radius:14px;display:flex;flex-direction:column;gap:.55rem;">
          <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
            <h4 style="margin:0;font-size:.6rem;letter-spacing:.12em;font-weight:700;text-transform:uppercase;color:var(--wi-gray-600);">Images</h4>
            <span id="al-view-img-count" style="background:var(--wi-gray-200);color:var(--wi-gray-700);font-size:.55rem;padding:.25rem .55rem;border-radius:999px;font-weight:600;letter-spacing:.05em;">0</span>
          </div>
          <div id="al-view-images" class="gallery-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:.55rem;"></div>
        </div>
      </div>
      <div class="modal-actions">
        <button class="btn btn-secondary" data-close type="button">Close</button>
        <button class="btn btn-primary" id="al-open-edit" type="button"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal hidden" id="al-edit-modal" role="dialog" aria-modal="true" aria-labelledby="al-edit-title-h">
    <div class="modal-header"><h3 class="modal-title" id="al-edit-title-h"><i class="fa-solid fa-pen-to-square"></i> <span>Edit Listing</span></h3><button class="modal-close" data-close aria-label="Close edit"><i class="fa-solid fa-xmark"></i></button></div>
    <div class="modal-body">
      <form id="al-edit-form" enctype="multipart/form-data">
        <input type="hidden" name="keep_images" id="al-edit-keep" />
        <input type="hidden" name="remove_images" id="al-edit-remove" />
        <div class="form-grid">
          <label>Title
            <input type="text" name="title" id="al-edit-title" required />
          </label>
          <label>Condition
            <select name="condition" id="al-edit-condition" required>
              <option value="good">Good</option>
              <option value="fixable">Fixable</option>
              <option value="scrap">Scrap</option>
            </select>
          </label>
          <label>Est. Weight (kg)
            <input type="number" step="0.01" min="0" name="estimated_weight" id="al-edit-weight" />
          </label>
          <label>Latitude
            <input type="number" step="0.000001" name="location[lat]" id="al-edit-lat" />
          </label>
            <label>Longitude
            <input type="number" step="0.000001" name="location[lng]" id="al-edit-lng" />
          </label>
          <label style="grid-column:1/-1">Notes
            <textarea name="notes" id="al-edit-notes" rows="3"></textarea>
          </label>
        </div>
        <div class="img-manage">
          <div class="img-list" id="al-edit-existing" style="display:flex;flex-wrap:wrap;gap:.5rem"></div>
          <label class="upload-tile">
            <input type="file" name="new_images[]" id="al-edit-new" multiple hidden accept="image/*" />
            <span><i class="fa-solid fa-upload"></i> Add Images</span>
          </label>
          <div id="al-edit-new-previews" style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.5rem"></div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" data-close>Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
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
</div>
@endpush


@push('admin-head')
  @vite(['resources/css/waste-items.css','resources/css/admin-listings.css'])
@endpush
@push('admin-scripts')
  @vite(['resources/js/admin-listings.js'])
@endpush
@stack('admin-modals')
@endsection
