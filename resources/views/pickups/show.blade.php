@extends('layouts.app')

@section('title', 'Pickup #'.$pickup->id)

@section('content')
<style>
    .modern-pickup-show {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
        min-height: 100vh;
        padding: 8rem 0 4rem 0;
    }
    
    .pickup-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    
    .page-header {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .page-title {
        color: #1e293b;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
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
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        justify-content: center;
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
    
    .btn-edit {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: #ffffff;
    }
    
    .btn-delete {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #ffffff;
    }
    
    .btn-back {
        background: linear-gradient(135deg, #64748b, #475569);
        color: #ffffff;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        color: #ffffff;
        text-decoration: none;
    }
    
    .pickup-details-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        position: relative;
        overflow: hidden;
    }
    
    .pickup-details-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #00ff88, #00d4aa, #38b2ac);
        border-radius: 20px 20px 0 0;
    }
    
    .details-grid {
        display: grid;
        gap: 2rem;
    }
    
    .detail-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .detail-item {
        background: rgba(248, 250, 252, 0.8);
        border-radius: 12px;
        padding: 1.5rem;
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
        font-size: 1rem;
        font-weight: 600;
    }
    
    .listing-section {
        background: rgba(0, 255, 136, 0.08);
        border: 1px solid rgba(0, 255, 136, 0.15);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .listing-label {
        color: #059669;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }
    
    .listing-title {
        color: #1e293b;
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .listing-id {
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
    
    .notes-section {
        background: rgba(0, 255, 136, 0.08);
        border: 1px solid rgba(0, 255, 136, 0.15);
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1.5rem;
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
        font-size: 0.95rem;
        line-height: 1.5;
        white-space: pre-line;
    }
    
    .timestamps {
        background: rgba(248, 250, 252, 0.8);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 2rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .timestamp-item {
        text-align: center;
    }
    
    .timestamp-label {
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    
    .timestamp-value {
        color: #1e293b;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .success-message {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(6, 182, 212, 0.1));
        border: 2px solid rgba(16, 185, 129, 0.3);
        color: #065f46;
        padding: 1.25rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .success-message::before {
        content: '✅';
        font-size: 1.2rem;
    }
    
    @media (max-width: 768px) {
        .pickup-container {
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
        
        .action-buttons {
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .pickup-details-card {
            padding: 1.5rem;
        }
        
        .detail-section {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .timestamps {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="modern-pickup-show">
    <div class="pickup-container">
    @if(session('ok'))
            <div class="success-message">{{ session('ok') }}</div>
    @endif

        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <span class="title-icon">
                        <i class="fa-solid fa-truck"></i>
                    </span>
                    Pickup #{{ $pickup->id }}
                </h1>
                <p class="page-subtitle">Detailed information about this pickup</p>
            </div>
        </div>

        <div class="pickup-details-card">
            <div class="listing-section">
                <div class="listing-label">Waste Item</div>
                <div class="listing-title">{{ $pickup->wasteItem->title ?? '—' }}</div>
                <div class="listing-id">Item ID: #{{ $pickup->waste_item_id }}</div>
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

            <div class="action-buttons">
                <a href="{{ route('pickups.edit', $pickup) }}" class="btn-action btn-edit">
                    <i class="fa-solid fa-pen"></i>
                    Edit Pickup
                </a>

            <form method="POST" action="{{ route('pickups.destroy', $pickup) }}"
                      onsubmit="return confirm('Delete this pickup? This cannot be undone.');" 
                      style="display: inline;">
                @csrf
                @method('DELETE')
                    <button type="submit" class="btn-action btn-delete">
                        <i class="fa-solid fa-trash"></i>
                        Delete Pickup
                </button>
            </form>
                
                <a href="{{ route('pickups.index') }}" class="btn-action btn-back">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to List
                </a>
        </div>
    </div>
    </div>
</div>
@endsection