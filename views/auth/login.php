<section class="panel">
    <div class="split-panel">
        <div>
          
            <h1 class="section-heading">Login to the Travel Guide portal</h1>
            <p class="section-intro">
                Sessions, password verification, and the optional “Remember Me” cookie are handled here.
            </p>
            <?php if (!empty($errors['general'])): ?>
                <div class="alert error-alert"><?= e($errors['general']) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= e(route_url('auth/login')) ?>" data-auth-form="login" novalidate>
                <?= csrf_input() ?>
                <div class="form-stack">
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="<?= e($email) ?>" required>
                        <div class="field-error" data-error-for="email"><?= e(validation_errors($errors, 'email')) ?></div>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required>
                        <div class="field-error" data-error-for="password"><?= e(validation_errors($errors, 'password')) ?></div>
                    </div>

                    <label class="small-label">
                        <input type="checkbox" name="remember_me" value="1">
                        Keep me logged in for 30 days
                    </label>
                </div>

                <div class="form-actions">
                    <button class="primary-button" type="submit">Login</button>
                    <a class="ghost-button" href="<?= e(route_url('auth/register')) ?>">Need an account?</a>
                </div>
            </form>
        </div>

        <aside class="panel compact-panel">
            <h3>Demo accounts after importing seed data</h3>
            
            <p class="muted-text">Password for all seeded accounts: <strong>12345678</strong></p>

            <div class="mini-card">
                <strong>Admin</strong>
                <p> unveified admin </p>
                <p class="muted-text">adminTest1@gmail.com</p>

                <p> veified admin </p>
                <p class="muted-text">admin@travelguide.test</p>
                

            </div>

            <div class="mini-card">
                <strong>Scout</strong>
                    <p> unveified scout </p>

                <p class="muted-text">un_scout@gmail.com</p>
                <p> veified scout </p>

                <p class="muted-text">scout@gmail.com</p>


            </div>

            <div class="mini-card">
                <strong>User</strong>
                <p> unveified user </p>
                <p class="muted-text">user1@gmail.com</p>

                <p> veified user </p>
                <p class="muted-text">test@gmail.com</p>
            </div>
        </aside>
    </div>
</section>
