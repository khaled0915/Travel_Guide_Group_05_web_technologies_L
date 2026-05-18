<?php

declare(strict_types=1);

function app_config(?string $key = null, mixed $default = null): mixed
{
    static $config;

    if ($config === null) {
        $config = require dirname(__DIR__) . '/config/app.php';
    }

    if ($key === null) {
        return $config;
    }

    return $config[$key] ?? $default;
}

function app_path(string $path = ''): string
{
    return dirname(__DIR__) . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function base_url(): string
{
    static $baseUrl;

    if ($baseUrl !== null) {
        return $baseUrl;
    }

    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/public/index.php');
    $baseUrl = rtrim(str_replace('/index.php', '', $scriptName), '/');

    return $baseUrl === '' ? '/' : $baseUrl;
}

function route_url(string $route, array $params = []): string
{
    $params = array_merge(['route' => $route], $params);
    $prefix = base_url() === '/' ? '' : base_url();

    return $prefix . '/index.php?' . http_build_query($params);
}

function asset_url(string $path): string
{
    $prefix = base_url() === '/' ? '' : base_url();
    $url = $prefix . '/' . ltrim($path, '/');
    $assetPath = app_path('public/' . ltrim($path, '/'));

    if (is_file($assetPath)) {
        $url .= '?v=' . filemtime($assetPath);
    }

    return $url;
}

function redirect_to(string $route, array $params = []): never
{
    header('Location: ' . route_url($route, $params));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function current_route(): string
{
    return $GLOBALS['app_current_route'] ?? 'home/index';
}

function is_route(string $route): bool
{
    return current_route() === $route;
}

function flash(string $key, ?string $message = null): ?string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return $value;
}

function validation_errors(array $errors, string $field): string
{
    return $errors[$field] ?? '';
}

function old(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf_token(?string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    return isset($_SESSION['_csrf']) && is_string($token) && hash_equals($_SESSION['_csrf'], $token);
}

function require_csrf(): void
{
    if (!verify_csrf_token($_POST['_csrf'] ?? null)) {
        json_or_redirect_error('Invalid security token. Please refresh and try again.', 419);
    }
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_or_redirect_error(string $message, int $status = 422, string $route = 'home/index'): never
{
    $isAjax = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');
    if ($isAjax) {
        json_response(['ok' => false, 'message' => $message], $status);
    }

    flash('error', $message);
    redirect_to($route);
}

function render(string $view, array $viewData = []): void
{
    extract($viewData, EXTR_SKIP);
    $viewFile = app_path('views/' . $view . '.php');
    require app_path('views/layouts/app.php');
}

function uploaded_file_is_present(array $file): bool
{
    return isset($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE;
}

function store_uploaded_file(
    array $file,
    string $directory,
    array $allowedMimeTypes,
    int $maxSize
): array {
    if (!uploaded_file_is_present($file)) {
        return ['ok' => true, 'path' => null];
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'message' => 'Upload failed.'];
    }

    if (($file['size'] ?? 0) > $maxSize) {
        return ['ok' => false, 'message' => 'Uploaded file is too large.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        return ['ok' => false, 'message' => 'Unsupported file type.'];
    }

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $extension = pathinfo((string) $file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(16)) . ($extension ? '.' . strtolower($extension) : '');
    $target = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['ok' => false, 'message' => 'Could not save the uploaded file.'];
    }

    $publicRoot = str_replace('\\', '/', app_path('public'));
    $normalisedTarget = str_replace('\\', '/', $target);
    $relativePath = ltrim(str_replace($publicRoot, '', $normalisedTarget), '/');

    return ['ok' => true, 'path' => $relativePath];
}

function post_image_url(?string $path): string
{
    if (!$path) {
        return asset_url('assets/img/placeholder.svg');
    }

    $prefix = base_url() === '/' ? '' : base_url();
    return $prefix . '/' . ltrim($path, '/');
}

function remember_cookie_name(): string
{
    return (string) app_config('remember_cookie', 'travel_guide_remember');
}

function input_trim(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function page_title(string $title = ''): string
{
    $appName = (string) app_config('name', 'Travel Guide');
    return $title !== '' ? $title . ' | ' . $appName : $appName;
}

function checked(bool $condition): string
{
    return $condition ? 'checked' : '';
}

function selected(string $value, string $expected): string
{
    return $value === $expected ? 'selected' : '';
}