<?php $user = Auth::user(); ?>
<section class="panel">
    <div class="meta-row">
        <div>
            <span class="eyebrow">Task 4</span>
            <h1 class="section-heading">Browse approved travel posts</h1>
            <p class="section-intro">
                Live search and filtering are handled through AJAX so the post grid updates without a page reload.
            </p>
        </div>
    </div>

    <form id="browseFilterForm" class="panel compact-panel" action="<?= e(route_url('api/browse/search')) ?>" method="get">
        <div class="form-grid">
            <div class="field">
                <label for="browse_q">Search</label>
                <input id="browse_q" type="text" name="q" placeholder="Search by title or country">
            </div>

            <div class="field">
                <label for="browse_country">Country</label>
                <select id="browse_country" name="country">
                    <option value="">All countries</option>
                    <?php foreach ($filters['countries'] as $country): ?>
                        <option value="<?= e($country) ?>"><?= e($country) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="filter-row">
            <div class="field">
                <label>Genres</label>
                <div class="inline-actions">
                    <?php foreach ($filters['genres'] as $genre): ?>
                        <label class="small-label">
                            <input type="checkbox" name="genre[]" value="<?= e($genre) ?>">
                            <?= e(ucfirst($genre)) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="field">
                <label>Cost level</label>
                <div class="inline-actions">
                    <label class="small-label"><input type="radio" name="cost" value=""> All</label>
                    <label class="small-label"><input type="radio" name="cost" value="low"> Low</label>
                    <label class="small-label"><input type="radio" name="cost" value="medium"> Medium</label>
                    <label class="small-label"><input type="radio" name="cost" value="high"> High</label>
                </div>
            </div>
        </div>
    </form>
</section>

<section class="panel">
    <div
        class="card-grid"
        id="browsePostGrid"
        data-can-wishlist="<?= $user && $user['role'] === 'user' ? '1' : '0' ?>"
        data-wishlist-post-ids="<?= e(implode(',', array_map('strval', $wishlistPostIds ?? []))) ?>"
    >
        <?php if ($posts === []): ?>
            <div class="empty-state">
                <p>No approved travel posts are available yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <article class="post-card" data-post-card="<?= (int) $post['id'] ?>">
                    <div class="post-image">
                        <img src="<?= e(post_image_url($post['image_path'] ?? null)) ?>" alt="<?= e($post['title']) ?>">
                    </div>
                    <div class="card-tags">
                        <span class="tag"><?= e($post['country']) ?></span>
                        <span class="tag"><?= e(ucfirst($post['genre'])) ?></span>
                        <span class="tag"><?= e(strtoupper($post['cost_level'])) ?></span>
                    </div>
                    <div>
                        <h3><?= e($post['title']) ?></h3>
                        <p class="muted-text"><?= e(mb_strimwidth($post['short_history'], 0, 125, '...')) ?></p>
                    </div>
                    <div class="inline-actions">
                        <a class="primary-button" href="<?= e(route_url('browse/detail', ['id' => $post['id']])) ?>">Read more</a>
                        <?php if ($user && $user['role'] === 'user'): ?>
                            <?php $isSaved = in_array((int) $post['id'], $wishlistPostIds, true); ?>
                            <button
                                class="<?= $isSaved ? 'danger-button js-wishlist-remove' : 'ghost-button js-wishlist-add' ?>"
                                type="button"
                                data-post-id="<?= (int) $post['id'] ?>"
                            >
                                <?= $isSaved ? 'Remove wishlist' : 'Save wishlist' ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
