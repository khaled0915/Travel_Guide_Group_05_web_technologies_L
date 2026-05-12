<?php

declare(strict_types=1);

$route = $_GET['route'] ?? 'home/index';

date_default_timezone_set('Asia/Dhaka');
require_once dirname(__DIR__) . '/config/helpers.php';

spl_autoload_register(static function (string $class): void {
    foreach (['config', 'models', 'controllers'] as $directory) {
        $path = dirname(__DIR__) . '/' . $directory . '/' . $class . '.php';
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

Auth::start();
Auth::restoreFromRememberCookie();
$routes = require app_path('config/routes.php');
$GLOBALS['app_current_route'] = $route;

if (!isset($routes[$route])) {
    http_response_code(404);
    render('partials/not-found', [
        'pageTitle' => 'Page Not Found',
    ]);
    exit;
}

[$controllerClass, $action] = $routes[$route];
$controller = new $controllerClass();
$controller->{$action}();
