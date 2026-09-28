<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    static $user = false;

    if ($user !== false) {
        return $user;
    }

    if (!is_logged_in()) {
        $user = null;
        return null;
    }

    global $pdo;

    $stmt = $pdo->prepare('
        SELECT id, employee_code, email, role, status
        FROM users
        WHERE id = ?
        LIMIT 1
    ');
    $stmt->execute([(int) $_SESSION['user_id']]);
    $user = $stmt->fetch() ?: null;

    if (!$user || normalize_role($user['status']) !== 'active') {
        logout_user(false);
        return null;
    }

    return $user;
}

function login_user(string $email, string $password): bool
{
    global $pdo;

    $stmt = $pdo->prepare('
        SELECT id, employee_code, email, password, role, status
        FROM users
        WHERE email = ?
        LIMIT 1
    ');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || normalize_role($user['status']) !== 'active') {
        return false;
    }

    if (!password_verify($password, $user['password'])) {
        return false;
    }

    regenerate_session();

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['employee_code'] = $user['employee_code'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = normalize_role($user['role']);

    return true;
}

function logout_user(bool $redirectToLogin = true): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    if ($redirectToLogin) {
        redirect(BASE_URL . 'login.php');
    }
}

function require_login(): void
{
    if (!is_logged_in() || !current_user()) {
        set_flash('error', 'Please login to continue.');
        redirect(BASE_URL . 'login.php');
    }
}

function is_super_admin(): bool
{
    $user = current_user();

    if (!$user) {
        return false;
    }

    // Until the dedicated super_admin role migration is introduced,
    // the existing Admin role is treated as the system's top-level role.
    return in_array(normalize_role($user['role']), ['admin', 'super_admin', 'superadmin'], true);
}

function require_super_admin(): void
{
    require_login();

    if (!is_super_admin()) {
        http_response_code(403);
        exit('403 - You are not authorized to access this area.');
    }
}
