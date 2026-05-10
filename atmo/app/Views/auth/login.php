<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 bg-dark text-white">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-globe fs-1 text-primary"></i>
                    <h2 class="fw-bold mt-2">Sign in to Atmo</h2>
                </div>
                
                <form action="<?= site_url('login') ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email or Username</label>
                        <input type="text" name="identifier" class="form-control bg-dark text-white border-secondary" required autofocus>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold">Sign In</button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <span class="text-muted">Don't have an account?</span> 
                    <a href="<?= site_url('register') ?>" class="text-decoration-none fw-bold">Sign up</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
