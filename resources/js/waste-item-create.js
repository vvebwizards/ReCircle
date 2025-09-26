document.addEventListener('DOMContentLoaded', () => {
    const addBtn = document.getElementById('addImageUrlBtn');
    const input = document.getElementById('newImageUrl');
    const list = document.getElementById('imageUrlList');

    const toggleButton = document.querySelector('.instructions-toggle');
    if (toggleButton) {
        toggleButton.addEventListener('click', function() {
            const content = document.querySelector('.instructions-content');
            content.classList.toggle('collapsed');
            this.classList.toggle('collapsed');
        });
    }

    function rebuildHiddenInputs() {
        // remove old hidden inputs
        list.querySelectorAll('input[type=hidden]').forEach(e => e.remove());
        // re-add based on current visible items
        const urls = Array.from(list.querySelectorAll('.image-url-item span'))
            .map(s => s.textContent.trim());
        urls.forEach(u => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'images[]';
            hidden.value = u;
            list.appendChild(hidden);
        });
    }

    function addUrl(url) {
        if (!url) return;
        const existing = Array.from(list.querySelectorAll('.image-url-item span'))
            .some(s => s.textContent.trim() === url);
        if (existing) return;
        const count = list.querySelectorAll('.image-url-item').length;
        if (count >= 10) return alert('Maximum of 10 images');

        const wrapper = document.createElement('div');
        wrapper.className = 'image-url-item';
        wrapper.style.display = 'flex';
        wrapper.style.alignItems = 'center';
        wrapper.style.gap = '0.5rem';
        wrapper.style.marginTop = '0.4rem';

        const order = document.createElement('span');
        order.textContent = url;
        order.style.flex = '1';
        order.style.fontSize = '0.75rem';
        order.style.wordBreak = 'break-all';

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.textContent = 'Remove';
        remove.className = 'btn btn-danger';
        remove.style.fontSize = '0.65rem';
        remove.onclick = () => {
            wrapper.remove();
            rebuildHiddenInputs();
        };

        wrapper.appendChild(order);
        wrapper.appendChild(remove);
        list.appendChild(wrapper);
        rebuildHiddenInputs();
    }

    addBtn?.addEventListener('click', () => {
        const url = input.value.trim();
        if (!url) return;
        try {
            new URL(url);
        } catch (e) {
            alert('Invalid URL');
            return;
        }
        addUrl(url);
        input.value = '';
    });
});
