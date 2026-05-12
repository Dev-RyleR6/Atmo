<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Profile Header -->
<div class="glass-panel text-center position-relative mb-4">
    <div style="height: 120px; background: rgba(255,255,255,0.05); border-radius: 8px 8px 0 0; margin: -20px -20px 0 -20px;"></div>
    
    <img src="<?= base_url(esc($user['profile_pic'] ?? 'default_user.png')) ?>" class="rounded-circle border border-secondary" width="120" height="120" style="object-fit:cover; margin-top: -60px; background: var(--bg-color);" onerror="this.src='https://via.placeholder.com/120';">
    
    <h3 class="fw-bold mt-2 mb-0"><?= esc($user['first_name'].' '.$user['last_name']) ?></h3>
    <p class="text-muted mb-2">@<?= esc($user['username']) ?></p>
    
    <p class="mb-3 fst-italic mx-auto" style="max-width: 80%;">"<?= esc($user['bio']) ?>"</p>
    
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

    <!-- Edit Profile Collapse -->
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
</div>

<!-- Feed Header -->
<div class="mb-3 d-flex gap-3 fw-bold" style="padding: 0 10px;">
    <span class="text-white pb-2" style="border-bottom: 2px solid var(--accent-color); cursor:pointer;">My Posts</span>
</div>

<!-- User Posts -->
<?php if(empty($posts)): ?>
    <div class="glass-panel text-center text-muted py-5">
        You haven't posted anything yet.
    </div>
<?php else: ?>
    <?php foreach($posts as $postBody): ?>
        <div class="glass-panel post-card" style="border-radius: var(--border-radius); padding: 16px 20px;">
            <div class="d-flex align-items-center mb-3">
                <img src="<?= base_url(esc($user['profile_pic'] ?? 'default_user.png')) ?>" class="rounded-circle me-3" width="48" height="48" onerror="this.src='https://via.placeholder.com/48';">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-baseline gap-2">
                        <h6 class="mb-0 fw-bold fs-6" style="color: var(--text-primary);"><?= esc($user['first_name'].' '.$user['last_name']) ?> <i class="bi bi-patch-check-fill text-muted small"></i></h6>
                        <span class="text-muted small">@<?= esc($user['username']) ?> • <?= date('M d', strtotime($postBody['created_at'])) ?></span>
                    </div>
                </div>
                <!-- Post Options -->
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
            </div>

            <!-- Edit Modal -->
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
