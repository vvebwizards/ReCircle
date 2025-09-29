<!-- View Waste Item Modal (extracted) -->
<div class="modal hidden" id="viewModal" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
    <div class="modal-header minimal" style="justify-content:flex-end;">
        <button class="modal-close" data-close="viewModal" aria-label="Close view"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body wi-view-body">
        <div id="viewLoading" class="wi-skeleton" aria-hidden="true">
            <div class="sk-head"></div>
            <div class="sk-pills"></div>
            <div class="sk-strip"></div>
            <div class="sk-panels">
                <div class="sk-panel"></div>
                <div class="sk-panel"></div>
            </div>
        </div>
        <div id="viewContent" class="hidden">
            <div class="wi-view-header">
                <div class="wi-view-title-block">
                    <h2 id="viewTitle" class="wi-view-title">—</h2>
                    <div class="wi-pill-row">
                        <span class="badge" id="viewCondition"><i class="fa-solid fa-circle"></i> —</span>
                        <span class="wi-pill" id="viewWeight"></span>
                        <span class="wi-pill subtle" id="viewId"></span>
                        <span class="wi-pill geo" id="viewLocation"></span>
                    </div>
                </div>
            </div>
            <div class="wi-horizontal-gallery-wrapper">
                <div class="wi-gallery-header">
                    <h4>Images <span id="viewImagesCount" class="wi-count-badge">0</span></h4>
                    <button type="button" class="btn btn-secondary btn-sm" id="viewAllPhotosBtn" title="See all photos"><i class="fa-solid fa-images"></i> See all</button>
                </div>
                <div id="viewImages" class="wi-horizontal-gallery" aria-live="polite"></div>
            </div>
            <div class="wi-info-grid">
                <div class="wi-panel">
                    <h4 class="wi-panel-title">Summary</h4>
                    <dl class="wi-attrs">
                        <dt>Created</dt><dd id="viewCreated">—</dd>
                        <dt>Updated</dt><dd id="viewUpdated">—</dd>
                        <dt>Materials</dt><dd id="viewMaterials">0</dd>
                        <dt>Location</dt><dd id="viewLocationDetail">—</dd>
                    </dl>
                </div>
                <div class="wi-panel">
                    <h4 class="wi-panel-title">Notes</h4>
                    <div id="viewNotes" class="wi-notes">—</div>
                </div>
            </div>
            <div class="modal-actions wi-view-actions">
                <button class="btn btn-secondary" data-close="viewModal">Close</button>
                <button class="btn btn-primary" id="openEditFromView"><i class="fa-solid fa-edit"></i> Edit</button>
            </div>
        </div>
        <div id="viewError" class="wi-error hidden" role="alert">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>Failed to load item. Please try again.</span>
        </div>
    </div>
</div>
