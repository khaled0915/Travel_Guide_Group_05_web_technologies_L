<?php
$successMessage = flash('success');
$errorMessage = flash('error');
?>
<?php if ($successMessage): ?>
    <div class="alert success-alert"><?= e($successMessage) ?></div>
<?php endif; ?>
<?php if ($errorMessage): ?>
    <div class="alert error-alert"><?= e($errorMessage) ?></div>
<?php endif; ?>
