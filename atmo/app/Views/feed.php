<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Post Creation Area -->
<div class="glass-panel composer">
    <form action="<?= site_url('posts/create') ?>" method="POST" enctype="multipart/form-data">
        <div class="composer-top">
            <div class="rounded-circle bg-secondary d-flex justify-content-center align-items-center" style="width: 40px; height: 40px; overflow: hidden; flex-shrink: 0;">
                <i class="bi bi-person-fill text-white fs-4"></i>
            </div>
            <input type="text" name="content" class="glass-input" placeholder="What's on your mind?" autocomplete="off">
        </div>
        <div class="composer-toolbar">
            <div class="d-flex gap-3 text-muted">
                <label style="cursor: pointer;" title="Attach Image/Video">
                    <i class="bi bi-image fs-5"></i>
                    <input type="file" name="media" accept="image/*,video/*" style="display: none;">
                </label>
                <label style="cursor: pointer;" title="Visibility">
                    <select name="visibility" style="background: transparent; color: inherit; border: none; outline: none; cursor: pointer;">
                        <option value="public" style="color: #000;">Public</option>
                        <option value="followers" style="color: #000;">Followers</option>
                        <option value="private" style="color: #000;">Private</option>
                    </select>
                </label>
            </div>
            <button type="submit" class="glass-btn">Post</button>
        </div>
    </form>
</div>

<!-- Feed Header -->
<div class="mb-3 d-flex gap-3 fw-bold" style="padding: 0 10px;">
    <span class="text-white pb-2" style="border-bottom: 2px solid var(--accent-color); cursor:pointer;">For You</span>
</div>

<!-- Feed List -->
<?php if(empty($posts)): ?>
    <div class="glass-panel mt-3 text-center text-muted py-5">
        No posts to show. Start following some users or make your first post!
    </div>
<?php else: ?>
    <?php foreach($posts as $post): ?>
        <div class="glass-panel mb-4" style="border-radius: var(--border-radius); padding: 16px 20px;">
            <!-- Repost Header -->
            <?php if($post['type'] == 'repost'): ?>
                <div class="text-muted small mb-2 d-flex align-items-center gap-2 fw-semibold">
                    <i class="bi bi-arrow-repeat"></i> <?= esc($post['reposted_by']['username'] ?? 'Unknown User') ?> reposted
                </div>
                <?php $postBody = $post['original_post']; ?>
            <?php else: ?>
                <?php $postBody = $post; ?>
            <?php endif; ?>

            <!-- User Info -->
            <div class="d-flex align-items-center mb-3">
                <?php $pic = $postBody['user']['profile_pic'] ?? 'default_user.png'; ?>
                <img src="<?= base_url(esc($pic)) ?>" class="rounded-circle me-3" width="48" height="48" alt="profile" style="object-fit:cover;" onerror="this.src='https://via.placeholder.com/48';">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-baseline gap-2">
                        <h6 class="mb-0 fw-bold fs-6" style="color: var(--text-primary);"><?= esc($postBody['user']['first_name'].' '.$postBody['user']['last_name']) ?> <i class="bi bi-patch-check-fill text-muted small"></i></h6>
                        <span class="text-muted small">@<?= esc($postBody['user']['username']) ?> • <?php
                            $diff = time() - strtotime($postBody['created_at']);
                            if($diff < 3600) echo floor($diff/60).'m';
                            else if($diff < 86400) echo floor($diff/3600).'h';
                            else echo floor($diff/86400).'d';
                        ?></span>
                    </div>
                </div>
                <!-- Post Options -->
                <i class="bi bi-three-dots text-muted" style="cursor:pointer;"></i>
            </div>

            <!-- Post Content -->
            <p class="mb-3" style="font-size: 1.05rem; line-height: 1.5; white-space: pre-wrap; color: var(--text-primary);"><?= esc($postBody['content']) ?></p>

            <!-- Media Attachment -->
            <?php if(!empty($postBody['media_path'])): ?>
                <?php if($postBody['media_type'] == 'image'): ?>
                    <img src="<?= base_url(esc($postBody['media_path'])) ?>" class="media-attachment">
                <?php elseif($postBody['media_type'] == 'video'): ?>
                    <video controls class="media-attachment">
                        <source src="<?= base_url(esc($postBody['media_path'])) ?>">
                    </video>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Interaction Bar -->
            <div class="interaction-bar mt-3 pt-2 border-top" style="border-color: var(--glass-border) !important;">
                <button class="action-btn">
                    <i class="bi bi-heart"></i> <span>Like</span>
                </button>
                <button class="action-btn">
                    <i class="bi bi-chat"></i> <span>Comment</span>
                </button>
                <form action="<?= site_url('posts/toggleRepost/'.$postBody['id']) ?>" method="POST" class="d-inline">
                    <button type="submit" class="action-btn">
                        <i class="bi bi-arrow-repeat"></i> <span>Repost</span>
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
