document.addEventListener('DOMContentLoaded', () => {
    const dropzone = document.getElementById('imageDropzone');
    const fileInput = document.getElementById('images');
    const previewList = document.getElementById('imagePreviewList');
    const maxFiles = 10;

    const toggleButton = document.querySelector('.instructions-toggle');
    if (toggleButton) {
        toggleButton.addEventListener('click', function() {
            const content = document.querySelector('.instructions-content');
            content.classList.toggle('collapsed');
            this.classList.toggle('collapsed');
        });
    }

    let filesState = []; // array of File objects

    function syncFileInput() {
        // Create a new DataTransfer to update the underlying input's FileList
        const dt = new DataTransfer();
        filesState.forEach(f => dt.items.add(f));
        fileInput.files = dt.files;
    }

    function renderPreviews() {
        previewList.innerHTML = '';
        filesState.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'preview-item';

            const img = document.createElement('img');
            img.alt = file.name;
            img.loading = 'lazy';
            img.className = 'preview-thumb';

            const reader = new FileReader();
            reader.onload = e => { img.src = e.target.result; };
            reader.readAsDataURL(file);

            const meta = document.createElement('div');
            meta.className = 'preview-meta';
            meta.innerHTML = `<strong>${index === 0 ? 'Primary • ' : ''}</strong>${file.name}<br><small>${(file.size/1024).toFixed(1)} KB</small>`;

            const actions = document.createElement('div');
            actions.className = 'preview-actions';

            if (index > 0) {
                const upBtn = document.createElement('button');
                upBtn.type = 'button';
                upBtn.title = 'Move earlier';
                upBtn.className = 'btn btn-sm btn-secondary';
                upBtn.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
                upBtn.onclick = () => {
                    const tmp = filesState[index-1];
                    filesState[index-1] = filesState[index];
                    filesState[index] = tmp;
                    renderPreviews();
                    syncFileInput();
                };
                actions.appendChild(upBtn);
            }

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-danger';
            removeBtn.title = 'Remove image';
            removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            removeBtn.onclick = () => {
                filesState.splice(index, 1);
                renderPreviews();
                syncFileInput();
            };
            actions.appendChild(removeBtn);

            item.appendChild(img);
            item.appendChild(meta);
            item.appendChild(actions);
            previewList.appendChild(item);
        });
    }

    function addFiles(fileList) {
        const incoming = Array.from(fileList);
        for (const f of incoming) {
            if (!f.type.startsWith('image/')) continue;
            if (f.size > 2 * 1024 * 1024) { // 2MB
                alert(`${f.name} exceeds 2MB limit and was skipped.`);
                continue;
            }
            if (filesState.length >= maxFiles) {
                alert('Maximum of 10 images reached.');
                break;
            }
            filesState.push(f);
        }
        renderPreviews();
        syncFileInput();
    }

    dropzone?.addEventListener('click', () => fileInput.click());
    dropzone?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            fileInput.click();
        }
    });

    ;['dragenter','dragover'].forEach(ev => dropzone?.addEventListener(ev, e => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.add('drag-active');
    }));
    ;['dragleave','drop'].forEach(ev => dropzone?.addEventListener(ev, e => {
        e.preventDefault();
        e.stopPropagation();
        if (ev === 'dragleave' && e.target !== dropzone) return;
        dropzone.classList.remove('drag-active');
    }));
    dropzone?.addEventListener('drop', e => {
        if (e.dataTransfer?.files) {
            addFiles(e.dataTransfer.files);
        }
    });

    fileInput?.addEventListener('change', () => {
        addFiles(fileInput.files);
        // Clear the real input so re-selecting same file triggers change
        fileInput.value = '';
    });
});
