<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Post Creation Area -->
<div class="glass-panel composer mb-4">
    <form action="<?= site_url('posts/create') ?>" method="POST" enctype="multipart/form-data">
    <div class="composer-top d-flex align-items-start gap-3 mb-3">
            <div class="rounded-circle bg-secondary d-flex justify-content-center align-items-center shadow-sm" style="width: 48px; height: 48px; overflow: hidden; flex-shrink: 0; border: 2px solid var(--glass-border);">
                <i class="bi bi-person-fill text-white fs-3"></i>
            </div>
            <div class="flex-grow-1">
                <textarea name="content" class="glass-input" placeholder="What's on your mind, <?= esc(session()->get('username')) ?>?" rows="1" style="resize: none; overflow: hidden; min-height: 48px; padding-top: 12px; width: 100%;"></textarea>
            </div>
        </div>

        <!-- Media Preview Area (JS Managed) -->
        <div class="media-preview-container mb-2" style="display: none;">
            <button type="button" class="remove-media-btn"><i class="bi bi-x-lg"></i></button>
            <img src="" class="media-preview">
        </div>

        <div class="composer-toolbar d-flex justify-content-between align-items-center mt-2">
            <div class="d-flex gap-2 align-items-center">
                <label class="action-btn" style="cursor: pointer;" title="Attach Image/Video">
                    <i class="bi bi-image"></i>
                    <span class="d-none d-md-inline">Media</span>
                    <input type="file" name="media" accept="image/*,video/*" style="display: none;">
                </label>
                <label class="action-btn" title="Visibility">
                    <i class="bi bi-globe-americas"></i>
                    <select name="visibility" style="background: transparent; color: inherit; border: none; outline: none; cursor: pointer; font-size: 0.9rem;">
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

<!-- Feed Header Tabs -->
<div class="feed-tabs d-flex gap-2 mb-4">
    <div class="feed-tab active px-4 py-2">For You</div>
    <div class="feed-tab px-4 py-2">Following</div>
</div>

<!-- Feed List -->
<div class="mt-2">
    <?php if(empty($posts)): ?>
    <div class="glass-panel text-center text-muted py-5 mt-3">
            <i class="bi bi-wind fs-1 mb-3 d-block opacity-25"></i>
            <p>No posts to show yet. <br>Start following users to fill your atmosphere!</p>
        </div>
    <?php else: ?>
        <?php foreach($posts as $post): ?>
            <!-- Repost Context -->
            <?php if($post['type'] == 'repost'): ?>
                <?php 
                    // Skip if original post or user data is missing
                    if (empty($post['original_post']) || empty($post['original_post']['user'])) {
                        continue;
                    }
                ?>
                <div class="text-muted small mb-2 d-flex align-items-center gap-2 fw-semibold" style="padding: 0 10px; margin-left: 56px;">
                    <i class="bi bi-arrow-repeat fs-6"></i> <?= esc($post['reposted_by']['username'] ?? 'Someone') ?> reposted
                </div>
                <?php $postBody = $post['original_post']; ?>
            <?php else: ?>
                <?php 
                    // Skip if user data is missing
                    if (empty($post['user'])) {
                        continue;
                    }
                ?>
                <?php $postBody = $post; ?>
            <?php endif; ?>

            <div class="glass-panel post-card" style="border-radius: var(--border-radius); padding: 16px 20px;">
                <!-- Post Header -->
                <div class="d-flex align-items-center mb-3">
                    <img src="<?= base_url(esc($postBody['user']['profile_pic'] ?? 'default_user.png')) ?>" class="rounded-circle me-3" width="48" height="48" onerror="this.src='https://via.placeholder.com/48';">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-baseline gap-2">
                            <a href="<?= site_url('profile/'.esc($postBody['user']['username'] ?? 'unknown')) ?>" class="text-decoration-none">
                                <h6 class="mb-0 fw-bold fs-6" style="color: var(--text-primary);"><?= esc(($postBody['user']['first_name'] ?? 'Unknown').' '.($postBody['user']['last_name'] ?? 'User')) ?> 
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
                    <!-- Post Options Dropdown -->
                    <div class="dropdown">
                        <i class="bi bi-three-dots text-muted fs-5" style="cursor:pointer;" data-bs-toggle="dropdown"></i>
                        <ul class="dropdown-menu dropdown-menu-end glass-panel" style="background: var(--bg-color); border: 1px solid var(--glass-border); padding: 8px; min-width: 160px;">
                            <?php if ($postBody['user_id'] == session()->get('user_id')): ?>
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
                            <?php else: ?>
                                <li>
                                    <a class="dropdown-item" href="#" style="color: var(--text-primary); border-radius: 6px;">
                                        <i class="bi bi-flag me-2"></i> Report
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Edit Modal -->
                <?php if ($postBody['user_id'] == session()->get('user_id')): ?>
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
                    <button class="action-btn">
                        <i class="bi bi-share"></i>
                    </button>
                </div>
                
                <!-- Comment Modal -->
                <div class="modal fade" id="commentModal<?= $postBody['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content glass-panel" style="background: var(--bg-color);">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">Add Comment</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?= site_url('posts/addComment/'.$postBody['id']) ?>" method="POST">
                                <div class="modal-body">
                                    <textarea name="comment_text" class="glass-input" rows="3" placeholder="Write your comment..." style="width: 100%; resize: none;"></textarea>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="glass-btn">Comment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
