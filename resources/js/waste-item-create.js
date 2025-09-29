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
        // Do NOT clear the input; we rely on underlying FileList for submission.
    });

    // ---------------- Inline Validation -----------------
    const form = document.getElementById('wasteItemForm');
    if (form) {
        // remove native required to avoid browser tooltip interfering
        form.querySelectorAll('[required]').forEach(el => el.removeAttribute('required'));

        function clearErrors() {
            form.querySelectorAll('.error-text.inline').forEach(e => e.remove());
            form.querySelectorAll('.has-error').forEach(e => e.classList.remove('has-error'));
        }

        function showError(inputEl, message) {
            if (!inputEl) return;
            const group = inputEl.closest('.form-group') || inputEl.parentElement;
            if (!group) return;
            group.classList.add('has-error');
            // Avoid duplicate
            const existing = group.querySelector('.error-text.inline');
            if (existing) existing.remove();
            const small = document.createElement('small');
            small.className = 'error-text inline';
            small.textContent = message;
            // Place after label if exists else at end
            const label = group.querySelector('label');
            if (label && label.nextSibling) {
                label.parentNode.insertBefore(small, label.nextSibling);
            } else {
                group.appendChild(small);
            }
        }

        function validate() {
            clearErrors();
            let valid = true;
            const title = form.querySelector('#title');
            if (!title.value.trim()) { showError(title, 'A title is required.'); valid = false; }

            // images required
            if (filesState.length === 0) {
                showError(dropzone, 'Please upload at least one image.');
                valid = false;
            }

            const condition = form.querySelector('#condition');
            if (!condition.value) { showError(condition, 'Select a condition.'); valid = false; }

            const est = form.querySelector('#estimated_weight');
            if (est.value === '' || isNaN(Number(est.value)) || Number(est.value) < 0) {
                showError(est, 'Provide a non-negative number.');
                valid = false;
            }

            const lat = form.querySelector('input[name="location[lat]"]');
            const lng = form.querySelector('input[name="location[lng]"]');
            const latVal = lat.value.trim();
            const lngVal = lng.value.trim();
            const latNum = Number(latVal);
            const lngNum = Number(lngVal);
            if (latVal === '' || isNaN(latNum) || latNum < -90 || latNum > 90) {
                showError(lat, 'Latitude must be between -90 and 90.');
                valid = false;
            }
            if (lngVal === '' || isNaN(lngNum) || lngNum < -180 || lngNum > 180) {
                showError(lng, 'Longitude must be between -180 and 180.');
                valid = false;
            }
            return valid;
        }

        form.addEventListener('submit', (e) => {
            if (!validate()) {
                e.preventDefault();
                // focus first error
                const firstErr = form.querySelector('.has-error input, .has-error select, .has-error textarea, #imageDropzone');
                if (firstErr && firstErr.focus) firstErr.focus();
            }
        });

        // live validation on blur/change
        form.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('blur', () => {
                // run a lightweight validation for that field only
                const name = el.getAttribute('name');
                // run full for simplicity (fields interdependent minimal)
                validate();
            });
        });
    }
});
