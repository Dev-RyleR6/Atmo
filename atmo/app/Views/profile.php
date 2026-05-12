<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Profile Header -->
<div class="glass-panel text-center position-relative mb-4">
    <div style="height: 120px; background: rgba(255,255,255,0.05); border-radius: 8px 8px 0 0; margin: -20px -20px 0 -20px;"></div>
    
    <img src="<?= base_url(esc($user['profile_pic'] ?? '')) ?>" class="rounded-circle profile-pic-img" width="120" height="120" style="object-fit:cover; margin-top: -60px; background: var(--bg-color);" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
    <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center mx-auto" style="width: 120px; height: 120px; margin-top: -60px; background: var(--bg-color); border: 2px solid var(--glass-border);">
        <i class="bi bi-person-fill text-white fs-1"></i>
    </div>
    
    <h3 class="fw-bold mt-2 mb-0"><?= esc($user['first_name'].' '.$user['last_name']) ?></h3>
    <p class="text-muted mb-2">@<?= esc($user['username']) ?></p>
    
    <?php if(!empty($user['bio'])): ?>
    <p class="mb-3 fst-italic mx-auto" style="max-width: 80%;">"<?= esc($user['bio']) ?>"</p>
    <?php endif; ?>
    
    <!-- Stats -->
    <div class="d-flex justify-content-center gap-4 mb-4">
        <div>
            <h5 class="mb-0 fw-bold"><?= esc($followers_count) ?></h5>
            <small class="text-muted">Followers</small>
        </div>
        <div>
            <h5 class="mb-0 fw-bold"><?= esc($following_count) ?></h5>
            <small class="text-muted">Following</small>
        </div>
        <div>
            <h5 class="mb-0 fw-bold"><?= count($posts) ?></h5>
            <small class="text-muted">Posts</small>
        </div>
    </div>

    <!-- Edit Profile / Follow Button -->
    <?php if($is_own_profile): ?>
        <button class="glass-btn w-100 mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#editProfileForm">
            Edit Profile
        </button>
        
        <div class="collapse text-start mt-3" id="editProfileForm">
            <form action="<?= site_url('profile/update') ?>" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label small text-muted">First Name</label>
                        <input type="text" name="first_name" class="glass-input border-bottom border-secondary pb-1" value="<?= esc(old('first_name') ?? $user['first_name']) ?>">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label small text-muted">Last Name</label>
                        <input type="text" name="last_name" class="glass-input border-bottom border-secondary pb-1" value="<?= esc(old('last_name') ?? $user['last_name']) ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Email</label>
                    <input type="email" name="email" class="glass-input border-bottom border-secondary pb-1" value="<?= esc(old('email') ?? $user['email']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Bio</label>
                    <textarea name="bio" class="glass-input border-bottom border-secondary pb-1" rows="2"><?= esc(old('bio') ?? $user['bio']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted d-block">Profile Picture</label>
                    <input type="file" name="profile_pic" class="glass-input" accept="image/*" style="font-size: 0.9rem;">
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="glass-btn">Save Changes</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <form action="<?= site_url('users/toggleFollow/'.$user['id']) ?>" method="POST">
            <button type="submit" class="glass-btn w-100 mb-2" style="<?= $is_following ? 'background: transparent; border: 1px solid var(--glass-border); color: var(--text-primary);' : '' ?>">
                <?= $is_following ? 'Following' : 'Follow' ?>
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
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
