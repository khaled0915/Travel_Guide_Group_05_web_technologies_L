<?php
$requestTemplate = [
    'request_id' => 0,
    'original_post_id' => 0,
    'title' => '',
    'short_history' => '',
    'country' => '',
    'country_representation' => '',
    'genre' => '',
    'cost_level' => '',
    'travel_medium_info' => '',
];
?>
<section class="panel">
    <div class="meta-row">
        <div>
            <span class="eyebrow">Task 2</span>
            <h1 class="section-heading">Scout post requests</h1>
            <p class="section-intro">
                Create, update, and delete your own pending post requests. You can also use an approved post as the
                base for a change request.
            </p>
        </div>
    </div>

    <div class="split-panel">
        <div>
            <form
                id="scoutRequestForm"
                class="panel compact-panel"
                method="post"
                action="<?= e(route_url('api/scout/save')) ?>"
                enctype="multipart/form-data"
                novalidate
            >
                <?= csrf_input() ?>
                <input type="hidden" name="request_id" value="0">
                <input type="hidden" name="original_post_id" value="0">

                <div class="meta-row">
                    <div>
                        <h2 class="section-heading">Submit destination information</h2>
                        <p class="muted-text">Editing a pending request will reuse this same form.</p>
                    </div>
                    <button class="ghost-button" type="button" id="resetScoutForm">New request</button>
                </div>

                <div class="form-grid">
                    <div class="field">
                        <label for="title">Title</label>
                        <input id="title" type="text" name="title" required>
                        <div class="field-error" data-error-for="title"></div>
                    </div>

                    <div class="field">
                        <label for="country">Country</label>
                        <input id="country" type="text" name="country" required>
                        <div class="field-error" data-error-for="country"></div>
                    </div>

                    <div class="field">
                        <label for="genre">Genre</label>
                        <select id="genre" name="genre" required>
                            <option value="">Choose genre</option>
                            <option value="beach">Beach</option>
                            <option value="mountain">Mountain</option>
                            <option value="city">City</option>
                            <option value="historical">Historical</option>
                            <option value="nature">Nature</option>
                            <option value="adventure">Adventure</option>
                        </select>
                        <div class="field-error" data-error-for="genre"></div>
                    </div>

                    <div class="field">
                        <label for="cost_level">Cost level</label>
                        <select id="cost_level" name="cost_level" required>
                            <option value="">Choose cost level</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                        <div class="field-error" data-error-for="cost_level"></div>
                    </div>

                    <div class="field">
                        <label for="image">Place image</label>
                        <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                        <div class="field-help">Optional. Max 4 MB.</div>
                        <div class="field-error" data-error-for="image"></div>
                    </div>
                </div>

                <div class="field">
                    <label for="short_history">Short history</label>
                    <textarea id="short_history" name="short_history" required></textarea>
                    <div class="field-error" data-error-for="short_history"></div>
                </div>

                <div class="field">
                    <label for="country_representation">Country representation</label>
                    <textarea id="country_representation" name="country_representation" required></textarea>
                    <div class="field-error" data-error-for="country_representation"></div>
                </div>

                <div class="field">
                    <label for="travel_medium_info">Travel medium information</label>
                    <textarea id="travel_medium_info" name="travel_medium_info" required></textarea>
                    <div class="field-error" data-error-for="travel_medium_info"></div>
                </div>

                <div class="form-actions">
                    <button class="primary-button" type="submit">Save request</button>
                </div>
            </form>
        </div>

        <div class="panel compact-panel">
            <h2 class="section-heading">My requests</h2>
            <p class="muted-text">Edit or remove pending requests. Rejected requests remain visible for reference.</p>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="scoutRequestRows">
                        <?php if ($requests === []): ?>
                            <tr>
                                <td colspan="4" class="muted-text">No requests submitted yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $request): ?>
                                <tr data-request-row="<?= (int) $request['id'] ?>">
                                    <td>
                                        <strong><?= e($request['title']) ?></strong><br>
                                        <span class="small-label"><?= e($request['country']) ?> | <?= e($request['genre']) ?></span>
                                    </td>
                                    <td>
                                        <span class="tag <?= e($request['status']) ?>">
                                            <?= e(ucfirst($request['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= e(date('d M Y', strtotime($request['requested_at']))) ?></td>
                                    <td>
                                        <div class="inline-actions">
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <button
                                                    class="ghost-button js-edit-request"
                                                    type="button"
                                                    data-request="<?= e(base64_encode(json_encode($requestTemplate + $request, JSON_UNESCAPED_UNICODE))) ?>"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    class="danger-button js-delete-request"
                                                    type="button"
                                                    data-request-id="<?= (int) $request['id'] ?>"
                                                >
                                                    Delete
                                                </button>
                                            <?php else: ?>
                                                <span class="small-label">Locked</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <h2 class="section-heading">Approved posts from your submissions</h2>
    <p class="section-intro">
        Use “Request changes” to prefill the form with an approved post and submit a revised version for admin review.
    </p>

    <?php if ($approvedPosts === []): ?>
        <div class="empty-state">
            <p>No approved posts from your account yet.</p>
        </div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($approvedPosts as $post): ?>
                <article class="post-card">
                    <div class="post-image">
                        <img src="<?= e(post_image_url($post['image_path'] ?? null)) ?>" alt="<?= e($post['title']) ?>">
                    </div>
                    <div class="card-tags">
                        <span class="tag approved">Approved</span>
                        <span class="tag"><?= e($post['country']) ?></span>
                        <span class="tag"><?= e(ucfirst($post['genre'])) ?></span>
                    </div>
                    <div>
                        <h3><?= e($post['title']) ?></h3>
                        <p class="muted-text"><?= e(mb_strimwidth($post['short_history'], 0, 110, '...')) ?></p>
                    </div>
                    <div class="inline-actions">
                        <a class="ghost-button" href="<?= e(route_url('browse/detail', ['id' => $post['id']])) ?>">View</a>
                        <button
                            class="secondary-button js-request-change"
                            type="button"
                            data-post="<?= e(base64_encode(json_encode($post, JSON_UNESCAPED_UNICODE))) ?>"
                        >
                            Request changes
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
