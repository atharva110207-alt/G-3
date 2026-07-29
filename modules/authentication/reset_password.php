<?php
// Practical Assessment System - Reset Password Controller
// Zeal College of Engineering & Research

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Security: Block access if OTP not verified
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true || !isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pass = trim($_POST['new_password'] ?? '');
    $confirm_pass = trim($_POST['confirm_password'] ?? '');
    
    if (empty($new_pass) || empty($confirm_pass)) {
        $error = "All fields are required.";
    } else if ($new_pass !== $confirm_pass) {
        $error = "Passwords do not match.";
    } else if (strlen($new_pass) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Update password (PLAIN TEXT) and clear OTP fields
        $sql = "UPDATE users SET password = ?, reset_otp = NULL, otp_expires_at = NULL WHERE email = ?";
        $stmt = execute_prepared($conn, $sql, "ss", [$new_pass, $email]);
        
        if ($stmt) {
            mysqli_stmt_close($stmt);
            
            // Cleanup session
            unset($_SESSION['reset_email']);
            unset($_SESSION['otp_verified']);
            
            // Set success flash message and redirect to login
            set_flash('success', 'Your password has been reset successfully. You can now login.');
            header("Location: login.php");
            exit();
        } else {
            $error = "Failed to update password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create New Password - <?php echo APP_NAME; ?> | Zeal College</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
  <style>
    /* Aesthetic neon focus logic for matching passwords */
    .match-success {
      border-color: #10b981 !important;
      box-shadow: 0 0 12px rgba(16, 185, 129, 0.5) !important;
    }
    .match-error {
      border-color: #ef4444 !important;
      box-shadow: 0 0 12px rgba(239, 68, 68, 0.5) !important;
    }
  </style>
</head>
<body class="login-page">
  <div class="login-split-container">
    <div class="login-split-left">
      <div class="slideshow-container">
        <div class="slideshow-slide fade" style="background-image: url('../../assets/images/slideshow/slide1.jpg');"></div>
        <div class="slideshow-slide fade" style="background-image: url('../../assets/images/slideshow/slide2.jpg');"></div>
        <div class="slideshow-slide fade" style="background-image: url('../../assets/images/slideshow/slide3.jpg');"></div>
      </div>
      <div class="slideshow-overlay">
        <h1>ZEAL COLLEGE OF ENGINEERING & RESEARCH</h1>
        <p>Practical Assessment System</p>
      </div>
    </div>
    
    <div class="login-split-right">
      <div class="login-modal">
    <div class="login-header">
      <div class="zeal-logo"><i class="fas fa-key"></i></div>
      <div class="institution-title"><?php echo COLLEGE_NAME; ?></div>
      <h2 class="system-title">Create New Password</h2>
      <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0.5rem;">Enter a strong password for your account.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" class="login-form" id="resetForm">
      <div class="form-group">
        <label for="new_password" class="form-label">New Password</label>
        <div class="password-container" style="position: relative;">
          <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6" placeholder="At least 6 characters">
          <i class="fas fa-eye password-toggle" id="toggleNew" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8;"></i>
        </div>
      </div>

      <div class="form-group">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <div class="password-container" style="position: relative;">
          <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6" placeholder="Retype new password">
          <i class="fas fa-eye password-toggle" id="toggleConfirm" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8;"></i>
        </div>
        <div id="passwordError" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: none;">Passwords do not match.</div>
      </div>

      <button type="submit" class="login-btn">
        <i class="fas fa-save me-2"></i> Update Password
      </button>
    </form>
  </div>
    </div>
  </div>

  <script>
    // Toggle Password Visibility
    const togglePassword = (inputId, iconId) => {
      const input = document.getElementById(inputId);
      const icon = document.getElementById(iconId);
      icon.addEventListener('click', () => {
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        }
      });
    };

    togglePassword('new_password', 'toggleNew');
    togglePassword('confirm_password', 'toggleConfirm');

    // Real-time ES6 Client-Side Validation
    const resetForm = document.getElementById('resetForm');
    const pass1 = document.getElementById('new_password');
    const pass2 = document.getElementById('confirm_password');
    const passError = document.getElementById('passwordError');

    const checkMatch = () => {
      if (pass2.value === '') {
        pass2.classList.remove('match-success', 'match-error');
        passError.style.display = 'none';
        return;
      }

      if (pass1.value === pass2.value) {
        pass2.classList.remove('match-error');
        pass2.classList.add('match-success');
        pass1.classList.add('match-success');
        passError.style.display = 'none';
      } else {
        pass2.classList.remove('match-success');
        pass1.classList.remove('match-success');
        pass2.classList.add('match-error');
        passError.style.display = 'block';
      }
    };

    pass1.addEventListener('input', checkMatch);
    pass2.addEventListener('input', checkMatch);

    resetForm.addEventListener('submit', (e) => {
      if (pass1.value !== pass2.value) {
        e.preventDefault();
        pass2.classList.add('match-error');
        passError.style.display = 'block';
      }
    });

    // Simple Slideshow logic
    document.addEventListener('DOMContentLoaded', () => {
      let slideIndex = 0;
      const slides = document.getElementsByClassName("slideshow-slide");
      if(slides.length > 0) {
          function showSlides() {
            for (let i = 0; i < slides.length; i++) {
              slides[i].style.opacity = "0";
            }
            slideIndex++;
            if (slideIndex > slides.length) {slideIndex = 1}
            slides[slideIndex-1].style.opacity = "1";
            setTimeout(showSlides, 4000); // Change image every 4 seconds
          }
          showSlides();
      }
    });
  </script>
</body>
</html>
