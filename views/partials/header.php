<?php $user = Auth::user(); ?>
<header class="site-header">
    <div class="nav-wrap">
        <a class="brand" href="<?= e(route_url('home/index')) ?>">
            <span class="brand-mark">TG</span>
            <span>Travel Guide</span>
        </a>

        <nav class="nav-links">
            <a class="<?= is_route('home/index') ? 'active' : '' ?>" href="<?= e(route_url('home/index')) ?>">Home</a>
            <?php if ($user && (int) $user['is_verified'] === 1): ?>
                <a class="<?= str_starts_with(current_route(), 'browse/') ? 'active' : '' ?>" href="<?= e(route_url('browse/index')) ?>">Browse</a>
            <?php endif; ?>
            <?php if ($user && $user['role'] === 'user' && (int) $user['is_verified'] === 1): ?>
                <a class="<?= is_route('wishlist/index') ? 'active' : '' ?>" href="<?= e(route_url('wishlist/index')) ?>">Wishlist</a>
            <?php endif; ?>
            <?php if ($user && $user['role'] === 'scout' && (int) $user['is_verified'] === 1): ?>
                <a class="<?= is_route('scout/index') ? 'active' : '' ?>" href="<?= e(route_url('scout/index')) ?>">Scout Panel</a>
            <?php endif; ?>
            <?php if ($user && $user['role'] === 'admin'): ?>
                <a class="<?= is_route('admin/dashboard') ? 'active' : '' ?>" href="<?= e(route_url('admin/dashboard')) ?>">Admin</a>
            <?php endif; ?>
        </nav>

        <div class="nav-actions">
            <?php if ($user): ?>
                <a class="profile-link" href="<?= e(route_url('auth/profile')) ?>">
                    <?= e($user['name']) ?>
                </a>
                <a class="ghost-button" href="<?= e(route_url('auth/logout')) ?>">Logout</a>
            <?php else: ?>
                <a class="ghost-button" href="<?= e(route_url('auth/login')) ?>">Login</a>
                <a class="primary-button" href="<?= e(route_url('auth/register')) ?>">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>
