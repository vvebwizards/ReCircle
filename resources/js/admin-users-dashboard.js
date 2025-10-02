document.addEventListener('DOMContentLoaded', () => {
    const popup = document.getElementById('genericConfirmPopup');
    const messageEl = popup.querySelector('.popup-message');
    const confirmBtn = popup.querySelector('.btn-confirm');
    const cancelBtn = popup.querySelector('.btn-cancel');

    let currentForm = null;


    function openPopup(action, user, form) {
        currentForm = form;


        messageEl.textContent = action === 'role-change'
            ? `Change ${user}'s role?`
            : `Are you sure you want to ${action} ${user}?`;

        popup.classList.remove('hidden');
    }

    confirmBtn.addEventListener('click', () => {
        if (currentForm) currentForm.submit();
        popup.classList.add('hidden');
    });

    cancelBtn.addEventListener('click', () => {
        popup.classList.add('hidden');
        currentForm = null;
    });

    document.querySelectorAll('.role-select').forEach(select => {
        select.addEventListener('change', () => {
            const form = select.closest('form');
            const user = select.dataset.user;
            const currentRole = select.dataset.current;
            const newRole = select.value;

            if (newRole !== currentRole) {
                openPopup('role-change', user, form);
            }
        });
    });

    document.querySelectorAll('.toggle-status-form .toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const form = btn.closest('form');
            const user = btn.dataset.user;
            const action = btn.dataset.action;
            openPopup(action, user, form);
        });
    });
});
