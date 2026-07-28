<?php
// Reset Password Handler

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $new_pass = $_POST['new_password'] ?? '';
    
    if ($email && $new_pass) {
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = execute_prepared($conn, $sql, "ss", [$new_pass, $email]);
        if ($stmt) {
            mysqli_stmt_close($stmt);
            set_flash('success', 'Password updated successfully! Please log in.');
            header('Location: login.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set New Password - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-header">
            <h1 class="login-title">Set New Password</h1>
        </div>
        <form action="" method="POST">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="Registered email">
            </div>
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" required placeholder="Enter new password">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Update Password</button>
        </form>
    </div>
</body>
</html>
