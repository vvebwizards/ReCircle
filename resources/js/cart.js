document.addEventListener('DOMContentLoaded', () => {
    const applyBtn = document.getElementById('apply-promo');
    const promoInput = document.getElementById('promo-code');
    const subtotalElem = document.getElementById('subtotal');
    const discountElem = document.getElementById('discount');
    const totalElem = document.getElementById('total');
    const hiddenPromo = document.getElementById('hidden-promo');
    const promoMessage = document.getElementById('promo-message');

    // Get the initial subtotal from the data attribute (safer than textContent)
    const initialSubtotal = parseFloat(subtotalElem.dataset.amount);

    /**
     * Simulates an API call to validate the promo code and calculate discount.
     * In a real application, this would use fetch/Axios to hit a Laravel route.
     * @param {string} code - The promo code entered by the user.
     * @returns {Promise<{discount: number, message: string, valid: boolean}>}
     */
    const validatePromoCode = async (code) => {
        // --- START: SIMULATED API CALL ---
        await new Promise(resolve => setTimeout(resolve, 800)); // Simulate network delay

        let discountPercentage = 0;
        let message = '';
        let valid = false;

        if (code.toUpperCase() === 'SAVE10') {
            discountPercentage = 0.10;
            message = 'Success: 10% discount applied!';
            valid = true;
        } else if (code.toUpperCase() === 'SAVE20') {
            discountPercentage = 0.20;
            message = 'Success: 20% discount applied!';
            valid = true;
        } else {
            message = 'Invalid promo code. Please try again.';
            valid = false;
        }

        const discount = initialSubtotal * discountPercentage;

        return {
            discount: discount,
            message: message,
            valid: valid
        };
        // --- END: SIMULATED API CALL ---
    };

    const updateTotals = (discountAmount, code) => {
        const discount = parseFloat(discountAmount);
        const total = initialSubtotal - discount;

        discountElem.textContent = discount.toFixed(2);
        totalElem.textContent = total.toFixed(2);
        hiddenPromo.value = code;
    };

    applyBtn.addEventListener('click', async () => {
        const code = promoInput.value.trim();
        
        // Reset state
        promoMessage.textContent = '';
        promoMessage.style.color = '';
        updateTotals(0, '');
        
        if (code === '') {
            promoMessage.textContent = 'Please enter a promo code.';
            promoMessage.style.color = '#EF4444'; // Red
            return;
        }

        // Add loading state
        applyBtn.textContent = 'Applying...';
        applyBtn.disabled = true;

        try {
            const result = await validatePromoCode(code);

            if (result.valid) {
                // Apply the discount and update message
                updateTotals(result.discount, code);
                promoMessage.textContent = result.message;
                promoMessage.style.color = '#10B981'; // Green (Success)
            } else {
                // Keep discount at 0, only update message
                updateTotals(0, '');
                promoMessage.textContent = result.message;
                promoMessage.style.color = '#EF4444'; // Red (Error)
            }
        } catch (error) {
            console.error('Error applying promo code:', error);
            promoMessage.textContent = 'An error occurred. Please try again.';
            promoMessage.style.color = '#EF4444';
            updateTotals(0, '');
        } finally {
            // Remove loading state
            applyBtn.textContent = 'Apply';
            applyBtn.disabled = false;
        }
    });
});