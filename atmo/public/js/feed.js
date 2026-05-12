document.addEventListener('DOMContentLoaded', function() {
    const mediaInput = document.querySelector('input[name="media"]');
    const previewContainer = document.querySelector('.media-preview-container');
    const previewImg = document.querySelector('.media-preview');
    const removeMediaBtn = document.querySelector('.remove-media-btn');
    const composerInput = document.querySelector('.composer .glass-input');
    
    // Live Search
    const searchInput = document.getElementById('searchInput');
    const searchDropdown = document.getElementById('searchDropdown');
    let searchTimeout;

    if (searchInput && searchDropdown) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length === 0) {
                searchDropdown.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`/api/users/search?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(users => {
                        if (users.length === 0) {
                            searchDropdown.innerHTML = `
                                <div class="text-center text-muted py-4">
                                    No users found
                                </div>
                            `;
                        } else {
                            searchDropdown.innerHTML = users.map(user => `
                                <a href="/profile/${user.username}" class="search-result-item">
                                    <img src="${user.profile_pic ? '/'+user.profile_pic : ''}" 
                                         class="search-result-avatar profile-pic-img"
                                         onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                                    <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center search-result-avatar" style="border: 1px solid var(--glass-border);">
                                        <i class="bi bi-person-fill text-white"></i>
                                    </div>
                                    <div class="search-result-info">
                                        <div class="search-result-name">${user.first_name} ${user.last_name}</div>
                                        <div class="search-result-username">@${user.username}</div>
                                    </div>
                                </a>
                            `).join('');
                        }
                        searchDropdown.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        searchDropdown.style.display = 'none';
                    });
            }, 300);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.style.display = 'none';
            }
        });

        // Keep dropdown open when focusing input
        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length > 0) {
                searchDropdown.style.display = 'block';
            }
        });
    }

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
