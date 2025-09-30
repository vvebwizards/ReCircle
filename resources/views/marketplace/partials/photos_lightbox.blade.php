<div class="modal hidden photo-lightbox" id="marketplacePhotosModal" role="dialog" aria-modal="true" aria-labelledby="marketplacePhotosTitle">
    <div class="modal-header minimal" style="justify-content:space-between;">
        <h3 class="modal-title" id="marketplacePhotosTitle"><i class="fa-solid fa-images"></i> <span>Photos</span></h3>
        <button class="modal-close" data-close="marketplacePhotosModal" aria-label="Close photos"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body photos-body">
        <div id="mpPhotosLoader" class="lightbox-loader" aria-hidden="true"></div>
        <div id="mpPhotosError" class="wi-error hidden" role="alert">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>Unable to load photos. Please try again.</span>
        </div>
        <div class="lightbox-main hidden" id="mpPhotosMainWrap">
            <button class="lb-nav prev" type="button" aria-label="Previous image">&#10094;</button>
            <img id="mpPhotosMainImage" class="lb-main-img" alt="Item image" />
            <button class="lb-nav next" type="button" aria-label="Next image">&#10095;</button>
        </div>
        <div id="mpPhotosCaption" class="lb-caption"></div>
        <div id="mpPhotosThumbs" class="lb-thumbs" aria-label="Image navigation" role="list"></div>
    </div>
</div>
