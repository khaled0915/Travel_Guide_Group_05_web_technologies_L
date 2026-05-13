<section class="panel">
    
    <h1 class="section-heading">Create a new account</h1>
    <p class="section-intro">
        Registration is available for all three roles. Every new account is saved with <code>is_verified = 0</code>
        and must be approved by an admin before detailed features unlock.
    </p>

    <form method="post" action="<?= e(route_url('auth/register')) ?>" data-auth-form="register" novalidate>
        <?= csrf_input() ?>
        <div class="form-grid">
            <div class="field">
                <label for="name">Full name</label>
                <input id="name" type="text" name="name" value="<?= e($data['name']) ?>" required>
                <div class="field-error" data-error-for="name"><?= e(validation_errors($errors, 'name')) ?></div>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="<?= e($data['email']) ?>" required>
                <div class="field-error" data-error-for="email"><?= e(validation_errors($errors, 'email')) ?></div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
                <div class="field-help">Must be at least 8 characters.</div>
                <div class="field-error" data-error-for="password"><?= e(validation_errors($errors, 'password')) ?></div>
            </div>

            <div class="field">
                <label for="confirm_password">Confirm password</label>
                <input id="confirm_password" type="password" name="confirm_password" required>
                <div class="field-error" data-error-for="confirm_password"><?= e(validation_errors($errors, 'confirm_password')) ?></div>
            </div>

            <div class="field">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="user" <?= selected($data['role'], 'user') ?>>General User</option>
                    <option value="scout" <?= selected($data['role'], 'scout') ?>>Scout</option>
                    <option value="admin" <?= selected($data['role'], 'admin') ?>>Admin</option>
                </select>
                <div class="field-error" data-error-for="role"><?= e(validation_errors($errors, 'role')) ?></div>
            </div>
        </div>

        <div class="form-actions">
            <button class="primary-button" type="submit">Register</button>
            <a class="ghost-button" href="<?= e(route_url('auth/login')) ?>">Already have an account?</a>
        </div>
    </form>
</section>
