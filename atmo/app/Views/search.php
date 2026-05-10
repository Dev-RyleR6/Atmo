<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        
        <div class="card bg-dark text-white border-secondary shadow-sm mb-4">
            <div class="card-body">
                <h4 class="card-title">Search Results</h4>
                <p class="text-muted">Showing results for: <strong><?= esc($query) ?></strong></p>
                
                <?php if(empty($users)): ?>
                    <div class="alert alert-secondary bg-dark text-white border-secondary mt-3">
                        No users found matching your search.
                    </div>
                <?php else: ?>
                    <div class="list-group mt-3">
                        <?php foreach($users as $u): ?>
                            <div class="list-group-item bg-dark text-white border-secondary d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="<?= base_url(esc($u['profile_pic'] ?? 'default_user.png')) ?>" class="rounded-circle me-3" width="50" height="50" onerror="this.src='https://via.placeholder.com/50';">
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?= esc($u['first_name'].' '.$u['last_name']) ?></h6>
                                        <small class="text-muted">@<?= esc($u['username']) ?></small>
                                    </div>
                                </div>
                                <form action="<?= site_url('users/toggleFollow/'.$u['id']) ?>" method="POST">
                                    <?php if($u['id'] != session()->get('user_id')): ?>
                                        <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3">Follow / Unfollow</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>
