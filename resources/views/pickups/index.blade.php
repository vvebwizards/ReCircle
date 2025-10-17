@extends('layouts.app')

@section('content')
<style>
    .modern-pickups-page {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
        min-height: 100vh;
        padding: 8rem 0 4rem 0;
    }
    
    .pickups-container {
        max-width: 1200px;
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
    
    .btn-new-pickup {
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        color: #ffffff;
        font-weight: 600;
        font-size: 1rem;
        padding: 1rem 2rem;
        border: none;
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .btn-new-pickup:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0, 255, 136, 0.5);
        color: #ffffff;
        text-decoration: none;
    }
    
    .pickups-grid {
        display: grid;
        gap: 2rem;
        margin-top: 1.5rem;
    }
    
    .pickup-card {
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
    
    .pickup-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #00ff88, #00d4aa, #38b2ac);
        border-radius: 20px 20px 0 0;
    }
    
    .pickup-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        border-color: rgba(0, 255, 136, 0.3);
    }
    
    .pickup-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }
    
    .pickup-title {
        color: #1e293b;
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .pickup-id {
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
    
    .status-picked {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #ffffff;
    }
    
    .status-failed {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #ffffff;
    }
    
    .status-cancelled {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: #ffffff;
    }
    
    .pickup-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }
    
    .detail-item {
        background: rgba(248, 250, 252, 0.8);
        border-radius: 12px;
        padding: 1.25rem;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    
    .detail-label {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }
    
    .detail-value {
        color: #1e293b;
        font-size: 0.95rem;
        font-weight: 600;
    }
    
    .pickup-notes {
        background: rgba(0, 255, 136, 0.08);
        border: 1px solid rgba(0, 255, 136, 0.15);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    
    .notes-label {
        color: #059669;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }
    
    .notes-content {
        color: #374151;
        font-size: 0.9rem;
        line-height: 1.5;
    }
    
    .pickup-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }
    
    .btn-action {
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-view {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #ffffff;
    }
    
    .btn-select {
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        color: #ffffff;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        color: #ffffff;
        text-decoration: none;
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
        margin-top: 2rem;
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
        .pickups-container {
            padding: 0 1rem;
        }
        
        .page-header {
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.6rem;
            flex-direction: column;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .title-icon {
            width: 45px;
            height: 45px;
            font-size: 1.2rem;
        }
        
        .pickup-card {
            padding: 1.5rem;
        }
        
        .pickup-details {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .pickup-actions {
            justify-content: center;
        }
        
        .pickups-grid {
            gap: 1.5rem;
        }
    }
</style>

<div class="modern-pickups-page">
    <div class="pickups-container">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <span class="title-icon">
                            <i class="fa-solid fa-truck"></i>
                        </span>
                        Pickups Management
    </h1>
                    <p class="page-subtitle">Manage and track all your waste pickups</p>
                </div>
               
            </div>
  </div>

  @if($pickups->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fa-solid fa-inbox"></i>
                </div>
                <h3 class="empty-title">No Pickups Yet</h3>
                <p class="empty-description">Create your first pickup to get started with waste management.</p>
    </div>
  @else
            <div class="pickups-grid">
          @foreach($pickups as $p)
                    <div class="pickup-card">
                        <div class="pickup-header">
                            <div>
                                <div class="pickup-title">{{ $p->wasteItem->title ?? '—' }}</div>
                            </div>
                            <span class="status-badge status-{{ $p->status }}">
                                {{ ucfirst(str_replace('_', ' ', $p->status)) }}
                            </span>
                        </div>

                        <div class="pickup-details">
                            <div class="detail-item">
                                <div class="detail-label">Window Start</div>
                                <div class="detail-value">
                                    {{ \Illuminate\Support\Carbon::parse($p->window_start)->format('M d, Y H:i') }}
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Window End</div>
                                <div class="detail-value">
                                    {{ \Illuminate\Support\Carbon::parse($p->window_end)->format('M d, Y H:i') }}
                                </div>
                            </div>
                        </div>

                        @if($p->notes)
                            <div class="pickup-notes">
                                <div class="notes-label">Notes</div>
                                <div class="notes-content">{{ $p->notes }}</div>
                            </div>
                        @endif

                        <div class="pickup-actions">
                    @if(optional(auth()->user()->role)->value === 'courier')
                                <a href="{{ route('deliveries.createFromPickup', $p) }}" class="btn-action btn-select">
                                    <i class="fa-solid fa-truck-fast"></i>
                                    Select Delivery
                                </a>
                    @else
                                <a href="{{ route('pickups.show', $p) }}" class="btn-action btn-view">
                                    <i class="fa-solid fa-eye"></i>
                                    View Details
                                </a>
                    @endif
                        </div>
                    </div>
          @endforeach
    </div>

            <div class="mt-4 d-flex justify-content-center">
      {{ $pickups->links() }}
    </div>
  @endif
    </div>
</div>
@endsection