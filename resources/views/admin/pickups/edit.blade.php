@extends('layouts.admin')

@section('title','Edit Pickup ')

@section('admin-content')
<style>
    .modern-admin-edit {
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
    
    .edit-form-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        position: relative;
        overflow: hidden;
    }
    
    .edit-form-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #00ff88, #00d4aa, #38b2ac);
        border-radius: 12px 12px 0 0;
    }
    
    .form-title {
        color: #1e293b;
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    
    .form-icon {
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
    
    .form-group {
        margin-bottom: 0.75rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }
    
    .form-row.two {
        grid-template-columns: 1fr 1fr;
    }
    
    .form-label {
        color: #374151;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.3rem;
        display: block;
    }
    
    .form-input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid rgba(209, 213, 219, 0.8);
        border-radius: 6px;
        font-size: 0.8rem;
        color: #1e293b;
        background: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #00ff88;
        box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.1);
        background: white;
    }
    
    .form-select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid rgba(209, 213, 219, 0.8);
        border-radius: 6px;
        font-size: 0.8rem;
        color: #1e293b;
        background: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
    }
    
    .form-select:focus {
        outline: none;
        border-color: #00ff88;
        box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.1);
        background: white;
    }
    
    .form-textarea {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid rgba(209, 213, 219, 0.8);
        border-radius: 6px;
        font-size: 0.8rem;
        color: #1e293b;
        background: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
        resize: vertical;
        min-height: 60px;
    }
    
    .form-textarea:focus {
        outline: none;
        border-color: #00ff88;
        box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.1);
        background: white;
    }
    
    .listing-display {
        background: rgba(0, 255, 136, 0.08);
        border: 1px solid rgba(0, 255, 136, 0.15);
        border-radius: 6px;
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
        color: #1e293b;
        font-weight: 600;
    }
    
    .listing-id {
        color: #64748b;
        font-weight: 500;
        font-size: 0.75rem;
    }
    
    .error-message {
        color: #dc2626;
        font-size: 0.7rem;
        margin-top: 0.25rem;
        font-weight: 500;
    }
    
    .form-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(226, 232, 240, 0.8);
    }
    
    .btn {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }
    
    .btn-cancel {
        background: rgba(107, 114, 128, 0.1);
        color: #6b7280;
        border: 1px solid rgba(107, 114, 128, 0.2);
    }
    
    .btn-cancel:hover {
        background: rgba(107, 114, 128, 0.15);
        transform: translateY(-1px);
    }
    
    .btn-save {
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        color: white;
        box-shadow: 0 2px 8px rgba(0, 255, 136, 0.3);
    }
    
    .btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 255, 136, 0.4);
    }
    
    @media (max-width: 768px) {
        .admin-container {
            padding: 0 0.5rem;
        }
        
        .form-row.two {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            justify-content: center;
        }
    }
</style>

