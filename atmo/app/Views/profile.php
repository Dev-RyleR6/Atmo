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
                <div id="followersCount" class="profile-stat-number" style="font-size: 1.4rem; font-weight: 700; color: var(--text-primary); line-height: 1; margin-bottom: 4px;"><?= esc($followers_count ?? 0) ?></div>
                <div class="profile-stat-label" style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1;">Followers</div>
            </div>
            <div class="text-center cursor-pointer profile-stat-item" id="followingTab" style="min-width: 70px; display: flex; flex-direction: column; align-items: center; padding: 8px 0;">
                <div id="followingCount" class="profile-stat-number" style="font-size: 1.4rem; font-weight: 700; color: var(--text-primary); line-height: 1; margin-bottom: 4px;"><?= esc($following_count ?? 0) ?></div>
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
                    <!-- Personal Information Section -->
                    <h6 class="fw-bold mb-3" style="color: var(--text-primary); border-bottom: 1px solid var(--glass-border); padding-bottom: 12px;">Personal Information</h6>
                    
                    <div class="row">
                        <div class="col-12 col-md-6 mb-4">
                            <label class="form-label small text-muted fw-semibold">Username</label>
                            <input type="text" class="glass-input" value="<?= esc($user['username']) ?>" disabled>
                            <small class="text-muted d-block mt-1">Cannot be changed</small>
                        </div>
                        <div class="col-12 col-md-6 mb-4">
                            <label class="form-label small text-muted fw-semibold">Email</label>
                            <input type="email" class="glass-input" value="<?= esc($user['email']) ?>" disabled>
                            <small class="text-muted d-block mt-1">Cannot be changed</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 col-md-6 mb-4">
                            <label class="form-label small text-muted fw-semibold">First Name</label>
                            <input type="text" name="first_name" class="glass-input" value="<?= esc(old('first_name') ?? ($user['first_name'] ?? '')) ?>">
                        </div>
                        <div class="col-12 col-md-6 mb-4">
                            <label class="form-label small text-muted fw-semibold">Last Name</label>
                            <input type="text" name="last_name" class="glass-input" value="<?= esc(old('last_name') ?? ($user['last_name'] ?? '')) ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 col-md-6 mb-4">
                            <label class="form-label small text-muted fw-semibold">Date of Birth</label>
                            <input type="date" name="dob" class="glass-input" value="<?= esc(old('dob') ?? ($user['dob'] ?? '')) ?>">
                        </div>
                        <div class="col-12 col-md-6 mb-4">
                            <label class="form-label small text-muted fw-semibold">Sex</label>
                            <select name="sex" class="glass-input">
                                <option value="">Select...</option>
                                <option value="Male" <?= (old('sex') ?? ($user['sex'] ?? '')) == 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= (old('sex') ?? ($user['sex'] ?? '')) == 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= (old('sex') ?? ($user['sex'] ?? '')) == 'Other' ? 'selected' : '' ?>>Other</option>
                                <option value="Prefer not to say" <?= (old('sex') ?? ($user['sex'] ?? '')) == 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Bio Section -->
                    <h6 class="fw-bold mb-3 mt-4" style="color: var(--text-primary); border-bottom: 1px solid var(--glass-border); padding-bottom: 12px;">Bio</h6>
                    
                    <div class="mb-4">
                        <label class="form-label small text-muted fw-semibold">Bio</label>
                        <textarea name="bio" class="glass-input" rows="3" placeholder="Tell us about yourself..."><?= esc(old('bio') ?? ($user['bio'] ?? '')) ?></textarea>
                    </div>
                    
                    <!-- Security Section -->
                    <h6 class="fw-bold mb-3 mt-4" style="color: var(--text-primary); border-bottom: 1px solid var(--glass-border); padding-bottom: 12px;">Security</h6>
                    
                    <div class="row">
                        <div class="col-12 col-md-6 mb-4">
                            <label class="form-label small text-muted fw-semibold">Current Password <span style="color: #dc3545;">*</span></label>
                            <input type="password" name="current_password" class="glass-input" placeholder="" required>
                            <small class="text-muted d-block mt-1">Required to change password</small>
                        </div>
                        <div class="col-12 col-md-6 mb-4">
                            <label class="form-label small text-muted fw-semibold">New Password <span style="color: #dc3545;">*</span></label>
                            <input type="password" name="new_password" class="glass-input" placeholder="" minlength="8" required>
                            <small class="text-muted d-block mt-1">Minimum 8 characters</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 col-md-6 mb-4">
                            <label class="form-label small text-muted fw-semibold">Confirm Password <span style="color: #dc3545;">*</span></label>
                            <input type="password" name="confirm_password" class="glass-input" placeholder="" minlength="8" required>
                            <small class="text-muted d-block mt-1">Must match new password</small>
                        </div>
                    </div>
                    
                    <!-- Profile Picture Section -->
                    <h6 class="fw-bold mb-3 mt-4" style="color: var(--text-primary); border-bottom: 1px solid var(--glass-border); padding-bottom: 12px;">Profile Picture</h6>
                    
                    <div class="mb-4">
                        <label class="form-label small text-muted fw-semibold d-block">Profile Picture</label>
                        <div class="profile-pic-preview-container mb-2" style="display: flex; align-items: center; gap: 12px;">
                            <img id="editProfilePicPreview" 
                                 src="<?= base_url(esc($user['profile_pic'] ?? '')) ?>" 
                                 class="rounded-circle" 
                                 width="60" 
                                 height="60"
                                 onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                            <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center" style="width: 60px; height: 60px; overflow: hidden; border: 1px solid var(--glass-border);">
                                <i class="bi bi-person-fill text-white fs-3"></i>
                            </div>
                        </div>
                        <input type="file" name="profile_pic" id="editProfilePicInput" class="glass-input" accept="image/*" style="font-size: 0.9rem;">
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-secondary px-4" data-bs-toggle="collapse" data-bs-target="#editProfileForm">Cancel</button>
                        <button type="submit" class="glass-btn edit-profile-submit-btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <form class="follow-toggle-form" action="<?= site_url('users/toggleFollow/'.($user['id'] ?? '')) ?>" method="POST" data-user-id="<?= $user['id'] ?? '' ?>">
            <button type="submit" class="glass-btn w-100 mb-2 follow-toggle-btn" style="<?= $is_following ? 'background: transparent; border: 1px solid var(--accent-color); color: var(--accent-color);' : '' ?>">
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
    <button class="profile-tab active" data-tab="posts" style="background: none; border: none; color: var(--text-primary); padding: 0 0 8px 0; border-bottom: 2px solid var(--accent-color); cursor: pointer; font-weight: bold; transition: all 0.2s ease;"><?= $is_own_profile ? 'My Posts' : 'Posts' ?></button>
    <?php if($is_own_profile): ?>
    <button class="profile-tab" data-tab="reposts" style="background: none; border: none; color: var(--text-secondary); padding: 0 0 8px 0; cursor: pointer; font-weight: bold; transition: all 0.2s ease;">Reposts</button>
    <?php endif; ?>
