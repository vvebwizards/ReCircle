<div class="modal hidden photo-lightbox" id="photosModal" role="dialog" aria-modal="true" aria-labelledby="photosModalTitle">
    <div class="modal-header minimal" style="justify-content:space-between;">
        <h3 class="modal-title" id="photosModalTitle"><i class="fa-solid fa-images"></i> <span>Photos</span></h3>
        <button class="modal-close" data-close="photosModal" aria-label="Close photos"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body photos-body">
        <div id="photosLoader" class="lightbox-loader" aria-hidden="true"></div>
        <div id="photosError" class="wi-error hidden" role="alert">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>Unable to load photos. Please try again.</span>
        </div>
        <div class="lightbox-main hidden" id="photosMainWrap">
            <button class="lb-nav prev" type="button" aria-label="Previous image">&#10094;</button>
            <img id="photosMainImage" class="lb-main-img" alt="Waste item image" />
            <button class="lb-nav next" type="button" aria-label="Next image">&#10095;</button>
        </div>
        <div id="photosCaption" class="lb-caption"></div>
        <div id="photosThumbs" class="lb-thumbs" aria-label="Image thumbnails" role="list"></div>
    </div>
</div>
