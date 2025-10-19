document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const startInput = form.querySelector('input[name="scheduled_pickup_window_start"]');
    const endInput = form.querySelector('input[name="scheduled_pickup_window_end"]');
    const addressInput = form.querySelector('input[name="pickup_address"]');

    // Helper: display error message under input
    function showError(inputName, message) {
        const errorEl = document.querySelector(`.js-error-message[data-for="${inputName}"]`);
        const inputEl = document.querySelector(`input[name="${inputName}"], textarea[name="${inputName}"]`);
        
        if (errorEl) {
            if (message) {
                errorEl.textContent = message;
                errorEl.style.color = '#e53e3e';
                errorEl.style.fontWeight = '600';
                errorEl.style.marginTop = '0.5rem';
                errorEl.style.fontSize = '0.9rem';
                errorEl.style.display = 'block';
                
                // Add red border to input
                if (inputEl) {
                    inputEl.style.borderColor = '#e53e3e';
                    inputEl.style.backgroundColor = '#fff5f5';
                }
            } else {
                errorEl.textContent = '';
                errorEl.style.display = 'none';
                
                // Remove red border from input
                if (inputEl) {
                    inputEl.style.borderColor = '#e2e8f0';
                    inputEl.style.backgroundColor = '#ffffff';
                }
            }
        }
    }

    // Address validation
    function validateAddress() {
        const address = addressInput.value.trim();
        const pattern = /^[a-zA-Z0-9\s\-,\.#]+$/;
        let valid = true;

        if (address.length === 0) {
            showError('pickup_address', '⚠️ Address is required');
            valid = false;
        } else if (address.length < 3) {
            showError('pickup_address', '⚠️ Address must be at least 3 characters long');
            valid = false;
        } else if (!pattern.test(address)) {
            showError('pickup_address', '⚠️ Invalid characters in address');
            valid = false;
        } else {
            showError('pickup_address', '');
        }

        return valid;
    }

    // Start date validation - INDEPENDENT
    function validateStartDate() {
        if (!startInput.value) {
            showError('scheduled_pickup_window_start', '⚠️ Start date and time is required');
            return false;
        }

        const now = new Date();
        const start = new Date(startInput.value);
        
        // Set to start of current day
        const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());

        if (start < startOfToday) {
            showError('scheduled_pickup_window_start', '⚠️ Start date must be today or later');
            return false;
        }

        showError('scheduled_pickup_window_start', '');
        return true;
    }

    // End date validation - INDEPENDENT
    function validateEndDate() {
        if (!endInput.value) {
            showError('scheduled_pickup_window_end', '⚠️ End date and time is required');
            return false;
        }

        if (startInput.value) {
            const start = new Date(startInput.value);
            const end = new Date(endInput.value);

            if (end <= start) {
                showError('scheduled_pickup_window_end', '⚠️ End time must be after start time');
                return false;
            }
        }

        showError('scheduled_pickup_window_end', '');
        return true;
    }

    // Real-time listeners - each field validates independently
    addressInput.addEventListener('input', validateAddress);
    addressInput.addEventListener('blur', validateAddress);
    
    startInput.addEventListener('change', () => {
        if (startInput.value) {
            endInput.min = startInput.value;
        }
        validateStartDate();
        validateEndDate();
    });
    
    startInput.addEventListener('blur', validateStartDate);
    
    endInput.addEventListener('change', () => {
        validateStartDate();
        validateEndDate();
    });
    
    endInput.addEventListener('blur', validateEndDate);

    // Before submit - validate all fields
    form.addEventListener('submit', function(e) {
        const validAddress = validateAddress();
        const validStart = validateStartDate();
        const validEnd = validateEndDate();
        
        if (!validAddress || !validStart || !validEnd) {
            e.preventDefault();
        }
    });

    // Initialize datetime min (start of today)
    const now = new Date();
    const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const minDateTime = startOfToday.toISOString().slice(0, 16);
    startInput.min = minDateTime;
    endInput.min = minDateTime;
});