</div>

<!-- User Posts Tab -->
<div class="profile-tab-content" data-tab-content="posts">
<?php if(empty($posts)): ?>
    <div class="glass-panel text-center text-muted py-5">
        <?php if($is_own_profile): ?>
            You haven't posted anything yet.
        <?php else: ?>
            This user hasn't posted anything yet.
        <?php endif; ?>
    </div>
<?php else: ?>
    <?php foreach(is_array($posts) ? $posts : [] as $post): ?>
        <?php 
            $isRepost = ($post['type'] ?? 'original') == 'repost';
            if($isRepost) {
                if (empty($post['original_post']) || empty($post['original_post']['user'])) {
                    continue;
                }
                $postBody = $post['original_post'];
            } else {
                if (empty($post['user'])) {
                    continue;
                }
                $postBody = $post;
            }
        ?>

        <div class="glass-panel post-card <?= $isRepost ? 'post-card-repost' : '' ?>" style="padding: 12px 16px;">
            <?php if($isRepost): ?>
                <div class="d-flex align-items-center gap-2 mb-2 repost-header" style="padding-left: 4px;">
                    <i class="bi bi-arrow-repeat text-muted"></i>
                    <span class="text-muted small fw-medium">
                        <?= $is_own_profile ? 'You' : esc($post['reposted_by']['first_name'] ?? $post['reposted_by']['username'] ?? 'Someone') ?> reposted
                    </span>
                </div>
            <?php endif; ?>

            <div class="d-flex align-items-start mb-2">
                <img src="<?= base_url(esc($postBody['user']['profile_pic'] ?? '')) ?>" class="rounded-circle me-2 profile-pic-img" width="40" height="40" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center me-2 profile-pic-placeholder" style="width: 40px; height: 40px; overflow: hidden; border: 1.5px solid var(--glass-border);">
                    <i class="bi bi-person-fill text-white fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-baseline gap-1 flex-wrap">
                        <a href="<?= site_url('profile/'.esc($postBody['user']['username'])) ?>" class="text-decoration-none">
                            <h6 class="mb-0 fw-bold fs-6" style="color: var(--text-primary);"><?= esc($postBody['user']['first_name'].' '.$postBody['user']['last_name']) ?> 
                                <?php if(!empty($postBody['user']['is_verified'])): ?>
                                    <i class="bi bi-patch-check-fill text-primary small"></i>
                                <?php endif; ?>
                            </h6>
                        </a>
                        <span class="text-muted small">@<?= esc($postBody['user']['username']) ?> • 
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
                <!-- Post Options (only for own profile and original post) -->
                <?php if($is_own_profile && !$isRepost): ?>
                <div class="dropdown">
                    <i class="bi bi-three-dots text-muted fs-5" style="cursor:pointer;" data-bs-toggle="dropdown"></i>
                    <ul class="dropdown-menu dropdown-menu-end glass-panel" style="background: var(--bg-color); border: 1px solid var(--glass-border); padding: 6px; min-width: 140px;">
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editModal<?= $postBody['id'] ?>" style="color: var(--text-primary); border-radius: 6px; padding: 6px 12px; font-size: 0.9rem;">
                                <i class="bi bi-pencil-square me-2"></i> Edit
                            </a>
                        </li>
                        <li>
                            <form action="<?= site_url('posts/delete/'.$postBody['id']) ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this post?');" class="m-0 p-0">
                                <button type="submit" class="dropdown-item text-danger" style="border-radius: 6px; padding: 6px 12px; font-size: 0.9rem;">
                                    <i class="bi bi-trash-fill me-2"></i> Delete
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            <!-- Edit Modal (only for own profile and original post) -->
            <?php if($is_own_profile && !$isRepost): ?>
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
            <p class="mb-2 post-content" style="font-size: 1rem; line-height: 1.4; white-space: pre-wrap; color: var(--text-primary);"><?= esc($postBody['content']) ?></p>
            <?php endif; ?>

            <!-- Post Media -->
            <?php if(!empty($postBody['media_path'])): ?>
                <div>
                    <?php if($postBody['media_type'] == 'image'): ?>
                        <img src="<?= base_url(esc($postBody['media_path'])) ?>" class="media-attachment <?= !empty($postBody['content']) ? 'media-with-content' : 'media-only' ?>">
                    <?php elseif($postBody['media_type'] == 'video'): ?>
                        <video controls class="media-attachment <?= !empty($postBody['content']) ? 'media-with-content' : 'media-only' ?>">
                            <source src="<?= base_url(esc($postBody['media_path'])) ?>">
                        </video>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Interaction Bar -->
            <div class="interaction-bar d-flex gap-2 align-items-center mt-2">
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
                
                foreach(is_array($commentsToShow) ? $commentsToShow : [] as $comment): ?>
                <div class="comment-item" data-comment-id="<?= $comment['id'] ?>" style="padding: 8px; gap: 8px; margin-bottom: 8px; position: relative; transition: all 0.2s ease;">
                    <img src="<?= base_url(esc($comment['user']['profile_pic'] ?? '')) ?>" class="rounded-circle profile-pic-img flex-shrink-0" width="32" height="32" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center profile-pic-placeholder flex-shrink-0" style="width: 32px; height: 32px; overflow: hidden; border: 1px solid var(--glass-border);">
                        <i class="bi bi-person-fill text-white fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-baseline gap-2 mb-0 flex-wrap">
                            <a href="<?= site_url('profile/'.esc($comment['user']['username'] ?? 'unknown')) ?>" class="text-decoration-none">
                                <span class="fw-bold" style="color: var(--text-primary); font-size: 0.85rem;"><?= esc(($comment['user']['first_name'] ?? 'Unknown').' '.($comment['user']['last_name'] ?? 'User')) ?></span>
                            </a>
                            <span class="text-muted" style="font-size: 0.75rem;">@<?= esc($comment['user']['username'] ?? 'unknown') ?></span>
                            <span class="text-muted" style="font-size: 0.75rem;">• 
                                <?php
                                    $diff = time() - strtotime($comment['created_at']);
                                    if($diff < 60) echo 'just now';
                                    else if($diff < 3600) echo floor($diff/60).'m';
                                    else if($diff < 86400) echo floor($diff/3600).'h';
                                    else echo floor($diff/86400).'d';
                                ?>
                            </span>
                        </div>
                        <div style="position: relative; display: flex; align-items: flex-start; gap: 8px;">
                            <p class="mb-0 comment-text" style="font-size: 0.9rem; line-height: 1.4; color: var(--text-primary); white-space: pre-wrap; flex: 1;"><?= esc($comment['comment_text']) ?></p>
                            <?php if($comment['user_id'] == session()->get('user_id')): ?>
                            <div class="comment-actions" style="display: flex; gap: 6px; opacity: 0; transition: opacity 0.2s ease; flex-shrink: 0;">
                                <button type="button" class="comment-edit-btn" data-comment-id="<?= $comment['id'] ?>" data-comment-text="<?= esc($comment['comment_text']) ?>" title="Edit comment" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; transition: all 0.2s ease;">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" class="comment-delete-btn" data-comment-id="<?= $comment['id'] ?>" title="Delete comment" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; transition: all 0.2s ease;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
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
                <button class="action-btn comment-btn mt-1" data-bs-toggle="modal" data-bs-target="#commentModal<?= $postBody['id'] ?>" style="padding: 2px 8px; font-size: 0.8rem;">
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
                            <?php foreach(is_array($postBody['comments']) ? $postBody['comments'] : [] as $comment): ?>
                            <div class="comment-item" data-comment-id="<?= $comment['id'] ?>" style="position: relative; transition: all 0.2s ease;">
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
                                    <div style="position: relative; display: flex; align-items: flex-start; gap: 8px;">
                                        <p class="mb-0 comment-text" style="font-size: 1rem; line-height: 1.5; color: var(--text-primary); white-space: pre-wrap; flex: 1;"><?= esc($comment['comment_text']) ?></p>
                                        <?php if($comment['user_id'] == session()->get('user_id')): ?>
                                        <div class="comment-actions" style="display: flex; gap: 6px; opacity: 0; transition: opacity 0.2s ease; flex-shrink: 0;">
                                            <button type="button" class="comment-edit-btn" data-comment-id="<?= $comment['id'] ?>" data-comment-text="<?= esc($comment['comment_text']) ?>" title="Edit comment" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 0.9rem; padding: 4px 8px; border-radius: 4px; transition: all 0.2s ease;">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="comment-delete-btn" data-comment-id="<?= $comment['id'] ?>" title="Delete comment" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 0.9rem; padding: 4px 8px; border-radius: 4px; transition: all 0.2s ease;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                    </div>
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
</div>

