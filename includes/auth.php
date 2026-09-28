<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';


/**
 * Check whether a user is currently logged in.
 */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}


/**
 * Get the currently authenticated user.
 */
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


/**
 * Authenticate a user using email and password.
 */
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

    // Prevent session fixation after successful authentication.
    regenerate_session();

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['employee_code'] = $user['employee_code'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = normalize_role($user['role']);

    return true;
}


/**
 * Logout the current user.
 */
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


/**
 * Require an authenticated user.
 */
function require_login(): void
{
    if (!is_logged_in() || !current_user()) {
        set_flash('error', 'Please login to continue.');
        redirect(BASE_URL . 'login.php');
    }
}


/**
 * Check whether the current user has a specific role.
 */
function has_role(string $role): bool
{
    $user = current_user();

    if (!$user) {
        return false;
    }

    return normalize_role($user['role']) === normalize_role($role);
}


/**
 * Check whether the current user has at least one
 * role from the supplied list.
 */
function has_any_role(array $roles): bool
{
    $user = current_user();

    if (!$user) {
        return false;
    }

    $currentRole = normalize_role($user['role']);

    foreach ($roles as $role) {
        if ($currentRole === normalize_role($role)) {
            return true;
        }
    }

    return false;
}


/**
 * Require a specific role.
 */
function require_role(string $role): void
{
    require_login();

    if (!has_role($role)) {
        http_response_code(403);
        exit('403 - You are not authorized to access this area.');
    }
}


/**
 * Require at least one role from the supplied list.
 */
function require_any_role(array $roles): void
{
    require_login();

    if (!has_any_role($roles)) {
        http_response_code(403);
        exit('403 - You are not authorized to access this area.');
    }
}


/**
 * Check whether the current user is a Super Admin.
 *
 * Until the dedicated role migration is fully standardized,
 * both superadmin and the existing Admin role are treated
 * as top-level administrative roles.
 */
function is_super_admin(): bool
{
    $user = current_user();

    if (!$user) {
        return false;
    }

    return in_array(
        normalize_role($user['role']),
        ['superadmin', 'super_admin', 'admin'],
        true
    );
}


/**
 * Require Super Admin access.
 */
function require_super_admin(): void
{
    require_login();

    if (!is_super_admin()) {
        http_response_code(403);
        exit('403 - You are not authorized to access this area.');
    }
}