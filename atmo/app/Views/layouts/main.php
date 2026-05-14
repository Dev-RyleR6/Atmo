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
                <div class="sidebar-header" style="padding: 8px 12px; display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <img src="<?= base_url('atmo_logo.png') ?>" alt="Atmo Logo" style="width: 44px; height: 44px; object-fit: contain;">
                    <div class="d-none d-lg-block">
                        <h4 class="fw-bold mb-0" style="letter-spacing: -0.8px; font-size: 1.5rem;">Atmo</h4>
                    </div>
                </div>

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
                        <a class="nav-link" href="#">
                            <i class="bi bi-compass"></i>
                            <span class="nav-label">Explore</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-bell"></i>
                            <span class="nav-label">Notifications</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= current_url() == site_url('profile') ? 'active' : '' ?>"
                            href="<?= site_url('profile') ?>">
                            <i class="bi <?= current_url() == site_url('profile') ? 'bi-person-fill' : 'bi-person' ?>"></i>
                            <span class="nav-label">Profile</span>
                        </a>
                    </li>
                </ul>

                <!-- Authenticated User Tag -->
                <div class="user-profile-tag mt-auto mb-2" style="padding: 12px; display: flex; align-items: center; gap: 12px; border-radius: 99px; transition: all 0.2s; cursor: pointer;">
                    <img src="<?= base_url(esc(session()->get('profile_pic') ?? '')) ?>" class="rounded-circle profile-pic-img shadow-sm" width="40" height="40" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center profile-pic-placeholder shadow-sm" style="width: 40px; height: 40px; overflow: hidden; border: 1px solid var(--glass-border);">
                        <i class="bi bi-person-circle fs-3 text-white"></i>
                    </div>
                    <div class="nav-label overflow-hidden flex-grow-1">
                        <div class="fw-bold text-truncate" style="font-size: 0.95rem;"><?= esc(session()->get('username')) ?></div>
                        <div class="text-muted text-truncate" style="font-size: 0.8rem;">@<?= esc(session()->get('username')) ?></div>
                    </div>
                    <a href="<?= site_url('logout') ?>" class="text-danger fs-5 ms-auto d-none d-lg-block" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
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

            <!-- Right Sidebar -->
            <aside class="sidebar-right">
                <!-- Search Widget -->
                <div class="search-widget mb-4" style="position: relative;">
                    <div class="glass-panel search-input-container"
                        style="padding: 10px 16px; border-radius: 999px; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-search text-muted"></i>
                        <input type="search" id="searchInput" class="glass-input" placeholder="Search Atmo"
                            value="<?= esc(request()->getGet('q') ?? '') ?>" autocomplete="off">
                    </div>
                    <!-- Search Results Dropdown -->
                    <div id="searchDropdown" class="search-dropdown glass-panel" style="display: none;"></div>
                </div>

                <!-- Trending Section -->
                <div class="glass-panel mb-4" style="padding: 16px; border-radius: 16px;">
                    <h5 class="fw-bold mb-3" style="font-size: 1.1rem;">What's Happening</h5>
                    <div class="d-flex flex-column gap-3">
                        <div class="trending-item">
                            <small class="text-muted">Technology · Trending</small>
                            <div class="fw-bold">#AtmoBeta</div>
                            <small class="text-muted">1.2K Posts</small>
                        </div>
                        <div class="trending-item">
                            <small class="text-muted">Atmosphere · Trending</small>
                            <div class="fw-bold">Glassmorphism</div>
                            <small class="text-muted">856 Posts</small>
                        </div>
                        <div class="trending-item">
                            <small class="text-muted">Global · Trending</small>
                            <div class="fw-bold">#PHP8.3</div>
                            <small class="text-muted">2.4K Posts</small>
                        </div>
                    </div>
                    <a href="#" class="d-block mt-3 text-decoration-none small" style="color: var(--accent-color);">Show more</a>
                </div>

                <!-- Suggested Users -->
                <div class="glass-panel mb-4" style="padding: 16px; border-radius: 16px;" id="suggestedUsersSection">
                    <h5 class="fw-bold mb-3" style="font-size: 1.1rem;">Who to follow</h5>
                    <div id="suggestedUsersList" class="d-flex flex-column gap-3">
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-muted" role="status"></div>
                        </div>
                    </div>
                    <a href="#" class="d-block mt-3 text-decoration-none small" style="color: var(--accent-color);">Show more</a>
                </div>

                <div class="px-3 text-muted" style="font-size: 0.75rem;">
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <a href="#" class="text-muted text-decoration-none">Terms of Service</a>
                        <a href="#" class="text-muted text-decoration-none">Privacy Policy</a>
                        <a href="#" class="text-muted text-decoration-none">Cookie Policy</a>
                        <a href="#" class="text-muted text-decoration-none">Accessibility</a>
                    </div>
                    Powered by Atmo Engine &copy; <?= date('Y') ?>
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