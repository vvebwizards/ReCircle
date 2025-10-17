@extends('layouts.app')

@section('title','Edit Pickup #'.$pickup->id)

@section('content')
<style>
    .modern-edit-page {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
        min-height: 100vh;
        padding: 8rem 0 4rem 0;
    }
    
    .edit-container {
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
    
    .listing-info {
        background: rgba(0, 255, 136, 0.08);
        border: 1px solid rgba(0, 255, 136, 0.15);
        border-radius: 12px;
        padding: 1.25rem;
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
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .form-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        position: relative;
        overflow: hidden;
    }
    
    .form-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #00ff88, #00d4aa, #38b2ac);
        border-radius: 20px 20px 0 0;
    }
    
    .form-group {
        margin-bottom: 2rem;
    }
    
    .form-label {
        color: #1e293b;
        font-weight: 700;
        margin-bottom: 0.75rem;
        display: block;
        font-size: 1rem;
        letter-spacing: 0.025em;
    }
    
    .form-input {
        width: 100%;
        padding: 1.25rem 1.5rem;
        background: #ffffff;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        color: #1e293b;
        font-size: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .form-input:focus {
        outline: none;
        border-color: #00ff88;
        background: #ffffff;
        box-shadow: 
            0 0 0 4px rgba(0, 255, 136, 0.1),
            0 8px 25px rgba(0, 255, 136, 0.15);
        transform: translateY(-2px);
    }
    
    .form-input::placeholder {
        color: #a0aec0;
        font-weight: 500;
    }
    
    .form-select {
        width: 100%;
        padding: 1.25rem 1.5rem;
        background: #ffffff;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        color: #1e293b;
        font-size: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 1.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 4rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .form-select:focus {
        outline: none;
        border-color: #00ff88;
        background: #ffffff;
        box-shadow: 
            0 0 0 4px rgba(0, 255, 136, 0.1),
            0 8px 25px rgba(0, 255, 136, 0.15);
        transform: translateY(-2px);
    }
    
    .form-textarea {
        resize: vertical;
        min-height: 140px;
        font-family: inherit;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .error-message {
        color: #e53e3e;
        font-size: 0.9rem;
        margin-top: 0.5rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .error-message::before {
        content: '⚠️';
        font-size: 0.8rem;
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
    
    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        justify-content: center;
    }
    
    .btn-action {
        padding: 1rem 2rem;
        border-radius: 14px;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        border: none;
        cursor: pointer;
    }
    
    .btn-save {
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
    }
    
    .btn-cancel {
        background: linear-gradient(135deg, #64748b, #475569);
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(100, 116, 139, 0.3);
    }
    
    .btn-action:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        color: #ffffff;
        text-decoration: none;
    }
    
    .btn-save:hover {
        box-shadow: 0 15px 40px rgba(0, 255, 136, 0.5);
    }
    
    .btn-cancel:hover {
        box-shadow: 0 15px 40px rgba(100, 116, 139, 0.5);
    }
    
    @media (max-width: 768px) {
        .edit-container {
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
        
        .form-container {
            padding: 1.5rem;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
            align-items: center;
        }
    }
</style>

<div class="modern-edit-page">
    <div class="edit-container">
        @if(session('success'))
            <div class="success-message">{{ session('success') }}</div>
        @endif

        <div class="page-header">
            <h1 class="page-title">
                <span class="title-icon">
                    <i class="fa-solid fa-pen-to-square"></i>
                </span>
                Edit Pickup 
            </h1>
            <p class="page-subtitle">Update pickup information and settings</p>
            
            <div class="listing-info">
                <div class="listing-label">Current Waste Item</div>
                <div class="listing-title">{{ $pickup->wasteItem->title ?? '—' }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('pickups.update', $pickup) }}" class="form-container">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Pickup address *</label>
                <input name="pickup_address" value="{{ old('pickup_address', $pickup->pickup_address) }}"
                       class="form-input" placeholder="Enter the complete pickup address" 
                       required minlength="10" maxlength="255"
                       pattern="^[a-zA-Z0-9\s\-,\.#]+$"
                       title="Please enter a valid address (letters, numbers, spaces, hyphens, commas, periods, and # allowed)">
                @error('pickup_address') <p class="error-message">{{ $message }}</p> @enderror
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Window start *</label>
                    <input type="datetime-local" name="scheduled_pickup_window_start"
                           value="{{ old('scheduled_pickup_window_start', optional($pickup->scheduled_pickup_window_start)->format('Y-m-d\TH:i')) }}"
                           class="form-input" required
                           title="Please select a valid date and time">
                    @error('scheduled_pickup_window_start') <p class="error-message">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Window end *</label>
                    <input type="datetime-local" name="scheduled_pickup_window_end"
                           value="{{ old('scheduled_pickup_window_end', optional($pickup->scheduled_pickup_window_end)->format('Y-m-d\TH:i')) }}"
                           class="form-input" required
                           title="Please select a valid date and time">
                    @error('scheduled_pickup_window_end') <p class="error-message">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" disabled>
                    <option value="scheduled" selected>Scheduled</option>
                </select>
                <input type="hidden" name="status" value="scheduled">
                <small style="color: #64748b; font-size: 0.8rem; margin-top: 0.5rem; display: block;">
                    Status is automatically set to "Scheduled" for pickup updates
                </small>
                @error('status') <p class="error-message">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Notes (Optional)</label>
                <textarea name="notes" rows="4" class="form-input form-textarea" 
                          placeholder="Add any special instructions or additional details..."
                          maxlength="500">{{ old('notes', $pickup->notes) }}</textarea>
                <small style="color: #64748b; font-size: 0.8rem; margin-top: 0.5rem; display: block;">
                    Optional field - maximum 500 characters
                </small>
                @error('notes') <p class="error-message">{{ $message }}</p> @enderror
            </div>

            <div class="form-actions">
                <a href="{{ route('pickups.show', $pickup) }}" class="btn-action btn-cancel">
                    <i class="fa-solid fa-xmark"></i>
                    Cancel
                </a>
                <button type="submit" class="btn-action btn-save">
                    <i class="fa-solid fa-check"></i>
                    Save Changes
                </button>
            </div>
        </form>
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

    // Mise à jour automatique du min des inputs datetime
    const now = new Date();
    now.setMinutes(now.getMinutes() - 60); // Permettre 1 heure dans le passé pour l'édition
    const minDateTime = now.toISOString().slice(0, 16);
    
    startInput.min = minDateTime;
    endInput.min = minDateTime;
});
</script>
@endsection