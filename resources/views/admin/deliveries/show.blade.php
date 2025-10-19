@extends('layouts.admin')

@section('title', "Delivery #{$delivery->id}")

@section('admin-content')
<style>
    .modern-admin-delivery-show {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
        min-height: 100vh;
        padding: 1.5rem 0;
    }
    
    .admin-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }
    
    .page-header {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.5rem;
    }
    
    .header-left {
        flex: 1;
        min-width: 250px;
    }
    
    .page-title {
        color: #1e293b;
        font-size: 1.6rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .title-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        box-shadow: 
            0 8px 20px rgba(102, 126, 234, 0.4),
            0 0 0 4px rgba(255, 255, 255, 0.1);
        position: relative;
    }
    
    .title-icon::before {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
        z-index: -1;
        opacity: 0.7;
        filter: blur(8px);
    }
    
    .page-subtitle {
        color: #64748b;
        font-size: 1rem;
        font-weight: 500;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    
    .btn-action {
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        text-align: center;
        justify-content: center;
    }
    
    .btn-edit {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }
    
    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
    }
    
    .btn-back {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
        box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
    }
    
    .btn-back:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(107, 114, 128, 0.4);
    }
    
    .delivery-details-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 
            0 20px 40px rgba(0, 0, 0, 0.1),
            0 0 0 1px rgba(255, 255, 255, 0.05);
        position: relative;
        overflow: hidden;
    }
    
    .delivery-details-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        border-radius: 20px 20px 0 0;
    }
    
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .detail-section {
        background: rgba(248, 250, 252, 0.8);
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid rgba(226, 232, 240, 0.6);
        position: relative;
    }
    
    .detail-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #3b82f6, #1d4ed8);
        border-radius: 12px 12px 0 0;
    }
    
    .detail-item {
        margin-bottom: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }
    
    .detail-label {
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .detail-label::before {
        content: '';
        width: 4px;
        height: 4px;
        background: #3b82f6;
        border-radius: 50%;
    }
    
    .detail-value {
        color: #1e293b;
        font-size: 1rem;
        font-weight: 500;
        padding: 0.5rem 0.75rem;
        background: rgba(255, 255, 255, 0.7);
        border-radius: 8px;
        border: 1px solid rgba(226, 232, 240, 0.5);
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-scheduled {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: white;
        box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
    }
    
    .status-assigned {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }
    
    .status-in-transit {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: white;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
    }
    
    .status-delivered {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }
    
    .status-failed {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }
    
    .status-cancelled {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
        box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
    }
    
    .notes-section {
        background: rgba(59, 130, 246, 0.05);
        border: 1px solid rgba(59, 130, 246, 0.2);
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1.5rem;
    }
    
    .notes-label {
        color: #1e40af;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .notes-label::before {
        content: '📝';
        font-size: 1rem;
    }
    
    .notes-content {
        color: #1e293b;
        font-size: 0.95rem;
        line-height: 1.6;
        background: rgba(255, 255, 255, 0.8);
        padding: 1rem;
        border-radius: 8px;
        border-left: 4px solid #3b82f6;
    }
    
    .courier-number {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.3);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        color: #065f46;
        font-weight: 600;
        display: inline-block;
        margin-top: 0.5rem;
    }
    
    .tracking-code {
        background: rgba(139, 92, 246, 0.1);
        border: 1px solid rgba(139, 92, 246, 0.3);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        color: #5b21b6;
        font-weight: 600;
        font-family: 'Courier New', monospace;
        letter-spacing: 1px;
        display: inline-block;
        margin-top: 0.5rem;
    }
    
    .timestamps {
        background: rgba(248, 250, 252, 0.8);
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 1.5rem;
        border: 1px solid rgba(226, 232, 240, 0.6);
    }
    
    .timestamp-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(226, 232, 240, 0.5);
    }
    
    .timestamp-item:last-child {
        border-bottom: none;
    }
    
    .timestamp-label {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .timestamp-value {
        color: #1e293b;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .admin-container {
            padding: 0 1rem;
        }
        
        .page-header {
            padding: 1.5rem;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .delivery-details-card {
            padding: 1.5rem;
        }
        
        .details-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .action-buttons {
            width: 100%;
            justify-content: stretch;
        }
        
        .btn-action {
            flex: 1;
        }
    }
</style>

<div class="modern-admin-delivery-show">
    <div class="admin-container">
        <div class="page-header">
            <div class="header-left">
                <h1 class="page-title">
                    <div class="title-icon">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    Delivery Details
                </h1>
                <p class="page-subtitle">Complete information about this delivery</p>
            </div>
            
            <div class="action-buttons">
                <a href="{{ route('admin.deliveries.edit', $delivery) }}" class="btn-action btn-edit">
                    <i class="fa-solid fa-pen"></i>
                    Edit
                </a>
                <a href="{{ route('admin.deliveries.index') }}" class="btn-action btn-back">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back
                </a>
            </div>
        </div>

        <div class="delivery-details-card">
            <div class="details-grid">
                <div class="detail-section">
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">
                            <span class="status-badge 
                                @if($delivery->status==='scheduled') status-scheduled
                                @elseif($delivery->status==='assigned') status-assigned
                                @elseif($delivery->status==='in_transit') status-in-transit
                                @elseif($delivery->status==='delivered') status-delivered
                                @elseif($delivery->status==='failed') status-failed
                                @elseif($delivery->status==='cancelled') status-cancelled
                                @else status-cancelled @endif">
                                {{ ucfirst(str_replace('_',' ', $delivery->status)) }}
                            </span>
                        </div>
                    </div>
                    
                    @if($delivery->tracking_code)
                    <div class="detail-item">
                        <div class="detail-label">Tracking Code</div>
                        <div class="detail-value">
                            <span class="tracking-code">{{ $delivery->tracking_code }}</span>
                        </div>
                    </div>
                    @endif
                    
                    @if($delivery->courier_numero)
                    <div class="detail-item">
                        <div class="detail-label">Courier Phone</div>
                        <div class="detail-value">
                            <span class="courier-number">{{ $delivery->courier_numero }}</span>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="detail-section">
                    <div class="detail-item">
                        <div class="detail-label">Waste Item Title</div>
                        <div class="detail-value">{{ $delivery->pickup->wasteItem->title ?? '—' }}</div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Pickup Address</div>
                        <div class="detail-value">{{ $delivery->pickup->pickup_address ?? '—' }}</div>
                    </div>
                    
                    @if($delivery->hub_address)
                    <div class="detail-item">
                        <div class="detail-label">Hub Address</div>
                        <div class="detail-value">{{ $delivery->hub_address }}</div>
                    </div>
                    @endif
                </div>

                @if($delivery->courier)
                <div class="detail-section">
                    <div class="detail-item">
                        <div class="detail-label">Courier Name</div>
                        <div class="detail-value">{{ $delivery->courier->name }}</div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Courier Email</div>
                        <div class="detail-value">{{ $delivery->courier->email }}</div>
                    </div>
                    
                    @if($delivery->courier_phone)
                    <div class="detail-item">
                        <div class="detail-label">Courier Phone</div>
                        <div class="detail-value">
                            <span style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 8px; padding: 0.75rem 1rem; color: #1e40af; font-weight: 600; font-family: 'Courier New', monospace; letter-spacing: 1px; display: inline-block;">{{ $delivery->courier_phone }}</span>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            @if($delivery->notes)
            <div class="notes-section">
                <div class="notes-label">Delivery Notes</div>
                <div class="notes-content">{{ $delivery->notes }}</div>
            </div>
            @endif

            <div class="timestamps">
                <div class="timestamp-item">
                    <span class="timestamp-label">Created</span>
                    <span class="timestamp-value">{{ $delivery->created_at->format('M d, Y H:i') }}</span>
                </div>
                
                @if($delivery->updated_at != $delivery->created_at)
                <div class="timestamp-item">
                    <span class="timestamp-label">Last Updated</span>
                    <span class="timestamp-value">{{ $delivery->updated_at->format('M d, Y H:i') }}</span>
                </div>
                @endif
                
                @if($delivery->scheduled_at)
                <div class="timestamp-item">
                    <span class="timestamp-label">Scheduled</span>
                    <span class="timestamp-value">{{ $delivery->scheduled_at->format('M d, Y H:i') }}</span>
                </div>
                @endif
                
                @if($delivery->arrived_hub_at)
                <div class="timestamp-item">
                    <span class="timestamp-label">Delivered</span>
                    <span class="timestamp-value">{{ $delivery->arrived_hub_at->format('M d, Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection