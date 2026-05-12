<?php

declare(strict_types=1);

class AuthController
{
    public function login(): void
    {
        $errors = [];
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();

            $email = input_trim('email');
            $password = (string) ($_POST['password'] ?? '');
            $rememberMe = isset($_POST['remember_me']);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address.';
            }

            if ($password === '') {
                $errors['password'] = 'Password is required.';
            }

            if ($errors === []) {
                $user = (new User())->findByEmail($email);
                if (!$user || !password_verify($password, $user['password_hash'])) {
                    $errors['general'] = 'Invalid email or password.';
                } else {
                    Auth::login($user, $rememberMe);

                    if ((int) $user['is_verified'] !== 1) {
                        flash('success', 'Logged in successfully. Your account is pending admin approval.');
                        redirect_to('home/index');
                    }

                    flash('success', 'Welcome back, ' . $user['name'] . '!');
                    redirect_to('home/index');
                }
            }
        }

        render('auth/login', [
            'pageTitle' => 'Login',
            'errors' => $errors,
            'email' => $email,
            'pageScripts' => ['assets/js/auth.js'],
        ]);
    }

    public function register(): void
    {
        $errors = [];
        $data = [
            'name' => '',
            'email' => '',
            'role' => 'user',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();

            $data['name'] = input_trim('name');
            $data['email'] = input_trim('email');
            $data['role'] = (string) ($_POST['role'] ?? 'user');
            $password = (string) ($_POST['password'] ?? '');
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

            $errors = $this->validateRegistration($data, $password, $confirmPassword);

            if ($errors === []) {
                (new User())->create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $password,
                    'role' => $data['role'],
                    'is_verified' => 0,
                ]);

                flash('success', 'Registration completed. Please log in. Your account must be verified by an admin first.');
                redirect_to('auth/login');
            }
        }

        render('auth/register', [
            'pageTitle' => 'Register',
            'errors' => $errors,
            'data' => $data,
            'pageScripts' => ['assets/js/auth.js'],
        ]);
    }

    public function profile(): void
    {
        Auth::requireLogin();

        $userModel = new User();
        $user = $userModel->findById((int) Auth::id());
        $errors = [];

        if (!$user) {
            Auth::logout();
            redirect_to('auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_csrf();

            $name = input_trim('name');
            $email = input_trim('email');
            $currentPassword = (string) ($_POST['current_password'] ?? '');
            $newPassword = (string) ($_POST['new_password'] ?? '');
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
            $profilePicturePath = $user['profile_picture'];

            if ($name === '') {
                $errors['name'] = 'Name is required.';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address.';
            } elseif ($userModel->emailExists($email, (int) $user['id'])) {
                $errors['email'] = 'This email is already taken.';
            }

            if (uploaded_file_is_present($_FILES['profile_picture'] ?? [])) {
                $upload = store_uploaded_file(
                    $_FILES['profile_picture'],
                    (string) app_config('profile_upload_dir'),
                    ['image/jpeg', 'image/png', 'image/webp'],
                    (int) app_config('max_profile_upload_size')
                );

                if (!$upload['ok']) {
                    $errors['profile_picture'] = $upload['message'];
                } else {
                    $profilePicturePath = $upload['path'];
                }
            }

            if ($newPassword !== '' || $confirmPassword !== '' || $currentPassword !== '') {
                if ($currentPassword === '' || !password_verify($currentPassword, $user['password_hash'])) {
                    $errors['current_password'] = 'Current password is incorrect.';
                }

                if (strlen($newPassword) < 8) {
                    $errors['new_password'] = 'New password must be at least 8 characters.';
                }

                if ($newPassword !== $confirmPassword) {
                    $errors['confirm_password'] = 'Passwords do not match.';
                }
            }

            if ($errors === []) {
                $userModel->updateProfile((int) $user['id'], [
                    'name' => $name,
                    'email' => $email,
                    'profile_picture' => $profilePicturePath,
                ]);

                if ($newPassword !== '') {
                    $userModel->updatePassword((int) $user['id'], $newPassword);
                }

                Auth::refreshUser();
                flash('success', 'Profile updated successfully.');
                redirect_to('auth/profile');
            }

            $user['name'] = $name;
            $user['email'] = $email;
            $user['profile_picture'] = $profilePicturePath;
        }

        render('auth/profile', [
            'pageTitle' => 'Profile',
            'errors' => $errors,
            'user' => $user,
            'pageScripts' => ['assets/js/auth.js'],
        ]);
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'You have been logged out.');
        redirect_to('home/index');
    }

    private function validateRegistration(array $data, string $password, string $confirmPassword): array
    {
        $errors = [];
        $allowedRoles = ['admin', 'scout', 'user'];

        if ($data['name'] === '') {
            $errors['name'] = 'Name is required.';
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif ((new User())->emailExists($data['email'])) {
            $errors['email'] = 'This email is already registered.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        if (!in_array($data['role'], $allowedRoles, true)) {
            $errors['role'] = 'Please choose a valid role.';
        }

        return $errors;
    }
}
