function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'alert alert-success alert-dismissible fade show glass-panel';
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 280px;
        max-width: 90vw;
        border-radius: 12px;
        background: rgba(25, 135, 84, 0.2);
        border-color: rgba(25, 135, 84, 0.4);
        color: white;
        backdrop-filter: blur(10px);
    `;
    notification.innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(notification);
        bsAlert.close();
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 500);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    const mediaInput = document.querySelector('input[name="media"]');
    const previewContainer = document.querySelector('.media-preview-container');
    const previewImg = document.querySelector('.media-preview');
    const removeMediaBtn = document.querySelector('.remove-media-btn');
    const composerInput = document.querySelector('.composer .glass-input');
    const composerForm = document.querySelector('.composer form');
    const feedContainer = document.querySelector('.mt-1');
    
    // Live Search
    const searchInput = document.getElementById('searchInput');
    const searchDropdown = document.getElementById('searchDropdown');
    let searchTimeout;

    if (searchInput && searchDropdown) {
        searchInput.addEventListener('focus', function() {
            this.closest('.search-input-container').style.boxShadow = '0 0 0 2px var(--accent-color)';
            if (this.value.trim().length > 0) {
                searchDropdown.style.display = 'block';
            }
        });
        
        searchInput.addEventListener('blur', function() {
            this.closest('.search-input-container').style.boxShadow = 'none';
        });

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
                const mediaFileName = document.getElementById('mediaFileName');
                const mediaFileIcon = document.getElementById('mediaFileIcon');
                const mediaPreviewImage = document.getElementById('mediaPreviewImage');
                const mediaPreviewVideo = document.getElementById('mediaPreviewVideo');
                
                // Set filename
                mediaFileName.textContent = file.name;
                
                // Set icon based on file type
                if (file.type.startsWith('image/')) {
                    mediaFileIcon.className = 'bi bi-file-earmark-image';
                } else if (file.type.startsWith('video/')) {
                    mediaFileIcon.className = 'bi bi-file-earmark-play';
                } else {
                    mediaFileIcon.className = 'bi bi-file-earmark';
                }
                
                reader.onload = function(e) {
                    // Hide both previews first
                    mediaPreviewImage.style.display = 'none';
                    mediaPreviewVideo.style.display = 'none';
                    
                    if (file.type.startsWith('image/')) {
                        mediaPreviewImage.src = e.target.result;
                        mediaPreviewImage.style.display = 'block';
                    } else if (file.type.startsWith('video/')) {
                        mediaPreviewVideo.src = e.target.result;
                        mediaPreviewVideo.style.display = 'block';
                    }
                    
                    previewContainer.classList.add('active');
                    previewContainer.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    }

    if (removeMediaBtn) {
        removeMediaBtn.addEventListener('click', function() {
            const mediaPreviewImage = document.getElementById('mediaPreviewImage');
            const mediaPreviewVideo = document.getElementById('mediaPreviewVideo');
            
            mediaInput.value = '';
            previewContainer.classList.remove('active');
            previewContainer.style.display = 'none';
            mediaPreviewImage.src = '';
            mediaPreviewImage.style.display = 'none';
            mediaPreviewVideo.src = '';
            mediaPreviewVideo.style.display = 'none';
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
    
    // Suggested Users Logic
    const suggestedUsersList = document.getElementById('suggestedUsersList');
    if (suggestedUsersList) {
        fetch('/api/users/suggested')
            .then(response => response.json())
            .then(users => {
                if (users.length === 0) {
                    suggestedUsersList.innerHTML = '<div class="text-muted small">No suggestions right now.</div>';
                    return;
                }

                suggestedUsersList.innerHTML = users.map(user => `
                    <div class="suggested-user-item">
                        <a href="/profile/${user.username}" class="text-decoration-none">
                            <img src="${user.profile_pic ? '/'+user.profile_pic : ''}" 
                                 class="suggested-user-avatar profile-pic-img"
                                 onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                            <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center suggested-user-avatar" style="border: 1px solid var(--glass-border);">
                                <i class="bi bi-person-fill text-white"></i>
                            </div>
                        </a>
                        <div class="suggested-user-info">
                            <a href="/profile/${user.username}" class="text-decoration-none">
                                <div class="suggested-user-name text-truncate">${user.first_name} ${user.last_name}</div>
                                <div class="suggested-user-username text-truncate">@${user.username}</div>
                            </a>
                        </div>
                        <form action="/users/toggleFollow/${user.id}" method="POST" class="m-0">
                            <button type="submit" class="glass-btn btn-sm" style="padding: 4px 12px; font-size: 0.75rem;">Follow</button>
                        </form>
                    </div>
                `).join('');
            })
            .catch(error => {
                console.error('Error fetching suggested users:', error);
                suggestedUsersList.innerHTML = '<div class="text-muted small">Failed to load suggestions.</div>';
            });
    }

    // Close search dropdown when any modal opens
    document.addEventListener('show.bs.modal', function () {
        if (searchDropdown) {
            searchDropdown.style.display = 'none';
        }
    });

    // Let the form submit normally to the regular endpoint (it's already working!)
    // We'll remove the AJAX override for now to avoid issues

    // Toggle Like via AJAX
    document.addEventListener('click', async function(e) {
        const likeBtn = e.target.closest('.like-btn');
        if (likeBtn) {
            e.preventDefault();
            const form = likeBtn.closest('form');
            if (!form) return;

            const actionUrl = form.getAttribute('action');
            const postId = actionUrl.split('/').pop();

            try {
                const response = await fetch(`/api/posts/toggleLike/${postId}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    
                    // Update UI
                    likeBtn.classList.toggle('text-danger', data.is_liked);
                    likeBtn.querySelector('i').className = 
                        `bi ${data.is_liked ? 'bi-heart-fill' : 'bi-heart'}`;
                    likeBtn.querySelector('span').textContent = data.like_count;
                }
            } catch (error) {
                console.error('Error toggling like:', error);
            }
        }
    });

    // Toggle Repost via AJAX
    document.addEventListener('click', async function(e) {
        const repostBtn = e.target.closest('.repost-btn');
        if (repostBtn) {
            e.preventDefault();
            const form = repostBtn.closest('form');
            if (!form) return;

            const actionUrl = form.getAttribute('action');
            const postId = actionUrl.split('/').pop();

            try {
                const response = await fetch(`/api/posts/toggleRepost/${postId}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    
                    // Update UI
                    repostBtn.classList.toggle('text-success', data.is_reposted);
                    repostBtn.querySelector('span').textContent = data.repost_count;
                    
                    // Show notification when reposted
                    if (data.is_reposted) {
                        showNotification('You reposted this!');
                    }
                }
            } catch (error) {
                console.error('Error toggling repost:', error);
            }
        }
    });

    // Add Comment via AJAX
    document.addEventListener('submit', async function(e) {
        const commentForm = e.target.closest('[action*="addComment"]');
        if (commentForm) {
            e.preventDefault();
            const actionUrl = commentForm.getAttribute('action');
            const postId = actionUrl.split('/').pop();
            const textarea = commentForm.querySelector('textarea[name="comment_text"]');
            const commentText = textarea.value.trim();

            if (!commentText) return;

            try {
                const formData = new FormData();
                formData.append('comment_text', commentText);

                const response = await fetch(`/api/posts/addComment/${postId}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                if (response.ok) {
                    const data = await response.json();
                    
                    // Clear the textarea
                    textarea.value = '';
                    
                    // Update comment count
                    const commentBtns = document.querySelectorAll(`[data-bs-target="#commentModal${postId}"]`);
                    commentBtns.forEach(btn => {
                        const countSpan = btn.querySelector('span');
                        if (countSpan) {
                            countSpan.textContent = data.comment_count;
                        }
                    });

                    // Create comment HTML
                    const commentHtml = createCommentHtml(data.comment);
                    
                    // Add to modal comments list
                    const modal = document.getElementById(`commentModal${postId}`);
                    if (modal) {
                        const commentsList = modal.querySelector('.comments-list');
                        const noCommentsDiv = modal.querySelector('.text-center.text-muted.py-4');
                        
                        if (noCommentsDiv) {
                            noCommentsDiv.remove();
                            if (!commentsList) {
                                const commentsContainer = modal.querySelector('.modal-body');
                                const newCommentsList = document.createElement('div');
                                newCommentsList.className = 'comments-list mb-3';
                                newCommentsList.style.cssText = 'max-height: 300px; overflow-y: auto;';
                                commentsContainer.insertBefore(newCommentsList, commentsContainer.querySelector('.comment-form-container'));
                            }
                        }
                        
                        const targetCommentsList = modal.querySelector('.comments-list');
                        if (targetCommentsList) {
                            targetCommentsList.insertAdjacentHTML('beforeend', commentHtml);
                            targetCommentsList.scrollTop = targetCommentsList.scrollHeight;
                        }
                    }

                    // Add to feed comments section
                    const postCard = document.querySelector(`[data-bs-target="#commentModal${postId}"]`).closest('.post-card');
                    if (postCard) {
                        const commentsSection = postCard.querySelector('.comments-section');
                        
                        if (commentsSection) {
                            // Check if we need to show "View all" button
                            let currentComments = commentsSection.querySelectorAll('.comment-item');
                            
                            if (currentComments.length < 2) {
                                commentsSection.insertAdjacentHTML('beforeend', commentHtml);
                            } else if (currentComments.length === 2) {
                                // Check if first two are from same user
                                const firstUserId = currentComments[0].dataset.userId;
                                const secondUserId = currentComments[1].dataset.userId;
                                if (firstUserId === secondUserId) {
                                    commentsSection.insertAdjacentHTML('beforeend', commentHtml);
                                }
                            }
                            
                            // Update "View all" button
                            const viewAllBtn = commentsSection.querySelector('.comment-btn.mt-1');
                            if (viewAllBtn) {
                                viewAllBtn.textContent = `View all ${data.comment_count} comments`;
                            } else if (data.comment_count > 1) {
                                // Check if we need to add view all button
                                let shouldAddViewAll = false;
                                const updatedComments = commentsSection.querySelectorAll('.comment-item');
                                if (updatedComments.length === 1) {
                                    shouldAddViewAll = data.comment_count > 1;
                                } else if (updatedComments.length === 2) {
                                    const firstUserId = updatedComments[0].dataset.userId;
                                    const secondUserId = updatedComments[1].dataset.userId;
                                    shouldAddViewAll = data.comment_count > (firstUserId === secondUserId ? 1 : 2);
                                }
                                
                                if (shouldAddViewAll) {
                                    const viewAllHtml = `<button class="action-btn comment-btn mt-1" data-bs-toggle="modal" data-bs-target="#commentModal${postId}" style="padding: 2px 8px; font-size: 0.8rem;">View all ${data.comment_count} comments</button>`;
                                    commentsSection.insertAdjacentHTML('beforeend', viewAllHtml);
                                }
                            }
                        } else {
                            // Create new comments section
                            const interactionBar = postCard.querySelector('.interaction-bar');
                            const newCommentsSection = document.createElement('div');
                            newCommentsSection.className = 'comments-section';
                            newCommentsSection.innerHTML = commentHtml;
                            interactionBar.after(newCommentsSection);
                        }
                    }
                }
            } catch (error) {
                console.error('Error adding comment:', error);
            }
        }
    });

    function createCommentHtml(comment) {
        const profilePic = comment.user?.profile_pic ? `/${comment.user.profile_pic}` : '';
        const firstName = comment.user?.first_name || 'Unknown';
        const lastName = comment.user?.last_name || 'User';
        const username = comment.user?.username || 'unknown';
        const now = new Date();
        const createdDate = new Date(comment.created_at);
        const diff = Math.floor((now - createdDate) / 1000);
        let timeAgo = 'just now';
        if (diff >= 60) {
            timeAgo = Math.floor(diff / 60) + 'm';
        }
        if (diff >= 3600) {
            timeAgo = Math.floor(diff / 3600) + 'h';
        }
        if (diff >= 86400) {
            timeAgo = Math.floor(diff / 86400) + 'd';
        }

        return `
            <div class="comment-item" data-user-id="${comment.user_id}" style="padding: 8px; gap: 8px; margin-bottom: 8px;">
                <img src="${profilePic}" class="rounded-circle profile-pic-img flex-shrink-0" width="32" height="32" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center profile-pic-placeholder flex-shrink-0" style="width: 32px; height: 32px; overflow: hidden; border: 1px solid var(--glass-border);">
                    <i class="bi bi-person-fill text-white fs-5"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-baseline gap-2 mb-0 flex-wrap">
                        <a href="/profile/${username}" class="text-decoration-none">
                            <span class="fw-bold" style="color: var(--text-primary); font-size: 0.85rem;">${firstName} ${lastName}</span>
                        </a>
                        <span class="text-muted" style="font-size: 0.75rem;">@${username}</span>
                        <span class="text-muted" style="font-size: 0.75rem;">• ${timeAgo}</span>
                    </div>
                    <p class="mb-0" style="font-size: 0.9rem; line-height: 1.4; color: var(--text-primary); white-space: pre-wrap;">${escapeHtml(comment.comment_text)}</p>
                </div>
            </div>
        `;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
