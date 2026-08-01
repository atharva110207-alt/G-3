<?php
// Practical Assessment System - Forgot Password Controller
// Zeal College of Engineering & Research

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Include PHPMailer classes manually
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../includes/PHPMailer/SMTP.php';

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = "Please enter your registered email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $sql = "SELECT id, full_name, role FROM users WHERE email = ?";
        $stmt = execute_prepared($conn, $sql, "s", [$email]);
        if ($stmt) {
            $res = mysqli_stmt_get_result($stmt);
            if ($user_db = mysqli_fetch_assoc($res)) {
                // Generate a secure 6-digit OTP
                $otp = sprintf("%06d", random_int(100000, 999999));
                
                // Update DB with OTP and Expiry (10 minutes from NOW)
                $update_sql = "UPDATE users SET reset_otp = ?, otp_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE email = ?";
                $update_stmt = execute_prepared($conn, $update_sql, "ss", [$otp, $email]);
                
                if ($update_stmt) {
                    mysqli_stmt_close($update_stmt);
                    
                    // --- SEND OTP VIA PHPMAILER ---
                    $mail = new PHPMailer(true);
                    
                    try {
                        // Server settings
                        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output if needed
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        
                        // [PLACEHOLDERS FOR SMTP CREDENTIALS]
                        $mail->Username   = 'zealpas@gmail.com'; // e.g. zcoer.practical@gmail.com
                        $mail->Password   = 'ghdqkachbeusawgv';      // 16-character App Password
                        
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;
                    
                        // Recipients
                        $mail->setFrom('zealpas@gmail.com', 'PALPMS Security');
                        $mail->addAddress($email, $user_db['full_name']);
                    
                        // Content
                        $mail->isHTML(false);
                        $mail->Subject = 'Password Reset OTP - ZCOER';
                        $mail->Body    = "Hello " . $user_db['full_name'] . ",\n\n"
                                       . "Your ZCOER Practical Assessment Portal OTP is: [$otp]. It expires in 10 minutes.\n\n"
                                       . "If you did not request this, please ignore this email.";
                    
                        // Send OTP
                        $mail->send(); 
                        
                        // Simulated success since we don't have valid credentials configured yet
                        $_SESSION['reset_email'] = $email;
                        
                        log_audit($conn, $user_db['id'], $user_db['role'], 'OTP Generated', 'authentication', 'OTP sent for password reset.');
                        header('Location: verify_otp.php');
                        exit();
                        
                    } catch (Exception $e) {
                        $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                } else {
                    $error = "Failed to generate OTP. Please try again.";
                }
            } else {
                // To prevent email enumeration, you might want to show a success message anyway,
                // but for internal portals, showing error is often fine.
                $error = "No user found with the provided email address.";
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
  <title>Forgot Password - <?php echo APP_NAME; ?> | Zeal College</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
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
        <img src="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/G-3/assets/images/logos/logo.png'; ?>" alt="ZEAL Logo" style="width: 160px; max-width: 100%; height: auto; margin-bottom: 20px;">
        <h1>ZEAL COLLEGE OF ENGINEERING & RESEARCH</h1>
        <p>Practical Assessment and Laboratory Performance Management System</p>
      </div>
    </div>
    
    <div class="login-split-right">
      <div class="login-modal">
    <div class="login-header">
      <div class="zeal-logo"><i class="fas fa-lock"></i></div>
      <div class="institution-title"><?php echo COLLEGE_NAME; ?></div>
      <h2 class="system-title">Password Recovery</h2>
      <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0.5rem;">Enter your registered email to receive an OTP.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" class="login-form" id="forgotForm">
      <div class="form-group">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="e.g. user@zcoer.edu.in" value="<?php echo sanitize($_POST['email'] ?? ''); ?>" required autofocus>
        <div id="emailError" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: none;">Please enter a valid email address.</div>
      </div>

      <button type="submit" class="login-btn">
        <i class="fas fa-paper-plane me-2"></i> Send OTP
      </button>
    </form>

    <div class="login-footer">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="<?php echo get_role_dashboard($_SESSION['role']); ?>" class="forgot-link">
          <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
      <?php else: ?>
        <a href="login.php" class="forgot-link">
          <i class="fas fa-arrow-left me-1"></i> Back to Login Page
        </a>
      <?php endif; ?>
    </div>
  </div>

    </div>
  </div>

  <script>
    document.getElementById('forgotForm').addEventListener('submit', function(e) {
      const emailInput = document.getElementById('email');
      const emailError = document.getElementById('emailError');
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      
      if (!emailPattern.test(emailInput.value)) {
        e.preventDefault();
        emailInput.style.borderColor = '#ef4444';
        emailError.style.display = 'block';
      } else {
        emailInput.style.borderColor = 'rgba(255, 255, 255, 0.2)';
        emailError.style.display = 'none';
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
  <script>
    document.addEventListener("DOMContentLoaded", () => {
        const toggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const currentTheme = localStorage.getItem('theme') || 'dark'; // Default to dark

        // Apply on load
        document.documentElement.setAttribute('data-theme', currentTheme);
        themeIcon.className = currentTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

        // Toggle Event
        toggleBtn.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-theme');
            let newTheme = theme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            themeIcon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        });
    });
  </script>
</body>
</html>
