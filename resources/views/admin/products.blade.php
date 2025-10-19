@extends('layouts.admin')

@section('title','Products')

@section('admin-content')
<div class="admin-topbar">
  <div>
    <h1>Products</h1>
    <div class="tb-sub">Manage all products</div>
  </div>
  <div class="tb-right"></div>
</div>

<div class="filters-row" style="margin:1rem 0;display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
  <select id="ap-status" class="a-select" style="min-width:140px">
    <option value="">All Status</option>
    <option value="draft">Draft</option>
    <option value="published">Published</option>
    <option value="archived">Archived</option>
  </select>
  <select id="ap-maker" class="a-select" style="min-width:140px">
    <option value="">All Makers</option>
    @foreach($makers as $maker)
      <option value="{{ $maker->id }}">{{ $maker->name }}</option>
    @endforeach
  </select>
  <select id="ap-sort" class="a-select" style="min-width:140px">
    <option value="newest">Newest</option>
    <option value="oldest">Oldest</option>
    <option value="name_asc">Name A→Z</option>
    <option value="name_desc">Name Z→A</option>
    <option value="price_desc">Price (High)</option>
    <option value="price_asc">Price (Low)</option>
    <option value="stock_desc">Stock (High)</option>
    <option value="stock_asc">Stock (Low)</option>
  </select>
  <div class="tb-search"><input id="ap-search" type="search" placeholder="Search name or SKU..." aria-label="Search products" /><button type="button" class="clear-search" id="ap-search-clear" aria-label="Clear search">&times;</button></div>
  <div style="margin-left:auto;font-size:.875rem;color:#64748b" id="ap-meta"></div>
</div>

<div class="a-card wide">
  <div class="a-title"><i class="fa-solid fa-cube"></i> Products</div>
  <table class="a-table" id="ap-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>SKU</th>
        <th>Name</th>
        <th>Status</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Maker</th>
        <th>Materials</th>
        <th>Featured</th>
        <th>Created</th>
        <th></th>
      </tr>
    </thead>
    <tbody id="ap-tbody">
      @foreach($products as $product)
        <tr data-id="{{ $product->id }}">
          <td>{{ $product->id }}</td>
          <td>{{ $product->sku }}</td>
          <td>{{ $product->name }}</td>
          <td><span class="badge status-{{ $product->status->value }}">{{ ucfirst($product->status->value) }}</span></td>
          <td>${{ number_format($product->price, 2) }}</td>
          <td>{{ $product->stock }}</td>
          <td>{{ $product->maker->name ?? '—' }}</td>
          <td>{{ $product->materials->count() }}</td>
          <td>
            @if($product->is_featured)
              <span class="badge featured">Featured</span>
            @else
              <span class="badge">—</span>
            @endif
          </td>
          <td>{{ $product->created_at?->diffForHumans() }}</td>
          <td class="actions">
            <div class="action-group">
              <button class="icon-btn view is-blue" data-action="view" data-tooltip="View" aria-label="View product">
                <i class="fa-solid fa-eye"></i>
                <span class="sr-only">View</span>
              </button>
              <button class="icon-btn edit is-green" data-action="edit" data-tooltip="Edit" aria-label="Edit product">
                <i class="fa-solid fa-pen"></i>
                <span class="sr-only">Edit</span>
              </button>
              <button class="icon-btn delete is-red" data-action="delete" data-tooltip="Delete" aria-label="Delete product">
                <i class="fa-solid fa-trash"></i>
                <span class="sr-only">Delete</span>
              </button>
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
  <div id="ap-pagination" class="pagination" style="padding:.75rem 1rem;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
    <button id="ap-prev" class="pg-btn" disabled>Prev</button>
    <div id="ap-pageinfo" style="font-size:.75rem;letter-spacing:.5px;text-transform:uppercase"></div>
    <button id="ap-next" class="pg-btn" disabled>Next</button>
  </div>
</div>

@push('admin-modals')
<div class="modal-overlay hidden" id="ap-modal-overlay" aria-hidden="true">
  <!-- View Modal -->
  <div class="modal hidden" id="ap-view-modal" role="dialog" aria-modal="true" aria-labelledby="ap-view-title">
    <div class="modal-header minimal">
      <h3 class="modal-title" id="ap-view-title"><i class="fa-solid fa-eye"></i> <span>Product</span></h3>
      <button class="modal-close" data-close aria-label="Close view"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="ap-view-body">
      <!-- Loading, error, and content similar to materials -->
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal hidden" id="ap-edit-modal" role="dialog" aria-modal="true" aria-labelledby="ap-edit-title-h">
    <div class="modal-header minimal">
      <h3 class="modal-title" id="ap-edit-title-h"><i class="fa-solid fa-pen-to-square"></i> <span>Edit Product</span></h3>
      <button class="modal-close" data-close aria-label="Close edit"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body ap-edit-body">
      <form id="ap-edit-form" enctype="multipart/form-data" class="ap-edit-form">
        <!-- Form fields similar to materials but for product data -->
      </form>
    </div>
  </div>

  <!-- Delete Modal -->
  <div class="modal hidden confirm-box" id="ap-delete-modal" role="alertdialog" aria-modal="true" aria-labelledby="ap-delete-title">
    <div class="modal-header">
      <h3 class="modal-title" id="ap-delete-title"><i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;"></i> <span>Delete Product</span></h3>
      <button class="modal-close" data-close aria-label="Close delete"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <p id="ap-delete-text">Are you sure you want to delete this product? This action cannot be undone.</p>
      <div class="modal-actions">
        <button class="btn btn-secondary" data-close type="button">Cancel</button>
        <button class="btn btn-danger" id="ap-delete-confirm" type="button"><i class="fa-solid fa-trash"></i> Delete</button>
      </div>
    </div>
  </div>
</div>
@endpush

@push('admin-head')
  @vite(['resources/css/admin.css','resources/css/admin-listings.css','resources/css/waste-items.css','resources/css/admin-materials.css'])
@endpush
@push('admin-scripts')
  @vite(['resources/js/admin-products.js'])
@endpush
@stack('admin-modals')
@endsection