<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="mb-3 d-flex gap-3 fw-bold" style="padding: 0 10px;">
    <span class="text-white pb-2" style="border-bottom: 2px solid var(--accent-color); cursor:pointer;">Search Results</span>
</div>

<div class="glass-panel text-muted mb-4">
    Showing results for: <strong class="text-white">"<?= esc($query) ?>"</strong>
</div>

<?php if(empty($users)): ?>
    <div class="glass-panel text-center text-muted py-5">
        No users found matching your search.
    </div>
<?php else: ?>
    <div class="d-flex flex-column gap-3">
        <?php foreach(is_array($users) ? $users : [] as $u): ?>
            <div class="glass-panel d-flex justify-content-between align-items-center" style="padding: 16px 20px;">
                <div class="d-flex align-items-center">
                    <img src="<?= base_url(esc($u['profile_pic'] ?? 'vecteezy_user-solid-icon_22808249.svg')) ?>" class="rounded-circle me-3 border border-secondary" width="56" height="56" onerror="this.src='https://via.placeholder.com/56';">
                    <div>
                        <h6 class="mb-1 fw-bold" style="color: var(--text-primary);"><?= esc($u['first_name'].' '.$u['last_name']) ?></h6>
                        <small class="text-muted">@<?= esc($u['username']) ?></small>
                        <p class="mb-0 mt-1 small text-muted" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?= esc($u['bio']) ?>
                        </p>
                    </div>
                </div>
                
                <form class="follow-toggle-form" action="<?= site_url('users/toggleFollow/'.($u['id'] ?? '')) ?>" method="POST" data-user-id="<?= $u['id'] ?? '' ?>">
                    <?php if(($u['id'] ?? null) != session()->get('user_id')): ?>
                        <button type="submit" class="glass-btn btn-sm follow-toggle-btn" style="background: var(--text-primary); color: var(--bg-color);">Follow / Unfollow</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
