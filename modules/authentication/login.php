<?php
// Practical Assessment System - Login Controller
// Zeal College of Engineering & Research

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// --- AUTO-LOGIN LOGIC ---
if (!is_logged_in() && (isset($_COOKIE['remember_user']) || isset($_COOKIE['pas_user']))) {
    $cookie_user_id = intval($_COOKIE['remember_user'] ?? $_COOKIE['pas_user']);
    if ($cookie_user_id > 0) {
        $sql = "SELECT id, full_name, email, role, student_roll_no, zprn, class, division FROM users WHERE id = ? LIMIT 1";
        $stmt = execute_prepared($conn, $sql, "i", [$cookie_user_id]);
        if ($stmt) {
            $res = mysqli_stmt_get_result($stmt);
            if ($user = mysqli_fetch_assoc($res)) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['student_roll_no'] = $user['student_roll_no'];
                $_SESSION['zprn'] = $user['zprn'];
                $_SESSION['class'] = $user['class'] ?? 'TY';
                $_SESSION['division'] = $user['division'] ?? 'Division C';
                $_SESSION['academic_year'] = DEFAULT_ACADEMIC_YEAR;
                $_SESSION['class_filter'] = $user['class'] ?? 'TY';

                log_audit($conn, $user['id'], $user['role'], 'Auto Login', 'authentication', 'Logged in via Remember Me cookie.');
                
                header('Location: ' . get_role_dashboard($user['role']));
                exit();
            }
            mysqli_stmt_close($stmt);
        }
    }
}
// ------------------------

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . get_role_dashboard(get_user_role()));
    exit();
}

