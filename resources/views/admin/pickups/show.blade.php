@extends('layouts.admin')

@section('title', 'Pickup ')

@section('admin-content')
<style>
    .modern-admin-pickup {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
        min-height: 100vh;
        padding: 0.5rem 0;
    }
    
    .admin-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 0.75rem;
    }
    
    .page-header {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    }
    
    .page-title {
        color: #1e293b;
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .title-icon {
        width: 28px;
        height: 28px;
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.8rem;
        box-shadow: 0 2px 8px rgba(0, 255, 136, 0.3);
    }
    
    .page-subtitle {
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .pickup-details-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        position: relative;
        overflow: hidden;
    }
    
    .pickup-details-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #00ff88, #00d4aa, #38b2ac);
        border-radius: 12px 12px 0 0;
    }
    
    .card-title {
        color: #1e293b;
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    
    .card-icon {
        width: 20px;
        height: 20px;
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.7rem;
    }
    
    .details-grid {
        display: grid;
        gap: 1rem;
    }
    
    .detail-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0.75rem;
    }
    
    .detail-item {
        background: rgba(248, 250, 252, 0.8);
        border-radius: 8px;
        padding: 0.75rem;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    
    .detail-label {
        color: #64748b;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.3rem;
    }
    
    .detail-value {
        color: #1e293b;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .listing-section {
        background: rgba(0, 255, 136, 0.08);
        border: 1px solid rgba(0, 255, 136, 0.15);
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .listing-label {
        color: #059669;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.3rem;
    }
    
    .listing-title {
        color: #1e293b;
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.2rem;
    }
    
    .listing-id {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-badge {
        padding: 0.3rem 0.75rem;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
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
    
    .notes-section {
        background: rgba(0, 255, 136, 0.08);
        border: 1px solid rgba(0, 255, 136, 0.15);
        border-radius: 8px;
        padding: 0.75rem;
        margin-top: 1rem;
    }
    
    .notes-label {
        color: #059669;
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.3rem;
    }
    
    .notes-content {
        color: #374151;
        font-size: 0.8rem;
        line-height: 1.4;
        white-space: pre-line;
    }
    
    .timestamps {
        background: rgba(248, 250, 252, 0.8);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 8px;
        padding: 0.75rem;
        margin-top: 1rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.5rem;
    }
    
    .timestamp-item {
        text-align: center;
    }
    
    .timestamp-label {
        color: #64748b;
        font-size: 0.6rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.2rem;
    }
    
    .timestamp-value {
        color: #1e293b;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .admin-container {
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
        
        .pickup-details-card {
            padding: 1.5rem;
        }
        
        .detail-section {
            grid-template-columns: 1fr;
        }
        
        .timestamps {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="modern-admin-pickup">
    <div class="admin-container">
        <div class="page-header">
            <h1 class="page-title">
                <span class="title-icon">
                    <i class="fa-solid fa-truck"></i>
        </span>
                Pickup 
            </h1>
            <p class="page-subtitle">Detailed information about this pickup</p>
      </div>

        <div class="pickup-details-card">
            <div class="card-title">
                <span class="card-icon">
                    <i class="fa-solid fa-box"></i>
                </span>
                Pickup Summary
      </div>

            <div class="listing-section">
                <div class="listing-label">Waste Item</div>
                <div class="listing-title">{{ $pickup->wasteItem->title ?? '—' }}</div>
      </div>

            <div class="details-grid">
                <div class="detail-section">
                    <div class="detail-item">
                        <div class="detail-label">Pickup Address</div>
                        <div class="detail-value">{{ $pickup->pickup_address }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <span class="status-badge status-{{ $pickup->status }}">
                            {{ ucfirst(str_replace('_', ' ', $pickup->status)) }}
        </span>
      </div>
      </div>

                <div class="detail-section">
                    <div class="detail-item">
                        <div class="detail-label">Window Start</div>
                        <div class="detail-value">
                            {{ optional($pickup->scheduled_pickup_window_start)->format('M d, Y H:i') ?? '—' }}
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Window End</div>
                        <div class="detail-value">
                            {{ optional($pickup->scheduled_pickup_window_end)->format('M d, Y H:i') ?? '—' }}
                        </div>
                    </div>
      </div>

                <div class="detail-section">
                    <div class="detail-item">
                        <div class="detail-label">Tracking Code</div>
                        <div class="detail-value">{{ $pickup->tracking_code ?? '—' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Assigned Courier</div>
                        <div class="detail-value">{{ $pickup->courier_id ? '#'.$pickup->courier_id : '—' }}</div>
                    </div>
                </div>
      </div>

            @if($pickup->notes)
                <div class="notes-section">
                    <div class="notes-label">Notes</div>
                    <div class="notes-content">{{ $pickup->notes }}</div>
      </div>
            @endif

            <div class="timestamps">
                <div class="timestamp-item">
                    <div class="timestamp-label">Created</div>
                    <div class="timestamp-value">{{ $pickup->created_at->format('M d, Y H:i') }}</div>
        </div>
                <div class="timestamp-item">
                    <div class="timestamp-label">Last Updated</div>
                    <div class="timestamp-value">{{ $pickup->updated_at->format('M d, Y H:i') }}</div>
        </div>
        </div>
      </div>
    </div>
  </div>
@endsection