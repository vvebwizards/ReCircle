@extends('layouts.admin')
@section('title','Cart #'.$cart->id)
@section('admin-content')
<div class="cart-container">
  <div class="cart-header">
    <div class="header-left">
      <div class="cart-id-badge">
        <i class="fas fa-shopping-cart"></i>
        <span>Cart #{{ $cart->id }}</span>
      </div>
      <div class="user-info">
        <div class="user-avatar">
          @if($cart->user->avatar)
            <img src="{{ $cart->user->avatar }}" alt="User Avatar">
          @else
            <i class="fas fa-user"></i>
          @endif
        </div>
        <div class="user-details">
          <h1>{{ $cart->user->name ?? 'Guest User' }}</h1>
          <p class="user-email">{{ $cart->user->email ?? 'No email' }}</p>
        </div>
      </div>
    </div>
    <div class="header-right">
      <div class="status-badge status-{{ $cart->status }}">
        <span class="status-dot"></span>
        {{ ucfirst($cart->status) }}
      </div>
      <div class="cart-total">
        <div class="total-label">Total Amount</div>
        <div class="total-amount">${{ number_format($cart->total_amount ?? 0, 2) }}</div>
      </div>
    </div>
  </div>

  <div class="summary-cards">
    <div class="summary-card">
      <div class="card-icon calendar">
        <i class="fas fa-calendar-alt"></i>
      </div>
      <div class="card-content">
        <div class="card-label">Created Date</div>
        <div class="card-value">{{ $cart->created_at?->format('M d, Y') }}</div>
      </div>
    </div>
    <div class="summary-card">
      <div class="card-icon payment">
        <i class="fas fa-credit-card"></i>
      </div>
      <div class="card-content">
        <div class="card-label">Payment ID</div>
        <div class="card-value">{{ $cart->stripe_payment_intent_id ? substr($cart->stripe_payment_intent_id, -8) : '—' }}</div>
      </div>
    </div>
    <div class="summary-card">
      <div class="card-icon items">
        <i class="fas fa-boxes"></i>
      </div>
      <div class="card-content">
        <div class="card-label">Items</div>
        <div class="card-value">{{ $cart->items->count() }}</div>
      </div>
    </div>
  </div>

  <div class="items-section">
    <div class="section-header">
      <h2><i class="fas fa-list"></i> Cart Items</h2>
      <div class="items-count">{{ $cart->items->count() }} item{{ $cart->items->count() !== 1 ? 's' : '' }}</div>
    </div>
    
    <div class="table-container">
      <table class="modern-table">
        <thead>
          <tr>
            <th class="th-id"><i class="fas fa-hashtag"></i></th>
            <th>Item</th>
            <th>Type</th>
            <th class="th-right">Price</th>
            <th class="th-center">Qty</th>
            <th class="th-right">Subtotal</th>
            <th>Status</th>
            <th class="th-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($cart->items as $index => $item)
          <tr class="item-row">
            <td class="item-id">#{{ $item->id }}</td>
            <td class="item-details">
              <div class="item-info">
                @if($item->product)
                  <div class="product-image">
                    @if($item->product->image)
                      <img src="{{ $item->product->image }}" alt="{{ $item->product->name }}">
                    @else
                      <div class="no-image"><i class="fas fa-image"></i></div>
                    @endif
                  </div>
                  <div class="item-name">{{ $item->product->name }}</div>
                @elseif($item->bid)
                  <div class="bid-badge">
                    <i class="fas fa-gavel"></i>
                    <span>Bid #{{ $item->bid->id }}</span>
                  </div>
                @else
                  <div class="item-name">—</div>
                @endif
              </div>
            </td>
            <td class="item-type">
              <span class="type-badge type-{{ $item->product ? 'product' : ($item->bid ? 'bid' : 'item') }}">
                {{ $item->product ? 'Product' : ($item->bid ? 'Bid' : 'Item') }}
              </span>
            </td>
            <td class="price">${{ number_format($item->price, 2) }}</td>
            <td class="quantity">{{ $item->quantity }}</td>
            <td class="subtotal">${{ number_format($item->price * $item->quantity, 2) }}</td>
            <td class="item-status">
              <span class="status-badge status-{{ $item->status ?? 'pending' }}">
                <span class="status-dot"></span>
                {{ ucfirst($item->status ?? 'pending') }}
              </span>
            </td>
            <td class="actions">
              <button class="action-btn delete" onclick="confirmDelete({{ $item->id }})" title="Remove Item">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="8" class="empty-state">
              <div class="empty-content">
                <div class="empty-icon">
                  <i class="fas fa-shopping-cart"></i>
                </div>
                <p class="empty-title">No items in this cart</p>
                <p class="empty-subtitle">This cart is currently empty</p>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
    
    <div class="danger-zone">
      <div class="danger-zone-content">
        <div class="danger-zone-text">
          <h3>Delete Cart</h3>
          <p>This action cannot be undone</p>
        </div>
        <form method="POST" action="{{ route('admin.carts.destroy', $cart) }}" class="delete-form" onsubmit="return confirm('This will permanently delete the cart and all its items. Are you sure?')">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash-alt"></i>
            <span>Delete Cart</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
