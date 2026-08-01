<?php
// Practical Assessment System - OTP Verification
// Zeal College of Engineering & Research

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Ensure user has requested an OTP
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Reconstruct OTP from the 6 inputs
    $otp_arr = $_POST['otp'] ?? [];
    $entered_otp = implode('', $otp_arr);
    
    if (strlen($entered_otp) !== 6 || !is_numeric($entered_otp)) {
        $error = "Please enter a valid 6-digit OTP.";
    } else {
        $sql = "SELECT id, reset_otp, (otp_expires_at >= NOW()) as is_valid FROM users WHERE email = ?";
        $stmt = execute_prepared($conn, $sql, "s", [$email]);
        
        if ($stmt) {
            $res = mysqli_stmt_get_result($stmt);
            if ($user_db = mysqli_fetch_assoc($res)) {
                $db_otp = $user_db['reset_otp'];
                $is_valid = $user_db['is_valid'];
                
                if ($db_otp === $entered_otp) {
                    if ($is_valid == 1) {
                        // OTP is valid and not expired
                        $_SESSION['otp_verified'] = true;
                        header("Location: reset_password.php");
                        exit();
                    } else {
                        $error = "OTP has expired. Please request a new one.";
                    }
                } else {
                    $error = "Invalid OTP. Please try again.";
                }
            } else {
                $error = "Session invalid. Please restart the process.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <script>
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
  </script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <title>Verify OTP - <?php echo APP_NAME; ?> | Zeal College</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
  <style>
    .otp-container {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin: 1.5rem 0;
    }
    .otp-input {
      width: 45px;
      height: 55px;
      text-align: center;
      font-size: 1.5rem;
      font-weight: 700;
      background: var(--bg-canvas);
      border: 1px solid var(--border-color);
      border-radius: 8px;
      color: var(--text-primary);
      transition: all 0.3s ease;
    }
    .otp-input:focus {
      outline: none;
      border-color: #6366f1;
      box-shadow: 0 0 12px rgba(99, 102, 241, 0.5);
      background: var(--bg-canvas);
    }
    /* Hide number arrows */
    .otp-input::-webkit-outer-spin-button,
    .otp-input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    .otp-input[type=number] {
      -moz-appearance: textfield;
    }
  </style>
</head>
<body class="login-page">
  <button id="themeToggle" style="position: absolute; top: 20px; right: 20px; z-index: 9999; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; border: 1px solid var(--text-muted); background: var(--bg-card); color: var(--text-primary);">
     <i class="fas fa-moon" id="themeIcon"></i>
  </button>
  <div class="login-split-container">
    <div class="login-split-left">
      <div class="slideshow-container">
        <div class="slideshow-slide fade" style="background-image: url('../../assets/images/background/background.jpeg'); opacity: 1;"></div>
      </div>
      <div class="slideshow-overlay">
        <img src="../../assets/images/logos/logo.png" alt="ZEAL Logo" style="width: 160px; max-width: 100%; height: auto; margin-bottom: 20px;">
        <h1>ZEAL COLLEGE OF ENGINEERING & RESEARCH</h1>
        <p>Practical Assessment and Laboratory Performance Management System</p>
      </div>
    </div>
    
    <div class="login-split-right">
      <div class="login-modal">
    <div class="login-header">
      <div class="zeal-logo"><i class="fas fa-shield-alt"></i></div>
      <div class="institution-title"><?php echo COLLEGE_NAME; ?></div>
      <h2 class="system-title">Verify OTP</h2>
      <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0.5rem;">Enter the 6-digit code sent to<br><strong><?php echo sanitize($email); ?></strong></p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" class="login-form">
      <div class="otp-container" id="otp-container">
        <input type="text" name="otp[]" class="otp-input" maxlength="1" pattern="[0-9]" required autofocus>
        <input type="text" name="otp[]" class="otp-input" maxlength="1" pattern="[0-9]" required>
        <input type="text" name="otp[]" class="otp-input" maxlength="1" pattern="[0-9]" required>
        <input type="text" name="otp[]" class="otp-input" maxlength="1" pattern="[0-9]" required>
        <input type="text" name="otp[]" class="otp-input" maxlength="1" pattern="[0-9]" required>
        <input type="text" name="otp[]" class="otp-input" maxlength="1" pattern="[0-9]" required>
      </div>

      <button type="submit" class="login-btn">
        <i class="fas fa-check-circle me-2"></i> Verify Code
      </button>
    </form>

    <div class="login-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
      <a href="forgot_password.php" class="forgot-link">
        <i class="fas fa-arrow-left me-1"></i> Change Email
      </a>
      <a href="forgot_password.php" class="forgot-link" style="color: #6366f1;">
        Resend OTP <i class="fas fa-sync-alt ms-1"></i>
      </a>
    </div>
  </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
        const toggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const currentTheme = localStorage.getItem('theme') || 'dark'; // Default to dark

        // Apply on load
        document.documentElement.setAttribute('data-theme', currentTheme);
        if(themeIcon) themeIcon.className = currentTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

        // Toggle Event
        if(toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                let theme = document.documentElement.getAttribute('data-theme');
                let newTheme = theme === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                themeIcon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            });
        }
    });

    const inputs = document.querySelectorAll('.otp-input');

    inputs.forEach((input, index) => {
      input.addEventListener('input', (e) => {
        // Ensure only numbers are entered
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
        
        if (e.target.value !== '') {
          if (index < inputs.length - 1) {
            inputs[index + 1].focus();
          }
        }
      });

      input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && e.target.value === '') {
          if (index > 0) {
            inputs[index - 1].focus();
            inputs[index - 1].value = '';
          }
        }
      });
      
      // Handle paste
      input.addEventListener('paste', (e) => {
          e.preventDefault();
          const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
          if (pastedData) {
              const chars = pastedData.split('');
              for (let i = 0; i < chars.length; i++) {
                  if (inputs[i]) {
                      inputs[i].value = chars[i];
                      if (i < inputs.length - 1) {
                          inputs[i+1].focus();
                      } else {
                          inputs[i].focus();
                      }
                  }
              }
          }
      });
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
