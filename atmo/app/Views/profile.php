<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <!-- Left Column: Profile Info & Edit -->
    <div class="col-md-4 mb-4">
        <div class="card bg-dark text-white border-secondary shadow-sm text-center">
            <div class="card-body">
                <img src="<?= base_url(esc($user['profile_pic'] ?? 'default_user.png')) ?>" class="rounded-circle mb-3 border border-secondary" width="120" height="120" style="object-fit:cover;" onerror="this.src='https://via.placeholder.com/120';">
                <h3 class="card-title fw-bold">@<?= esc($user['username']) ?></h3>
                <p class="text-muted">
                    <?= esc($user['first_name'].' '.$user['last_name']) ?><br>
                    <?= esc($user['email']) ?>
                </p>
                <p class="mb-3 fst-italic">"<?= esc($user['bio']) ?>"</p>
                
                <div class="d-flex justify-content-center gap-3 mb-3">
                    <div>
                        <h5 class="mb-0"><?= esc($followers_count) ?></h5>
                        <small class="text-muted">Followers</small>
                    </div>
                    <div>
                        <h5 class="mb-0"><?= esc($following_count) ?></h5>
                        <small class="text-muted">Following</small>
                    </div>
                    <div>
                        <h5 class="mb-0"><?= count($posts) ?></h5>
                        <small class="text-muted">Posts</small>
                    </div>
                </div>

                <hr class="border-secondary">
                
                <h5 class="text-start mb-3">Edit Profile</h5>
                <form action="<?= site_url('profile/update') ?>" method="POST" enctype="multipart/form-data" class="text-start">
                    
                    <div class="mb-2">
                        <label class="form-label small">First Name</label>
                        <input type="text" name="first_name" class="form-control form-control-sm bg-dark text-white border-secondary" value="<?= esc(old('first_name') ?? $user['first_name']) ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Last Name</label>
                        <input type="text" name="last_name" class="form-control form-control-sm bg-dark text-white border-secondary" value="<?= esc(old('last_name') ?? $user['last_name']) ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Email</label>
                        <input type="email" name="email" class="form-control form-control-sm bg-dark text-white border-secondary" value="<?= esc(old('email') ?? $user['email']) ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Bio</label>
                        <textarea name="bio" class="form-control form-control-sm bg-dark text-white border-secondary" rows="2"><?= esc(old('bio') ?? $user['bio']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Profile Picture</label>
                        <input type="file" name="profile_pic" class="form-control form-control-sm bg-dark text-white border-secondary" accept="image/*">
                    </div>
                    
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">Update Profile</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: User Posts -->
    <div class="col-md-8">
        <h4 class="mb-3">Your Posts</h4>
        <?php if(empty($posts)): ?>
            <div class="alert alert-secondary bg-dark text-white border-secondary">
                You haven't posted anything yet.
            </div>
        <?php else: ?>
            <?php foreach($posts as $postBody): ?>
                <div class="card mb-3 bg-dark text-white border-secondary shadow-sm">
                    <div class="card-body">

                        <div class="d-flex align-items-center mb-3">
                            <img src="<?= base_url(esc($user['profile_pic'] ?? 'default_user.png')) ?>" class="rounded-circle me-2" width="40" height="40" onerror="this.src='https://via.placeholder.com/40';">
                            <div>
                                <h6 class="mb-0 fw-bold"><?= esc($user['first_name'].' '.$user['last_name']) ?></h6>
                                <small class="text-muted">@<?= esc($user['username']) ?> • <?= date('M d, Y h:i A', strtotime($postBody['created_at'])) ?></small>
                            </div>
                        </div>

                        <p class="card-text mb-3" style="white-space: pre-wrap;"><?= esc($postBody['content']) ?></p>

                        <?php if(!empty($postBody['media_path'])): ?>
                            <?php if($postBody['media_type'] == 'image'): ?>
                                <img src="<?= base_url(esc($postBody['media_path'])) ?>" class="img-fluid rounded border border-secondary w-100 mb-3" style="max-height: 400px; object-fit: contain;">
                            <?php elseif($postBody['media_type'] == 'video'): ?>
                                <video controls class="w-100 rounded border border-secondary mb-3" style="max-height: 400px;">
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
