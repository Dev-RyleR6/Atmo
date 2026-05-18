/**
 * ATMO Social Media Feed JavaScript
 * =====================================
 * 
 * This file handles all frontend functionality for the Atmo social media platform:
 * - Theme toggling (dark/light mode with localStorage persistence)
 * - Feed rendering and interactions (likes, reposts, comments)
 * - Real-time notifications and trending topics
 * - Post composition and media handling
 * - Comment CRUD operations with AJAX
 * - Search functionality
 * 
 * Key Features:
 * - Event delegation for dynamic elements
 * - AJAX-based interactions for smooth UX
 * - Automatic UI updates without page reload
 * - Responsive design support
 * 
 * Dependencies:
 * - Bootstrap 5.3+ (for modals and alerts)
 * - Bootstrap Icons (for UI icons)
 * 
 * API Endpoints Used:
 * - POST /api/posts/toggleLike/{postId}
 * - POST /api/posts/toggleRepost/{postId}
 * - POST /api/posts/addComment/{postId}
 * - POST /posts/editComment/{commentId}
 * - DELETE /posts/deleteComment/{commentId}
 * - POST /api/users/toggleFollow/{userId}
 * - GET /api/users/search?q={query}
 * - GET /api/users/suggested
 * - GET /api/posts/trending
 * - GET /api/notifications
 * - GET /api/notifications/unreadCount
 * - POST /api/notifications/markAsRead/{notificationId}
 */

