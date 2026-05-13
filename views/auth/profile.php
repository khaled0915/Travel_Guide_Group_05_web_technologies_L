<section class="panel">
    <div class="split-panel">
        <aside class="panel compact-panel">
            
            <h2 class="section-heading">Profile summary</h2>
            <div class="post-image">
                <img src="<?= e(post_image_url($user['profile_picture'] ?? null)) ?>" alt="<?= e($user['name']) ?>">
            </div>
            <p><strong><?= e($user['name']) ?></strong></p>
            <p class="muted-text"><?= e($user['email']) ?></p>
            <p class="muted-text">Role: <?= e(ucfirst($user['role'])) ?></p>
            <span class="tag <?= (int) $user['is_verified'] === 1 ? 'approved' : 'pending' ?>">
                <?= (int) $user['is_verified'] === 1 ? 'Verified' : 'Pending approval' ?>
            </span>
        </aside>

        <div>
            <h1 class="section-heading">Update profile</h1>
            <p class="section-intro">
                You can edit name, email, profile picture, and password. Password change requires the current password.
            </p>

            <form method="post" action="<?= e(route_url('auth/profile')) ?>" enctype="multipart/form-data" data-auth-form="profile" novalidate>
                <?= csrf_input() ?>
                <div class="form-grid">
                    <div class="field">
                        <label for="name">Name</label>
                        <input id="name" type="text" name="name" value="<?= e($user['name']) ?>" required>
                        <div class="field-error" data-error-for="name"><?= e(validation_errors($errors, 'name')) ?></div>
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="<?= e($user['email']) ?>" required>
                        <div class="field-error" data-error-for="email"><?= e(validation_errors($errors, 'email')) ?></div>
                    </div>

                    <div class="field">
                        <label for="profile_picture">Profile picture</label>
                        <input id="profile_picture" type="file" name="profile_picture" accept=".jpg,.jpeg,.png,.webp">
                        <div class="field-help">Accepted: JPG, PNG, WEBP. Max 2 MB.</div>
                        <div class="field-error" data-error-for="profile_picture"><?= e(validation_errors($errors, 'profile_picture')) ?></div>
                    </div>

                    <div class="field">
                        <label for="current_password">Current password</label>
                        <input id="current_password" type="password" name="current_password">
                        <div class="field-error" data-error-for="current_password"><?= e(validation_errors($errors, 'current_password')) ?></div>
                    </div>

                    <div class="field">
                        <label for="new_password">New password</label>
                        <input id="new_password" type="password" name="new_password">
                        <div class="field-error" data-error-for="new_password"><?= e(validation_errors($errors, 'new_password')) ?></div>
                    </div>

                    <div class="field">
                        <label for="confirm_password">Confirm new password</label>
                        <input id="confirm_password" type="password" name="confirm_password">
                        <div class="field-error" data-error-for="confirm_password"><?= e(validation_errors($errors, 'confirm_password')) ?></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="primary-button" type="submit">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</section>
