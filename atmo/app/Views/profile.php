<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Profile Header -->
<div class="glass-panel text-center position-relative mb-4" style="overflow: hidden;">
    <!-- Cover Image -->
    <div style="height: 140px; background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(59, 130, 246, 0.15) 100%); border-radius: 8px 8px 0 0; margin: -20px -20px 0 -20px;"></div>
    
    <!-- Profile Picture -->
    <div style="position: relative; margin-top: -70px; display: flex; justify-content: center;">
        <img src="<?= base_url(esc($user['profile_pic'] ?? '')) ?>" class="rounded-circle profile-pic-img mx-auto" width="140" height="140" style="object-fit: cover; background: var(--bg-color); border: 4px solid var(--bg-color);" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
        <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center mx-auto" style="width: 140px; height: 140px; background: var(--bg-color); border: 4px solid var(--bg-color); overflow: hidden;">
            <i class="bi bi-person-fill text-white" style="font-size: 4rem;"></i>
        </div>
    </div>
    
    <!-- User Info -->
    <div class="mt-3">
        <h2 class="fw-bold mb-1" style="font-size: 1.75rem;"><?= esc($user['first_name'].' '.$user['last_name']) ?></h2>
        <p class="text-muted mb-3" style="font-size: 1rem;">@<?= esc($user['username']) ?></p>
        
        <?php if(!empty($user['bio'])): ?>
        <p class="mb-4 mx-auto" style="max-width: 90%; line-height: 1.6; color: var(--text-primary);"><?= esc($user['bio']) ?></p>
        <?php endif; ?>
        
        <!-- Stats -->
        <div class="d-flex justify-content-center align-items-center mb-4" style="gap: 16px;">
            <div class="text-center cursor-pointer profile-stat-item" id="followersTab" style="min-width: 70px; display: flex; flex-direction: column; align-items: center; padding: 8px 0;">
                <div class="profile-stat-number" style="font-size: 1.4rem; font-weight: 700; color: var(--text-primary); line-height: 1; margin-bottom: 4px;"><?= esc($followers_count) ?></div>
                <div class="profile-stat-label" style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1;">Followers</div>
            </div>
            <div class="text-center cursor-pointer profile-stat-item" id="followingTab" style="min-width: 70px; display: flex; flex-direction: column; align-items: center; padding: 8px 0;">
                <div class="profile-stat-number" style="font-size: 1.4rem; font-weight: 700; color: var(--text-primary); line-height: 1; margin-bottom: 4px;"><?= esc($following_count) ?></div>
                <div class="profile-stat-label" style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1;">Following</div>
            </div>
            <div class="text-center profile-stat-item" style="min-width: 70px; display: flex; flex-direction: column; align-items: center; padding: 8px 0;">
                <div class="profile-stat-number" style="font-size: 1.4rem; font-weight: 700; color: var(--text-primary); line-height: 1; margin-bottom: 4px;"><?= count($posts) ?></div>
                <div class="profile-stat-label" style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1;">Posts</div>
            </div>
        </div>
    </div>
    
    <!-- Followers/Following Modal -->
    <div class="modal fade" id="followModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content glass-panel" style="background: var(--bg-color);">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex gap-3 w-100">
                        <button class="nav-link fw-bold" id="modalFollowersTab" data-type="followers" style="color: var(--text-primary); border-bottom: 2px solid var(--accent-color);">Followers</button>
                        <button class="nav-link fw-bold" id="modalFollowingTab" data-type="following" style="color: var(--text-secondary);">Following</button>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="followModalBody">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile / Follow Button -->
    <?php if($is_own_profile): ?>
        <button class="glass-btn w-100 mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#editProfileForm">
            <i class="bi bi-pencil-square me-2"></i> Edit Profile
        </button>
        
        <div class="collapse text-start mt-4" id="editProfileForm">
            <div class="glass-panel" style="padding: 20px;">
                <form action="<?= site_url('profile/update') ?>" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-4">
                            <label class="form-label small text-muted fw-semibold">First Name</label>
                            <input type="text" name="first_name" class="glass-input" value="<?= esc(old('first_name') ?? $user['first_name']) ?>">
                        </div>
                        <div class="col-12 col-md-6 mb-4">
                            <label class="form-label small text-muted fw-semibold">Last Name</label>
                            <input type="text" name="last_name" class="glass-input" value="<?= esc(old('last_name') ?? $user['last_name']) ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small text-muted fw-semibold">Email</label>
                        <input type="email" name="email" class="glass-input" value="<?= esc(old('email') ?? $user['email']) ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small text-muted fw-semibold">Bio</label>
                        <textarea name="bio" class="glass-input" rows="3" placeholder="Tell us about yourself..."><?= esc(old('bio') ?? $user['bio']) ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small text-muted fw-semibold d-block">Profile Picture</label>
                        <input type="file" name="profile_pic" class="glass-input" accept="image/*" style="font-size: 0.9rem;">
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-toggle="collapse" data-bs-target="#editProfileForm">Cancel</button>
                        <button type="submit" class="glass-btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <form action="<?= site_url('users/toggleFollow/'.$user['id']) ?>" method="POST">
            <button type="submit" class="glass-btn w-100 mb-2" style="<?= $is_following ? 'background: transparent; border: 1px solid var(--accent-color); color: var(--accent-color);' : '' ?>">
                <?php if($is_following): ?>
                    <i class="bi bi-person-check-fill me-2"></i> Following
                <?php else: ?>
                    <i class="bi bi-person-plus-fill me-2"></i> Follow
                <?php endif; ?>
            </button>
        </form>
    <?php endif; ?>
