<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center align-items-center mt-4 mb-5">
    <div class="col-md-7">
        <div class="glass-panel" style="padding: 40px; border-radius: 20px;">
            <div class="text-center mb-4">
                <i class="bi bi-person-plus bg-transparent text-primary" style="font-size: 3rem;"></i>
                <h2 class="fw-bold mt-2" style="color: var(--text-primary);">Create an Account</h2>
            </div>

            <form action="<?= site_url('register') ?>" method="POST" id="registerForm">

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted small fw-bold">Username <span style="color: #dc3545;">*</span></label>
                        <input type="text" name="username" value="<?= old('username') ?>"
                            class="glass-input border-bottom border-secondary pb-2" required>
                        <small class="text-muted d-block mt-1">Unique identifier for your account</small>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted small fw-bold">Email address <span style="color: #dc3545;">*</span></label>
                        <input type="email" name="email" value="<?= old('email') ?>"
                            class="glass-input border-bottom border-secondary pb-2" required>
                        <small class="text-muted d-block mt-1">We'll use this to recover your account</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted small fw-bold">First Name <span style="color: #dc3545;">*</span></label>
                        <input type="text" name="first_name" value="<?= old('first_name') ?>"
                            class="glass-input border-bottom border-secondary pb-2" required>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted small fw-bold">Last Name <span style="color: #dc3545;">*</span></label>
                        <input type="text" name="last_name" value="<?= old('last_name') ?>"
                            class="glass-input border-bottom border-secondary pb-2" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted small fw-bold">Date of Birth <span style="color: #dc3545;">*</span></label>
                        <input type="date" name="dob" value="<?= old('dob') ?>"
                            class="glass-input border-bottom border-secondary pb-2" required>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted small fw-bold">Sex <span style="color: #dc3545;">*</span></label>
                        <select name="sex" class="glass-input border-bottom border-secondary pb-2" required
                            style="background: transparent; -webkit-appearance: none; appearance: none;">
                            <option value="" disabled <?= empty(old('sex')) ? 'selected' : '' ?>>Select</option>
                            <option value="Male" <?= old('sex') == 'Male' ? 'selected' : '' ?> style="color: black;">Male
                            </option>
                            <option value="Female" <?= old('sex') == 'Female' ? 'selected' : '' ?> style="color: black;">
                                Female</option>
                            <option value="Other" <?= old('sex') == 'Other' ? 'selected' : '' ?> style="color: black;">
                                Other</option>
                            <option value="Prefer not to say" <?= old('sex') == 'Prefer not to say' ? 'selected' : '' ?>
                                style="color: black;">Prefer not to say</option>
                        </select>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="form-label text-muted small fw-bold">Password <span style="color: #dc3545;">*</span></label>
                    <input type="password" name="password" class="glass-input border-bottom border-secondary pb-2"
                        required minlength="8">
                    <div class="form-text text-muted mt-2">Minimum 8 characters.</div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="glass-btn btn-lg w-100" style="padding: 14px;">Register</button>
                </div>
            </form>

            <div class="text-center mt-5">
                <span class="text-muted">Already have an account?</span>
                <a href="<?= site_url('login') ?>" class="text-decoration-none fw-bold"
                    style="color: var(--text-primary);">Sign in here</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Frontend form validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const username = document.querySelector('input[name="username"]').value.trim();
        const email = document.querySelector('input[name="email"]').value.trim();
        const firstName = document.querySelector('input[name="first_name"]').value.trim();
        const lastName = document.querySelector('input[name="last_name"]').value.trim();
        const dob = document.querySelector('input[name="dob"]').value.trim();
        const sex = document.querySelector('select[name="sex"]').value.trim();
        const password = document.querySelector('input[name="password"]').value;
        
        // Validate all required fields are filled
        if (!username || !email || !firstName || !lastName || !dob || !sex || !password) {
            e.preventDefault();
            alert('Please fill in all required fields marked with *');
            return false;
        }
        
        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Please enter a valid email address');
            return false;
        }
        
        // Validate password length
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long');
            return false;
        }
        
        return true;
    });
</script>
<?= $this->endSection() ?>