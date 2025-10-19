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

    const filesState = []; // array of File objects

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

            const lat = form.querySelector('#locationLat') || form.querySelector('input[name="location[lat]"]');
            const lng = form.querySelector('#locationLng') || form.querySelector('input[name="location[lng]"]');
            const latVal = (lat && lat.value) ? lat.value.trim() : '';
            const lngVal = (lng && lng.value) ? lng.value.trim() : '';
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

        form.addEventListener('submit', async (e) => {
            if (!validate()) {
                e.preventDefault();
                // focus first error
                const firstErr = form.querySelector('.has-error input, .has-error select, .has-error textarea, #imageDropzone');
                if (firstErr && firstErr.focus) firstErr.focus();
                return;
            }
            e.preventDefault();
            // AJAX submit
            const formData = new FormData(form);
            try {
                const resp = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                });
                if (!resp.ok) {
                    const data = await resp.json();
                    if (data.errors) {
                        Object.entries(data.errors).forEach(([field, messages]) => {
                            const input = form.querySelector(`[name="${field}"]`);
                            showError(input, messages[0]);
                        });
                    } else {
                        alert('Error creating waste item.');
                    }
                    return;
                }
                // Success: close modal/page, refresh grid
                if (window.WasteItemsUI && WasteItemsUI.updateContent) {
                    // Show success message then redirect
                    const msg = document.createElement('div');
                    msg.className = 'alert alert-success';
                    msg.textContent = 'Waste item created successfully!';
                    form.parentNode.insertBefore(msg, form);
                    setTimeout(() => {
                        window.location.href = '/waste-items';
                    }, 1500);
                }
            } catch (err) {
                alert('Network error. Please try again.');
            }
        });

        // live validation on blur/change
        form.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('blur', () => {
                // run a lightweight validation for that field only

                // run full for simplicity (fields interdependent minimal)
                validate();
            });
        });
    }

    // ---------------- Map / Address picker -----------------
    // lazy-load Leaflet to avoid always shipping heavy assets
    async function loadLeaflet() {
        if (window.L) return window.L;
        // load CSS already added in blade
        await import(/* webpackIgnore: true */ 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js');
        return window.L;
    }

    const mapEl = document.getElementById('locationMap');
    if (mapEl) {
        (async () => {
            const L = await loadLeaflet();

            // default view: if old values exist, use them, else use a general view
            const latInput = document.getElementById('locationLat');
            const lngInput = document.getElementById('locationLng');
            const addrInput = document.getElementById('locationAddress');

            const initialLat = latInput && latInput.value ? Number(latInput.value) : 20.0;
            const initialLng = lngInput && lngInput.value ? Number(lngInput.value) : 0.0;
            const map = L.map(mapEl).setView([initialLat, initialLng], (latInput && latInput.value) ? 13 : 2);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            let marker = null;
            // expose map helpers so other modules (modal logic) can call invalidate/center
            window.WasteItemMap = window.WasteItemMap || {};
            function setMarker(lat, lng, address) {
                if (marker) marker.remove();
                marker = L.marker([lat, lng], {draggable:true}).addTo(map);
                marker.on('dragend', async (ev) => {
                    const p = ev.target.getLatLng();
                    await fillLocation(p.lat, p.lng);
                });
                map.setView([lat, lng], 13);
                if (latInput) latInput.value = lat;
                if (lngInput) lngInput.value = lng;
                if (addrInput && address) addrInput.value = address;
            }

            async function reverseGeocode(lat, lng) {
                try {
                    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`;
                    const res = await fetch(url, {headers:{'Accept':'application/json'}});
                    if (!res.ok) return null;
                    const data = await res.json();
                    return data.display_name || null;
                } catch (e) { return null; }
            }

            async function geocodeAddress(q) {
                try {
                    const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(q)}&limit=5`;
                    const res = await fetch(url, {headers:{'Accept':'application/json'}});
                    if (!res.ok) return [];
                    const data = await res.json();
                    return data; // array
                } catch (e) { return []; }
            }

            async function fillLocation(lat, lng) {
                const addr = await reverseGeocode(lat, lng);
                setMarker(lat, lng, addr);
            }

            // if there are old coordinates, place marker
            if (latInput && lngInput && latInput.value && lngInput.value) {
                setMarker(Number(latInput.value), Number(lngInput.value), addrInput?.value || null);
            }

            // store helper references
            window.WasteItemMap.map = map;
            window.WasteItemMap.setMarker = setMarker;
            window.WasteItemMap.invalidate = () => { try{ map.invalidateSize(); }catch(e){} };

            map.on('click', async (e) => {
                const {lat, lng} = e.latlng;
                const address = await reverseGeocode(lat, lng);
                setMarker(lat, lng, address);
            });

            // address search handling
            const addrSearch = document.getElementById('addressSearch');
            const addrSearchBtn = document.getElementById('addressSearchBtn');
            const useMyLocBtn = document.getElementById('useMyLocationBtn');

            async function runSearch() {
                const q = addrSearch.value.trim();
                if (!q) return alert('Enter an address to search.');
                const results = await geocodeAddress(q);
                if (!results || results.length === 0) return alert('No results found. Try a different query.');
                // use first result
                const r = results[0];
                const lat = Number(r.lat);
                const lon = Number(r.lon);
                setMarker(lat, lon, r.display_name || q);
            }

            addrSearchBtn?.addEventListener('click', runSearch);
            addrSearch?.addEventListener('keydown', (ev) => { if (ev.key === 'Enter') { ev.preventDefault(); runSearch(); } });

            useMyLocBtn?.addEventListener('click', () => {
                if (!navigator.geolocation) return alert('Geolocation not supported.');
                navigator.geolocation.getCurrentPosition(async (pos) => {
                    const lat = pos.coords.latitude;
                    const lon = pos.coords.longitude;
                    const addr = await reverseGeocode(lat, lon);
                    setMarker(lat, lon, addr);
                }, (err) => {
                    alert('Unable to get your location: ' + (err.message || 'Permission denied'));
                }, {maximumAge:60000, timeout:10000});
            });

            // If the modal opens after the map is created, a global event will be dispatched
            document.addEventListener('modalOpened', (ev) => {
                try{
                    if (ev?.detail?.id === 'createModal'){
                        // run several attempts to invalidate size after the modal open + animation
                        const tryInvalidate = (attemptsLeft) => {
                            try{
                                map.invalidateSize();
                                if (marker){ map.setView(marker.getLatLng(), map.getZoom() || 13); }
                            }catch(e){}
                            if (attemptsLeft > 0){
                                setTimeout(()=> tryInvalidate(attemptsLeft - 1), 120);
                            }
                        };
                        setTimeout(()=> tryInvalidate(4), 80);
                    }
                }catch(e){}
            });

        })();
    }
});
