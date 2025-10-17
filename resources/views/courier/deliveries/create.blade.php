@extends('layouts.app')

@section('title', 'Select Delivery')

@section('content')
<style>
    .modern-delivery-page {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
        min-height: 100vh;
        padding: 8rem 0 4rem 0;
    }
    
    .delivery-container {
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
    
    .pickup-info-card {
        background: rgba(0, 255, 136, 0.08);
        border: 1px solid rgba(0, 255, 136, 0.15);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .pickup-info-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #00ff88, #00d4aa);
    }
    
    .pickup-title {
        color: #1e293b;
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .pickup-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9rem;
    }
    
    .pickup-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .pickup-detail {
        background: rgba(255, 255, 255, 0.8);
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid rgba(0, 255, 136, 0.1);
    }
    
    .pickup-detail-label {
        color: #059669;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    
    .pickup-detail-value {
        color: #1e293b;
        font-size: 0.9rem;
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
    
    .form-section {
        background: rgba(248, 250, 252, 0.8);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    
    .section-title {
        color: #1e293b;
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .section-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
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
    
    .form-textarea {
        resize: vertical;
        min-height: 120px;
        font-family: inherit;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .checkbox-container {
        background: rgba(0, 255, 136, 0.08);
        border: 1px solid rgba(0, 255, 136, 0.15);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .checkbox-input {
        width: 20px;
        height: 20px;
        accent-color: #00ff88;
        cursor: pointer;
    }
    
    .checkbox-label {
        color: #1e293b;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        flex: 1;
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
    
    .form-actions {
        display: flex;
        gap: 1rem;
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
        .delivery-container {
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
        
        .form-section {
            padding: 1.5rem;
        }
        
        .pickup-details {
            grid-template-columns: 1fr;
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

<div class="modern-delivery-page">
    <div class="delivery-container">
        <div class="page-header">
            <h1 class="page-title">
                <span class="title-icon">
                    <i class="fa-solid fa-truck-fast"></i>
                </span>
                Select Delivery
            </h1>
            <p class="page-subtitle">Create a new delivery for the selected pickup</p>
        </div>

        <div class="pickup-info-card">
            <div class="pickup-title">
                <span class="pickup-icon">
                    <i class="fa-solid fa-box"></i>
                </span>
                Pickup For Waste Item: {{ $pickup->wasteItem->title ?? '—' }}
            </div>
            
            <div class="pickup-details">
                <div class="pickup-detail">
                    <div class="pickup-detail-label">Pickup Address</div>
                    <div class="pickup-detail-value">{{ $pickup->pickup_address }}</div>
                </div>
                <div class="pickup-detail">
                    <div class="pickup-detail-label">Window Start</div>
                    <div class="pickup-detail-value">
                        {{ optional($pickup->scheduled_pickup_window_start)->format('M d, Y H:i') ?? '—' }}
                    </div>
                </div>
                <div class="pickup-detail">
                    <div class="pickup-detail-label">Window End</div>
                    <div class="pickup-detail-value">
                        {{ optional($pickup->scheduled_pickup_window_end)->format('M d, Y H:i') ?? '—' }}
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('deliveries.storeFromPickup', $pickup) }}" class="form-container">
            @csrf

            <div class="form-section">
                <h3 class="section-title">
                    <span class="section-icon">📞</span>
                    Courier Information
                </h3>
                
                <div class="form-group">
                    <label class="form-label">Courier Phone *</label>
                    <input type="text" name="courier_phone" value="{{ old('courier_phone', $defaults['courier_phone']) }}"
                           class="form-input" placeholder="Enter courier phone number (8 digits)" 
                           required pattern="^\d{8}$" maxlength="8" minlength="8"
                           title="Please enter exactly 8 digits">
                    <small style="color: #64748b; font-size: 0.8rem; margin-top: 0.5rem; display: block;">
                        Must be exactly 8 digits
                    </small>
                    @error('courier_phone') <p class="error-message">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">
                    <span class="section-icon">🏢</span>
                    Hub Information
                </h3>
                
                <div class="form-group">
                    <label class="form-label">Hub Address *</label>
                    <input type="text" name="hub_address" value="{{ old('hub_address', $defaults['hub_address']) }}"
                           class="form-input" placeholder="Enter hub address" required>
                    @error('hub_address') <p class="error-message">{{ $message }}</p> @enderror
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Hub Latitude</label>
                        <input type="number" step="0.0000001" name="hub_lat" value="{{ old('hub_lat', $defaults['hub_lat']) }}"
                               class="form-input" placeholder="Latitude">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hub Longitude</label>
                        <input type="number" step="0.0000001" name="hub_lng" value="{{ old('hub_lng', $defaults['hub_lng']) }}"
                               class="form-input" placeholder="Longitude">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">
                    <span class="section-icon">📝</span>
                    Additional Information
                </h3>
                
                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" rows="4" class="form-input form-textarea" 
                              placeholder="Add any special instructions or additional details..."
                              maxlength="500">{{ old('notes') }}</textarea>
                    <small style="color: #64748b; font-size: 0.8rem; margin-top: 0.5rem; display: block;">
                        Optional field - maximum 500 characters
                    </small>
                    @error('notes') <p class="error-message">{{ $message }}</p> @enderror
                </div>

                <div class="checkbox-container">
                    <input type="checkbox" name="start_now" value="1" class="checkbox-input" id="start_now">
                    <label for="start_now" class="checkbox-label">
                        Start delivery now (set status to <strong>in_transit</strong>)
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('pickups.index') }}" class="btn-action btn-cancel">
                    <i class="fa-solid fa-xmark"></i>
                    Cancel
                </a>
                <button type="submit" class="btn-action btn-save">
                    <i class="fa-solid fa-check"></i>
                    Create Delivery
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.querySelector('input[name="courier_phone"]');
    const form = document.querySelector('form');

    // Validation du numéro de téléphone
    function validatePhone() {
        const phone = phoneInput.value.trim();
        
        // Supprimer tous les caractères non numériques
        const cleanPhone = phone.replace(/\D/g, '');
        
        if (cleanPhone.length !== 8) {
            phoneInput.setCustomValidity('Phone number must be exactly 8 digits');
            phoneInput.reportValidity();
            return false;
        } else {
            phoneInput.setCustomValidity('');
            // Mettre à jour la valeur avec seulement les chiffres
            phoneInput.value = cleanPhone;
        }
        return true;
    }

    // Événements de validation
    phoneInput.addEventListener('input', function() {
        // Supprimer automatiquement les caractères non numériques
        this.value = this.value.replace(/\D/g, '');
        
        // Limiter à 8 caractères
        if (this.value.length > 8) {
            this.value = this.value.slice(0, 8);
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