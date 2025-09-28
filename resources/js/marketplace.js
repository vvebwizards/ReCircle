document.addEventListener('DOMContentLoaded', () => {
    // Open modal and populate content
    document.querySelectorAll('[data-modal-open]').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-modal-open');
            const modal = document.getElementById(modalId);
            if (modal) {
                const card = button.closest('.dash-card');
                const title = card.querySelector('.card-title').textContent;
                const description = card.querySelector('.card-desc').textContent;
                
                const orderTitle = modal.querySelector('#order-title');
                const orderDescription = modal.querySelector('#order-description');
                orderTitle.textContent = title;
                orderDescription.textContent = description;

                modal.classList.remove('hidden');
                modal.setAttribute('aria-hidden', 'false');
            }
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

    document.getElementById('order-confirm').addEventListener('click', async () => {
    try {
        const response = await fetch('/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({})
        });

        const data = await response.json();
        alert(data.message);
    } catch (error) {
        console.error('Order failed:', error);
        alert('Failed to create order');
    }
});
});