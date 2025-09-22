document.addEventListener('DOMContentLoaded', () => {
    const popup = document.getElementById('genericConfirmPopup');
    const messageEl = popup.querySelector('.popup-message');
    const confirmBtn = popup.querySelector('.btn-confirm');
    const cancelBtn = popup.querySelector('.btn-cancel');

    let currentAction = null;
    let currentUser = null;
    let currentRoleSelect = null;
    let newRole = null;

    function openPopup(action, user, roleSelect = null, role = null) {
        currentAction = action;
        currentUser = user;
        currentRoleSelect = roleSelect;
        newRole = role;

        if(action === 'role-change') {
            messageEl.textContent = `Change ${user}'s role to "${role}"?`;
        } else {
            messageEl.textContent = `Are you sure you want to ${action} ${user}?`;
        }

        popup.classList.remove('hidden');
    }

    confirmBtn.addEventListener('click', () => {
        if(currentAction === 'role-change') {
            currentRoleSelect.setAttribute('data-current', newRole);
            alert(`Role changed to ${newRole} for ${currentUser} (demo)`);
        } else {
            alert(`${currentAction.toUpperCase()} confirmed for ${currentUser} (demo)`);
        }
        popup.classList.add('hidden');
    });

    cancelBtn.addEventListener('click', () => popup.classList.add('hidden'));

    document.querySelectorAll('.icon-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.getAttribute('data-action');
            const user = btn.closest('tr').querySelector('.user-info').textContent.trim();
            openPopup(action, user);
        });
    });

    document.querySelectorAll('.role-select').forEach(select => {
        select.addEventListener('change', () => {
            const user = select.closest('tr').querySelector('.user-info').textContent.trim();
            const currentRole = select.getAttribute('data-current');
            const newRoleValue = select.value;

            if(newRoleValue !== currentRole) {
                openPopup('role-change', user, select, newRoleValue);
            }
        });
    });
});