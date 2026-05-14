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
                <div class="sidebar-header">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="<?= base_url('atmo_logo.png') ?>" alt="Atmo Logo" class="sidebar-logo">
                        <div class="sidebar-title-container">
                            <h4 class="sidebar-title mb-0">Atmo</h4>
                            <p class="sidebar-tagline mb-0">Create and own your atmosphere</p>
                        </div>
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
                        <a class="nav-link <?= (current_url() == site_url('profile') || str_contains(current_url(), site_url('profile/'))) ? 'active' : '' ?>"
                            href="<?= site_url('profile') ?>">
                            <i class="bi <?= (current_url() == site_url('profile') || str_contains(current_url(), site_url('profile/'))) ? 'bi-person-fill' : 'bi-person' ?>"></i>
                            <span class="nav-label">Profile</span>
                        </a>
                    </li>
                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="nav-label">Logout</span>
                        </a>
                    </li>
                </ul>

                <!-- Authenticated User Tag -->
                <div class="user-profile-tag">
                    <img src="<?= base_url(esc(session()->get('profile_pic') ?? '')) ?>" class="rounded-circle profile-pic-img shadow-sm" width="40" height="40" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');">
                    <div class="rounded-circle bg-secondary d-none d-flex justify-content-center align-items-center profile-pic-placeholder shadow-sm" style="width: 40px; height: 40px; overflow: hidden; border: 1px solid var(--glass-border);">
                        <i class="bi bi-person-circle fs-3 text-white"></i>
                    </div>
                    <div class="nav-label overflow-hidden flex-grow-1">
                        <div class="fw-bold text-truncate" style="font-size: 1rem;"><?= esc(session()->get('username')) ?></div>
                        <div class="text-muted text-truncate" style="font-size: 0.85rem;">@<?= esc(session()->get('username')) ?></div>
                    </div>
                    <a href="#" class="logout-btn d-none d-lg-block" title="Logout" data-bs-toggle="modal" data-bs-target="#logoutModal">
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

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-panel" style="border-radius: 16px; border: none; background: var(--glass-bg); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
                <div class="modal-header" style="border-bottom: 1px solid var(--glass-border);">
                    <h5 class="modal-title" id="logoutModalLabel">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Logout
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to logout?</p>
                </div>
                <div class="modal-footer" style="border-top: 1px solid var(--glass-border);">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 12px;">Cancel</button>
                    <a href="<?= site_url('logout') ?>" class="btn glass-btn" style="border-radius: 12px;">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/feed.js') ?>"></script>
    
    <!-- Auto-hide alerts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach((alert, index) => {
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }, 3000 + (index * 500));
            });
        });
    </script>
</body>

</html>