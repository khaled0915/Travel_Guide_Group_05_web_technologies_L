<section class="panel">
    <div class="meta-row">
        <div>
            
            <h1 class="section-heading">Your saved wishlist</h1>
            <p class="section-intro">Verified general users can keep a checklist of places they want to visit later.</p>
        </div>
    </div>

    <?php if ($wishlistItems === []): ?>
        <div class="empty-state">
            <p>Your wishlist is empty. Browse approved posts and save the ones you like.</p>
            <a class="primary-button" href="<?= e(route_url('browse/index')) ?>">Browse posts</a>
        </div>
    <?php else: ?>
        <div class="card-grid" data-wishlist-grid>
            <?php foreach ($wishlistItems as $item): ?>
                <article class="post-card" data-wishlist-item="<?= (int) $item['post_id'] ?>">
                    <div class="post-image">
                        <img src="<?= e(post_image_url($item['image_path'] ?? null)) ?>" alt="<?= e($item['title']) ?>">
                    </div>
                    <div class="card-tags">
                        <span class="tag"><?= e($item['country']) ?></span>
                        <span class="tag"><?= e(ucfirst($item['genre'])) ?></span>
                        <span class="tag"><?= e(strtoupper($item['cost_level'])) ?></span>
                    </div>
                    <div>
                        <h3><?= e($item['title']) ?></h3>
                        <p class="muted-text">Saved on <?= e(date('d M Y', strtotime($item['added_at']))) ?></p>
                    </div>
                    <div class="inline-actions">
                        <a class="ghost-button" href="<?= e(route_url('browse/detail', ['id' => $item['post_id']])) ?>">Open post</a>
                        <button
                            class="danger-button js-wishlist-remove"
                            type="button"
                            data-post-id="<?= (int) $item['post_id'] ?>"
                            data-mode="page"
                        >
                            Remove
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
