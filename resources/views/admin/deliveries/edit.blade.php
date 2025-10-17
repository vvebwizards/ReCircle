@extends('layouts.admin')

@section('title', "Edit Delivery #{$delivery->id}")

@section('admin-content')
<style>
    .modern-admin-delivery-edit {
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
    
    .btn-cancel {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
        box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
    }
    
    .btn-cancel:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(107, 114, 128, 0.4);
    }
    
    .edit-form-card {
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
    
    .edit-form-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        border-radius: 20px 20px 0 0;
    }
    
    .form-title {
        color: #1e293b;
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .form-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-label::before {
        content: '';
        width: 4px;
        height: 4px;
        background: #3b82f6;
        border-radius: 50%;
    }
    
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.8);
        color: #1e293b;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(5px);
    }
    
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background: rgba(255, 255, 255, 0.95);
    }
    
    .form-textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .error-message {
        color: #ef4444;
        font-size: 0.85rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-actions {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(226, 232, 240, 0.6);
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        flex-wrap: wrap;
    }
    
    .btn {
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
    
    .btn-save {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }
    
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    }
    
    .btn-cancel-form {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
        box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
    }
    
    .btn-cancel-form:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(107, 114, 128, 0.4);
    }
    
    .listing-display {
        background: rgba(248, 250, 252, 0.8);
        border: 1px solid rgba(226, 232, 240, 0.6);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .listing-id {
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 500;
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
        
        .edit-form-card {
            padding: 1.5rem;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
        }
    }
</style>

<div class="modern-admin-delivery-edit">
    <div class="admin-container">
        <div class="page-header">
            <div class="header-left">
                <h1 class="page-title">
                    <div class="title-icon">
                        <i class="fa-solid fa-edit"></i>
                    </div>
                    Edit Delivery
                </h1>
                <p class="page-subtitle">Update delivery information and status</p>
            </div>
            
            <div class="action-buttons">
                <a href="{{ url()->previous(route('admin.deliveries.index')) }}" class="btn-action btn-cancel">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back
                </a>
            </div>
        </div>

        <div class="edit-form-card">
            {{-- Header Recap --}}
            <div class="listing-display">
                @php
                    $pick = optional($delivery->pickup);
                    $item = optional($pick->wasteItem);
                    $seed = (($pick->waste_item_id ?? 1) * 47) % 360;
                @endphp
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="width: 48px; height: 48px; background: linear-gradient({{ $seed }}deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1rem;">
                            {{ strtoupper(substr($item->title ?? '—', 0, 2)) }}
                        </span>
                        <div>
                            <h3 style="color: #1e293b; font-size: 1.2rem; font-weight: 700; margin-bottom: 0.25rem;">
                                {{ $item->title ?? 'No listing' }}
                            </h3>
                            <p style="color: #64748b; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-location-dot"></i>
                                {{ $pick->pickup_address ?? 'No address' }}
                            </p>
                        </div>
                    </div>
                    
                    <div style="background: rgba(255, 255, 255, 0.8); padding: 1rem; border-radius: 8px; border: 1px solid rgba(226, 232, 240, 0.6);">
                        <div style="color: #64748b; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem;">Pickup Window</div>
                        <div style="color: #1e293b; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <i class="fa-regular fa-calendar"></i>
                            {{ optional($pick->scheduled_pickup_window_start)->format('M d, Y') ?? '—' }}
                        </div>
                        <div style="color: #64748b; font-size: 0.85rem;">
                            {{ optional($pick->scheduled_pickup_window_start)->format('H:i') ?? '—' }} - 
                            {{ optional($pick->scheduled_pickup_window_end)->format('H:i') ?? '—' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                    <div style="color: #dc2626; font-weight: 600; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-exclamation-triangle"></i>
                        Please fix the following errors:
                    </div>
                    <ul style="color: #dc2626; list-style: none; padding: 0; margin: 0;">
                        @foreach ($errors->all() as $e)
                            <li style="padding: 0.25rem 0; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i>
                                {{ $e }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Success Message --}}
            @if (session('success'))
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                    <div style="color: #059669; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('admin.deliveries.update', $delivery) }}">
                @csrf
                @method('PATCH')

                <h2 class="form-title">
                    <div class="form-icon">
                        <i class="fa-solid fa-edit"></i>
                    </div>
                    Edit Delivery Information
                </h2>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">
                    {{-- Left Column --}}
                    <div>
                        {{-- Delivery Information --}}
                        <div style="background: rgba(248, 250, 252, 0.8); border: 1px solid rgba(226, 232, 240, 0.6); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                            <h3 style="color: #1e293b; font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                                <i class="fa-solid fa-truck" style="color: #3b82f6;"></i>
                                Delivery Information
                            </h3>
                            
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Status *</label>
                                    <select name="status" class="form-select" required>
                                        @foreach($statuses as $s)
                                            <option value="{{ $s }}" @selected(old('status', $delivery->status) === $s)>
                                                {{ ucfirst(str_replace('_', ' ', $s)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="error-message">
                                            <i class="fa-solid fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Courier Phone *</label>
                                    <div style="position: relative;">
                                        <input 
                                            name="courier_phone" 
                                            class="form-input"
                                            style="padding-left: 2.5rem;"
                                            placeholder="Enter phone number"
                                            value="{{ old('courier_phone', $delivery->courier_phone) }}"
                                            required
                                            pattern="^\d{8}$"
                                            maxlength="8"
                                            minlength="8"
                                            title="Please enter exactly 8 digits"
                                        />
                                        <i class="fa-solid fa-phone" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748b;"></i>
                                    </div>
                                    <div style="color: #64748b; font-size: 0.8rem; margin-top: 0.5rem;">
                                        Phone number used by the hub to contact the courier (exactly 8 digits required)
                                    </div>
                                    @error('courier_phone')
                                        <div class="error-message">
                                            <i class="fa-solid fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Notes</label>
                                    <textarea 
                                        name="notes" 
                                        rows="3" 
                                        class="form-textarea"
                                        placeholder="Add any delivery notes..."
                                        maxlength="500"
                                    >{{ old('notes', $delivery->notes) }}</textarea>
                                    <div style="color: #64748b; font-size: 0.8rem; margin-top: 0.5rem;">
                                        Optional notes about the delivery (maximum 500 characters)
                                    </div>
                                    @error('notes')
                                        <div class="error-message">
                                            <i class="fa-solid fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Delivery Timeline --}}
                        <div style="background: rgba(248, 250, 252, 0.8); border: 1px solid rgba(226, 232, 240, 0.6); border-radius: 12px; padding: 1.5rem;">
                            <h3 style="color: #1e293b; font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                                <i class="fa-solid fa-clock" style="color: #3b82f6;"></i>
                                Delivery Timeline
                            </h3>
                            
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid rgba(226, 232, 240, 0.6);">
                                    <span style="color: #64748b; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="fa-solid fa-user-check" style="color: #3b82f6;"></i>
                                        Assigned
                                    </span>
                                    <span style="font-family: 'Courier New', monospace; color: #1e293b; font-size: 0.85rem;">
                                        {{ optional($delivery->assigned_at)->format('Y-m-d H:i') ?? '—' }}
                                    </span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid rgba(226, 232, 240, 0.6);">
                                    <span style="color: #64748b; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="fa-solid fa-box-open" style="color: #10b981;"></i>
                                        Picked Up
                                    </span>
                                    <span style="font-family: 'Courier New', monospace; color: #1e293b; font-size: 0.85rem;">
                                        {{ optional($delivery->picked_up_at)->format('Y-m-d H:i') ?? '—' }}
                                    </span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0;">
                                    <span style="color: #64748b; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="fa-solid fa-warehouse" style="color: #8b5cf6;"></i>
                                        Arrived at Hub
                                    </span>
                                    <span style="font-family: 'Courier New', monospace; color: #1e293b; font-size: 0.85rem;">
                                        {{ optional($delivery->arrived_hub_at)->format('Y-m-d H:i') ?? '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column --}}
                    <div>
                        {{-- Hub Information --}}
                        <div style="background: rgba(248, 250, 252, 0.8); border: 1px solid rgba(226, 232, 240, 0.6); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem;">
                            <h3 style="color: #1e293b; font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                                <i class="fa-solid fa-warehouse" style="color: #3b82f6;"></i>
                                Hub Information
                            </h3>
                            
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">Hub Address</label>
                                    <div style="position: relative;">
                                        <input 
                                            name="hub_address" 
                                            class="form-input"
                                            style="padding-left: 2.5rem;"
                                            placeholder="Enter hub address"
                                            value="{{ old('hub_address', $delivery->hub_address) }}" 
                                        />
                                        <i class="fa-solid fa-location-dot" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #64748b;"></i>
                                    </div>
                                    @error('hub_address')
                                        <div class="error-message">
                                            <i class="fa-solid fa-exclamation-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div class="form-group">
                                        <label class="form-label">Latitude</label>
                                        <div style="position: relative;">
                                            <input 
                                                name="hub_lat" 
                                                type="number" 
                                                step="any" 
                                                class="form-input"
                                                placeholder="36.8065"
                                                value="{{ old('hub_lat', $delivery->hub_lat) }}" 
                                            />
                                            <i class="fa-solid fa-globe" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 0.8rem;"></i>
                                        </div>
                                        @error('hub_lat')
                                            <div class="error-message">
                                                <i class="fa-solid fa-exclamation-circle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Longitude</label>
                                        <div style="position: relative;">
                                            <input 
                                                name="hub_lng" 
                                                type="number" 
                                                step="any" 
                                                class="form-input"
                                                placeholder="10.1815"
                                                value="{{ old('hub_lng', $delivery->hub_lng) }}" 
                                            />
                                            <i class="fa-solid fa-globe" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 0.8rem;"></i>
                                        </div>
                                        @error('hub_lng')
                                            <div class="error-message">
                                                <i class="fa-solid fa-exclamation-circle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Current Status Badge --}}
                        <div style="background: rgba(248, 250, 252, 0.8); border: 1px solid rgba(226, 232, 240, 0.6); border-radius: 12px; padding: 1.5rem;">
                            <h3 style="color: #1e293b; font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                                <i class="fa-solid fa-info-circle" style="color: #3b82f6;"></i>
                                Current Status
                            </h3>
                            
                            <div style="text-align: center; padding: 1rem;">
                                <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 1rem;">Current Delivery Status</div>
                                <span style="display: inline-block; padding: 0.75rem 1.5rem; border-radius: 20px; font-weight: 600; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.5px;
                                    @if($delivery->status === 'scheduled') background: linear-gradient(135deg, #fbbf24, #f59e0b); color: white; box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
                                    @elseif($delivery->status === 'assigned') background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
                                    @elseif($delivery->status === 'in_transit') background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
                                    @elseif($delivery->status === 'delivered') background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
                                    @elseif($delivery->status === 'failed') background: linear-gradient(135deg, #ef4444, #dc2626); color: white; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
                                    @elseif($delivery->status === 'cancelled') background: linear-gradient(135deg, #6b7280, #4b5563); color: white; box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
                                    @else background: linear-gradient(135deg, #6b7280, #4b5563); color: white; box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3); @endif">
                                    {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                </span>
                                
                                @if($delivery->courier)
                                <div style="margin-top: 1.5rem; color: #64748b; font-size: 0.9rem;">
                                    <div style="font-weight: 600; margin-bottom: 0.5rem;">Assigned Courier:</div>
                                    <div style="color: #1e293b; font-weight: 500;">{{ $delivery->courier->name }}</div>
                                    <div style="color: #64748b; font-size: 0.85rem;">{{ $delivery->courier->email }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="form-actions">
                    <a href="{{ url()->previous(route('admin.deliveries.index')) }}" class="btn btn-cancel-form">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.querySelector('input[name="courier_phone"]');
    const form = document.querySelector('form');

    // Validation du numéro de téléphone
    function validatePhone() {
        const phone = phoneInput.value.trim();
        if (phone.length !== 8 || !/^\d{8}$/.test(phone)) {
            phoneInput.setCustomValidity('Please enter exactly 8 digits');
            phoneInput.reportValidity();
            return false;
        } else {
            phoneInput.setCustomValidity('');
        }
        return true;
    }

    // Événements de validation
    phoneInput.addEventListener('input', function() {
        // Ne garder que les chiffres
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Limiter à 8 chiffres
        if (this.value.length > 8) {
            this.value = this.value.substring(0, 8);
        }
    });

    phoneInput.addEventListener('blur', validatePhone);

    // Validation avant soumission
    form.addEventListener('submit', function(e) {
        if (!validatePhone()) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endsection