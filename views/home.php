<?php
$verified = $user && (int) $user['is_verified'] === 1;
$role = $user['role'] ?? null;
?>
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow">Discover Hidden Gems Around the World</span>
        <h1>
            Explore Extraordinary Destinations
        </h1>
        <p>
            Connect with authentic travel experiences shared by local scouts. Browse curated destinations, save your 
            favorites, estimate travel costs, and plan your next adventure with a community of passionate travelers.
        </p>

        <?php if (!$user): ?>
            <div class="hero-actions">
                <a class="primary-button" href="<?= e(route_url('auth/register')) ?>">Create account</a>
                <a class="ghost-button" href="<?= e(route_url('auth/login')) ?>">Login</a>
            </div>
        <?php elseif (!$verified): ?>
            <div class="panel compact-panel">
                <strong>Your account is pending admin approval.</strong>
                <p class="muted-text">
                    You can log in, but detailed site features remain locked until an admin verifies your account.
                </p>
            </div>
        <?php else: ?>
            <div class="hero-actions">
                <a class="primary-button" href="<?= e(route_url('browse/index')) ?>">Browse published posts</a>
                <?php if ($role === 'scout'): ?>
                    <a class="secondary-button" href="<?= e(route_url('scout/index')) ?>">Open scout panel</a>
                <?php elseif ($role === 'admin'): ?>
                    <a class="secondary-button" href="<?= e(route_url('admin/dashboard')) ?>">Open admin dashboard</a>
                <?php else: ?>
                    <a class="secondary-button" href="<?= e(route_url('wishlist/index')) ?>">Open wishlist</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="hero-visual">
        <div class="hero-orb"></div>
        <div class="hero-slab"></div>
        <div class="hero-card-stack">
            <div class="mini-card">
                <strong>🔍 Curator</strong>
                <p class="muted-text">Review submissions, ensure quality, approve destinations, and keep the community safe.</p>
            </div>
            <div class="mini-card">
                <strong>✍️ Scout</strong>
                <p class="muted-text">Share local knowledge. Submit hidden gems with rich history, cultural context, and insider tips.</p>
            </div>
            <div class="mini-card">
                <strong>🗺️ Traveler</strong>
                <p class="muted-text">Search anywhere. Save favorites, read reviews, calculate budgets, and plan dream trips together.</p>
            </div>
        </div>
    </div>
</section>

<?php if (!$user): ?>
    <section class="panel">
        <h2 class="section-heading">Start Your Journey Today</h2>
        <p class="section-intro">
            Join our community of travelers and scouts. Create a free account to unlock unlimited access to destinations, 
            travel tips, cost estimates, and connect with fellow adventurers around the world.
        </p>
    </section>
<?php elseif (!$verified): ?>
    <section class="panel">
        <h2 class="section-heading">Account Under Review</h2>
        <p class="section-intro">
            Welcome! Your account is being verified by our team to ensure community quality and safety. 
            Full access to destinations, filtering, wishlists, and advanced features will unlock shortly. 
            Thank you for your patience.
        </p>
    </section>
<?php else: ?>
    <section class="panel">
        <div class="meta-row">
            <div>
                <span class="eyebrow">Recently Published</span>
                <h2 class="section-heading">Trending Destinations This Week</h2>
            </div>
            <a class="ghost-button" href="<?= e(route_url('browse/index')) ?>">View all posts</a>
        </div>

        <?php if ($latestPosts === []): ?>
            <div class="empty-state">
                <p>Be the first! No destinations published yet. Scouts are working on amazing travel stories to share with you soon.</p>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($latestPosts as $post): ?>
                    <article class="post-card">
                        <div class="post-image">
                            <img src="<?= e(post_image_url($post['image_path'] ?? null)) ?>" alt="<?= e($post['title']) ?>">
                        </div>
                        <div class="card-tags">
                            <span class="tag"><?= e(ucfirst($post['genre'])) ?></span>
                            <span class="tag"><?= e($post['country']) ?></span>
                            <span class="tag"><?= e(strtoupper($post['cost_level'])) ?></span>
                        </div>
                        <div>
                            <h3><?= e($post['title']) ?></h3>
                            <p class="muted-text"><?= e(mb_strimwidth($post['short_history'], 0, 120, '...')) ?></p>
                        </div>
                        <div class="inline-actions">
                            <a class="primary-button" href="<?= e(route_url('browse/detail', ['id' => $post['id']])) ?>">Read more</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
