@extends('layouts.admin')

@section('title', 'Manage Deliveries')

@section('admin-content')
<style>
    .modern-admin-deliveries {
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
    
    .search-container {
        flex: 1;
        max-width: 400px;
        position: relative;
    }
    
    .search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 2px solid rgba(102, 126, 234, 0.2);
        border-radius: 16px;
        font-size: 1rem;
        color: #1e293b;
        background: rgba(255, 255, 255, 0.95);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(10px);
    }
    
    .search-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 
            0 0 0 4px rgba(102, 126, 234, 0.1),
            0 8px 20px rgba(102, 126, 234, 0.2);
        background: white;
        transform: translateY(-1px);
    }
    
    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1rem;
    }
    
    .deliveries-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 24px;
        padding: 2.5rem;
        box-shadow: 
            0 20px 40px rgba(0, 0, 0, 0.1),
            0 0 0 1px rgba(255, 255, 255, 0.1);
        position: relative;
        overflow: hidden;
    }
    
    .deliveries-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        border-radius: 24px 24px 0 0;
    }
    
    .deliveries-card::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.05) 0%, transparent 70%);
        pointer-events: none;
    }
    
    .card-title {
        color: #1e293b;
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    
    .card-icon {
        width: 28px;
        height: 28px;
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9rem;
    }
    
    .tabs-container {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    
    .tab-link {
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    
    .tab-link.active {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }
    
    .tab-link.inactive {
        background: rgba(107, 114, 128, 0.1);
        color: #6b7280;
        border-color: rgba(107, 114, 128, 0.2);
    }
    
    .tab-link.inactive:hover {
        background: rgba(107, 114, 128, 0.15);
        transform: translateY(-1px);
    }
    
    .deliveries-grid {
        display: grid;
        gap: 1.25rem;
        margin-top: 1.25rem;
    }
    
    .delivery-card {
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgba(226, 232, 240, 0.6);
        border-radius: 20px;
        padding: 2rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .delivery-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        border-radius: 20px 20px 0 0;
    }
    
    .delivery-card:hover {
        transform: translateY(-4px);
        box-shadow: 
            0 20px 40px rgba(0, 0, 0, 0.1),
            0 0 0 1px rgba(102, 126, 234, 0.1);
        border-color: rgba(102, 126, 234, 0.3);
    }
    
    .delivery-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(226, 232, 240, 0.5);
    }
    
    .delivery-id {
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 700;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        padding: 0.5rem 0.8rem;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.2);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .delivery-status {
        padding: 0.6rem 1rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .status-scheduled {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        color: white;
    }
    
    .status-assigned {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
    }
    
    .status-in-transit {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
        color: white;
    }
    
    .status-delivered {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }
    
    .status-failed {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }
    
    .status-cancelled {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
    }
    
    .delivery-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 1.5rem;
    }
    
    .delivery-section {
        min-width: 0;
        background: rgba(248, 250, 252, 0.5);
        padding: 1.25rem;
        border-radius: 16px;
        border: 1px solid rgba(226, 232, 240, 0.3);
        position: relative;
    }
    
    .delivery-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        border-radius: 16px 16px 0 0;
    }
    
    .section-label {
        color: #475569;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .section-label::before {
        content: '';
        width: 8px;
        height: 8px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 50%;
    }
    
    .section-content {
        color: #1e293b;
        font-size: 0.95rem;
        font-weight: 500;
        line-height: 1.5;
    }
    
    .listing-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .listing-avatar {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9rem;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        position: relative;
    }
    
    .listing-avatar::before {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));
        z-index: -1;
    }
    
    .listing-details {
        flex: 1;
        min-width: 0;
    }
    
    .listing-title {
        font-weight: 700;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 1.1rem;
        margin-bottom: 0.2rem;
    }
    
    .listing-id {
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 600;
        background: rgba(100, 116, 139, 0.1);
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        display: inline-block;
    }
    
    .address-info {
        color: #374151;
        font-size: 0.9rem;
        line-height: 1.6;
    }
    
    .address-label {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 0.4rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    
    .address-label::before {
        content: '';
        width: 6px;
        height: 6px;
        background: #667eea;
        border-radius: 50%;
    }
    
    .courier-info {
        color: #374151;
        font-size: 0.85rem;
    }
    
    .courier-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }
    
    .courier-email {
        color: #64748b;
        font-size: 0.8rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .schedule-info {
        color: #374151;
        font-size: 0.85rem;
    }
    
    .schedule-date {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }
    
    .schedule-time {
        color: #64748b;
        font-size: 0.8rem;
    }
    
    .tracking-code {
        background: rgba(107, 114, 128, 0.1);
        color: #374151;
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-family: monospace;
        display: inline-block;
    }
    
    .delivery-actions {
        display: flex;
        gap: 0.8rem;
        justify-content: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 2px solid rgba(226, 232, 240, 0.6);
        position: relative;
    }
    
    .delivery-actions::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 2px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        border-radius: 1px;
    }
    
    .action-btn {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 1rem;
        border: none;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .action-btn::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.1));
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .action-btn:hover::before {
        opacity: 1;
    }
    
    .action-btn:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }
    
    .action-btn.view {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .action-btn.edit {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }
    
    .action-btn.delete {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
    
    .notes-link {
        color: #3b82f6;
        text-decoration: none;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        margin-top: 0.5rem;
        transition: color 0.3s ease;
    }
    
    .notes-link:hover {
        color: #1d4ed8;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #64748b;
        font-size: 1.1rem;
    }
    
    .pagination-container {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
    }
    
    @media (max-width: 768px) {
        .admin-container {
            padding: 0 0.5rem;
        }
        
        .page-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .search-container {
            max-width: none;
        }
        
        .delivery-content {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
        
        .delivery-actions {
            justify-content: center;
        }
    }
</style>

<div class="modern-admin-deliveries">
    <div class="admin-container">
        <div class="page-header">
            <div class="header-left">
                <h1 class="page-title">
                    <div class="title-icon">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    Manage Deliveries
                </h1>
                <p class="page-subtitle">View, filter, and manage all deliveries</p>
    </div>
            
            <form method="GET" action="{{ route('admin.deliveries.index') }}" class="search-container">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input
                type="search"
                name="q"
                    placeholder="Search deliveries..."
                value="{{ request('q') }}"
                class="search-input"
            />
        </form>
    </div>

        <div class="deliveries-card">
            <div class="card-title">
                <div class="card-icon">
                    <i class="fa-solid fa-list"></i>
                </div>
                All Deliveries
</div>

            <div class="tabs-container">
            <a href="{{ route('admin.deliveries.index') }}"
                   class="tab-link {{ $tab==='active' ? 'active' : 'inactive' }}">
                Active
            </a>
            <a href="{{ route('admin.deliveries.completed') }}"
                   class="tab-link {{ $tab==='completed' ? 'active' : 'inactive' }}">
                Completed
            </a>
        </div>

        @if(session('ok'))
                <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 0.5rem; border-radius: 6px; font-size: 0.8rem; margin-bottom: 1rem;">
                    {{ session('ok') }}
                </div>
        @endif

        @if($deliveries->isEmpty())
                <div class="empty-state">
                    <i class="fa-solid fa-box-open" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                    <p>No deliveries found.</p>
                </div>
        @else
                <div class="deliveries-grid">
                        @foreach($deliveries as $d)
                        <div class="delivery-card" id="row-{{ $d->id }}">
                            <div class="delivery-header">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <span style="color: #64748b; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Status:</span>
                                    <span class="delivery-status 
                                        @if($d->status==='scheduled') status-scheduled
                                        @elseif($d->status==='assigned') status-assigned
                                        @elseif($d->status==='in_transit') status-in-transit
                                        @elseif($d->status==='delivered') status-delivered
                                        @elseif($d->status==='failed') status-failed
                                        @elseif($d->status==='cancelled') status-cancelled
                                        @else status-cancelled @endif">
                                        {{ ucfirst(str_replace('_',' ', $d->status)) }}
                                    </span>
                                </div>
                                <div></div>
                            </div>

                            <div class="delivery-content">
                                <div class="delivery-section">
                                    <div class="section-label">Waste Item Title</div>
                                    <div class="section-content">
                                    @php
                                        $title = optional(optional($d->pickup)->wasteItem)->title ?? '—';
                                        $seed  = (optional($d->pickup)->waste_item_id ?? 1) * 47 % 360;
                                    @endphp
                                        <div class="listing-info">
                                            <div class="listing-avatar" style="background: hsl({{ $seed }}, 70%, 50%);">
                                            {{ strtoupper(substr($title, 0, 2)) }}
                                            </div>
                                            <div class="listing-details">
                                                <div class="listing-title" title="{{ $title }}">
                                                {{ $title }}
                                            </div>
                                            </div>
                                            </div>
                                        </div>
                                    </div>

                                <div class="delivery-section">
                                    <div class="section-label">Schedule</div>
                                    <div class="section-content">
                                        @if($d->pickup?->scheduled_pickup_window_start)
                                            <div class="schedule-info">
                                                <div class="schedule-date">
                                                    {{ $d->pickup->scheduled_pickup_window_start->format('M d') }}
                                                </div>
                                                <div class="schedule-time">
                                                    {{ $d->pickup->scheduled_pickup_window_start->format('H:i') }} - 
                                                    {{ $d->pickup->scheduled_pickup_window_end->format('H:i') }}
                                                </div>
                                            </div>
                                        @else
                                            <span style="color: #9ca3af;">—</span>
                                        @endif
                                            </div>
                                        </div>
                                        
                                <div class="delivery-section">
                                    <div class="section-content">
                                        <div class="address-info">
                                            <div class="address-label">Address From:</div>
                                            <div title="{{ optional($d->pickup)->pickup_address ?? '—' }}">
                                                {{ optional($d->pickup)->pickup_address ?? '—' }}
                                            </div>
                                            
                                        @if($d->hub_address)
                                                <div class="address-label" style="margin-top: 0.3rem;">To Hub:</div>
                                                <div title="{{ $d->hub_address }}">
                                                {{ $d->hub_address }}
                                        </div>
                                        @endif
                                    </div>
                                    
                                    @if($d->notes)
                                            <div class="notes-section" style="margin-top: 0.8rem;">
                                                <div class="address-label">Notes:</div>
                                                <div class="notes-content" style="background: rgba(59, 130, 246, 0.1); padding: 0.5rem; border-radius: 6px; font-size: 0.85rem; color: #1e293b; border-left: 3px solid #3b82f6;">
                                                    {{ $d->notes }}
                                        </div>
                                    </div>
                                    @endif
                                        </div>
                                        </div>

                                <div class="delivery-section">
                                    <div class="section-label">Courier </div>
                                    <div class="section-content">
                                    @php $c = $d->courier ?? null; @endphp
                                    @if($c)
                                            <div class="courier-info">
                                                <div class="courier-name">{{ $c->name }}</div>
                                                <div class="courier-email" title="{{ $c->email }}">
                                            {{ $c->email }}
                                                </div>
                                        </div>
                                    @else
                                            <span style="color: #9ca3af;">—</span>
                                        @endif
                                        
                                        @if($d->tracking_code)
                                            <div style="margin-top: 0.5rem;">
                                                <div class="address-label">Tracking Code:</div>
                                                <span class="tracking-code">{{ $d->tracking_code }}</span>
                                            </div>
                                        @endif
                                        
                                        @if($d->arrived_hub_at)
                                            <div style="margin-top: 0.5rem; background: rgba(59, 130, 246, 0.1); padding: 0.4rem 0.6rem; border-radius: 6px; border-left: 3px solid #3b82f6;">
                                                <div class="address-label" style="color: #1e40af; font-size: 0.65rem; margin-bottom: 0.2rem;">Arrived Hub At:</div>
                                                <span style="color: #1e40af; font-size: 0.8rem; font-weight: 600;">{{ $d->arrived_hub_at->format('M d, H:i') }}</span>
                                            </div>
                                    @endif
                                        
                                        @if($d->courier_numero)
                                            <div style="margin-top: 0.5rem;">
                                                <div class="address-label">Courier Number:</div>
                                                <div style="background: rgba(16, 185, 129, 0.1); padding: 0.4rem 0.6rem; border-radius: 6px; font-size: 0.85rem; color: #1e293b; border-left: 3px solid #10b981; display: inline-block;">
                                                    {{ $d->courier_numero }}
                                                </div>
                                            </div>
                                        @endif

                                    @if($d->status === 'delivered' && $d->arrived_hub_at)
                                            <div style="color: #059669; font-size: 0.7rem; margin-top: 0.5rem; background: rgba(16, 185, 129, 0.1); padding: 0.4rem; border-radius: 6px; border-left: 3px solid #10b981;">
                                                <strong>Delivered:</strong> {{ $d->arrived_hub_at->format('M d, H:i') }}
                                        </div>
                                    @endif
                                    </div>
                                </div>
                            </div>

                            <div class="delivery-actions">
                                        <a href="{{ route('admin.deliveries.show', $d) }}"
                                   class="action-btn view" title="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.deliveries.edit', $d) }}"
                                   class="action-btn edit" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                <button class="action-btn delete js-del"
                                                title="Delete"
                                                data-url="{{ route('admin.deliveries.destroy', $d) }}"
                                                data-row="#row-{{ $d->id }}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                        </div>
                        @endforeach
            </div>

                <div class="pagination-container">
                {{ $deliveries->onEachSide(1)->links('pagination::simple-tailwind') }}
            </div>
        @endif
    </div>
    </div>
</div>

{{-- Modal pour les notes --}}
<div id="notesModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Delivery Notes</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600 js-close-notes">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <div id="notesContent" class="text-gray-700 whitespace-pre-wrap"></div>
        <div class="mt-6 text-right">
            <button type="button" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 js-close-notes">
                Close
            </button>
        </div>
    </div>
</div>
@endsection

@push('admin-scripts')
<script>
(function () {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  // Gestion de la suppression
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.js-del');
    if (!btn) return;

    if (!confirm('Delete this delivery? This cannot be undone.')) return;

    const url = btn.dataset.url;
    const rowSel = btn.dataset.row;

    try {
      const res = await fetch(url, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      if (res.status === 204 || res.ok) {
        const row = document.querySelector(rowSel);
        if (row) row.remove();
      } else {
        const msg = await res.text();
        alert('Delete failed.\n' + msg);
      }
    } catch (err) {
      console.error(err);
      alert('Network error while deleting.');
    }
  });

  // Gestion des notes (modal)
  const notesModal = document.getElementById('notesModal');
  const notesContent = document.getElementById('notesContent');

  document.addEventListener('click', (e) => {
    if (e.target.closest('.js-toggle-notes')) {
      const btn = e.target.closest('.js-toggle-notes');
      notesContent.textContent = btn.dataset.notes;
      notesModal.classList.remove('hidden');
    }

    if (e.target.closest('.js-close-notes')) {
      notesModal.classList.add('hidden');
    }
  });

  // Fermer la modal avec ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !notesModal.classList.contains('hidden')) {
      notesModal.classList.add('hidden');
    }
  });
})();
</script>

<style>
/* Styles pour table compacte */
.a-table.compact th,
.a-table.compact td {
  padding: 0.5rem 0.75rem;
}

.a-table.compact .badge.compact {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
}

.a-table.compact .icon-btn.is-sm {
  padding: 0.375rem;
  font-size: 0.875rem;
}

/* Amélioration de la responsive */
.overflow-x-auto {
  -webkit-overflow-scrolling: touch;
}
</style>
@endpush