<!-- Reposts Tab -->
<div class="profile-tab-content" data-tab-content="reposts" style="display: none;">
<?php 
// Separate reposts from posts
$reposts_only = array_filter($posts, function($post) {
    return ($post['type'] ?? 'original') == 'repost';
});
?>
<?php if(empty($reposts_only)): ?>
    <div class="glass-panel text-center text-muted py-5">
        You haven't reposted anything yet.
    </div>
<?php else: ?>
    <?php foreach(is_array($reposts_only) ? $reposts_only : [] as $post): ?>
        <?php 
            if (empty($post['original_post']) || empty($post['original_post']['user'])) {
                continue;
            }
            $postBody = $post['original_post'];
            $repost = $post;
        ?>

        <div class="glass-panel post-card post-card-repost" style="padding: 12px 16px; position: relative;">
            <div class="d-flex align-items-center gap-2 mb-2 repost-header" style="padding-left: 4px; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-arrow-repeat text-muted"></i>
                    <span class="text-muted small fw-medium">You reposted</span>
                </div>
                <!-- Delete button: removes the repost relationship permanently from database -->
                <button type="button" class="repost-delete-btn" data-repost-id="<?= $repost['id'] ?>" data-post-id="<?= $postBody['id'] ?>" title="Remove repost" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 0.9rem; padding: 4px 8px; border-radius: 4px; transition: all 0.2s ease;">
                    <i class="bi bi-trash"></i>
                </button>
            </div>

            <div class="d-flex align-items-start mb-2">
                <img src="<?= base_url(esc($postBody['user']['profile_pic'] ?? '')) ?>" class="rounded-circle me-2 profile-pic-img" width="40" height="40" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center me-2 profile-pic-placeholder" style="width: 40px; height: 40px; overflow: hidden; flex-shrink: 0; border: 1.5px solid var(--glass-border);">
                    <i class="bi bi-person-fill text-white fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-baseline gap-1 flex-wrap">
                        <a href="<?= site_url('profile/'.esc($postBody['user']['username'] ?? 'unknown')) ?>" class="text-decoration-none">
                            <h6 class="mb-0 fw-bold fs-6" style="color: var(--text-primary);"><?= esc($postBody['user']['first_name'].' '.$postBody['user']['last_name']) ?> 
                                <?php if(!empty($postBody['user']['is_verified'])): ?>
                                    <i class="bi bi-patch-check-fill text-primary small"></i>
                                <?php endif; ?>
                            </h6>
                        </a>
                        <span class="text-muted small">@<?= esc($postBody['user']['username'] ?? 'unknown') ?> • 
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
            </div>

            <!-- Post Body Text -->
            <?php if(!empty($postBody['content'])): ?>
            <p class="mb-2 post-content" style="font-size: 1rem; line-height: 1.4; white-space: pre-wrap; color: var(--text-primary);"><?= esc($postBody['content']) ?></p>
            <?php endif; ?>

            <!-- Post Media -->
            <?php if(!empty($postBody['media_path'])): ?>
                <div>
                    <?php if($postBody['media_type'] == 'image'): ?>
                        <img src="<?= base_url(esc($postBody['media_path'])) ?>" class="media-attachment <?= !empty($postBody['content']) ? 'media-with-content' : 'media-only' ?>">
                    <?php elseif($postBody['media_type'] == 'video'): ?>
                        <video controls class="media-attachment <?= !empty($postBody['content']) ? 'media-with-content' : 'media-only' ?>">
                            <source src="<?= base_url(esc($postBody['media_path'])) ?>">
                        </video>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Interaction Bar -->
            <div class="interaction-bar d-flex gap-2 align-items-center mt-2">
                <form action="<?= site_url('posts/toggleLike/'.$postBody['id']) ?>" method="POST" class="m-0">
                    <button type="submit" class="action-btn like-btn <?= $postBody['is_liked'] ? 'text-danger' : '' ?>">
                        <i class="bi <?= $postBody['is_liked'] ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                        <span><?= $postBody['like_count'] ?></span>
                    </button>
                </form>
                <button class="action-btn comment-btn" data-bs-toggle="modal" data-bs-target="#repostCommentModal<?= $postBody['id'] ?>">
                    <i class="bi bi-chat"></i>
                    <span><?= $postBody['comment_count'] ?></span>
                </button>
            </div>
            
            <!-- Comments Section (Preview) -->
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
                
                foreach(is_array($commentsToShow) ? $commentsToShow : [] as $comment): ?>
                <div class="comment-item" data-comment-id="<?= $comment['id'] ?>" style="padding: 8px; gap: 8px; margin-bottom: 8px; position: relative; transition: all 0.2s ease;">
                    <img src="<?= base_url(esc($comment['user']['profile_pic'] ?? '')) ?>" class="rounded-circle profile-pic-img flex-shrink-0" width="32" height="32" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center profile-pic-placeholder flex-shrink-0" style="width: 32px; height: 32px; overflow: hidden; border: 1px solid var(--glass-border);">
                        <i class="bi bi-person-fill text-white fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-baseline gap-2 mb-0 flex-wrap">
                            <a href="<?= site_url('profile/'.esc($comment['user']['username'] ?? 'unknown')) ?>" class="text-decoration-none">
                                <span class="fw-bold" style="color: var(--text-primary); font-size: 0.85rem;"><?= esc(($comment['user']['first_name'] ?? 'Unknown').' '.($comment['user']['last_name'] ?? 'User')) ?></span>
                            </a>
                            <span class="text-muted" style="font-size: 0.75rem;">@<?= esc($comment['user']['username'] ?? 'unknown') ?></span>
                            <span class="text-muted" style="font-size: 0.75rem;">• 
                                <?php
                                    $diff = time() - strtotime($comment['created_at']);
                                    if($diff < 60) echo 'just now';
                                    else if($diff < 3600) echo floor($diff/60).'m';
                                    else if($diff < 86400) echo floor($diff/3600).'h';
                                    else echo floor($diff/86400).'d';
                                ?>
                            </span>
                        </div>
                        <div style="position: relative; display: flex; align-items: flex-start; gap: 8px;">
                            <p class="mb-0 comment-text" style="font-size: 0.9rem; line-height: 1.4; color: var(--text-primary); white-space: pre-wrap; flex: 1;"><?= esc($comment['comment_text']) ?></p>
                            <?php if($comment['user_id'] == session()->get('user_id')): ?>
                            <div class="comment-actions" style="display: flex; gap: 6px; opacity: 0; transition: opacity 0.2s ease; flex-shrink: 0;">
                                <button type="button" class="comment-edit-btn" data-comment-id="<?= $comment['id'] ?>" data-comment-text="<?= esc($comment['comment_text']) ?>" title="Edit comment" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; transition: all 0.2s ease;">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" class="comment-delete-btn" data-comment-id="<?= $comment['id'] ?>" title="Delete comment" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; transition: all 0.2s ease;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
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
                <button class="action-btn comment-btn mt-1" data-bs-toggle="modal" data-bs-target="#repostCommentModal<?= $postBody['id'] ?>" style="padding: 2px 8px; font-size: 0.8rem;">
                    View all <?= $postBody['comment_count'] ?> comments
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Comment Modal for Repost -->
        <div class="modal fade" id="repostCommentModal<?= $postBody['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content glass-panel" style="background: var(--bg-color);">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Comments</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if(!empty($postBody['comments'])): ?>
                        <div class="comments-list mb-3" style="max-height: 300px; overflow-y: auto;">
                            <?php foreach(is_array($postBody['comments']) ? $postBody['comments'] : [] as $comment): ?>
                            <div class="comment-item" data-comment-id="<?= $comment['id'] ?>" style="position: relative; transition: all 0.2s ease;">
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
                                    <div style="position: relative; display: flex; align-items: flex-start; gap: 8px;">
                                        <p class="mb-0 comment-text" style="font-size: 1rem; line-height: 1.5; color: var(--text-primary); white-space: pre-wrap; flex: 1;"><?= esc($comment['comment_text']) ?></p>
                                        <?php if($comment['user_id'] == session()->get('user_id')): ?>
                                        <div class="comment-actions" style="display: flex; gap: 6px; opacity: 0; transition: opacity 0.2s ease; flex-shrink: 0;">
                                            <button type="button" class="comment-edit-btn" data-comment-id="<?= $comment['id'] ?>" data-comment-text="<?= esc($comment['comment_text']) ?>" title="Edit comment" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 0.9rem; padding: 4px 8px; border-radius: 4px; transition: all 0.2s ease;">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="comment-delete-btn" data-comment-id="<?= $comment['id'] ?>" title="Delete comment" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 0.9rem; padding: 4px 8px; border-radius: 4px; transition: all 0.2s ease;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                    </div>
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
</div>

