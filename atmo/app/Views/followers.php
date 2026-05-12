<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Header -->
<div class="mb-3 d-flex gap-3 fw-bold" style="padding: 0 10px;">
    <a href="<?= site_url('profile/'.$user['username']) ?>" class="text-muted text-decoration-none">
        <i class="bi bi-arrow-left me-2"></i>
    </a>
    <div class="d-flex align-items-center gap-2">
        <i class="bi <?= $title === 'Followers' ? 'bi-people' : 'bi-person-check' ?>" style="font-size: 1.2rem; color: var(--accent-color);"></i>
        <span class="text-white pb-2" style="border-bottom: 2px solid var(--accent-color); cursor:pointer;"><?= esc($user['first_name'].' '.$user['last_name']) ?>'s <?= esc($title) ?></span>
    </div>
</div>

<!-- User List -->
<?php if(empty($users)): ?>
    <div class="glass-panel text-center text-muted py-5">
        <?php if($title === 'Followers'): ?>
            <i class="bi bi-people fs-1 mb-3 opacity-25"></i>
            <p>No followers yet.</p>
        <?php else: ?>
            <i class="bi bi-person-check fs-1 mb-3 opacity-25"></i>
            <p>Not following anyone yet.</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="d-flex flex-column gap-3">
        <?php foreach($users as $u): ?>
            <div class="glass-panel d-flex justify-content-between align-items-center" style="padding: 16px 20px;">
                <div class="d-flex align-items-center">
                    <img src="<?= base_url(esc($u['profile_pic'] ?? '')) ?>" class="rounded-circle me-3 profile-pic-img" width="56" height="56" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center me-3 profile-pic-placeholder" style="width: 56px; height: 56px; overflow: hidden; border: 2px solid var(--glass-border);">
                        <i class="bi bi-person-fill text-white fs-3"></i>
                    </div>
                    <div>
                        <a href="<?= site_url('profile/'.esc($u['username'])) ?>" class="text-decoration-none">
                            <h6 class="mb-1 fw-bold" style="color: var(--text-primary);"><?= esc($u['first_name'].' '.$u['last_name']) ?></h6>
                        </a>
                        <small class="text-muted">@<?= esc($u['username']) ?></small>
                        <?php if(!empty($u['bio'])): ?>
                        <p class="mb-0 mt-1 small text-muted" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?= esc($u['bio']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if($u['id'] != session()->get('user_id')): ?>
                <form action="<?= site_url('users/toggleFollow/'.$u['id']) ?>" method="POST">
                    <button type="submit" class="glass-btn btn-sm" style="<?= $u['is_following'] ? 'background: transparent; border: 1px solid var(--glass-border); color: var(--text-primary);' : '' ?>">
                        <?= $u['is_following'] ? 'Following' : 'Follow' ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
