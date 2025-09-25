document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.querySelector('.instructions-toggle');
    if (toggleButton) {
        toggleButton.addEventListener('click', function() {
            const instructionsContent = document.querySelector('.instructions-content');
            if (instructionsContent) {
                instructionsContent.classList.toggle('collapsed');
                this.classList.toggle('collapsed');
                
                const isCollapsed = instructionsContent.classList.contains('collapsed');
                this.setAttribute('aria-label', isCollapsed ? 'Expand instructions' : 'Collapse instructions');
            }
        });
    }
});