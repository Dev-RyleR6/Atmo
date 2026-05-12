document.addEventListener('DOMContentLoaded', function() {
    const mediaInput = document.querySelector('input[name="media"]');
    const previewContainer = document.querySelector('.media-preview-container');
    const previewImg = document.querySelector('.media-preview');
    const removeMediaBtn = document.querySelector('.remove-media-btn');
    const composerInput = document.querySelector('.composer .glass-input');

    // Media Preview Logic
    if (mediaInput) {
        mediaInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.classList.add('active');
                }
                reader.readAsDataURL(file);
            }
        });
    }

    if (removeMediaBtn) {
        removeMediaBtn.addEventListener('click', function() {
            mediaInput.value = '';
            previewContainer.classList.remove('active');
            previewImg.src = '';
        });
    }

    // Composer Interactivity
    if (composerInput) {
        composerInput.addEventListener('focus', function() {
            this.closest('.composer').style.borderColor = 'var(--accent-color)';
        });
        composerInput.addEventListener('blur', function() {
            this.closest('.composer').style.borderColor = 'var(--glass-border)';
        });
        
        // Auto-resize textarea
        composerInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }

    // Post card animations on scroll
    const postCards = document.querySelectorAll('.post-card');
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    postCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(10px)';
        card.style.transition = 'all 0.4s ease-out';
        observer.observe(card);
    });
});
