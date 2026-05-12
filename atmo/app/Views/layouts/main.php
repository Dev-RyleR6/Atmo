<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Atmo - Create Your Own Atmosphere') ?></title>
    <link rel="icon" href="<?= base_url('atmo_logo.png') ?>" type="image/x-icon">
    <!-- Bootstrap CSS (Reset/Grid base only) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Our Glassmorphism Override -->
    <link href="<?= base_url('css/glass.css') ?>" rel="stylesheet">
</head>

<body>

    <?php if (session()->has('user_id')): ?>
        <div class="atmo-layout">
            <!-- Left Sidebar: Navbar -->
            <aside class="sidebar-left">
                <h3 class="fw-bold mb-1" style="padding-left: 20px;">Atmo</h3>
                <p class="text-muted small" style="padding-left: 20px;">Own Your Atmosphere</p>

                <ul class="nav-menu">
                    <li class="nav-item">
                        <a class="nav-link <?= current_url() == site_url('feed') ? 'active' : '' ?>"
                            href="<?= site_url('feed') ?>">
                            <i
                                class="bi <?= current_url() == site_url('feed') ? 'bi-house-door-fill' : 'bi-house-door' ?>"></i>
                            <span class="nav-label">Home</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= current_url() == site_url('profile') ? 'active' : '' ?>"
                            href="<?= site_url('profile') ?>">
                            <i class="bi <?= current_url() == site_url('profile') ? 'bi-person-fill' : 'bi-person' ?>"></i>
                            <span class="nav-label">Profile</span>
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="<?= site_url('logout') ?>">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="nav-label">Logout</span>
                        </a>
                    </li>
                </ul>

                <!-- Authenticated User Tag -->
                <div style="margin-top: auto; padding: 20px; display: flex; align-items: center; gap: 12px; border-top: 1px solid var(--glass-border);">
                    <div class="rounded-circle bg-secondary d-flex justify-content-center align-items-center" style="width: 40px; height: 40px; overflow: hidden; border: 1px solid var(--glass-border);">
                        <i class="bi bi-person-circle fs-3 text-white"></i>
                    </div>
                    <div class="nav-label">
                        <div class="fw-bold" style="font-size: 0.9rem;"><?= esc(session()->get('username')) ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;">@<?= esc(session()->get('username')) ?></div>
                    </div>
                </div>
            </aside>

            <!-- Center Feed -->
            <main class="center-feed">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert"
                        style="border-radius: 12px; background: rgba(25, 135, 84, 0.2); border-color: rgba(25, 135, 84, 0.4); color: white;">
                        <?= esc(session()->getFlashdata('success')) ?>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert"
                        style="border-radius: 12px; background: rgba(220, 53, 69, 0.2); border-color: rgba(220, 53, 69, 0.4); color: white;">
                        <?= esc(session()->getFlashdata('error')) ?>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert"
                        style="border-radius: 12px; background: rgba(220, 53, 69, 0.2); border-color: rgba(220, 53, 69, 0.4); color: white;">
                        <ul class="mb-0">
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>
            </main>

            <!-- Right Sidebar (Backend Data Focus) -->
            <aside class="sidebar-right">
                <!-- Search Widget -->
                <div class="glass-panel"
                    style="padding: 10px 16px; border-radius: 999px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-search text-muted"></i>
                    <form action="<?= site_url('users/search') ?>" method="GET" style="width: 100%;">
                        <input type="search" name="q" class="glass-input" placeholder="Search Atmo"
                            value="<?= esc(request()->getGet('q') ?? '') ?>">
                    </form>
                </div>

                <div class="mt-4 px-3 text-muted" style="font-size: 0.85rem;">
                    Powered by Atmo Backend Engine &copy; <?= date('Y') ?>
                </div>
            </aside>
        </div>

    <?php else: ?>
        <!-- Fallback Layout for Login / Register -->
        <div class="container my-5">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert"
                    style="border-radius: 12px; background: rgba(25, 135, 84, 0.2); border-color: rgba(25, 135, 84, 0.4); color: white;">
                    <?= esc(session()->getFlashdata('success')) ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert"
                    style="border-radius: 12px; background: rgba(220, 53, 69, 0.2); border-color: rgba(220, 53, 69, 0.4); color: white;">
                    <?= esc(session()->getFlashdata('error')) ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('errors') && is_array(session()->getFlashdata('errors'))): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert"
                    style="border-radius: 12px; background: rgba(220, 53, 69, 0.2); border-color: rgba(220, 53, 69, 0.4); color: white;">
                    <ul class="mb-0">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/feed.js') ?>"></script>
</body>

</html>