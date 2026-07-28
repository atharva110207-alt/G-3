<?php
// Practical Assessment System - Forgot Password Controller
// Zeal College of Engineering & Research

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$message = '';
$error = '';
$found_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = "Please enter your registered email address.";
    } else {
        $sql = "SELECT id, full_name, role, password FROM users WHERE email = ?";
        $stmt = execute_prepared($conn, $sql, "s", [$email]);
        if ($stmt) {
            $res = mysqli_stmt_get_result($stmt);
            if ($user = mysqli_fetch_assoc($res)) {
                $found_password = $user['password'];
                $message = "Account verified for " . $user['full_name'] . " (" . get_role_label($user['role']) . ").";
                log_audit($conn, $user['id'], $user['role'], 'Password Recovery Request', 'authentication', 'Password recovered via forgot password form.');
            } else {
                $error = "No user found with the provided email address.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - <?php echo APP_NAME; ?> | Zeal College</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
</head>
<body class="login-page">
  <div class="login-modal">
    <div class="login-header">
      <div class="zeal-logo"><i class="fas fa-key"></i></div>
      <div class="institution-title"><?php echo COLLEGE_NAME; ?></div>
      <h2 class="system-title">Password Recovery</h2>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
      <div class="alert alert-success" style="margin-bottom: 1.25rem; flex-direction: column; align-items: flex-start; gap: 0.5rem;">
        <div><i class="fas fa-check-circle me-2"></i> <?php echo sanitize($message); ?></div>
        <div style="font-weight: 700; background: rgba(0,0,0,0.2); padding: 0.5rem 1rem; border-radius: 6px; width: 100%;">
          Your Registered Password is: <span style="color: #38bdf8; font-size: 1.1rem;"><?php echo sanitize($found_password); ?></span>
        </div>
      </div>
    <?php endif; ?>

    <form method="POST" action="" class="login-form">
      <div class="form-group">
        <label for="email" class="form-label">Registered Email Address</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="e.g. user@zcoer.edu.in" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" required autofocus>
      </div>

      <button type="submit" class="login-btn">
        <i class="fas fa-search me-2"></i> Recover Password
      </button>
    </form>

    <div class="login-footer">
      <a href="login.php" class="forgot-link">
        <i class="fas fa-arrow-left me-1"></i> Back to Login Page
      </a>
    </div>
  </div>
</body>
</html>
