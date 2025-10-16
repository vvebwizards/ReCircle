@extends('layouts.admin')

@section('title','Carts')

@section('admin-content')

<!-- The admin-topbar is kept clean for the main heading -->
<div class="admin-topbar">
  <div>
    <h1>Carts</h1>
    <div class="tb-sub">All user carts — view payments and items</div>
  </div>
  <!-- Removed old filter form from tb-right -->
</div>

<!-- NEW COMPREHENSIVE FILTER BAR -->
<div class="filter-controls-bar mb-6 p-4 bg-white rounded-xl shadow-md">
    <!-- 
        Using a single GET form for all filtering and sorting controls.
        The 'flex flex-wrap items-center gap-3' classes provide a responsive
        layout that wraps controls onto new lines if the screen is too narrow.
    -->
    <form method="GET" action="{{ route('admin.carts.index') }}" class="flex flex-wrap items-center gap-3">
        
        <!-- 1. Search Input -->
        <div class="flex-grow min-w-[200px]">
            <input type="text" name="search" placeholder="Search ID, user name, or email..." class="a-input w-full" value="{{ request('search') }}">
        </div>
        
        <!-- 2. Status Filter -->
        <div class="flex-shrink-0">
            <select name="status" class="a-select">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ request('status')=='paid' ? 'selected' : '' }}>Paid</option>
                <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <!-- 3. Min/Max Total Filter (Price Range) -->
        <div class="flex items-center gap-1 flex-shrink-0">
            <input type="number" name="min_total" placeholder="Min Total" class="a-input max-w-[120px]" value="{{ request('min_total') }}" step="0.01">
            <span class="text-gray-500 text-sm">–</span>
            <input type="number" name="max_total" placeholder="Max Total" class="a-input max-w-[120px]" value="{{ request('max_total') }}" step="0.01">
        </div>

        <!-- 4. Sorting -->
        <div class="flex-shrink-0">
            <select name="sort" class="a-select">
                <option value="">Sort By...</option>
                <option value="total_desc" {{ request('sort')=='total_desc' ? 'selected' : '' }}>Total (Highest First)</option>
                <option value="total_asc" {{ request('sort')=='total_asc' ? 'selected' : '' }}>Total (Lowest First)</option>
                <option value="date_desc" {{ request('sort')=='date_desc' ? 'selected' : '' }}>Date (Newest First)</option>
                <option value="date_asc" {{ request('sort')=='date_asc' ? 'selected' : '' }}>Date (Oldest First)</option>
            </select>
        </div>

        <!-- 5. Submit Button -->
        <button type="submit" class="a-btn is-blue flex-shrink-0 px-4 py-2">
            <i class="fa-solid fa-filter mr-1"></i> Apply Filters
        </button>
        
        <!-- 6. Reset Button (Only visible if filters are currently applied) -->
        @if(request()->hasAny(['search', 'status', 'min_total', 'max_total', 'sort']))
            <a href="{{ route('admin.carts.index') }}" class="a-btn is-ghost-red flex-shrink-0 px-4 py-2">
                Reset
            </a>
        @endif
    </form>
</div>
<!-- END FILTER BAR -->

<div class="a-card wide">
  <div class="a-title"><i class="fa-solid fa-shopping-cart"></i> Carts</div>
  <table class="a-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>User</th>
        <th>Items</th>
        <th>Total</th>
        <th>Status</th>
        <th>Created</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($carts as $cart)
      <tr data-id="{{ $cart->id }}">
        <td>{{ $cart->id }}</td>
        <td>
          <div class="user-info">
            <span class="avatar sm">{{ strtoupper(substr($cart->user->name ?? 'U', 0, 2)) }}</span>
            <div style="display:inline-block;margin-left:.5rem">{{ $cart->user->name ?? '—' }}<div style="font-size:.75rem;color:#64748b">{{ $cart->user->email ?? '—' }}</div></div>
          </div>
        </td>
        <td>{{ $cart->items()->count() }}</td>
        <td>{{ $cart->total_amount ? number_format($cart->total_amount,2) : '—' }}</td>
        <td><span class="badge {{ $cart->status=='paid' ? 'active' : ($cart->status=='pending' ? '' : 'disabled') }}">{{ ucfirst($cart->status) }}</span></td>
        <td>{{ $cart->created_at?->diffForHumans() }}</td>
        <td class="actions">
          <div class="action-group">
            <a href="{{ route('admin.carts.show', $cart) }}" class="icon-btn view is-blue" title="View cart"><i class="fa-solid fa-eye"></i></a>
            
            <!-- FIXED: Using data attributes to trigger the included confirm modal, avoiding alert()/confirm() -->
            <button type="button" 
                    class="icon-btn delete is-red confirm-delete" 
                    title="Delete cart" 
                    data-action="{{ route('admin.carts.destroy', $cart) }}"
                    data-name="Cart #{{ $cart->id }}"
                    data-target-type="cart">
                <i class="fa-solid fa-trash"></i>
            </button>
            
            <form id="delete-form-{{ $cart->id }}" method="POST" action="{{ route('admin.carts.destroy', $cart) }}" style="display:none;">
              @csrf
              @method('DELETE')
            </form>
          </div>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="admin-pagination mt-4">
    {{ $carts->links('pagination::simple-tailwind') }}
  </div>
</div>

@include('components.confirm-modal')
@endsection
