<?php

require_once __DIR__ . '/includes/auth.php';

if (is_logged_in() && current_user()) {
    redirect(BASE_URL . 'admin/dashboard.php');
}

$error = '';
$flash = get_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your session has expired. Please refresh the page and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($password === '') {
            $error = 'Please enter your password.';
        } elseif (login_user($email, $password)) {
            redirect(BASE_URL . 'admin/dashboard.php');
        } else {
            $error = 'Invalid email or password, or your account is inactive.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(SITE_NAME); ?> Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="left-panel">
            <div>
                <h1>Company CRM</h1>
                <p class="tagline">Employee &amp; Business Operations Platform</p>
                <ul>
                    <li>Attendance &amp; HR Operations</li>
                    <li>Employee &amp; Team Management</li>
                    <li>Projects &amp; Work Management</li>
                    <li>Reports &amp; Business Insights</li>
                    <li>Secure Role-Based Access</li>
                </ul>
            </div>
        </div>

        <div class="right-panel">
            <form class="login-form" method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">

                <h2>Welcome Back</h2>
                <p>Please login to access your workspace.</p>

                <?php if ($flash): ?>
                    <div class="login-message <?= e($flash['type']); ?>">
                        <?= e($flash['message']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <div class="login-message error">
                        <?= e($error); ?>
                    </div>
                <?php endif; ?>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter email" value="<?= old('email'); ?>" autocomplete="username" required>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="password-box">
                        <input type="password" id="password" name="password" placeholder="Enter password" autocomplete="current-password" required>
                        <i class="fa-solid fa-eye" id="togglePassword" aria-label="Show password"></i>
                    </div>
                </div>

                <div class="options">
                    <label>
                        <input type="checkbox" name="remember" value="1">
                        Remember Me
                    </label>
                    <span>Secure access</span>
                </div>

                <button id="loginBtn" type="submit">Login</button>
            </form>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>
