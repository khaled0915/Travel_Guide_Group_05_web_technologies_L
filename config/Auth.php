<?php

declare(strict_types=1);

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function user(): ?array
    {
        self::start();
        if (isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])) {
            return $_SESSION['auth_user'];
        }

        return null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isVerified(): bool
    {
        $user = self::user();
        return $user !== null && (int) $user['is_verified'] === 1;
    }

    public static function hasRole(string ...$roles): bool
    {
        $user = self::user();
        return $user !== null && in_array($user['role'], $roles, true);
    }

    public static function login(array $user, bool $rememberMe = false): void
    {
        self::start();
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['is_verified'] = (int) $user['is_verified'];
        $_SESSION['auth_user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'is_verified' => (int) $user['is_verified'],
            'profile_picture' => $user['profile_picture'] ?? null,
        ];

        if ($rememberMe) {
            $token = bin2hex(random_bytes(32));
            (new User())->updateRememberToken((int) $user['id'], hash('sha256', $token));
            setcookie(
                remember_cookie_name(),
                $user['id'] . ':' . $token,
                [
                    'expires' => time() + ((int) app_config('remember_days', 30) * 86400),
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]
            );
        }
    }

    public static function logout(): void
    {
        self::start();
        $userId = self::id();
        if ($userId !== null) {
            (new User())->clearRememberToken($userId);
        }

        setcookie(remember_cookie_name(), '', time() - 3600, '/');
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], '', false, true);
        }

        session_destroy();
    }

    public static function restoreFromRememberCookie(): void
    {
        self::start();

        if (self::check()) {
            return;
        }

        $cookie = $_COOKIE[remember_cookie_name()] ?? '';
        if (!is_string($cookie) || !str_contains($cookie, ':')) {
            return;
        }

        [$userId, $token] = explode(':', $cookie, 2);
        $user = (new User())->findById((int) $userId);
        if (!$user || empty($user['remember_token'])) {
            return;
        }

        if (!hash_equals((string) $user['remember_token'], hash('sha256', $token))) {
            return;
        }

        self::login($user, false);
    }

    public static function refreshUser(): void
    {
        $userId = self::id();
        if ($userId === null) {
            return;
        }

        $user = (new User())->findById($userId);
        if ($user) {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['is_verified'] = (int) $user['is_verified'];
            $_SESSION['auth_user'] = [
                'id' => (int) $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'is_verified' => (int) $user['is_verified'],
                'profile_picture' => $user['profile_picture'] ?? null,
            ];
        }
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            flash('error', 'Please log in to continue.');
            redirect_to('auth/login');
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        if (!in_array((string) $_SESSION['role'], $roles, true)) {
            flash('error', 'You are not allowed to access that page.');
            redirect_to('home/index');
        }
    }

    public static function requireVerified(): void
    {
        self::requireLogin();
        if ((int) $_SESSION['is_verified'] !== 1) {
            flash('error', 'Your account is pending admin approval.');
            redirect_to('home/index');
        }
    }
}