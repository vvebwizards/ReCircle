/* global initImageDropZone */
// Global (namespaced) object to avoid polluting window too much. Create a local const for ESLint.
const WasteItemsUI = window.WasteItemsUI = window.WasteItemsUI || {};

// Expose updateContent globally so other scripts (pagination, modals) can call it
WasteItemsUI.updateContent = async function updateContent(url, { pushState = true } = {}) {
    const contentContainer = document.querySelector('.materials-container .container');
    if (!contentContainer) return;

    // Abort previous in-flight request to prevent race conditions
    if (window.WasteItemsUI._abortController) {
        window.WasteItemsUI._abortController.abort();
    }
    const abortController = new AbortController();
    window.WasteItemsUI._abortController = abortController;

    // Visual loading state
    contentContainer.classList.add('is-loading');
    contentContainer.style.opacity = '0.6';
    contentContainer.style.pointerEvents = 'none';

    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json, text/html'
            },
            signal: abortController.signal
        });
        if (!response.ok) throw new Error('Network response was not ok');
        const contentType = response.headers.get('Content-Type') || '';
        if (contentType.includes('application/json')) {
            const data = await response.json();
            if (data.grid) {
                const grid = document.querySelector('.materials-grid');
                if (grid) grid.outerHTML = data.grid; // replace wrapper
            }
            if (data.stats) {
                const stats = document.querySelector('.stats-grid');
                if (stats) stats.outerHTML = data.stats;
            }
            if (data.pagination) {
                const pag = document.querySelector('.pagination');
                if (pag) {
                    pag.outerHTML = data.pagination;
                } else if (data.pagination.trim()) {
                    // If pagination doesn't exist yet but should, append
                    const afterGrid = document.querySelector('.materials-grid');
                    if (afterGrid) afterGrid.insertAdjacentHTML('afterend', data.pagination);
                }
            }
        } else {
            // Fallback: treat as full HTML document
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const sections = [ '.stats-grid', '.materials-grid', '.pagination' ];
            sections.forEach(sel => {
                const current = document.querySelector(sel);
                const fresh = doc.querySelector(sel);
                if (current && fresh) current.innerHTML = fresh.innerHTML;
            });
        }

        if (pushState) window.history.pushState({ url }, '', url);

        // Re-init dynamic UI (idempotent)
    WasteItemsUI.initDynamic();
    } catch (err) {
        if (err.name === 'AbortError') return; // silently ignore aborted requests
        console.error('[WasteItemsUI] update error', err);
        WasteItemsUI.notify('Error updating content. Please retry.', 'danger');
    } finally {
        contentContainer.classList.remove('is-loading');
        contentContainer.style.opacity = '';
        contentContainer.style.pointerEvents = '';
    }
};

// Simple notification helper
WasteItemsUI.notify = function(message, type = 'info', timeout = 4000) {
    const div = document.createElement('div');
    div.className = `alert alert-${type} position-fixed top-0 end-0 m-3 shadow`;
    div.style.zIndex = '1060';
    div.innerHTML = `<div class="d-flex align-items-center justify-content-between gap-3"><span>${message}</span><button type="button" class="btn-close" aria-label="Close"></button></div>`;
    div.querySelector('.btn-close').addEventListener('click', () => div.remove());
    document.body.appendChild(div);
    if (timeout) setTimeout(() => div.remove(), timeout);
};

// Debounce utility (shared)
WasteItemsUI.debounce = function(fn, wait = 300) {
    let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
};

// Initialize / re-initialize dynamic parts (idempotent)
WasteItemsUI.initDynamic = function() {
    // Re-bind pagination via event delegation
    const pagination = document.querySelector('.pagination');
    if (pagination && !pagination._delegated) {
        pagination.addEventListener('click', (e) => {
            const a = e.target.closest('a');
            if (a && pagination.contains(a)) {
                e.preventDefault();
            WasteItemsUI.updateContent(a.href);
            }
        });
        pagination._delegated = true;
    }

    // Re-bind modal triggers etc. (stubs for future)
    if (typeof initImageDropZone === 'function') initImageDropZone();
};

// Init filters (once) with delegation
WasteItemsUI.initFilters = function() {
    const form = document.getElementById('filterForm');
    if (!form || form._wired) return;
    form._wired = true;

    const applyForm = () => {
        const fd = new FormData(form);
        const qs = new URLSearchParams(fd).toString();
        const url = `${form.action}${qs ? '?' + qs : ''}`;
    WasteItemsUI.updateContent(url);
    };

    // Listen for changes on selects & search input
    form.addEventListener('change', (e) => {
        if (e.target.matches('select')) applyForm();
    });

    const search = form.querySelector('#search');
    if (search) search.addEventListener('input', WasteItemsUI.debounce(applyForm, 350));

    const clearBtn = form.querySelector('.clear-search');
    if (clearBtn) clearBtn.addEventListener('click', () => { const s = form.querySelector('#search'); if (s) { s.value=''; applyForm(); }});

    const resetBtn = form.querySelector('#filtersReset');
    if (resetBtn) resetBtn.addEventListener('click', (e) => { e.preventDefault(); form.reset(); WasteItemsUI.updateContent(form.action); });

    form.addEventListener('submit', (e) => { e.preventDefault(); applyForm(); });
};

// Handle browser back/forward
window.addEventListener('popstate', (e) => {
    const url = (e.state && e.state.url) ? e.state.url : window.location.href;
    WasteItemsUI.updateContent(url, { pushState: false });
});

// DOM Ready
document.addEventListener('DOMContentLoaded', () => {
    WasteItemsUI.initFilters();
    WasteItemsUI.initDynamic();
});