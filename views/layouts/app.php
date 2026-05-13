<?php
$pageTitle = $pageTitle ?? '';
$pageScripts = $pageScripts ?? [];
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="base-url" content="<?= e(base_url()) ?>">
    <title><?= e(page_title($pageTitle)) ?></title>
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/app.css')) ?>">
</head>
<body>
    <?php require app_path('views/partials/header.php'); ?>
    <main class="page-shell">
        <?php require app_path('views/partials/flash.php'); ?>
        <?php require $viewFile; ?>
    </main>
    <?php require app_path('views/partials/footer.php'); ?>
    <script src="<?= e(asset_url('assets/js/app.js')) ?>"></script>
    <?php foreach ($pageScripts as $script): ?>
        <script src="<?= e(asset_url($script)) ?>"></script>
    <?php endforeach; ?>
</body>
</html>
