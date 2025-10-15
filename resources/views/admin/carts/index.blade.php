@extends('layouts.admin')

@section('title','Carts')

@section('admin-content')
<div class="admin-topbar">
  <div>
    <h1>Carts</h1>
    <div class="tb-sub">All user carts — view payments and items</div>
  </div>
  <div class="tb-right">
    <form method="GET" action="{{ route('admin.carts.index') }}">
      <select name="status" class="a-select">
        <option value="">All statuses</option>
        <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
        <option value="paid" {{ request('status')=='paid' ? 'selected' : '' }}>Paid</option>
        <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>Cancelled</option>
      </select>
    </form>
  </div>
</div>

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
            <form method="POST" action="{{ route('admin.carts.destroy', $cart) }}" style="display:inline">
              @csrf
              @method('DELETE')
              <button class="icon-btn delete is-red" title="Delete cart" onclick="return confirm('Delete this cart?')"><i class="fa-solid fa-trash"></i></button>
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
