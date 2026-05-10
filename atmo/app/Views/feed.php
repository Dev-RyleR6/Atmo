<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        
        <!-- Post Creation Area -->
        <div class="card mb-4 bg-dark text-white border-secondary shadow-sm">
            <div class="card-body">
                <form action="<?= site_url('posts/create') ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <textarea name="content" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="What's on your mind?"></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <input class="form-control form-control-sm bg-dark text-white border-secondary d-inline-block w-auto" type="file" name="media" accept="image/*,video/*">
                            <select name="visibility" class="form-select form-select-sm bg-dark text-white border-secondary d-inline-block w-auto ms-2">
                                <option value="public">Public</option>
                                <option value="followers">Followers</option>
                                <option value="private">Private</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Post</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Feed List -->
        <h4 class="mb-3">Your Feed</h4>
        <?php if(empty($posts)): ?>
            <div class="alert alert-secondary bg-dark text-white border-secondary">
                No posts to show. Start following some users or make your first post!
            </div>
        <?php else: ?>
            <?php foreach($posts as $post): ?>
                <div class="card mb-3 bg-dark text-white border-secondary shadow-sm">
                    <div class="card-body">
                        <!-- Repost Header -->
                        <?php if($post['type'] == 'repost'): ?>
                            <div class="text-muted small mb-2">
                                <i class="bi bi-arrow-repeat"></i> <?= esc($post['reposted_by']['username'] ?? 'Unknown User') ?> reposted
                            </div>
                            <?php $postBody = $post['original_post']; ?>
                        <?php else: ?>
                            <?php $postBody = $post; ?>
                        <?php endif; ?>

                        <!-- User Info -->
                        <div class="d-flex align-items-center mb-3">
                            <?php $pic = $postBody['user']['profile_pic'] ?? 'default_user.png'; ?>
                            <img src="<?= base_url(esc($pic)) ?>" class="rounded-circle me-2" width="40" height="40" alt="profile" onerror="this.src='https://via.placeholder.com/40';">
                            <div>
                                <h6 class="mb-0 fw-bold"><?= esc($postBody['user']['first_name'].' '.$postBody['user']['last_name']) ?></h6>
                                <small class="text-muted">@<?= esc($postBody['user']['username']) ?> • <?= date('M d, Y h:i A', strtotime($postBody['created_at'])) ?></small>
                            </div>
                        </div>

                        <!-- Post Content -->
                        <p class="card-text mb-3" style="white-space: pre-wrap;"><?= esc($postBody['content']) ?></p>

                        <!-- Media Attachment -->
                        <?php if(!empty($postBody['media_path'])): ?>
                            <?php if($postBody['media_type'] == 'image'): ?>
                                <img src="<?= base_url(esc($postBody['media_path'])) ?>" class="img-fluid rounded border border-secondary mb-3 w-100" style="max-height: 500px; object-fit: contain;">
                            <?php elseif($postBody['media_type'] == 'video'): ?>
                                <video controls class="w-100 rounded border border-secondary mb-3" style="max-height: 500px;">
                                    <source src="<?= base_url(esc($postBody['media_path'])) ?>">
                                </video>
                            <?php endif; ?>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>
<?= $this->endSection() ?>
