<?php
// Practical Assessment System - Reset Password Module
// Zeal College of Engineering & Research

$page_title = "Reset Password";
require_once __DIR__ . '/../../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_pass = trim($_POST['current_password'] ?? '');
    $new_pass = trim($_POST['new_password'] ?? '');
    $confirm_pass = trim($_POST['confirm_password'] ?? '');
    
    if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
        $error = "All password fields are required.";
    } else if ($new_pass !== $confirm_pass) {
        $error = "New password and Confirm password do not match.";
    } else {
        // Verify existing password
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = execute_prepared($conn, $sql, "i", [$user['id']]);
        if ($stmt) {
            $res = mysqli_stmt_get_result($stmt);
            $user_db = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);
            
            if ($user_db && $user_db['password'] === $current_pass) {
                // Update password (PLAIN TEXT)
                $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                $up_stmt = execute_prepared($conn, $update_sql, "si", [$new_pass, $user['id']]);
                if ($up_stmt) {
                    mysqli_stmt_close($up_stmt);
                    log_audit($conn, $user['id'], $user['role'], 'Reset Password', 'authentication', 'Updated user password successfully.');
                    $success = "Your password has been reset successfully!";
                } else {
                    $error = "Failed to update password in database.";
                }
            } else {
                $error = "Incorrect current password.";
            }
        }
    }
}
?>

<div class="card" style="max-width: 600px; margin: 2rem auto;">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-key text-primary me-2"></i> Reset Account Password</h3>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?></div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i> <?php echo sanitize($success); ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="form-group">
      <label for="current_password" class="form-label">Current Password</label>
      <input type="password" id="current_password" name="current_password" class="form-control" required placeholder="Enter current password">
    </div>

    <div class="form-group">
      <label for="new_password" class="form-label">New Password</label>
      <input type="password" id="new_password" name="new_password" class="form-control" required placeholder="Enter new password">
    </div>

    <div class="form-group">
      <label for="confirm_password" class="form-label">Confirm New Password</label>
      <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="Confirm new password">
    </div>

    <button type="submit" class="btn btn-primary">
      <i class="fas fa-save me-2"></i> Update Password
    </button>
  </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
