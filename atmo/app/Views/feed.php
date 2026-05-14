<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Post Creation Area -->
<div class="glass-panel composer mb-3">
    <form action="<?= site_url('posts/create') ?>" method="POST" enctype="multipart/form-data">
    <div class="composer-top d-flex align-items-start gap-2 mb-2">
            <img src="<?= base_url(esc(session()->get('profile_pic') ?? '')) ?>" class="rounded-circle shadow-sm profile-pic-img" style="width: 40px; height: 40px; overflow: hidden; flex-shrink: 0; border: 1.5px solid var(--glass-border);" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
            <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center shadow-sm profile-pic-placeholder" style="width: 40px; height: 40px; overflow: hidden; flex-shrink: 0; border: 1.5px solid var(--glass-border);">
                <i class="bi bi-person-fill text-white fs-4"></i>
            </div>
            <div class="flex-grow-1">
                <textarea name="content" class="glass-input" placeholder="What's on your mind, <?= esc(session()->get('username')) ?>?" rows="1" style="resize: none; overflow: hidden; min-height: 40px; padding-top: 8px; width: 100%; font-size: 1rem;"></textarea>
            </div>
        </div>

        <!-- Media Preview Area (JS Managed) -->
        <div class="media-preview-container mb-2" style="display: none;">
            <button type="button" class="remove-media-btn"><i class="bi bi-x-lg"></i></button>
            <div class="media-file-info mb-2 d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark" id="mediaFileIcon"></i>
                <span id="mediaFileName" class="text-muted small"></span>
            </div>
            <img src="" class="media-preview" id="mediaPreviewImage" style="display: none;">
            <video controls class="media-preview" id="mediaPreviewVideo" style="display: none;"></video>
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
                    <select name="visibility" style="background: transparent; color: inherit; border: none; outline: none; cursor: pointer; font-size: 0.85rem;">
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
<div class="feed-tabs d-flex gap-1 mb-3">
    <div class="feed-tab <?= (isset($current_feed_type) && $current_feed_type == 'for_you') ? 'active' : '' ?> px-3 py-2" data-feed-type="for_you">For You</div>
    <div class="feed-tab <?= (isset($current_feed_type) && $current_feed_type == 'your_atmosphere') ? 'active' : '' ?> px-3 py-2" data-feed-type="your_atmosphere">Your Atmosphere</div>
</div>

<!-- Feed List -->
<div class="mt-1">
    <?php if(empty($posts)): ?>
    <div class="glass-panel text-center text-muted py-4 mt-2">
            <i class="bi bi-wind fs-2 mb-2 d-block opacity-25"></i>
            <p>No posts to show yet. <br>Start following users to fill your atmosphere!</p>
        </div>
    <?php else: ?>
        <?php 
            // Collect all post bodies for modals
            $allPostBodies = [];
        ?>
        <?php foreach($posts as $post): ?>
            <!-- Repost Context -->
            <?php $isRepost = ($post['type'] == 'repost'); ?>
            <?php if($isRepost): ?>
                <?php 
                    // Skip if original post or user data is missing
                    if (empty($post['original_post']) || empty($post['original_post']['user'])) {
                        continue;
                    }
                ?>
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

            <?php $allPostBodies[] = $postBody; ?>

            <div class="glass-panel post-card <?= $isRepost ? 'post-card-repost' : '' ?>" style="padding: 12px 16px;">
                <?php if($isRepost): ?>
                    <div class="d-flex align-items-center gap-2 mb-2 repost-header" style="padding-left: 4px;">
                        <i class="bi bi-arrow-repeat text-muted"></i>
                        <span class="text-muted small fw-medium">
                            <?php 
                                $isOwnRepost = session()->get('user_id') == ($post['reposted_by']['id'] ?? null);
                                echo $isOwnRepost ? 'You' : esc($post['reposted_by']['first_name'] ?? $post['reposted_by']['username'] ?? 'Someone');
                            ?> reposted
                        </span>
                    </div>
                <?php endif; ?>
                <!-- Post Header -->
                <div class="d-flex align-items-start mb-2">
                    <img src="<?= base_url(esc($postBody['user']['profile_pic'] ?? '')) ?>" class="rounded-circle me-2 profile-pic-img" width="40" height="40" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center me-2 profile-pic-placeholder" style="width: 40px; height: 40px; overflow: hidden; flex-shrink: 0; border: 1.5px solid var(--glass-border);">
                        <i class="bi bi-person-fill text-white fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-baseline gap-1 flex-wrap">
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
                        <ul class="dropdown-menu dropdown-menu-end glass-panel" style="background: var(--bg-color); border: 1px solid var(--glass-border); padding: 6px; min-width: 140px;">
                            <?php if ($postBody['user_id'] == session()->get('user_id')): ?>
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
                            <?php else: ?>
                                <li>
                                    <a class="dropdown-item" href="#" style="color: var(--text-primary); border-radius: 6px; padding: 6px 12px; font-size: 0.9rem;">
                                        <i class="bi bi-flag me-2"></i> Report
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
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
                    <div class="comment-item" data-user-id="<?= $comment['user_id'] ?>" style="padding: 8px; gap: 8px; margin-bottom: 8px;">
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
                            <p class="mb-0" style="font-size: 0.9rem; line-height: 1.4; color: var(--text-primary); white-space: pre-wrap;"><?= esc($comment['comment_text']) ?></p>
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

        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modals (outside post loop to avoid stacking issues) -->
<?php foreach($allPostBodies as $postBody): ?>
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
    
    <!-- Comment Modal -->
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
                        <div class="comment-item" data-user-id="<?= $comment['user_id'] ?>">
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

<?= $this->endSection() ?>
