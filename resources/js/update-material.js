document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('image_path');
    const newImagePreview = document.getElementById('newImagePreview');
    
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            newImagePreview.innerHTML = '';
            
            if (this.files) {
                Array.from(this.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewWrapper = document.createElement('div');
                            previewWrapper.className = 'image-preview-wrapper';
                            
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'image-preview';
                            
                            const removeBtn = document.createElement('button');
                            removeBtn.type = 'button';
                            removeBtn.className = 'remove-image';
                            removeBtn.innerHTML = '×';
                            removeBtn.onclick = function() {
                                previewWrapper.remove();
                            };
                            
                            previewWrapper.appendChild(img);
                            previewWrapper.appendChild(removeBtn);
                            newImagePreview.appendChild(previewWrapper);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    }

    document.querySelectorAll('input, select, textarea').forEach(element => {
        element.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
                const errorText = this.parentNode.querySelector('.error-text');
                if (errorText) {
                    errorText.style.display = 'none';
                }
            }
        });
        
        element.addEventListener('change', function() {
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
                const errorText = this.parentNode.querySelector('.error-text');
                if (errorText) {
                    errorText.style.display = 'none';
                }
            }
        });
    });
});