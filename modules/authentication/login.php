<?php
// Practical Assessment System - Login Controller
// Zeal College of Engineering & Research

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

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
        
        // 1. Try matching with explicit role filter if role card selected
        if (!empty($role)) {
            $sql = "SELECT id, full_name, email, password, role, student_roll_no, zprn, class, division FROM users 
                    WHERE (email = ? OR student_roll_no = ? OR zprn = ? OR full_name = ?) AND role = ?";
            $stmt = execute_prepared($conn, $sql, "sssss", [$identity, $identity, $identity, $identity, $role]);
            if ($stmt) {
                $res = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($res);
                mysqli_stmt_close($stmt);
            }
        }
        
        // 2. Fallback to matching identity regardless of role filter
        if (!$user) {
            $sql = "SELECT id, full_name, email, password, role, student_roll_no, zprn, class, division FROM users 
                    WHERE (email = ? OR student_roll_no = ? OR zprn = ? OR full_name = ?)";
            $stmt = execute_prepared($conn, $sql, "ssss", [$identity, $identity, $identity, $identity]);
            if ($stmt) {
                $res = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($res);
                mysqli_stmt_close($stmt);
            }
        }
        
        if ($user) {
            // Verify PLAIN TEXT password per requirements specification
            if ($password === $user['password']) {
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

                log_audit($conn, $user['id'], $user['role'], 'User Login', 'authentication', 'Logged in via username/email/roll credentials.');
                
                set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
                header('Location: ' . get_role_dashboard($user['role']));
                exit();
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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - <?php echo APP_NAME; ?> | Zeal College</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
</head>
<body class="login-page">
  <div class="login-modal">
    <div class="login-header">
      <div class="zeal-logo">Z</div>
      <div class="institution-title"><?php echo COLLEGE_NAME; ?></div>
      <div class="department-title"><?php echo DEPARTMENT_NAME; ?></div>
      <h2 class="system-title"><?php echo APP_NAME; ?></h2>
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
          <div class="role-card" data-role="student" onclick="selectRole('student', this)">
            <span class="role-icon">🎓</span>
            <span class="role-title">Student</span>
          </div>

          <div class="role-card" data-role="faculty" onclick="selectRole('faculty', this)">
            <span class="role-icon">👨‍🏫</span>
            <span class="role-title">Subject Faculty</span>
          </div>

          <div class="role-card" data-role="admin" onclick="selectRole('admin', this)">
            <span class="role-icon">🛡️</span>
            <span class="role-title">Admin</span>
          </div>

          <div class="role-card" data-role="hod" onclick="selectRole('hod', this)">
            <span class="role-icon">🏛️</span>
            <span class="role-title">HOD</span>
          </div>

          <div class="role-card" data-role="gfm" onclick="selectRole('gfm', this)">
            <span class="role-icon">📋</span>
            <span class="role-title">GFM</span>
          </div>

          <div class="role-card" data-role="parent" onclick="selectRole('parent', this)">
            <span class="role-icon">👨‍👩‍👧</span>
            <span class="role-title">Parent</span>
          </div>
        </div>

        <!-- Sleek Selected Role Banner -->
        <div id="selectedRoleBanner" class="selected-role-banner"></div>
      </div>

      <!-- IDENTITY INPUT FIELD -->
      <div class="form-group">
        <label for="identity" class="form-label"><i class="fas fa-user me-1"></i> Username / Registration No / Email</label>
        <input type="text" id="identity" name="identity" class="form-control" placeholder="e.g. EC1301 or admin@zcoer.edu.in" value="<?php echo sanitize($_POST['identity'] ?? $_POST['email'] ?? ''); ?>" required autofocus>
      </div>

      <!-- PASSWORD FIELD -->
      <div class="form-group">
        <label for="password" class="form-label"><i class="fas fa-lock me-1"></i> Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
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

  <script>
  function selectRole(role, elem) {
    document.getElementById('selected_role').value = role;

    document.querySelectorAll('.role-card').forEach(card => card.classList.remove('selected'));
    elem.classList.add('selected');

    const banner = document.getElementById('selectedRoleBanner');
    const roleNames = {
      'student': 'STUDENT',
      'faculty': 'SUBJECT FACULTY',
      'admin': 'SYSTEM ADMINISTRATOR',
      'hod': 'HEAD OF DEPARTMENT (HOD)',
      'gfm': 'GUARDIAN FACULTY MEMBER (GFM)',
      'parent': 'PARENT'
    };

    const roleColors = {
      'student': '#3B82F6',
      'faculty': '#8B5CF6',
      'admin': '#EF4444',
      'hod': '#10B981',
      'gfm': '#F59E0B',
      'parent': '#06B6D4'
    };

    if (banner && roleNames[role]) {
      banner.style.display = 'block';
      banner.style.color = roleColors[role];
      banner.style.border = `1px solid ${roleColors[role]}`;
      banner.style.background = `rgba(15, 23, 42, 0.8)`;
      banner.innerHTML = `<i class="fas fa-check-circle me-1"></i> SELECTED ROLE: ${roleNames[role]}`;
    }
  }

  // Restore previous selected role if form was reloaded
  document.addEventListener('DOMContentLoaded', () => {
    const savedRole = document.getElementById('selected_role').value;
    if (savedRole) {
      const card = document.querySelector(`.role-card[data-role="${savedRole}"]`);
      if (card) {
        selectRole(savedRole, card);
      }
    }
  });
  </script>
</body>
</html>
