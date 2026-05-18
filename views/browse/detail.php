<?php
$user = Auth::user();
$canComment = $user && $user['role'] === 'user' && (int) $user['is_verified'] === 1;
?>
<section class="panel">
    <div class="detail-grid">
        <div class="detail-media">
            <img src="<?= e(post_image_url($post['image_path'] ?? null)) ?>" alt="<?= e($post['title']) ?>">
        </div>

        <div class="detail-copy">
            <span class="eyebrow"><?= e($post['country']) ?></span>
            <h1 class="section-heading"><?= e($post['title']) ?></h1>
            <div class="info-badges">
                <span class="tag"><?= e(ucfirst($post['genre'])) ?></span>
                <span class="tag"><?= e(strtoupper($post['cost_level'])) ?></span>
                <span class="tag approved">Published</span>
            </div>

            <div>
                <h3>Short history</h3>
                <p class="muted-text"><?= nl2br(e($post['short_history'])) ?></p>
            </div>

            <div>
                <h3>Country representation</h3>
                <p class="muted-text"><?= nl2br(e($post['country_representation'] ?? '')) ?></p>
            </div>

            <div>
                <h3>Travel medium information</h3>
                <p class="muted-text"><?= nl2br(e($post['travel_medium_info'])) ?></p>
            </div>

            <div class="inline-actions">
                <a class="ghost-button" href="<?= e(route_url('browse/index')) ?>">Back to browse</a>
                <?php if ($user && $user['role'] === 'user'): ?>
                    <button
                        class="<?= $isWishlisted ? 'danger-button js-wishlist-remove' : 'ghost-button js-wishlist-add' ?>"
                        type="button"
                        data-post-id="<?= (int) $post['id'] ?>"
                    >
                        <?= $isWishlisted ? 'Remove wishlist' : 'Save wishlist' ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <div class="split-panel">
        <div class="panel compact-panel">
            <span class="eyebrow">Probable cost estimate</span>
            <h2 class="section-heading">Trip calculator</h2>
            <p class="muted-text">
                Base cost: <strong><?= e($costEstimate['currency']) ?> <?= e(number_format((float) $costEstimate['base_cost'], 2)) ?></strong>
                per traveler for a one-week baseline.
            </p>

            <form id="costCalculatorForm" novalidate>
                <input type="hidden" id="base_cost" value="<?= e((string) $costEstimate['base_cost']) ?>">
                <input type="hidden" id="base_currency" value="<?= e($costEstimate['currency']) ?>">

                <div class="form-grid">
                    <div class="field">
                        <label for="travelers">Travelers</label>
                        <input id="travelers" type="number" min="1" max="10" name="travelers" value="1" required>
                        <div class="field-error" data-error-for="travelers"></div>
                    </div>

                    <div class="field">
                        <label for="days">Days</label>
                        <input id="days" type="number" min="1" max="30" name="days" value="7" required>
                        <div class="field-error" data-error-for="days"></div>
                    </div>
                </div>

                <div class="panel compact-panel" id="costCalculatorResult">
                    Estimated total: <?= e($costEstimate['currency']) ?> <?= e(number_format((float) $costEstimate['base_cost'], 2)) ?>
                </div>
            </form>
        </div>

        <div class="panel compact-panel">
            <span class="eyebrow">Comments</span>
            <h2 class="section-heading">Traveler discussion</h2>

            <?php if ($canComment): ?>
                <form id="commentForm" method="post" action="<?= e(route_url('api/comments/add')) ?>" novalidate>
                    <?= csrf_input() ?>
                    <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">

                    <div class="field">
                        <label for="display_name">Name</label>
                        <input id="display_name" type="text" name="display_name" value="<?= e($user['name']) ?>" required>
                        <div class="field-error" data-error-for="display_name"></div>
                    </div>

                    <div class="field">
                        <label for="content">Comment</label>
                        <textarea id="content" name="content" maxlength="500" required></textarea>
                        <div class="field-help">Keep it respectful and under 500 characters.</div>
                        <div class="field-error" data-error-for="content"></div>
                    </div>

                    <div class="form-actions">
                        <button class="primary-button" type="submit">Post comment</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert error-alert">
                    Only verified general users can post comments. Admins and scouts can still read them.
                </div>
            <?php endif; ?>

            <div class="comment-list" id="commentList">
                <?php if ($comments === []): ?>
                    <div class="comment-card">
                        <p class="muted-text">No comments yet. Be the first traveler to leave one.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <article class="comment-card" data-comment-id="<?= (int) $comment['id'] ?>">
                            <div class="meta-row">
                                <div>
                                    <strong><?= e($comment['commenter_name']) ?></strong><br>
                                    <span class="small-label"><?= e(date('d M Y, h:i A', strtotime($comment['created_at']))) ?></span>
                                </div>
                                <?php if ($user && (int) $comment['user_id'] === (int) $user['id']): ?>
                                    <button
                                        class="danger-button js-delete-comment"
                                        type="button"
                                        data-comment-id="<?= (int) $comment['id'] ?>"
                                    >
                                        Delete
                                    </button>
                                <?php endif; ?>
                            </div>
                            <p class="muted-text"><?= nl2br(e($comment['content'])) ?></p>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