* {
  box-sizing: border-box;
}

.cart-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem 1.5rem;
}

/* Header Section */
.cart-header {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  color: white;
  padding: 2rem;
  border-radius: 16px;
  margin-bottom: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.header-left {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.cart-id-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  width: fit-content;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  border: 2px solid rgba(255, 255, 255, 0.2);
  flex-shrink: 0;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 10px;
  object-fit: cover;
}

.user-details h1 {
  margin: 0;
  font-size: 1.75rem;
  font-weight: 700;
  line-height: 1.2;
}

.user-email {
  margin: 0.25rem 0 0 0;
  opacity: 0.85;
  font-size: 0.95rem;
  font-weight: 400;
}

.header-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 1rem;
}

.cart-total {
  text-align: right;
}

.total-label {
  font-size: 0.875rem;
  opacity: 0.85;
  margin-bottom: 0.25rem;
  font-weight: 500;
}

.total-amount {
  font-size: 2.25rem;
  font-weight: 700;
  line-height: 1;
  letter-spacing: -0.025em;
}

/* Status Badges */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  text-transform: capitalize;
  font-size: 0.875rem;
  border: 1px solid;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.status-badge.status-pending {
  background: #fef3c7;
  color: #92400e;
  border-color: #fbbf24;
}

.status-badge.status-pending .status-dot {
  background: #f59e0b;
}

.status-badge.status-paid {
  background: #d1fae5;
  color: #065f46;
  border-color: #34d399;
}

.status-badge.status-paid .status-dot {
  background: #10b981;
}

.status-badge.status-cancelled {
  background: #fee2e2;
  color: #991b1b;
  border-color: #f87171;
}

.status-badge.status-cancelled .status-dot {
  background: #ef4444;
}

/* Summary Cards */
.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.summary-card {
  background: white;
  padding: 1.75rem;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  border: 1px solid #e5e7eb;
  transition: all 0.2s ease;
}

.summary-card:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transform: translateY(-2px);
}

.card-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.card-icon.calendar {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
}

.card-icon.payment {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  color: white;
}