$error = '';
$selected_role = sanitize($_POST['role'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = sanitize($_POST['identity'] ?? $_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = sanitize($_POST['role'] ?? '');
    
    if (empty($identity) || empty($password)) {
        $error = "Please enter Username / Registration No / Email and Password.";
    } else {
        $user = null;
        
        // 1. Try matching with explicit role filter if a role card was selected
        if (!empty($role)) {
            $sql = "SELECT id, full_name, email, password, role, student_roll_no, zprn, class, division, phone FROM users 
                    WHERE (email = ? OR student_roll_no = ? OR zprn = ? OR full_name = ? OR phone = ?) AND LOWER(role) = LOWER(?) LIMIT 1";
            $stmt = execute_prepared($conn, $sql, "ssssss", [$identity, $identity, $identity, $identity, $identity, $role]);
            if ($stmt) {
                $res = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($res);
                mysqli_stmt_close($stmt);
            }
        }
        
        // 2. Fallback: match identity regardless of role selection if no record was found above
        if (!$user) {
            $sql = "SELECT id, full_name, email, password, role, student_roll_no, zprn, class, division, phone FROM users 
                    WHERE (email = ? OR student_roll_no = ? OR zprn = ? OR full_name = ? OR phone = ?) LIMIT 1";
            $stmt = execute_prepared($conn, $sql, "sssss", [$identity, $identity, $identity, $identity, $identity]);
            if ($stmt) {
                $res = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($res);
                mysqli_stmt_close($stmt);
            }
        }
        
        if ($user) {
            // Check plain-text password directly
            if ($password === $user['password']) {
                $is_authorized = false;
                $active_role = $user['role']; // Default

                if (empty($role) || strtolower($role) === strtolower($user['role'])) {
                    $is_authorized = true;
                    $active_role = $user['role'];
                } else if (strtolower($user['role']) === 'faculty') {
                    if (strtolower($role) === 'gfm') {
                        $chk_sql = "SELECT id FROM gfm_allocations WHERE gfm_id = ? LIMIT 1";
                        $chk_stmt = execute_prepared($conn, $chk_sql, "i", [$user['id']]);
                        if ($chk_stmt) {
                            if (mysqli_stmt_get_result($chk_stmt)->num_rows > 0) {
                                $is_authorized = true;
                                $active_role = 'gfm';
                            }
                            mysqli_stmt_close($chk_stmt);
                        }
                    } else if (strtolower($role) === 'class_teacher') {
                        $chk_sql = "SELECT id FROM class_teacher_allocations WHERE class_teacher_id = ? LIMIT 1";
                        $chk_stmt = execute_prepared($conn, $chk_sql, "i", [$user['id']]);
                        if ($chk_stmt) {
                            if (mysqli_stmt_get_result($chk_stmt)->num_rows > 0) {
                                $is_authorized = true;
                                $active_role = 'class_teacher';
                            }
                            mysqli_stmt_close($chk_stmt);
                        }
                    }
                }

                if (!$is_authorized) {
                    $error = "Error: Please select your correct registered user role or you are not assigned to this role.";
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = strtolower($active_role);
                    $_SESSION['student_roll_no'] = $user['student_roll_no'];
                    $_SESSION['zprn'] = $user['zprn'];
                    $_SESSION['class'] = $user['class'] ?? 'TY';
                    $_SESSION['division'] = $user['division'] ?? 'Division C';
                    $_SESSION['academic_year'] = DEFAULT_ACADEMIC_YEAR;
                    $_SESSION['class_filter'] = $user['class'] ?? 'TY';

                    log_audit($conn, $user['id'], $_SESSION['role'], 'User Login', 'authentication', 'Logged in via credentials.');
                    
                    // Remember Me Logic
                    if (isset($_POST['remember_me'])) {
                        setcookie("remember_user", $user['id'], time() + (86400 * 30), "/");
                        setcookie("pas_role", $_SESSION['role'], time() + (86400 * 30), "/");
                    }

                    set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
                    header('Location: ' . get_role_dashboard($_SESSION['role']));
                    exit();
                }
            } else {
                $error = "Invalid Credentials or Password. Please try again.";
            }
        } else {
            $error = "No account found matching the provided Registration No / Email / Username.";
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
  <title>Login - <?php echo APP_NAME; ?> | Zeal College</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
  <style>
    .role-card { transition: all 0.3s ease; }
    .role-card[data-role="admin"]:hover, .role-card[data-role="admin"].selected { border-color: #3b82f6 !important; box-shadow: 0 0 15px rgba(59, 130, 246, 0.6) !important; }
    .role-card[data-role="hod"]:hover, .role-card[data-role="hod"].selected { border-color: #a855f7 !important; box-shadow: 0 0 15px rgba(168, 85, 247, 0.6) !important; }
    .role-card[data-role="faculty"]:hover, .role-card[data-role="faculty"].selected { border-color: #10b981 !important; box-shadow: 0 0 15px rgba(16, 185, 129, 0.6) !important; }
    .role-card[data-role="class_teacher"]:hover, .role-card[data-role="class_teacher"].selected { border-color: #eab308 !important; box-shadow: 0 0 15px rgba(234, 179, 8, 0.6) !important; }
    .role-card[data-role="gfm"]:hover, .role-card[data-role="gfm"].selected { border-color: #f97316 !important; box-shadow: 0 0 15px rgba(249, 115, 22, 0.6) !important; }
    .role-card[data-role="student"]:hover, .role-card[data-role="student"].selected { border-color: #06b6d4 !important; box-shadow: 0 0 15px rgba(6, 182, 212, 0.6) !important; }
    .role-card[data-role="parent"]:hover, .role-card[data-role="parent"].selected { border-color: #f43f5e !important; box-shadow: 0 0 15px rgba(244, 63, 94, 0.6) !important; }
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
        <img src="../../assets/images/logos/logo.png" alt="ZEAL Logo" style="width: 160px; height: auto; margin-bottom: 20px;">
        <h1>ZEAL COLLEGE OF ENGINEERING & RESEARCH</h1>
        <p>Practical Assessment and Laboratory Performance Management System</p>
      </div>
    </div>
    
    <div class="login-split-right">
      <div class="login-modal">
        <div class="login-header">
          <img src="../../assets/images/logos/banner.png" alt="" class="login-banner">
          <h2 class="system-title" style="font-size: 1.2rem; line-height: 1.4; text-align: center; margin-bottom: 20px; white-space: normal;">Practical Assessment and Laboratory Performance Management System</h2>
        </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.25rem;">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?>
      </div>
    <?php endif; ?>

    <?php $flash = get_flash(); if ($flash): ?>
      <div class="alert alert-<?php echo $flash['type']; ?>" style="margin-bottom: 1.25rem;">
        <i class="fas fa-info-circle me-2"></i> <?php echo sanitize($flash['message']); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" class="login-form" id="loginForm">
      <input type="hidden" name="role" id="selected_role" value="<?php echo sanitize($selected_role); ?>">

      <!-- USER ROLE SELECTION GRID -->
      <div class="role-grid-container">
        <label class="form-label" style="text-align: center; margin-bottom: 0.75rem; color: #cbd5e1;">
          <i class="fas fa-user-tag me-1"></i> Select User Role
        </label>

        <div class="role-grid">
          <div class="role-card" data-role="admin" onclick="selectRole('admin', this)">
            <span class="role-icon">🛡️</span>
            <span class="role-title">Admin</span>
          </div>

          <div class="role-card" data-role="hod" onclick="selectRole('hod', this)">
            <span class="role-icon">🏛️</span>
            <span class="role-title">HOD</span>
          </div>

          <div class="role-card" data-role="faculty" onclick="selectRole('faculty', this)">
            <span class="role-icon">👨‍🏫</span>
            <span class="role-title">Subject Faculty</span>
          </div>

          <div class="role-card" data-role="gfm" onclick="selectRole('gfm', this)">
            <span class="role-icon">📋</span>
            <span class="role-title">GFM</span>
          </div>

          <div class="role-card" data-role="class_teacher" onclick="selectRole('class_teacher', this)">
            <span class="role-icon">📊</span>
            <span class="role-title">Class Teacher</span>
          </div>

          <div class="role-card" data-role="student" onclick="selectRole('student', this)">
            <span class="role-icon">🎓</span>
            <span class="role-title">Student</span>
          </div>

          <div class="role-card" data-role="parent" onclick="selectRole('parent', this)">
            <span class="role-icon">👨‍👩‍👧</span>
            <span class="role-title">Parent</span>
          </div>
        </div>

      </div>

      <!-- IDENTITY INPUT FIELD -->
      <div class="form-group">
        <label for="identity" class="form-label"><i class="fas fa-user me-1"></i> Username / Registration No / Email / Mobile</label>
        <input type="text" id="identity" name="identity" class="form-control" placeholder="e.g. EC1301, 9876543210 or admin@zcoer.edu.in" value="<?php echo sanitize($_POST['identity'] ?? $_POST['email'] ?? ''); ?>" required autofocus>
      </div>

      <!-- PASSWORD FIELD -->
      <div class="form-group">
        <label for="password" class="form-label"><i class="fas fa-lock me-1"></i> Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
      </div>

      <!-- Remember Me Checkbox -->
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
        <input type="checkbox" name="remember_me" id="remember_me" style="cursor: pointer; width: 16px; height: 16px; accent-color: var(--primary-color);">
        <label for="remember_me" style="color: var(--text-primary); cursor: pointer; font-size: 0.9rem; user-select: none;">Remember me</label>
      </div>

      <button type="submit" class="login-btn">
        <i class="fas fa-sign-in-alt me-2"></i> Sign In to Portal
      </button>
    </form>

    <div class="login-footer">
      <a href="forgot_password.php" class="forgot-link">
        <i class="fas fa-key me-1"></i> Forgot Password?
      </a>
    </div>
  </div>
    </div>
  </div>

  <script>
  function selectRole(role, elem) {
    document.getElementById('selected_role').value = role;

    document.querySelectorAll('.role-card').forEach(card => card.classList.remove('selected'));
    elem.classList.add('selected');

  }

  // Restore previous selected role if form was reloaded
  document.addEventListener('DOMContentLoaded', () => {
    // Form Validation for Role
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      const selectedRole = document.getElementById('selected_role').value;
      if (!selectedRole) {
        e.preventDefault();
        alert('Please select a User Role from the grid above before signing in.');
      }
    });

    const savedRole = document.getElementById('selected_role').value;
    if (savedRole) {
      const card = document.querySelector(`.role-card[data-role="${savedRole}"]`);
      if (card) {
        selectRole(savedRole, card);
      }
    }
    
    // Simple Slideshow logic
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