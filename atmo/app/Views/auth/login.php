<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-5">
        <div class="glass-panel" style="padding: 40px; border-radius: 20px;">
            <div class="text-center mb-4">
                <img src="/atmo_logo.png" alt="Atmo Logo" class="w-25">
                <h2 class="fw-bold mt-2" style="color: var(--text-primary);">Sign in to Atmo</h2>
            </div>

            <form action="<?= site_url('login') ?>" method="POST">
                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">Email or Username</label>
                    <input type="text" name="identifier" class="glass-input border-bottom border-secondary pb-2"
                        required autofocus>
                </div>

                <div class="mb-5">
                    <label class="form-label text-muted small fw-bold">Password</label>
                    <input type="password" name="password" class="glass-input border-bottom border-secondary pb-2"
                        required>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="glass-btn btn-lg w-100" style="padding: 14px;">Sign In</button>
                </div>
            </form>

            <div class="text-center mt-5">
                <span class="text-muted">Don't have an account?</span>
                <a href="<?= site_url('register') ?>" class="text-decoration-none fw-bold"
                    style="color: var(--text-primary);">Sign up</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>