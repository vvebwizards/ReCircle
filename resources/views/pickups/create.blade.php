@extends('layouts.app')

@section('title','Create Pickup')

@push('head')
@vite(['resources/js/pickup.js'])
@endpush

@section('content')
<style>
    .modern-form {
        background: #ffffff;
        min-height: 100vh;
        padding: 8rem 0 4rem 0;
        position: relative;
    }
    
    .form-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 24px;
        padding: 3rem;
        max-width: 900px;
        margin: 0 auto;
        box-shadow: 
            0 25px 50px rgba(0, 0, 0, 0.15),
            0 0 0 1px rgba(255, 255, 255, 0.1);
        position: relative;
        z-index: 1;
    }

    .js-error-message {
    min-height: 1.2rem;
    transition: opacity 0.2s ease;
}
    
    .form-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #00ff88, #00d4aa, #38b2ac, #2d7a7b);
        border-radius: 24px 24px 0 0;
    }
    
    .form-title {
        color: #2d3748;
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        text-align: center;
        justify-content: center;
    }
    
    .form-title-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
        box-shadow: 0 8px 25px rgba(0, 255, 136, 0.4);
    }
    
    .form-subtitle {
        color: #718096;
        font-size: 1.1rem;
        text-align: center;
        margin-bottom: 3rem;
        font-weight: 500;
    }
    
    .form-group {
        margin-bottom: 2rem;
    }
    
    .form-label {
        color: #2d3748;
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
        color: #2d3748;
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
        color: #2d3748;
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
    
    .waste-item-display {
        background: linear-gradient(135deg, rgba(0, 255, 136, 0.1), rgba(0, 212, 170, 0.1));
        border: 2px solid rgba(0, 255, 136, 0.2);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2.5rem;
        color: #2d3748;
        position: relative;
        overflow: hidden;
    }
    
    .waste-item-display::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg,rgb(225, 225, 225),rgb(241, 244, 243));
    }
    
    .waste-item-display strong {
        color: #00ff88;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .btn-save {
        background: linear-gradient(135deg, #00ff88, #00d4aa);
        color: #ffffff;
        font-weight: 700;
        font-size: 1.2rem;
        padding: 1.25rem 3rem;
        border: none;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 10px 30px rgba(0, 255, 136, 0.4);
        width: 100%;
        margin-top: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .btn-save::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }
    
    .btn-save:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0, 255, 136, 0.5);
    }
    
    .btn-save:hover::before {
        left: 100%;
    }
    
    .btn-save:active {
        transform: translateY(-1px);
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
    
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    
    .form-section {
        background: rgba(255, 255, 255, 0.5);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .section-title {
        color: #2d3748;
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
        background: linear-gradient(135deg,rgb(5, 5, 5), #00d4aa);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
    }
    
    @media (max-width: 768px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }
        
        .form-container {
            margin: 1rem;
            padding: 2rem;
        }
        
        .form-title {
            font-size: 1.8rem;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .modern-form {
            padding: 4rem 0 2rem 0;
        }
    }
    
    @media (max-width: 480px) {
        .form-container {
            padding: 1.5rem;
            border-radius: 20px;
        }
        
        .form-title {
            font-size: 1.6rem;
        }
        
        .form-input, .form-select {
            padding: 1rem 1.25rem;
        }
    }
</style>

<div class="modern-form">
    <div class="form-container">
        <h1 class="form-title">
            <span class="form-title-icon">+</span>
            Create Pickup
        </h1>

        <form method="POST" action="{{ route('pickups.store') }}">
    @csrf

    {{-- waste item pre-selected and hidden --}}
    <input type="hidden" name="waste_item_id" value="{{ optional($wasteItem)->id }}">

    @if($wasteItem)
        <div class="waste-item-display">
            <strong>Waste item:</strong> {{ $wasteItem->title ?? 'N/A' }}
        </div>
    @endif

    <div class="form-section">
        <h3 class="section-title">
            <span class="section-icon">📍</span>
            Pickup Details
        </h3>

        {{-- Address --}}
        <div class="form-group">
            <label class="form-label">Pickup address *</label>
            <input name="pickup_address" value="{{ old('pickup_address') }}"
                   class="form-input" placeholder="Enter the complete pickup address" required>
            <p class="js-error-message" data-for="pickup_address"></p>
            @error('pickup_address') <p class="error-message">{{ $message }}</p> @enderror
        </div>

        {{-- Window start / end --}}
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label">Window start *</label>
                <input type="datetime-local" name="scheduled_pickup_window_start"
                       value="{{ old('scheduled_pickup_window_start') }}"
                       class="form-input" required>
                <p class="js-error-message" data-for="scheduled_pickup_window_start"></p>
                @error('scheduled_pickup_window_start') <p class="error-message">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Window end *</label>
                <input type="datetime-local" name="scheduled_pickup_window_end"
                       value="{{ old('scheduled_pickup_window_end') }}"
                       class="form-input" required>
                <p class="js-error-message" data-for="scheduled_pickup_window_end"></p>
                @error('scheduled_pickup_window_end') <p class="error-message">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div class="form-section">
        <h3 class="section-title">
            <span class="section-icon">⚙️</span>
            Status & Notes
        </h3>

        <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" disabled>
                <option value="scheduled" selected>Scheduled</option>
            </select>
            <input type="hidden" name="status" value="scheduled">
            <small style="color: #64748b; font-size: 0.8rem; margin-top: 0.5rem; display: block;">
                Status is automatically set to "Scheduled" for new pickups
            </small>
        </div>

        <div class="form-group">
            <label class="form-label">Notes (Optional)</label>
            <textarea name="notes" rows="4" class="form-input form-textarea"
                      placeholder="Add any special instructions or additional details..." 
                      maxlength="500">{{ old('notes') }}</textarea>
            <small style="color: #64748b; font-size: 0.8rem; margin-top: 0.5rem; display: block;">
                Optional field - maximum 500 characters
            </small>
        </div>
    </div>

    <button type="submit" class="btn-save">
        <span>Create Pickup</span>
    </button>
</form>

    </div>
</div>
@endsection