function showNotification(message) {
    /**
     * Display a temporary success notification toast
     * 
     * @param {string} message - The notification message to display
     * Shows for 3 seconds then auto-dismisses
     */
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
    // ===== THEME TOGGLE INITIALIZATION & HANDLERS =====
    /**
     * Theme Management System
     * 
     * Features:
     * - Loads theme preference from localStorage
     * - Falls back to system preference (prefers-color-scheme)
     * - Defaults to dark mode if no preference found
     * - Updates <html> data-bs-theme attribute
     * - Persists user choice in localStorage
     * 
     * Stored Value: localStorage.atmo-theme ('light' or 'dark')
     */
    const initializeTheme = () => {
        // Check localStorage first
        let savedTheme = localStorage.getItem('atmo-theme');
        
        // If no saved theme, check system preference
        if (!savedTheme) {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            savedTheme = prefersDark ? 'dark' : 'light';
        }
        
        // Apply the theme
        applyTheme(savedTheme);
    };
    
    // Apply theme and update UI
    const applyTheme = (theme) => {
        const htmlElement = document.documentElement;
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeLabel = document.querySelector('.theme-toggle-label');
        const icon = document.querySelector('#themeToggleBtn i');
        
        if (theme === 'dark') {
            htmlElement.setAttribute('data-bs-theme', 'dark');
            localStorage.setItem('atmo-theme', 'dark');
            if (themeLabel) themeLabel.textContent = 'Dark';
            if (icon) {
                icon.className = 'bi bi-moon-stars';
                icon.title = 'Switch to light mode';
            }
        } else {
            htmlElement.removeAttribute('data-bs-theme');
            localStorage.setItem('atmo-theme', 'light');
            if (themeLabel) themeLabel.textContent = 'Light';
            if (icon) {
                icon.className = 'bi bi-sun';
                icon.title = 'Switch to dark mode';
            }
        }
    };
    
    // Theme toggle button handler
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const htmlElement = document.documentElement;
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        });
    }
    
    // Initialize theme on page load
    initializeTheme();
    
    // ===== END THEME TOGGLE =====
    
    const mediaInput = document.querySelector('input[name="media"]');
    const previewContainer = document.querySelector('.media-preview-container');
    const previewImg = document.querySelector('.media-preview');
    const removeMediaBtn = document.querySelector('.remove-media-btn');
    const composerInput = document.querySelector('.composer .glass-input');
    const composerForm = document.querySelector('.composer form');
    const feedContainer = document.querySelector('.mt-1');
    const feedTabs = document.querySelectorAll('.feed-tab');
    
    let currentFeedType = 'for_you';
    
    function safeClosest(node, selector) {
        let element = node;
        while (element && element.nodeType !== Node.ELEMENT_NODE) {
            element = element.parentNode;
        }
        return element instanceof Element ? element.closest(selector) : null;
    }

    // Set current feed type from active tab
    const activeTab = document.querySelector('.feed-tab.active');
    if (activeTab && activeTab.dataset.feedType) {
        currentFeedType = activeTab.dataset.feedType;
    }
    
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
                                        <i class="bi bi-person-fill text-white fs-5"></i>
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

    // AJAX follow/unfollow handling (delegated)
    document.addEventListener('submit', function(e) {
        const form = safeClosest(e.target, '.follow-toggle-form');
        if (!form) return;
        e.preventDefault();

        const userId = form.dataset.userId;
        if (!userId) return;

        const btn = form.querySelector('.follow-toggle-btn');
        if (!btn) return;

        fetch(`/api/users/toggleFollow/${encodeURIComponent(userId)}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(res => res.json()).then(data => {
            if (data && data.status === 'success') {
                const isFollowing = data.is_following === true;
                // Update button label and style
                btn.textContent = isFollowing ? 'Following' : 'Follow';
                if (isFollowing) {
                    btn.style.background = 'transparent';
                    btn.style.border = '1px solid var(--glass-border)';
                    btn.style.color = 'var(--text-primary)';
                } else {
                    btn.style.background = '';
                    btn.style.border = '';
                    btn.style.color = '';
                }

                // Update follower count if present on page
                const followersCountEl = document.getElementById('followersCount');
                if (followersCountEl && typeof data.followers_count !== 'undefined') {
                    followersCountEl.textContent = data.followers_count;
                } else if (followersCountEl && data.is_following !== undefined) {
                    // adjust by +-1
                    let val = parseInt(followersCountEl.textContent || '0', 10) || 0;
                    followersCountEl.textContent = isFollowing ? (val + 1) : Math.max(0, val - 1);
                }

                // If button exists elsewhere (e.g., profile follow), update that too
                document.querySelectorAll(`form.follow-toggle-form[data-user-id="${userId}"]`).forEach(f => {
                    const b = f.querySelector('.follow-toggle-btn');
                    if (b) b.textContent = isFollowing ? 'Following' : 'Follow';
                });
            }
        }).catch(err => {
            console.error('Follow toggle error', err);
        });
    });

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
    
    // Suggested Users Logic with Pagination
    const suggestedUsersList = document.getElementById('suggestedUsersList');
    const loadMoreSuggestedBtn = document.getElementById('loadMoreSuggested');
    let suggestedUsersPage = 1;
    let suggestedUsersTotalPages = 1;
    let suggestedUsersLoading = false;
    
    if (suggestedUsersList) {
        async function loadSuggestedUsers(append = false) {
            if (suggestedUsersLoading) return;
            suggestedUsersLoading = true;
            
            try {
                const response = await fetch(`/api/users/suggested?page=${suggestedUsersPage}&limit=5`);
                const data = await response.json();
                const users = data.data || [];
                
                if (!append) {
                    if (users.length === 0) {
                        suggestedUsersList.innerHTML = '<div class="text-muted small">No suggestions right now.</div>';
                        if (loadMoreSuggestedBtn) loadMoreSuggestedBtn.style.display = 'none';
                        return;
                    }
                }
                
                const usersHtml = users.map(user => `
                    <div class="suggested-user-item">
                        <a href="/profile/${user.username}" class="text-decoration-none">
                            <img src="${user.profile_pic ? '/'+user.profile_pic : ''}" 
                                 class="suggested-user-avatar profile-pic-img"
                                 onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                            <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center suggested-user-avatar" style="border: 1px solid var(--glass-border);">
                                <i class="bi bi-person-fill text-white fs-5"></i>
                            </div>
                        </a>
                        <div class="suggested-user-info">
                            <a href="/profile/${user.username}" class="text-decoration-none">
                                <div class="suggested-user-name text-truncate">${user.first_name} ${user.last_name}</div>
                                <div class="suggested-user-username text-truncate">@${user.username}</div>
                            </a>
                        </div>
                        <form action="/users/toggleFollow/${user.id}" method="POST" class="follow-toggle-form m-0" data-user-id="${user.id}">
                            <button type="submit" class="glass-btn btn-sm follow-toggle-btn" style="padding: 4px 12px; font-size: 0.75rem;">Follow</button>
                        </form>
                    </div>
                `).join('');
                
                if (append) {
                    suggestedUsersList.insertAdjacentHTML('beforeend', usersHtml);
                } else {
                    suggestedUsersList.innerHTML = usersHtml;
                }
                
                suggestedUsersTotalPages = data.totalPages || 1;
                if (loadMoreSuggestedBtn) {
                    if (suggestedUsersPage >= suggestedUsersTotalPages) {
                        loadMoreSuggestedBtn.style.display = 'none';
                    } else {
                        loadMoreSuggestedBtn.style.display = 'block';
                    }
                }
            } catch (error) {
                console.error('Error fetching suggested users:', error);
                if (!append) {
                    suggestedUsersList.innerHTML = '<div class="text-muted small">Failed to load suggestions.</div>';
                }
            } finally {
                suggestedUsersLoading = false;
            }
        }
        
        loadSuggestedUsers();
        
        if (loadMoreSuggestedBtn) {
            loadMoreSuggestedBtn.addEventListener('click', function(e) {
                e.preventDefault();
                suggestedUsersPage++;
                loadSuggestedUsers(true);
            });
        }
    }

    // Trending Topics Logic with Pagination
    const trendingList = document.getElementById('trendingList');
    const loadMoreTrendingBtn = document.getElementById('loadMoreTrending');
    let trendingPage = 1;
    let trendingTotalPages = 1;
    let trendingLoading = false;
    
    if (trendingList) {
        async function loadTrending(append = false) {
            if (trendingLoading) return;
            trendingLoading = true;
            
            try {
                const response = await fetch(`/api/posts/trending?page=${trendingPage}&limit=7`);
                if (!response.ok) {
                    throw new Error('Trending request failed with status ' + response.status);
                }
                const data = await response.json();
                const topics = data.data || [];
                
                if (!append) {
                    if (!Array.isArray(topics) || topics.length === 0) {
                        trendingList.innerHTML = '<div class="text-muted small">No trending topics right now.</div>';
                        if (loadMoreTrendingBtn) loadMoreTrendingBtn.style.display = 'none';
                        return;
                    }
                }
                
                const topicsHtml = topics.map(topic => {
                    let postCountStr = topic.post_count;
                    if (postCountStr >= 1000) {
                        postCountStr = (postCountStr / 1000).toFixed(1) + 'K';
                    }
                    // Only link to profile if username exists (for fallback)
                    const onClickHandler = topic.username 
                        ? `onclick="window.location.href='/profile/${topic.username}'" style="cursor: pointer;"`
                        : '';
                    return `
                        <div class="trending-item" ${onClickHandler}>
                            <small class="text-muted">${topic.category} · Trending</small>
                            <div class="fw-bold" style="font-size: 0.95rem; max-width: 100%; word-wrap: break-word;">${escapeHtml(topic.topic)}</div>
                            <small class="text-muted">${topic.engagement || postCountStr + ' interactions'}</small>
                        </div>
                    `;
                }).join('');
                
                if (append) {
                    trendingList.insertAdjacentHTML('beforeend', topicsHtml);
                } else {
                    trendingList.innerHTML = topicsHtml;
                }
                
                trendingTotalPages = data.totalPages || 1;
                if (loadMoreTrendingBtn) {
                    if (trendingPage >= trendingTotalPages) {
                        loadMoreTrendingBtn.style.display = 'none';
                    } else {
                        loadMoreTrendingBtn.style.display = 'block';
                    }
                }
            } catch (error) {
                console.error('Error fetching trending topics:', error);
                if (!append) {
                    trendingList.innerHTML = '<div class="text-muted small">Failed to load trending.</div>';
                }
            } finally {
                trendingLoading = false;
            }
        }
        
        loadTrending();
        
        if (loadMoreTrendingBtn) {
            loadMoreTrendingBtn.addEventListener('click', function(e) {
                e.preventDefault();
                trendingPage++;
                loadTrending(true);
            });
        }
    }

    // Keep track of which button opened the active modal so we can restore focus safely.
    const modalFocusTrigger = new WeakMap();

    // Close search dropdown when any modal opens
    document.addEventListener('show.bs.modal', function (event) {
        if (searchDropdown) {
            searchDropdown.style.display = 'none';
        }

        if (event.relatedTarget instanceof HTMLElement) {
            modalFocusTrigger.set(event.target, event.relatedTarget);
        }
    });

    // Clear retained focus before a modal is hidden to avoid aria-hidden warnings.
    document.addEventListener('hide.bs.modal', function (event) {
        const modal = event.target;
        const active = document.activeElement;

        if (modal instanceof HTMLElement && active instanceof HTMLElement && modal.contains(active)) {
            active.blur();

            const trigger = modalFocusTrigger.get(modal);
            if (trigger instanceof HTMLElement && document.body.contains(trigger)) {
                trigger.focus({ preventScroll: true });
            } else {
                document.body.setAttribute('tabindex', '-1');
                document.body.focus();
                document.body.removeAttribute('tabindex');
            }
        }

        modalFocusTrigger.delete(modal);
    });

    // Feed Tab Switching
    if (feedTabs) {
        feedTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const feedType = this.dataset.feedType;
                if (!feedType) return;
                
                if (feedType === currentFeedType) return;

                // Update active tab
                feedTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Reload the page with the selected feed
                window.location.href = `/feed?feed=${feedType}`;
            });
        });
    }

    // Toggle Like via AJAX
    document.addEventListener('click', async function(e) {
        const likeBtn = safeClosest(e.target, '.like-btn');
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
        const repostBtn = safeClosest(e.target, '.repost-btn');
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
    /**
     * AJAX Comment Creation Handler
     * 
     * Handles comment submission via AJAX with real-time DOM updates:
     * 1. Intercepts form submission
     * 2. Sends comment text to API endpoint
     * 3. On success:
     *    - Clears textarea
     *    - Updates comment count badge
     *    - Renders new comment with Edit/Delete buttons (if user's comment)
     *    - Updates both modal and feed comment sections
     *    - Scrolls comments list to bottom
     * 4. Comment structure includes:
     *    - User avatar and profile link
     *    - Full name, username, timestamp
     *    - Comment text (pre-wrapped)
     *    - Edit/Delete buttons (visible on hover, only for own comments)
     * 
     * Event Delegation: Uses event listener on document to handle dynamically added forms
     * 
     * API Endpoint: POST /api/posts/addComment/{postId}
     * Expected Response:
     * {
     *   comment: {
     *     id: number,
     *     user_id: number,
     *     comment_text: string,
     *     created_at: datetime,
     *     user: { id, first_name, last_name, username, profile_pic }
     *   },
     *   comment_count: number
     * }
     */
    document.addEventListener('submit', async function(e) {
        const commentForm = safeClosest(e.target, '[action*="addComment"]');
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
        /**
         * Generates HTML for a comment item
         * 
         * Creates a comment element with:
         * - User profile picture and details
         * - Comment text (HTML-escaped)
         * - Timestamp (relative format: just now, 5m, 2h, 3d)
         * - Edit/Delete buttons (only for own comments, hidden by default)
         * 
         * Parameters:
         * comment: {
         *   id: number,
         *   user_id: number,
         *   comment_text: string,
         *   created_at: datetime,
         *   user: { first_name, last_name, username, profile_pic }
         * }
         * 
         * Returns: HTML string for insertion into DOM
         * 
         * Note: Edit/Delete buttons visibility is controlled by CSS (.comment-actions opacity)
         *       and shown on hover via .comment-item:hover .comment-actions
         */
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

        // Check if comment is owned by current user
        let isOwnComment = false;
        const userIdMeta = document.querySelector('meta[name="current-user-id"]');
        if (userIdMeta) {
            isOwnComment = comment.user_id === parseInt(userIdMeta.getAttribute('content'));
        }

        // Build action buttons HTML if this is the user's comment
        let actionButtonsHtml = '';
        if (isOwnComment) {
            actionButtonsHtml = `
                <div class="comment-actions" style="display: flex; gap: 6px; opacity: 0; transition: opacity 0.2s ease; flex-shrink: 0;">
                    <button type="button" class="comment-edit-btn" data-comment-id="${comment.id}" data-comment-text="${escapeHtml(comment.comment_text).replace(/"/g, '&quot;')}" title="Edit comment" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 0.9rem; padding: 4px 8px; border-radius: 4px; transition: all 0.2s ease;">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button type="button" class="comment-delete-btn" data-comment-id="${comment.id}" title="Delete comment" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 0.9rem; padding: 4px 8px; border-radius: 4px; transition: all 0.2s ease;">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
        }

        return `
            <div class="comment-item" data-user-id="${comment.user_id}" data-comment-id="${comment.id}" style="position: relative; transition: all 0.2s ease;">
                <img src="${profilePic}" class="rounded-circle profile-pic-img flex-shrink-0" width="40" height="40" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center profile-pic-placeholder flex-shrink-0" style="width: 40px; height: 40px; overflow: hidden; border: 1px solid var(--glass-border);">
                    <i class="bi bi-person-fill text-white fs-5"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-baseline gap-2 mb-1 flex-wrap">
                        <a href="/profile/${username}" class="text-decoration-none">
                            <span class="fw-bold" style="color: var(--text-primary);">${firstName} ${lastName}</span>
                        </a>
                        <span class="text-muted" style="font-size: 0.85rem;">@${username}</span>
                        <span class="text-muted" style="font-size: 0.8rem;">• ${timeAgo}</span>
                    </div>
                    <div style="position: relative; display: flex; align-items: flex-start; gap: 8px;">
                        <p class="mb-0 comment-text" style="font-size: 1rem; line-height: 1.5; color: var(--text-primary); white-space: pre-wrap; flex: 1;">${escapeHtml(comment.comment_text)}</p>
                        ${actionButtonsHtml}
                    </div>
                </div>
            </div>
        `;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Notification functionality
    const notificationBadge = document.querySelector('.notification-badge');
    const notificationsModal = document.getElementById('notificationsModal');
    const notificationsList = document.getElementById('notificationsList');
    const notificationsPagination = document.getElementById('notificationsPagination');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const pageInfo = document.getElementById('pageInfo');
    
    let currentNotificationPage = 1;
    let totalNotificationPages = 1;

    async function loadUnreadCount() {
        try {
            const response = await fetch('/api/notifications/unreadCount');
            if (response.ok) {
                const data = await response.json();
                if (notificationBadge) {
                    if (data.count > 0) {
                        notificationBadge.textContent = data.count > 99 ? '99+' : data.count;
                        notificationBadge.classList.remove('d-none');
                    } else {
                        notificationBadge.classList.add('d-none');
                    }
                }
            }
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    }

    async function loadNotifications(page = 1) {
        try {
            const response = await fetch(`/api/notifications?page=${page}`);
            if (response.ok) {
                const data = await response.json();
                renderNotifications(data.notifications);
                updatePagination(data.currentPage, data.totalPages, data.total);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            if (notificationsList) {
                notificationsList.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-exclamation-triangle fs-2 mb-2 d-block opacity-25"></i>
                        <p>Failed to load notifications.</p>
                    </div>
                `;
            }
        }
    }
    
    function updatePagination(currentPage, totalPages, total) {
        if (!notificationsPagination) return;
        
        currentNotificationPage = currentPage;
        totalNotificationPages = totalPages;
        
        if (totalPages > 1) {
            notificationsPagination.style.display = 'flex';
        } else {
            notificationsPagination.style.display = 'none';
        }
        
        if (prevPageBtn) {
            prevPageBtn.disabled = currentPage <= 1;
        }
        
        if (nextPageBtn) {
            nextPageBtn.disabled = currentPage >= totalPages;
        }
        
        if (pageInfo) {
            pageInfo.textContent = `Page ${currentPage} of ${totalPages} (${total} total)`;
        }
    }

    function renderNotifications(notifications) {
        if (!notificationsList) return;
        
        if (notifications.length === 0) {
            notificationsList.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-2 mb-2 d-block opacity-25"></i>
                    <p>No notifications yet.</p>
                </div>
            `;
            return;
        }

        const html = notifications.map(notification => createNotificationHtml(notification)).join('');
        notificationsList.innerHTML = html;
    }

    function createNotificationHtml(notification) {
        const sender = notification.sender || {};
        const profilePic = sender.profile_pic ? `/${sender.profile_pic}` : '';
        const firstName = sender.first_name || 'Unknown';
        const lastName = sender.last_name || 'User';
        const username = sender.username || 'unknown';
        
        let message = '';
        switch (notification.type) {
            case 'like':
                message = 'liked your post';
                break;
            case 'comment':
                message = 'commented on your post';
                break;
            case 'follow':
                message = 'started following you';
                break;
            case 'repost':
                message = 'reposted your post';
                break;
            default:
                message = 'interacted with you';
        }

        const now = new Date();
        const createdDate = new Date(notification.created_at);
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

        // Normalize is_read (could be 0, 1, '0', '1', true, false)
        const isReadValue = notification.is_read;
        const isReadBool = isReadValue === 1 || isReadValue === '1' || isReadValue === true;
        
        const isReadClass = isReadBool ? 'notification-read' : 'notification-unread';

        return `
            <div class="notification-item ${isReadClass}" data-notification-id="${notification.id}" data-is-read="${isReadBool}" style="padding: 12px; border-bottom: 1px solid var(--glass-border); cursor: pointer;">
                <div class="d-flex gap-3 align-items-start">
                    <img src="${profilePic}" class="rounded-circle profile-pic-img flex-shrink-0" width="40" height="40" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center profile-pic-placeholder flex-shrink-0" style="width: 40px; height: 40px; overflow: hidden; border: 1px solid var(--glass-border);">
                        <i class="bi bi-person-fill text-white fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-baseline gap-2 mb-1 flex-wrap">
                            <a href="/profile/${username}" class="text-decoration-none" onclick="event.stopPropagation();">
                                <span class="fw-bold" style="color: var(--text-primary);">${escapeHtml(firstName)} ${escapeHtml(lastName)}</span>
                            </a>
                            <span class="text-muted" style="font-size: 0.85rem;">@${escapeHtml(username)}</span>
                            <span class="text-muted" style="font-size: 0.8rem;">• ${timeAgo}</span>
                        </div>
                        <p class="mb-0" style="font-size: 0.95rem; line-height: 1.4; color: var(--text-primary);">${escapeHtml(message)}</p>
                    </div>
                </div>
            </div>
        `;
    }

    async function markNotificationAsRead(notificationId) {
        try {
            await fetch(`/api/notifications/markAsRead/${notificationId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            loadUnreadCount();
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // Load unread count on page load
    loadUnreadCount();

    // Load notifications when modal opens
    if (notificationsModal) {
        notificationsModal.addEventListener('show.bs.modal', function() {
            currentNotificationPage = 1;
            loadNotifications(1);
        });
    }

    // Handle notification clicks
    if (notificationsList) {
        notificationsList.addEventListener('click', function(e) {
            const notificationItem = safeClosest(e.target, '.notification-item');
            if (notificationItem) {
                const notificationId = notificationItem.dataset.notificationId;
                const isRead = notificationItem.dataset.isRead === '1' || notificationItem.dataset.isRead === 'true';
                if (notificationId && !isRead) {
                    // Update classes
                    notificationItem.classList.remove('notification-unread');
                    notificationItem.classList.add('notification-read');
                    // Update data attribute
                    notificationItem.dataset.isRead = '1';
                    // Mark as read in backend
                    markNotificationAsRead(notificationId);
                }
            }
        });
    }

    // Pagination buttons
    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', function() {
            if (currentNotificationPage > 1) {
                loadNotifications(currentNotificationPage - 1);
            }
        });
    }

    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', function() {
            if (currentNotificationPage < totalNotificationPages) {
                loadNotifications(currentNotificationPage + 1);
            }
        });
    }

    // Poll for new notifications every 30 seconds
    setInterval(loadUnreadCount, 30000);

    // Comment Hover Actions - Using mouseover/mouseout for proper event delegation
    document.addEventListener('mouseover', function(e) {
        const commentItem = safeClosest(e.target, '.comment-item');
        if (commentItem) {
            const commentActions = commentItem.querySelector('.comment-actions');
            if (commentActions) {
                commentActions.style.opacity = '1';
            }
        }
    });

    document.addEventListener('mouseout', function(e) {
        const commentItem = safeClosest(e.target, '.comment-item');
        if (commentItem) {
            const commentActions = commentItem.querySelector('.comment-actions');
            if (commentActions) {
                commentActions.style.opacity = '0';
            }
        }
    });

    // Comment Edit Handler
    document.addEventListener('click', function(e) {
        const editBtn = safeClosest(e.target, '.comment-edit-btn');
        if (editBtn) {
            e.preventDefault();
            const commentId = editBtn.dataset.commentId;
            const commentText = editBtn.dataset.commentText;
            const commentItem = editBtn.closest('.comment-item');
            
            // Create edit form
            const editForm = document.createElement('div');
            editForm.className = 'comment-edit-form';
            editForm.style.cssText = `
                display: flex;
                gap: 8px;
                align-items: flex-start;
                margin-top: 8px;
            `;
            
            editForm.innerHTML = `
                <textarea class="glass-input comment-edit-textarea" style="flex: 1; resize: none; min-height: 60px;">${escapeHtml(commentText)}</textarea>
                <div style="display: flex; gap: 4px; flex-direction: column;">
                    <button type="button" class="comment-save-btn" data-comment-id="${commentId}" style="background: var(--accent-color); border: none; color: white; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; transition: all 0.2s ease;">Save</button>
                    <button type="button" class="comment-cancel-btn" style="background: var(--text-secondary); border: none; color: white; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; transition: all 0.2s ease;">Cancel</button>
                </div>
            `;
            
            // Replace comment text with edit form
            const commentText_el = commentItem.querySelector('.comment-text');
            const commentActions = commentItem.querySelector('.comment-actions');
            if (commentText_el && commentActions) {
                commentText_el.style.display = 'none';
                commentActions.style.display = 'none';
                commentItem.querySelector('.flex-grow-1').appendChild(editForm);
                
                // Focus textarea
                editForm.querySelector('.comment-edit-textarea').focus();
                
                // Cancel handler
                editForm.querySelector('.comment-cancel-btn').addEventListener('click', function() {
                    editForm.remove();
                    commentText_el.style.display = '';
                    commentActions.style.display = '';
                });
                
                // Save handler
                editForm.querySelector('.comment-save-btn').addEventListener('click', async function() {
                    const newText = editForm.querySelector('.comment-edit-textarea').value.trim();
                    if (!newText) {
                        alert('Comment cannot be empty');
                        return;
                    }
                    
                    try {
                        const formData = new FormData();
                        formData.append('comment_text', newText);
                        
                        const response = await fetch(`/posts/editComment/${commentId}`, {
                            method: 'POST',
                            body: formData
                        });
                        
                        if (response.ok) {
                            // Update comment text
                            commentText_el.textContent = newText;
                            editForm.remove();
                            commentText_el.style.display = '';
                            commentActions.style.display = '';
                            showNotification('Comment updated successfully');
                        } else {
                            alert('Failed to update comment');
                        }
                    } catch (error) {
                        console.error('Error updating comment:', error);
                        alert('Error updating comment');
                    }
                });
            }
        }
    });

    // Comment Delete Handler
    document.addEventListener('click', function(e) {
        const deleteBtn = safeClosest(e.target, '.comment-delete-btn');
        if (deleteBtn) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this comment?')) {
                return;
            }
            
            const commentId = deleteBtn.dataset.commentId;
            const commentItem = deleteBtn.closest('.comment-item');
            
            // Create hidden form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/posts/deleteComment/${commentId}`;
            form.style.display = 'none';
            
            document.body.appendChild(form);
            
            // Show loading state
            deleteBtn.style.opacity = '0.5';
            deleteBtn.style.pointerEvents = 'none';
            
            // Submit form
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                if (response.ok) {
                    // Fade out and remove comment
                    commentItem.style.transition = 'all 0.3s ease';
                    commentItem.style.opacity = '0';
                    commentItem.style.maxHeight = '0';
                    setTimeout(() => {
                        commentItem.remove();
                        showNotification('Comment deleted successfully');
                    }, 300);
                } else {
                    alert('Failed to delete comment');
                    deleteBtn.style.opacity = '1';
                    deleteBtn.style.pointerEvents = 'auto';
                }
            }).catch(error => {
                console.error('Error deleting comment:', error);
                alert('Error deleting comment');
                deleteBtn.style.opacity = '1';
                deleteBtn.style.pointerEvents = 'auto';
            }).finally(() => {
                form.remove();
            });
        }
    });
});