.card-icon.items {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.card-content {
  flex: 1;
}

.card-label {
  color: #6b7280;
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.card-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #111827;
  line-height: 1.2;
}

/* Items Section */
.items-section {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  border: 1px solid #e5e7eb;
  margin-bottom: 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f3f4f6;
}

.section-header h2 {
  margin: 0;
  color: #111827;
  font-size: 1.5rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.items-count {
  background: #f3f4f6;
  padding: 0.375rem 0.875rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.table-container {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.modern-table th {
  background: #f9fafb;
  padding: 1rem 1rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  white-space: nowrap;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.modern-table th.th-right {
  text-align: right;
}

.modern-table th.th-center {
  text-align: center;
}

.modern-table th.th-id {
  width: 80px;
}

.modern-table td {
  padding: 1.25rem 1rem;
  border-bottom: 1px solid #f3f4f6;
  vertical-align: middle;
  font-size: 0.9375rem;
}

.item-row {
  transition: background-color 0.15s ease;
}

.item-row:hover {
  background: #f9fafb;
}

.item-row:last-child td {
  border-bottom: none;
}

.item-id {
  color: #6b7280;
  font-weight: 600;
  font-size: 0.875rem;
}

.item-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.product-image, .no-image {
  width: 56px;
  height: 56px;
  border-radius: 10px;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
  border: 1px solid #e5e7eb;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  color: #9ca3af;
  font-size: 1.25rem;
}

.item-name {
  font-weight: 600;
  color: #111827;
  line-height: 1.4;
}

.bid-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: #fef3c7;
  color: #92400e;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  border: 1px solid #fbbf24;
}

.type-badge {
  display: inline-block;
  padding: 0.375rem 0.875rem;
  border-radius: 8px;
  font-size: 0.8125rem;
  font-weight: 600;
  border: 1px solid;
}

.type-badge.type-product {
  background: #d1fae5;
  color: #065f46;
  border-color: #34d399;
}

.type-badge.type-bid {
  background: #fef3c7;
  color: #92400e;
  border-color: #fbbf24;
}

.type-badge.type-item {
  background: #e0e7ff;
  color: #3730a3;
  border-color: #818cf8;
}

.price, .subtotal {
  text-align: right;
  font-weight: 700;
  color: #059669;
  font-variant-numeric: tabular-nums;
}

.quantity {
  text-align: center;
  font-weight: 600;
  color: #374151;
}

.actions {
  text-align: center;
}

.action-btn {
  background: none;
  border: none;
  padding: 0.5rem;
  border-radius: 8px;
  cursor: pointer;
  color: #dc2626;
  transition: all 0.15s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.action-btn:hover {
  background: #fee2e2;
  transform: scale(1.1);
}

.action-btn:active {
  transform: scale(0.95);
}

.empty-state {
  padding: 4rem 2rem !important;
}

.empty-content {
  text-align: center;
}

.empty-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 1.5rem;
  border-radius: 50%;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  color: #9ca3af;
}

.empty-title {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #374151;
}

.empty-subtitle {
  margin: 0;
  color: #6b7280;
  font-size: 0.9375rem;
}

/* Action Section */
.action-section {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;
}

.status-form {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  border: 1px solid #e5e7eb;
}

.status-update-form {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  align-items: end;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
}

.select-wrapper {
  position: relative;
}

.form-select {
  width: 100%;
  padding: 0.75rem 2.75rem 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  background: white;
  font-size: 0.9375rem;
  font-weight: 500;
  color: #111827;
  cursor: pointer;
  transition: all 0.15s ease;
  appearance: none;
}

.form-select:hover {
  border-color: #d1d5db;
}

.form-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.select-icon {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #6b7280;
  pointer-events: none;
  font-size: 0.875rem;
}

.input-group {
  display: flex;
  align-items: stretch;
}

.input-prefix {
  background: #f9fafb;
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-right: none;
  border-radius: 10px 0 0 10px;
  color: #6b7280;
  font-weight: 600;
  display: flex;
  align-items: center;
}

.form-input {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-left: none;
  border-radius: 0 10px 10px 0;
  font-size: 0.9375rem;
  font-weight: 500;
  color: #111827;
  transition: all 0.15s ease;
}

.form-input:hover {
  border-color: #d1d5db;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input:focus + .input-prefix {
  border-color: #3b82f6;
}

/* Buttons */
.btn {
  padding: 0.875rem 1.75rem;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9375rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.625rem;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3), 0 4px 6px -2px rgba(59, 130, 246, 0.2);
}

.btn-primary:active {
  transform: translateY(0);
}

.btn-danger {
  background: #dc2626;
  color: white;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.btn-danger:hover {
  background: #b91c1c;
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(220, 38, 38, 0.3), 0 4px 6px -2px rgba(220, 38, 38, 0.2);
}

.btn-danger:active {
  transform: translateY(0);
}

/* Danger Zone */
.danger-zone {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  border: 2px solid #fee2e2;
}

.danger-zone-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
}

.danger-zone-text h3 {
  margin: 0 0 0.25rem 0;
  color: #991b1b;
  font-size: 1.125rem;
  font-weight: 700;
}

.danger-zone-text p {
  margin: 0;
  color: #6b7280;
  font-size: 0.875rem;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .status-update-form {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .cart-container {
    padding: 1rem;
  }

  .cart-header {
    flex-direction: column;
    gap: 1.5rem;
    align-items: flex-start;
  }

  .header-right {
    width: 100%;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }

  .cart-total {
    text-align: left;
  }

  .summary-cards {
    grid-template-columns: 1fr;
  }

  .items-section {
    padding: 1.25rem;
  }

  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .modern-table {
    font-size: 0.875rem;
  }

  .modern-table th,
  .modern-table td {
    padding: 0.875rem 0.75rem;
  }

  .product-image,
  .no-image {
    width: 48px;
    height: 48px;
  }

  .status-form {
    padding: 1.5rem;
  }

  .danger-zone {
    padding: 1.5rem;
  }

  .danger-zone-content {
    flex-direction: column;
    align-items: stretch;
  }

  .btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .user-details h1 {
    font-size: 1.25rem;
  }

  .total-amount {
    font-size: 1.75rem;
  }

  .card-value {
    font-size: 1.5rem;
  }

  .section-header h2 {
    font-size: 1.25rem;
  }
}
</style>

<script>
function confirmDelete(itemId) {
  if (confirm('Are you sure you want to remove this item from the cart?')) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/carts/{{ $cart->id }}/items/${itemId}`;
    form.innerHTML = `@csrf @method('DELETE')`;
    document.body.appendChild(form);
    form.submit();
  }
}
</script>
@endsection
