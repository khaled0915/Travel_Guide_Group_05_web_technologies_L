<section class="panel">
    <span class="eyebrow">Task 3</span>
    <h1 class="section-heading">Admin dashboard</h1>
    <p class="section-intro">
        Manage users, moderate scout submissions, publish posts, and remove inappropriate comments.
    </p>

    <div class="stats-grid">
        <article class="stat-card">
            <div class="stat-label">Admins</div>
            <div class="stat-value"><?= (int) $counts['roles']['admin'] ?></div>
        </article>
        <article class="stat-card">
            <div class="stat-label">Scouts</div>
            <div class="stat-value"><?= (int) $counts['roles']['scout'] ?></div>
        </article>
        <article class="stat-card">
            <div class="stat-label">General users</div>
            <div class="stat-value"><?= (int) $counts['roles']['user'] ?></div>
        </article>
        <article class="stat-card">
            <div class="stat-label">Pending requests / Posts / Comments</div>
            <div class="stat-value">
                <?= (int) $counts['pending_requests'] ?> / <?= (int) $counts['posts'] ?> / <?= (int) $counts['comments'] ?>
            </div>
        </article>
    </div>
</section>

<section class="panel">
    <div class="split-panel">
        <div class="panel compact-panel">
            <h2 class="section-heading">Add new user manually</h2>
            <form id="adminCreateUserForm" method="post" action="<?= e(route_url('api/admin/create-user')) ?>" novalidate>
                <?= csrf_input() ?>
                <div class="form-grid">
                    <div class="field">
                        <label for="admin_name">Name</label>
                        <input id="admin_name" type="text" name="name" required>
                        <div class="field-error" data-error-for="name"></div>
                    </div>

                    <div class="field">
                        <label for="admin_email">Email</label>
                        <input id="admin_email" type="email" name="email" required>
                        <div class="field-error" data-error-for="email"></div>
                    </div>

                    <div class="field">
                        <label for="admin_password">Password</label>
                        <input id="admin_password" type="password" name="password" required>
                        <div class="field-error" data-error-for="password"></div>
                    </div>

                    <div class="field">
                        <label for="admin_role">Role</label>
                        <select id="admin_role" name="role" required>
                            <option value="user">General User</option>
                            <option value="scout">Scout</option>
                            <option value="admin">Admin</option>
                        </select>
                        <div class="field-error" data-error-for="role"></div>
                    </div>
                </div>

                <label class="small-label">
                    <input type="checkbox" name="is_verified" value="1" checked>
                    Mark as verified immediately
                </label>

                <div class="form-actions">
                    <button class="primary-button" type="submit">Create user</button>
                </div>
            </form>
        </div>

        <div class="panel compact-panel">
            <h2 class="section-heading">User management</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $managedUser): ?>
                            <tr>
                                <td>
                                    <strong><?= e($managedUser['name']) ?></strong><br>
                                    <span class="small-label"><?= e($managedUser['email']) ?></span>
                                </td>
                                <td><?= e(ucfirst($managedUser['role'])) ?></td>
                                <td>
                                    <span class="tag <?= (int) $managedUser['is_verified'] === 1 ? 'approved' : 'pending' ?>">
                                        <?= (int) $managedUser['is_verified'] === 1 ? 'Verified' : 'Pending' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="inline-actions">
                                        <button
                                            class="ghost-button js-toggle-verification"
                                            type="button"
                                            data-user-id="<?= (int) $managedUser['id'] ?>"
                                            data-is-verified="<?= (int) $managedUser['is_verified'] === 1 ? '0' : '1' ?>"
                                        >
                                            <?= (int) $managedUser['is_verified'] === 1 ? 'Unverify' : 'Verify' ?>
                                        </button>
                                        <button
                                            class="danger-button js-delete-user"
                                            type="button"
                                            data-user-id="<?= (int) $managedUser['id'] ?>"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<section class="panel">
    <h2 class="section-heading">Pending scout requests</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Scout</th>
                    <th>Destination</th>
                    <th>Genre / Cost</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pendingRequests === []): ?>
                    <tr>
                        <td colspan="4" class="muted-text">No pending requests waiting for review.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pendingRequests as $request): ?>
                        <tr>
                            <td><?= e($request['scout_name']) ?></td>
                            <td>
                                <strong><?= e($request['title']) ?></strong><br>
                                <span class="small-label"><?= e($request['country']) ?></span>
                            </td>
                            <td><?= e(ucfirst($request['genre'])) ?> / <?= e(strtoupper($request['cost_level'])) ?></td>
                            <td>
                                <div class="inline-actions">
                                    <button
                                        class="primary-button js-approve-request"
                                        type="button"
                                        data-request-id="<?= (int) $request['id'] ?>"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        class="danger-button js-reject-request"
                                        type="button"
                                        data-request-id="<?= (int) $request['id'] ?>"
                                    >
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <div class="split-panel">
        <div class="panel compact-panel">
            <h2 class="section-heading">Published posts moderation</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Scout</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <strong><?= e($post['title']) ?></strong><br>
                                    <span class="small-label"><?= e($post['country']) ?> | <?= e($post['genre']) ?></span>
                                </td>
                                <td><?= e($post['scout_name']) ?></td>
                                <td><span class="tag <?= e($post['status']) ?>"><?= e(ucfirst($post['status'])) ?></span></td>
                                <td>
                                    <div class="inline-actions">
                                        <button
                                            class="ghost-button js-edit-post"
                                            type="button"
                                            data-post="<?= e(base64_encode(json_encode($post, JSON_UNESCAPED_UNICODE))) ?>"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            class="danger-button js-delete-post"
                                            type="button"
                                            data-post-id="<?= (int) $post['id'] ?>"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel compact-panel">
            <h2 class="section-heading">Edit approved post</h2>
            <form id="adminEditPostForm" method="post" action="<?= e(route_url('api/admin/update-post')) ?>" enctype="multipart/form-data" novalidate>
                <?= csrf_input() ?>
                <input type="hidden" name="post_id" value="0">

                <div class="form-grid">
                    <div class="field">
                        <label for="edit_title">Title</label>
                        <input id="edit_title" type="text" name="title" required>
                        <div class="field-error" data-error-for="title"></div>
                    </div>

                    <div class="field">
                        <label for="edit_country">Country</label>
                        <input id="edit_country" type="text" name="country" required>
                        <div class="field-error" data-error-for="country"></div>
                    </div>

                    <div class="field">
                        <label for="edit_genre">Genre</label>
                        <select id="edit_genre" name="genre" required>
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
                        <label for="edit_cost_level">Cost level</label>
                        <select id="edit_cost_level" name="cost_level" required>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                        <div class="field-error" data-error-for="cost_level"></div>
                    </div>

                    <div class="field">
                        <label for="edit_status">Status</label>
                        <select id="edit_status" name="status" required>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="pending">Pending</option>
                        </select>
                        <div class="field-error" data-error-for="status"></div>
                    </div>

                    <div class="field">
                        <label for="edit_image">Replace image</label>
                        <input id="edit_image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                        <div class="field-error" data-error-for="image"></div>
                    </div>
                </div>

                <div class="field">
                    <label for="edit_short_history">Short history</label>
                    <textarea id="edit_short_history" name="short_history" required></textarea>
                    <div class="field-error" data-error-for="short_history"></div>
                </div>

                <div class="field">
                    <label for="edit_country_representation">Country representation</label>
                    <textarea id="edit_country_representation" name="country_representation" required></textarea>
                    <div class="field-error" data-error-for="country_representation"></div>
                </div>

                <div class="field">
                    <label for="edit_travel_medium_info">Travel medium info</label>
                    <textarea id="edit_travel_medium_info" name="travel_medium_info" required></textarea>
                    <div class="field-error" data-error-for="travel_medium_info"></div>
                </div>

                <div class="form-actions">
                    <button class="primary-button" type="submit">Update post</button>
                </div>
            </form>
        </div>
    </div>
</section>

<section class="panel">
    <h2 class="section-heading">Comment moderation</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Post</th>
                    <th>Commenter</th>
                    <th>Comment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($comments === []): ?>
                    <tr>
                        <td colspan="4" class="muted-text">No comments have been posted yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td><?= e($comment['post_title']) ?></td>
                            <td><?= e($comment['commenter_name']) ?></td>
                            <td><?= e($comment['content']) ?></td>
                            <td>
                                <button
                                    class="danger-button js-delete-comment"
                                    type="button"
                                    data-comment-id="<?= (int) $comment['id'] ?>"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
