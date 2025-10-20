@extends('layouts.app')

@section('title', 'My Deliveries')

@section('content')
<style>
    .modern-deliveries-page {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
        min-height: 100vh;
        padding: 8rem 0 4rem 0;
    }
    
    .deliveries-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    
    .page-header {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 20px;
        padding: 2.5rem;
        margin-bottom: 2.5rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .page-title {
        color: #1e293b;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .title-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.4rem;
        box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
    }
    
    .page-subtitle {
        color: #64748b;
        font-size: 1.1rem;
        font-weight: 500;
        margin-bottom: 1.5rem;
    }
    
    .tabs-container {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }
    
    .tab-button {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 2px solid transparent;
    }
    
    .tab-active {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }
    
    .tab-inactive {
        background: rgba(255, 255, 255, 0.9);
        color: #64748b;
        border-color: rgba(0, 0, 0, 0.1);
    }
    
    .tab-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        color: #1e293b;
        text-decoration: none;
    }
    
    .tab-active:hover {
        color: #ffffff;
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    }
    
    .notification-container {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 1000;
        max-width: 400px;
        animation: slideInRight 0.5s ease-out;
    }
    
    .notification {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        padding: 1rem 1.5rem;
        box-shadow: 
            0 20px 40px rgba(0, 0, 0, 0.1),
            0 0 0 1px rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
        overflow: hidden;
    }
    
    .notification::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #00ff88, #00d4aa);
        border-radius: 16px 16px 0 0;
    }
    
    .notification-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        flex-shrink: 0;
        box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
    }
    
    .notification-content {
        flex: 1;
        min-width: 0;
    }
    
    .notification-title {
        color: #1e293b;
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .notification-message {
        color: #64748b;
        font-size: 0.85rem;
        line-height: 1.4;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 50%;
        transition: all 0.3s ease;
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .notification-close:hover {
        background: rgba(100, 116, 139, 0.1);
        color: #374151;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @media (max-width: 768px) {
        .notification-container {
            top: 80px;
            right: 10px;
            left: 10px;
            max-width: none;
        }
        
        .notification {
            padding: 0.75rem 1rem;
        }
        
        .notification-icon {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }
        
        .notification-title {
            font-size: 0.8rem;
        }
        
        .notification-message {
            font-size: 0.8rem;
        }
    }
    
    .deliveries-grid {
        display: grid;
        gap: 1.5rem;
    }
    
    .delivery-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 20px;
        padding: 2rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }
    
    .delivery-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #00ff88, #00d4aa, #38b2ac);
        border-radius: 20px 20px 0 0;
    }
    
    .delivery-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        border-color: rgba(0, 255, 136, 0.3);
    }
    
    .delivery-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }
    
    .delivery-title {
        color: #1e293b;
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .delivery-id {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .status-scheduled {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: #ffffff;
    }
    
    .status-assigned {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #ffffff;
    }
    
    .status-in-transit {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
        color: #ffffff;
    }
    
    .status-delivered {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #ffffff;
    }
    
    .status-failed {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #ffffff;
    }
    
    .delivery-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .detail-item {
        background: rgba(248, 250, 252, 0.8);
        border-radius: 12px;
        padding: 1rem;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    
    .detail-label {
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    
    .detail-value {
        color: #1e293b;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .tracking-section {
        background: rgba(0, 255, 136, 0.08);
        border: 1px solid rgba(0, 255, 136, 0.15);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .tracking-label {
        color: #059669;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    
    .tracking-code {
        color: #1e293b;
        font-size: 0.9rem;
        font-weight: 700;
        font-family: monospace;
        letter-spacing: 0.05em;
    }
    
    .delivery-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        flex-wrap: wrap;
    }
    
    .btn-action {
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: none;
        cursor: pointer;
    }
    
    .btn-start {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }
    
    .btn-delivered {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }
    
    .btn-edit {
        background: linear-gradient(135deg, #64748b, #475569);
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(100, 116, 139, 0.3);
    }
    
    .btn-completed {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #ffffff;
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        color: #ffffff;
        text-decoration: none;
    }
    
    .btn-start:hover {
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
    }
    
    .btn-delivered:hover {
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
    }
    
    .btn-edit:hover {
        box-shadow: 0 10px 25px rgba(100, 116, 139, 0.4);
    }
    
    .empty-state {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 20px;
        padding: 4rem 3rem;
        text-align: center;
        color: #64748b;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }
    
    .empty-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #64748b, #475569);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 1.8rem;
        color: #ffffff;
    }
    
    .empty-title {
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #1e293b;
    }
    
    .empty-description {
        font-size: 1rem;
        color: #64748b;
    }
    
    @media (max-width: 768px) {
        .deliveries-container {
            padding: 0 1rem;
        }
        
        .page-header {
            padding: 1.5rem;
        }
        
        .page-title {
            font-size: 1.6rem;
            flex-direction: column;
            text-align: center;
        }
        
        .title-icon {
            width: 45px;
            height: 45px;
            font-size: 1.2rem;
        }
        
        .tabs-container {
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .delivery-card {
            padding: 1.5rem;
        }
        
        .delivery-details {
            grid-template-columns: 1fr;
        }
        
        .delivery-actions {
            justify-content: center;
        }
    }
</style>

<div class="modern-deliveries-page">
    <div class="deliveries-container">
        <div class="page-header">
            <h1 class="page-title">
                <span class="title-icon">
                    <i class="fa-solid fa-truck-fast"></i>
                </span>
                My Deliveries
            </h1>
            <p class="page-subtitle">Track and manage your delivery assignments</p>
            
            <div class="tabs-container">
        <a href="{{ route('deliveries.index') }}"
                   class="tab-button {{ request()->routeIs('deliveries.index') ? 'tab-active' : 'tab-inactive' }}">
                    <i class="fa-solid fa-clock"></i>
                    Active Deliveries
        </a>
        <a href="{{ route('deliveries.completed') }}"
                   class="tab-button {{ request()->routeIs('deliveries.completed') ? 'tab-active' : 'tab-inactive' }}">
                    <i class="fa-solid fa-check-circle"></i>
                    Completed Deliveries
        </a>
            </div>
    </div>

    @if(session('ok'))
        <div class="notification-container">
            <div class="notification success-notification">
                <div class="notification-icon">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">Success!</div>
                    <div class="notification-message">{{ session('ok') }}</div>
                </div>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if($deliveries->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fa-solid fa-inbox"></i>
                </div>
                <h3 class="empty-title">No Deliveries Yet</h3>
                <p class="empty-description">You don't have any deliveries assigned at the moment.</p>
        </div>
    @else
            <div class="deliveries-grid">
                @php $isCompletedTab = request()->routeIs('deliveries.completed'); @endphp
                @foreach($deliveries as $d)
                    <div class="delivery-card">
                        <div class="delivery-header">
                            <div>
                                <div class="delivery-title">{{ $d->pickup->wasteItem->title ?? '—' }}</div>
                            </div>
                            <span class="status-badge status-{{ $d->status }}">
                                {{ ucfirst(str_replace('_', ' ', $d->status)) }}
                            </span>
                        </div>

                        <div class="delivery-details">
                            <div class="detail-item">
                                <div class="detail-label">Pickup Address</div>
                                <div class="detail-value">{{ $d->pickup->pickup_address ?? '—' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Window Start</div>
                                <div class="detail-value">
                                    {{ optional($d->pickup->scheduled_pickup_window_start)->format('M d, Y H:i') ?? '—' }}
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Window End</div>
                                <div class="detail-value">
                                    {{ optional($d->pickup->scheduled_pickup_window_end)->format('M d, Y H:i') ?? '—' }}
                                </div>
                            </div>
                        </div>

                        <div class="tracking-section">
                            <div class="tracking-label">Tracking Code</div>
                            <div class="tracking-code">{{ $d->tracking_code ?? '—' }}</div>
                            @if($d->status === 'delivered' && $d->arrived_hub_at)
                                <div style="margin-top: 0.5rem; color: #059669; font-size: 0.8rem; font-weight: 600;">
                                    Delivered: {{ $d->arrived_hub_at->format('M d, Y H:i') }}
                                </div>
                            @endif
                            @if($d->arrived_hub_at)
                                <div style="margin-top: 0.25rem; color: #3b82f6; font-size: 0.8rem; font-weight: 600;">
                                    Arrived Hub: {{ $d->arrived_hub_at->format('M d, Y H:i') }}
                                </div>
                            @endif
                        </div>

                        <div class="delivery-actions">
                                @if(!$isCompletedTab)
                                    @if(in_array($d->status, ['scheduled','assigned']))
                                    <form method="POST" action="{{ route('deliveries.start', $d) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                        <button type="submit" class="btn-action btn-start">
                                            <i class="fa-solid fa-play"></i>
                                                Start Delivery
                                            </button>
                                        </form>
                                    <a href="{{ route('deliveries.edit', $d) }}" class="btn-action btn-edit">
                                        <i class="fa-solid fa-pen"></i>
                                            Edit
                                        </a>
                                    @elseif($d->status === 'in_transit')
                                    <form method="POST" action="{{ route('deliveries.delivered', $d) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                        <button type="submit" class="btn-action btn-delivered">
                                            <i class="fa-solid fa-check"></i>
                                                Mark Delivered
                                            </button>
                                        </form>
                                    @endif
                                @else
                                <span class="btn-action btn-completed">
                                    <i class="fa-solid fa-check-circle"></i>
                                        Delivered
                                    </span>
                                @endif
                            </div>
                    </div>
                @endforeach
        </div>

            <div class="mt-4 d-flex justify-content-center">
            {{ $deliveries->links() }}
        </div>
    @endif
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide notification after 5 seconds
    const notification = document.querySelector('.notification-container');
    if (notification) {
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.5s ease-out forwards';
            setTimeout(() => {
                notification.remove();
            }, 500);
        }, 5000);
    }
    
    // Add slide out animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
});
</script>
@endsection