<script>

// Utility function to show notification messages
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    const bgColor = type === 'success' ? 'rgba(25, 135, 84, 0.2)' : 'rgba(220, 53, 69, 0.2)';
    const borderColor = type === 'success' ? 'rgba(25, 135, 84, 0.4)' : 'rgba(220, 53, 69, 0.4)';
    const icon = type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill';
    
    notification.className = `alert alert-dismissible fade show glass-panel`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 280px;
        max-width: 90vw;
        border-radius: 12px;
        background: ${bgColor};
        border-color: ${borderColor};
        color: white;
        backdrop-filter: blur(10px);
    `;
    notification.innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-${icon}"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    // Profile picture preview
    const profilePicInput = document.getElementById('editProfilePicInput');
    const profilePicPreview = document.getElementById('editProfilePicPreview');
    
    if (profilePicInput && profilePicPreview) {
        profilePicInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePicPreview.src = e.target.result;
                    profilePicPreview.classList.remove('d-none');
                    if (profilePicPreview.nextElementSibling) {
                        profilePicPreview.nextElementSibling.classList.add('d-none');
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

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
    
    function safeHideFollowModal() {
        if (document.activeElement && followModalElement.contains(document.activeElement)) {
            document.activeElement.blur();
        }
        followModal.hide();
    }

    // Close modal when clicking close button
    if (modalCloseBtn) {
        modalCloseBtn.addEventListener('click', function() {
            safeHideFollowModal();
        });
    }
    
    // Close modal when clicking outside the modal content
    followModalElement.addEventListener('click', function(e) {
        if (e.target === followModalElement) {
            safeHideFollowModal();
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
                            <form action="/users/toggleFollow/${u.id}" method="POST" class="follow-toggle-form flex-shrink-0" data-user-id="${u.id}">
                                <button type="submit" class="glass-btn btn-sm follow-toggle-btn" style="${u.is_following ? 'background: transparent; border: 1px solid var(--glass-border); color: var(--text-primary);' : ''}">
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

    // Profile Tab Switching
    const profileTabs = document.querySelectorAll('.profile-tab');
    profileTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Update active tab styling
            profileTabs.forEach(t => {
                t.style.color = 'var(--text-secondary)';
                t.style.borderBottom = 'none';
            });
            this.style.color = 'var(--text-primary)';
            this.style.borderBottom = '2px solid var(--accent-color)';
            
            // Show/hide tab content
            const tabContents = document.querySelectorAll('.profile-tab-content');
            tabContents.forEach(content => {
                content.style.display = 'none';
            });
            document.querySelector(`[data-tab-content="${tabName}"]`).style.display = 'block';
        });
    });

    // Repost Delete Handler - Permanently removes repost from database
    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.repost-delete-btn');
        if (deleteBtn) {
            e.preventDefault();
            if (!confirm('Remove this repost?')) {
                return;
            }
            
            const postId = deleteBtn.dataset.postId; // Original post ID to toggle repost for
            const repostId = deleteBtn.dataset.repostId; // Repost record ID for reference
            const postCard = deleteBtn.closest('.post-card');
            
            // Show loading state
            deleteBtn.style.opacity = '0.5';
            deleteBtn.style.pointerEvents = 'none';
            
            // Call toggleRepost with the original post ID (not repost ID) to delete the repost
            fetch(`/posts/toggleRepost/${postId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                if (response.ok) {
                    // Fade out and remove repost card
                    postCard.style.transition = 'all 0.3s ease';
                    postCard.style.opacity = '0';
                    postCard.style.maxHeight = '0';
                    setTimeout(() => {
                        postCard.remove();
                        
                        // Check if reposts tab is now empty and update display
                        const repostsContainer = document.querySelector('[data-tab-content="reposts"]');
                        if (repostsContainer) {
                            const remainingReposts = repostsContainer.querySelectorAll('.post-card-repost');
                            if (remainingReposts.length === 0) {
                                repostsContainer.innerHTML = `
                                    <div class="glass-panel text-center text-muted py-5">
                                        You haven't reposted anything yet.
                                    </div>
                                `;
                            }
                        }
                    }, 300);
                    showNotification('Repost removed successfully');
                } else {
                    alert('Failed to remove repost');
                    deleteBtn.style.opacity = '1';
                    deleteBtn.style.pointerEvents = 'auto';
                }
            }).catch(error => {
                console.error('Error removing repost:', error);
                alert('Error removing repost');
                deleteBtn.style.opacity = '1';
                deleteBtn.style.pointerEvents = 'auto';
            });
        }
    });

    // Profile Edit Form Validation - Check security fields
    const editProfileForm = document.querySelector('#editProfileForm form');
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', function(e) {
            const currentPassword = document.querySelector('input[name="current_password"]').value.trim();
            const newPassword = document.querySelector('input[name="new_password"]').value.trim();
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value.trim();
            
            // If any password field is filled, all must be filled
            if (currentPassword || newPassword || confirmPassword) {
                if (!currentPassword) {
                    e.preventDefault();
                    alert('Please enter your current password');
                    return false;
                }
                if (!newPassword) {
                    e.preventDefault();
                    alert('Please enter a new password');
                    return false;
                }
                if (!confirmPassword) {
                    e.preventDefault();
                    alert('Please confirm your new password');
                    return false;
                }
                if (newPassword.length < 8) {
                    e.preventDefault();
                    alert('New password must be at least 8 characters long');
                    return false;
                }
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match');
                    return false;
                }
            }
            return true;
        });
    }

});

</script>

<?= $this->endSection() ?>
