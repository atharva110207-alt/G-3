<?php
// Forgot Password Request Handler

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $msg = 'If your email is registered in our system, a password recovery link has been generated. (Demo Mode: Password reset is direct via reset_password.php)';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-header">
            <h1 class="login-title">Reset Password</h1>
            <p class="login-subtitle">Enter your email to receive recovery steps</p>
        </div>
        <?php if ($msg): ?>
            <div class="alert alert-info"><?php echo $msg; ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="Enter registered email">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Send Reset Link</button>
        </form>
        <div style="text-align: center; margin-top: 1rem;">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</body>
</html>
