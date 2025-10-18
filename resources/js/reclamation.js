// public/js/reclamationForm.js
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const topicInput = document.getElementById('topic');
    const descriptionInput = document.getElementById('description');

    form.addEventListener('submit', function (e) {
        let valid = true;

        // Clear previous error messages
        document.querySelectorAll('.error-message-js').forEach(el => el.remove());

        // Validate topic
        if (topicInput.value.trim().length < 3) {
            showError(topicInput, 'Topic must be at least 3 characters.');
            valid = false;
        }

        // Validate description
        if (descriptionInput.value.trim().length < 10) {
            showError(descriptionInput, 'Description must be at least 10 characters.');
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });

    function showError(input, message) {
        const error = document.createElement('p');
        error.classList.add('error-message-js', 'mt-1', 'text-sm', 'text-red-600');
        error.innerText = message;
        input.insertAdjacentElement('afterend', error);
        input.classList.add('border-red-500');
    }
});
