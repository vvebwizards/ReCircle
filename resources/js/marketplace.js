document.addEventListener('DOMContentLoaded', () => {
    // Open modal and populate content
    document.querySelectorAll('[data-modal-open]').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-modal-open');
            const modal = document.getElementById(modalId);
            if (!modal) return;

            const card = button.closest('.dash-card');
            const title = card.querySelector('.card-title').textContent;
            const description = card.querySelector('.card-desc').textContent;
            const priceText = card.querySelector('.chip').textContent; // "$5/kg"
            const unitPrice = parseFloat(priceText.replace(/[^0-9.]/g, ''));

            // Store unit price on modal for later
            modal.dataset.unitPrice = unitPrice;

            // Populate modal content
            modal.querySelector('#order-title').textContent = title;
            modal.querySelector('#order-description').textContent = description;

            const orderPriceElem = modal.querySelector('#order-price');
            if (orderPriceElem) {
                orderPriceElem.textContent = `$${unitPrice}/kg`;
            }

            // Clear previous quantity
            modal.querySelector('#order-amount').value = '';

            // Show modal
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
        });
    });

    // Close modal
    document.querySelectorAll('[data-modal-close]').forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal-overlay');
            if (modal) {
                modal.classList.add('hidden');
                modal.setAttribute('aria-hidden', 'true');
            }
        });
    });

    // Close when clicking outside modal
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) {
                overlay.classList.add('hidden');
                overlay.setAttribute('aria-hidden', 'true');
            }
        });
    });

    // Confirm order
    document.getElementById('order-confirm').addEventListener('click', async () => {
        const modal = document.getElementById('order-modal');
        const quantityInput = modal.querySelector('#order-amount');
        const quantity = parseInt(quantityInput.value);

        if (!quantity || quantity < 1) {
            alert('Please enter a valid quantity.');
            return;
        }

        const unit_price = parseFloat(modal.dataset.unitPrice);

        try {
            const response = await fetch('/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    quantity: quantity,
                    unit_price: unit_price
                })
            });

            const data = await response.json();
            alert(data.message);

            // Close modal
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
        } catch (error) {
            console.error('Order failed:', error);
            alert('Failed to create order');
        }
    });
});
