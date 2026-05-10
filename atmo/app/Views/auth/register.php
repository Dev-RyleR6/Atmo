<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center mt-3 mb-5">
    <div class="col-md-7">
        <div class="card shadow-sm border-0 bg-dark text-white">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus fs-1 text-primary"></i>
                    <h2 class="fw-bold mt-2">Create an Account</h2>
                </div>
                
                <form action="<?= site_url('register') ?>" method="POST">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" value="<?= old('username') ?>" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email address</label>
                            <input type="email" name="email" value="<?= old('email') ?>" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" value="<?= old('first_name') ?>" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" value="<?= old('last_name') ?>" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="dob" value="<?= old('dob') ?>" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sex</label>
                            <select name="sex" class="form-select bg-dark text-white border-secondary" required>
                                <option value="" disabled <?= empty(old('sex')) ? 'selected' : '' ?>>Select</option>
                                <option value="Male" <?= old('sex') == 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= old('sex') == 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= old('sex') == 'Other' ? 'selected' : '' ?>>Other</option>
                                <option value="Prefer not to say" <?= old('sex') == 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required minlength="8">
                        <div class="form-text text-muted">Minimum 8 characters.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold">Register</button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <span class="text-muted">Already have an account?</span> 
                    <a href="<?= site_url('login') ?>" class="text-decoration-none fw-bold">Sign in here</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