</div>

<!-- Feed Header -->
<div class="mb-3 d-flex gap-3 fw-bold" style="padding: 0 10px;">
    <span class="text-white pb-2" style="border-bottom: 2px solid var(--accent-color); cursor:pointer;"><?= $is_own_profile ? 'My Posts' : 'Posts' ?></span>
</div>

<!-- User Posts -->
<?php if(empty($posts)): ?>
    <div class="glass-panel text-center text-muted py-5">
        <?php if($is_own_profile): ?>
            You haven't posted anything yet.
        <?php else: ?>
            This user hasn't posted anything yet.
        <?php endif; ?>
    </div>
<?php else: ?>
    <?php foreach($posts as $postBody): ?>
        <div class="glass-panel post-card" style="border-radius: var(--border-radius); padding: 16px 20px;">
            <div class="d-flex align-items-center mb-3">
                <img src="<?= base_url(esc($user['profile_pic'] ?? '')) ?>" class="rounded-circle me-3 profile-pic-img" width="48" height="48" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center me-3 profile-pic-placeholder" style="width: 48px; height: 48px; overflow: hidden; border: 2px solid var(--glass-border);">
                    <i class="bi bi-person-fill text-white fs-3"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-baseline gap-2">
                        <a href="<?= site_url('profile/'.esc($user['username'])) ?>" class="text-decoration-none">
                            <h6 class="mb-0 fw-bold fs-6" style="color: var(--text-primary);"><?= esc($user['first_name'].' '.$user['last_name']) ?> 
                                <?php if(!empty($user['is_verified'])): ?>
                                    <i class="bi bi-patch-check-fill text-primary small"></i>
                                <?php endif; ?>
                            </h6>
                        </a>
                        <span class="text-muted small">@<?= esc($user['username']) ?> • 
                            <span title="<?= date('M j, Y g:i A', strtotime($postBody['created_at'])) ?>">
                                <?php
                                    $diff = time() - strtotime($postBody['created_at']);
                                    if($diff < 60) echo 'just now';
                                    else if($diff < 3600) echo floor($diff/60).'m';
                                    else if($diff < 86400) echo floor($diff/3600).'h';
                                    else echo floor($diff/86400).'d';
                                ?>
                            </span>
                        </span>
                    </div>
                </div>
                <!-- Post Options (only for own profile) -->
                <?php if($is_own_profile): ?>
                <div class="dropdown">
                    <i class="bi bi-three-dots text-muted fs-5" style="cursor:pointer;" data-bs-toggle="dropdown"></i>
                    <ul class="dropdown-menu dropdown-menu-end glass-panel" style="background: var(--bg-color); border: 1px solid var(--glass-border); padding: 8px; min-width: 150px;">
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editModal<?= $postBody['id'] ?>" style="color: var(--text-primary); border-radius: 6px;">
                                <i class="bi bi-pencil-square me-2"></i> Edit
                            </a>
                        </li>
                        <li>
                            <form action="<?= site_url('posts/delete/'.$postBody['id']) ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this post?');" class="m-0 p-0">
                                <button type="submit" class="dropdown-item text-danger" style="border-radius: 6px;">
                                    <i class="bi bi-trash-fill me-2"></i> Delete
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            <!-- Edit Modal (only for own profile) -->
            <?php if($is_own_profile): ?>
            <div class="modal fade" id="editModal<?= $postBody['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content glass-panel" style="background: var(--bg-color);">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">Edit Post</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="<?= site_url('posts/edit/'.$postBody['id']) ?>" method="POST">
                            <div class="modal-body">
                                <textarea name="content" class="glass-input" rows="4" style="width: 100%; resize: none;"><?= esc($postBody['content']) ?></textarea>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="glass-btn">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Post Body Text -->
            <?php if(!empty($postBody['content'])): ?>
            <p class="mb-3 post-content" style="font-size: 1.05rem; line-height: 1.5; white-space: pre-wrap; color: var(--text-primary);"><?= esc($postBody['content']) ?></p>
            <?php endif; ?>

            <!-- Post Media -->
            <?php if(!empty($postBody['media_path'])): ?>
                <?php if($postBody['media_type'] == 'image'): ?>
                    <img src="<?= base_url(esc($postBody['media_path'])) ?>" class="media-attachment <?= !empty($postBody['content']) ? 'media-with-content' : 'media-only' ?>">
                <?php elseif($postBody['media_type'] == 'video'): ?>
                    <video controls class="media-attachment <?= !empty($postBody['content']) ? 'media-with-content' : 'media-only' ?>">
                        <source src="<?= base_url(esc($postBody['media_path'])) ?>">
                    </video>
                <?php endif; ?>
            <?php endif; ?>
            
            <!-- Interaction Bar -->
            <div class="interaction-bar d-flex gap-3 align-items-center mt-3">
                <form action="<?= site_url('posts/toggleLike/'.$postBody['id']) ?>" method="POST" class="m-0">
                    <button type="submit" class="action-btn like-btn <?= $postBody['is_liked'] ? 'text-danger' : '' ?>">
                        <i class="bi <?= $postBody['is_liked'] ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                        <span><?= $postBody['like_count'] ?></span>
                    </button>
                </form>
                <button class="action-btn comment-btn" data-bs-toggle="modal" data-bs-target="#commentModal<?= $postBody['id'] ?>">
                    <i class="bi bi-chat"></i>
                    <span><?= $postBody['comment_count'] ?></span>
                </button>
                <form action="<?= site_url('posts/toggleRepost/'.$postBody['id']) ?>" method="POST" class="m-0">
                    <button type="submit" class="action-btn repost-btn <?= $postBody['is_reposted'] ? 'text-success' : '' ?>">
                        <i class="bi bi-arrow-repeat"></i>
                        <span><?= $postBody['repost_count'] ?></span>
                    </button>
                </form>
            </div>
            
            <!-- Comments Section -->
            <?php if(!empty($postBody['comments'])): ?>
            <div class="comments-section">
                <?php 
                $commentsToShow = [];
                if (count($postBody['comments']) >= 2) {
                    $firstUserId = $postBody['comments'][0]['user_id'];
                    $secondUserId = $postBody['comments'][1]['user_id'];
                    
                    if ($firstUserId === $secondUserId) {
                        $commentsToShow = array_slice($postBody['comments'], 0, 1);
                    } else {
                        $commentsToShow = array_slice($postBody['comments'], 0, 2);
                    }
                } else {
                    $commentsToShow = $postBody['comments'];
                }
                
                foreach($commentsToShow as $comment): ?>
                <div class="comment-item">
                    <img src="<?= base_url(esc($comment['user']['profile_pic'] ?? '')) ?>" class="rounded-circle profile-pic-img flex-shrink-0" width="36" height="36" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center profile-pic-placeholder flex-shrink-0" style="width: 36px; height: 36px; overflow: hidden; border: 1px solid var(--glass-border);">
                        <i class="bi bi-person-fill text-white fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-baseline gap-2 mb-1 flex-wrap">
                            <a href="<?= site_url('profile/'.esc($comment['user']['username'] ?? 'unknown')) ?>" class="text-decoration-none">
                                <span class="fw-bold" style="color: var(--text-primary); font-size: 0.9rem;"><?= esc(($comment['user']['first_name'] ?? 'Unknown').' '.($comment['user']['last_name'] ?? 'User')) ?></span>
                            </a>
                            <span class="text-muted" style="font-size: 0.8rem;">@<?= esc($comment['user']['username'] ?? 'unknown') ?></span>
                            <span class="text-muted" style="font-size: 0.8rem;">• 
                                <?php
                                    $diff = time() - strtotime($comment['created_at']);
                                    if($diff < 60) echo 'just now';
                                    else if($diff < 3600) echo floor($diff/60).'m';
                                    else if($diff < 86400) echo floor($diff/3600).'h';
                                    else echo floor($diff/86400).'d';
                                ?>
                            </span>
                        </div>
                        <p class="mb-0" style="font-size: 0.95rem; line-height: 1.5; color: var(--text-primary); white-space: pre-wrap;"><?= esc($comment['comment_text']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php 
                $showViewAll = false;
                if (count($postBody['comments']) > 1) {
                    $firstUserId = $postBody['comments'][0]['user_id'];
                    $secondUserId = $postBody['comments'][1]['user_id'] ?? null;
                    
                    if ($firstUserId === $secondUserId) {
                        $showViewAll = count($postBody['comments']) > 1;
                    } else {
                        $showViewAll = count($postBody['comments']) > 2;
                    }
                }
                
                if ($showViewAll): ?>
                <button class="action-btn comment-btn mt-2" data-bs-toggle="modal" data-bs-target="#commentModal<?= $postBody['id'] ?>">
                    View all <?= $postBody['comment_count'] ?> comments
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Comment Modal (outside post loop to avoid stacking issues) -->
        <div class="modal fade" id="commentModal<?= $postBody['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content glass-panel" style="background: var(--bg-color);">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Comments</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if(!empty($postBody['comments'])): ?>
                        <div class="comments-list mb-3" style="max-height: 300px; overflow-y: auto;">
                            <?php foreach($postBody['comments'] as $comment): ?>
                            <div class="comment-item">
                                <img src="<?= base_url(esc($comment['user']['profile_pic'] ?? '')) ?>" class="rounded-circle profile-pic-img flex-shrink-0" width="40" height="40" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                                <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center profile-pic-placeholder flex-shrink-0" style="width: 40px; height: 40px; overflow: hidden; border: 1px solid var(--glass-border);">
                                    <i class="bi bi-person-fill text-white fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-baseline gap-2 mb-1 flex-wrap">
                                        <a href="<?= site_url('profile/'.esc($comment['user']['username'] ?? 'unknown')) ?>" class="text-decoration-none">
                                            <span class="fw-bold" style="color: var(--text-primary);"><?= esc(($comment['user']['first_name'] ?? 'Unknown').' '.($comment['user']['last_name'] ?? 'User')) ?></span>
                                        </a>
                                        <span class="text-muted" style="font-size: 0.85rem;">@<?= esc($comment['user']['username'] ?? 'unknown') ?></span>
                                        <span class="text-muted" style="font-size: 0.8rem;">• 
                                            <?php
                                                $diff = time() - strtotime($comment['created_at']);
                                                if($diff < 60) echo 'just now';
                                                else if($diff < 3600) echo floor($diff/60).'m';
                                                else if($diff < 86400) echo floor($diff/3600).'h';
                                                else echo floor($diff/86400).'d';
                                            ?>
                                        </span>
                                    </div>
                                    <p class="mb-0" style="font-size: 1rem; line-height: 1.5; color: var(--text-primary); white-space: pre-wrap;"><?= esc($comment['comment_text']) ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-chat-square-text fs-1 mb-2 opacity-25"></i>
                            <p>No comments yet. Be the first!</p>
                        </div>
                        <?php endif; ?>
                        <div class="comment-form-container">
                            <form action="<?= site_url('posts/addComment/'.$postBody['id']) ?>" method="POST">
                                <div class="d-flex gap-3 align-items-start">
                                    <img src="<?= base_url(esc(session()->get('profile_pic') ?? '')) ?>" class="rounded-circle profile-pic-img flex-shrink-0" width="40" height="40" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                                    <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center profile-pic-placeholder flex-shrink-0" style="width: 40px; height: 40px; overflow: hidden; border: 1px solid var(--glass-border);">
                                        <i class="bi bi-person-fill text-white fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <textarea name="comment_text" class="glass-input" rows="2" placeholder="Write a comment..." style="width: 100%; resize: none;"></textarea>
                                    </div>
                                    <button type="submit" class="glass-btn">Comment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const username = '<?= esc($user['username']) ?>';
    const followersTab = document.getElementById('followersTab');
    const followingTab = document.getElementById('followingTab');
    const followModalElement = document.getElementById('followModal');
    const followModal = new bootstrap.Modal(followModalElement, {
        backdrop: false,
        keyboard: true
    });
    const modalFollowersTab = document.getElementById('modalFollowersTab');
    const modalFollowingTab = document.getElementById('modalFollowingTab');
    const followModalBody = document.getElementById('followModalBody');
    const modalCloseBtn = followModalElement.querySelector('.btn-close');
    
    let currentType = 'followers';
    
    // Open modal with followers
    followersTab.addEventListener('click', function() {
        currentType = 'followers';
        updateModalTabs();
        loadFollowData('followers');
        followModal.show();
    });
    
    // Open modal with following
    followingTab.addEventListener('click', function() {
        currentType = 'following';
        updateModalTabs();
        loadFollowData('following');
        followModal.show();
    });
    
    // Close modal when clicking close button
    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', function() {
            followModal.hide();
        });
    }
    
    // Close modal when clicking outside the modal content
    followModalElement.addEventListener('click', function(e) {
        if (e.target === followModalElement) {
            followModal.hide();
        }
    });
    
    // Switch between tabs in modal
    modalFollowersTab.addEventListener('click', function() {
        currentType = 'followers';
        updateModalTabs();
        loadFollowData('followers');
    });
    
    modalFollowingTab.addEventListener('click', function() {
        currentType = 'following';
        updateModalTabs();
        loadFollowData('following');
    });
    
    function updateModalTabs() {
        if (currentType === 'followers') {
            modalFollowersTab.style.color = 'var(--text-primary)';
            modalFollowersTab.style.borderBottom = '2px solid var(--accent-color)';
            modalFollowingTab.style.color = 'var(--text-secondary)';
            modalFollowingTab.style.borderBottom = 'none';
        } else {
            modalFollowingTab.style.color = 'var(--text-primary)';
            modalFollowingTab.style.borderBottom = '2px solid var(--accent-color)';
            modalFollowersTab.style.color = 'var(--text-secondary)';
            modalFollowersTab.style.borderBottom = 'none';
        }
    }
    
    async function loadFollowData(type) {
        followModalBody.innerHTML = '<div class="text-center py-5"><i class="bi bi-hourglass-split fs-1 text-muted"></i></div>';
        
        try {
            const response = await fetch(`/api/users/${type}/${username}`);
            if (!response.ok) throw new Error('Failed to load data');
            
            const users = await response.json();
            
            if (users.length === 0) {
                followModalBody.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <i class="bi ${type === 'followers' ? 'bi-people' : 'bi-person-check'} fs-1 mb-3 opacity-25"></i>
                        <p>${type === 'followers' ? 'No followers yet.' : 'Not following anyone yet.'}</p>
                    </div>
                `;
                return;
            }
            
            followModalBody.innerHTML = `
                <div class="d-flex flex-column gap-3">
                    ${users.map(u => `
                        <div class="glass-panel modal-user-item" style="padding: 16px 20px;">
                            <div class="modal-user-info">
                                <img src="${u.profile_pic ? '/'+u.profile_pic : ''}" class="rounded-circle profile-pic-img" width="56" height="56" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                                <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center profile-pic-placeholder" style="width: 56px; height: 56px; overflow: hidden; border: 2px solid var(--glass-border);">
                                    <i class="bi bi-person-fill text-white fs-3"></i>
                                </div>
                                <div class="modal-user-text">
                                    <a href="/profile/${u.username}" class="text-decoration-none">
                                        <h6 class="mb-0 fw-bold" style="color: var(--text-primary);">${u.first_name} ${u.last_name}</h6>
                                    </a>
                                    <small class="text-muted">@${u.username}</small>
                                    ${u.bio ? `<p class="modal-user-bio mb-0">${u.bio}</p>` : ''}
                                </div>
                            </div>
                            ${u.id != <?= session()->get('user_id') ?> ? `
                            <form action="/users/toggleFollow/${u.id}" method="POST" class="flex-shrink-0">
                                <button type="submit" class="glass-btn btn-sm" style="${u.is_following ? 'background: transparent; border: 1px solid var(--glass-border); color: var(--text-primary);' : ''}">
                                    ${u.is_following ? 'Following' : 'Follow'}
                                </button>
                            </form>
                            ` : ''}
                        </div>
                    `).join('')}
                </div>
            `;
        } catch (error) {
            followModalBody.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-exclamation-triangle fs-1 mb-3 text-danger"></i>
                    <p>Failed to load data. Please try again.</p>
                </div>
            `;
        }
    }
});
</script>

<?= $this->endSection() ?>