<div class="modern-admin-edit">
    <div class="admin-container">
        <div class="page-header">
            <h1 class="page-title">
                <div class="title-icon">
                    <i class="fa-solid fa-edit"></i>
                </div>
                Edit Pickup
            </h1>
            <p class="page-subtitle">Update details for pickup </p>
        </div>

        <div class="edit-form-card">
            <div class="form-title">
                <div class="form-icon">
                    <i class="fa-solid fa-truck"></i>
                </div>
                Pickup 
            </div>

            <form method="POST" action="{{ route('admin.pickups.update', $pickup) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Waste Item Title</label>
                    <div class="listing-display">
                        {{ $pickup->wasteItem->title ?? '—' }} 
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="pickup_address">Pickup address *</label>
                    <input type="text" 
                           id="pickup_address"
                           name="pickup_address" 
                           class="form-input"
                           value="{{ old('pickup_address',$pickup->pickup_address) }}" 
                           required minlength="10" maxlength="255"
                           pattern="^[a-zA-Z0-9\s\-,\.#]+$"
                           title="Please enter a valid address (letters, numbers, spaces, hyphens, commas, periods, and # allowed)">
                    @error('pickup_address') 
                        <div class="error-message">{{ $message }}</div> 
                    @enderror
                </div>

                <div class="form-row two">
                    <div class="form-group">
                        <label class="form-label" for="window_start">Window start *</label>
                        <input type="datetime-local"
                               id="window_start"
                               name="scheduled_pickup_window_start"
                               class="form-input"
                               value="{{ old('scheduled_pickup_window_start', optional($pickup->scheduled_pickup_window_start)->format('Y-m-d\TH:i')) }}"
                               required
                               title="Please select a valid date and time">
                        @error('scheduled_pickup_window_start') 
                            <div class="error-message">{{ $message }}</div> 
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="window_end">Window end *</label>
                        <input type="datetime-local"
                               id="window_end"
                               name="scheduled_pickup_window_end"
                               class="form-input"
                               value="{{ old('scheduled_pickup_window_end', optional($pickup->scheduled_pickup_window_end)->format('Y-m-d\TH:i')) }}"
                               required
                               title="Please select a valid date and time">
                        @error('scheduled_pickup_window_end') 
                            <div class="error-message">{{ $message }}</div> 
                        @enderror
                    </div>
                </div>

                <div class="form-row two">
                    <div class="form-group">
                        <label class="form-label" for="status">Status *</label>
                        <select id="status" name="status" class="form-select">
                            @foreach (['scheduled','assigned','in_transit','picked','failed','cancelled'] as $s)
                                <option value="{{ $s }}" @selected(old('status',$pickup->status)===$s)>
                                    {{ ucfirst(str_replace('_',' ',$s)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') 
                            <div class="error-message">{{ $message }}</div> 
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="tracking_code">Tracking code</label>
                        <input type="text" 
                               id="tracking_code"
                               name="tracking_code" 
                               class="form-input"
                               value="{{ old('tracking_code',$pickup->tracking_code) }}"
                               readonly
                               style="background-color: #f8f9fa; color: #6c757d; cursor: not-allowed;">
                        <small style="color: #64748b; font-size: 0.7rem; margin-top: 0.25rem; display: block;">
                            Tracking code is automatically generated and cannot be modified
                        </small>
                        @error('tracking_code') 
                            <div class="error-message">{{ $message }}</div> 
                        @enderror
                    </div>
                </div>

                <div class="form-row two">
                    <div class="form-group">
                        <label class="form-label" for="courier_id">Courier (id)</label>
                        <input type="number" 
                               id="courier_id"
                               name="courier_id" 
                               class="form-input"
                               value="{{ old('courier_id',$pickup->courier_id) }}">
                        @error('courier_id') 
                            <div class="error-message">{{ $message }}</div> 
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="notes">Notes</label>
                        <textarea id="notes" 
                                  name="notes" 
                                  class="form-textarea"
                                  rows="3">{{ old('notes',$pickup->notes) }}</textarea>
                        @error('notes') 
                            <div class="error-message">{{ $message }}</div> 
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ url()->previous() ?: route('admin.pickups.index') }}" class="btn btn-cancel">
                        <i class="fa-solid fa-times"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-save">
                        <i class="fa-solid fa-save"></i>
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startInput = document.querySelector('input[name="scheduled_pickup_window_start"]');
    const endInput = document.querySelector('input[name="scheduled_pickup_window_end"]');
    const addressInput = document.querySelector('input[name="pickup_address"]');
    const form = document.querySelector('form');

    // Validation de la fenêtre de temps
    function validateTimeWindow() {
        if (startInput.value && endInput.value) {
            const startTime = new Date(startInput.value);
            const endTime = new Date(endInput.value);
            
            if (endTime <= startTime) {
                endInput.setCustomValidity('End time must be after start time');
                endInput.reportValidity();
                return false;
            } else {
                endInput.setCustomValidity('');
            }
        }
        return true;
    }

    // Validation de l'adresse
    function validateAddress() {
        const address = addressInput.value.trim();
        if (address.length < 10) {
            addressInput.setCustomValidity('Address must be at least 10 characters long');
            addressInput.reportValidity();
            return false;
        } else {
            addressInput.setCustomValidity('');
        }
        return true;
    }

    // Événements de validation
    startInput.addEventListener('change', function() {
        if (endInput.value) {
            validateTimeWindow();
        }
        // Mettre à jour le min de l'input end
        endInput.min = this.value;
    });

    endInput.addEventListener('change', validateTimeWindow);
    addressInput.addEventListener('blur', validateAddress);

    // Validation avant soumission
    form.addEventListener('submit', function(e) {
        if (!validateAddress() || !validateTimeWindow()) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endsection
