<?php
// Practical Assessment System - Page Header Template
// Zeal College of Engineering & Research

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/functions.php';

// Enforce login for header inclusion unless on explicit auth pages
$current_script = basename($_SERVER['PHP_SELF']);
$auth_pages = ['login.php', 'forgot_password.php', 'reset_password.php', 'register.php'];

if (!in_array($current_script, $auth_pages)) {
    require_login();
}

$user = get_logged_user();

/**
 * Calculate dynamic greeting text based on time of day
 */
function get_time_greeting() {
    $hour = date('H');
    if ($hour < 12) {
        return "Good Morning";
    } else if ($hour < 17) {
        return "Good Afternoon";
    } else {
        return "Good Evening";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? sanitize($page_title) . " - " : ""; ?><?php echo APP_NAME; ?> | Zeal College</title>
  
  <!-- FontAwesome 6 Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  <!-- Core Custom Stylesheets -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/dashboard.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/assessment.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/attendance.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/report.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/responsive.css">

  <script>
    // Initialize saved theme from localStorage immediately
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
  </script>
</head>
<body>
<?php if (!in_array($current_script, $auth_pages)): ?>
<div class="app-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>
  
  <div class="main-content">
    <?php include __DIR__ . '/navbar.php'; ?>
    
    <main class="content-wrapper">
      <!-- Dynamic Top Greeting Header -->
      <div class="header-greeting">
        <div>
          <h2 class="greeting-title"><?php echo get_time_greeting(); ?>, <?php echo sanitize($user['full_name'] ?? 'User'); ?>!</h2>
          <p class="greeting-subtitle">
            Welcome to the <?php echo DEPARTMENT_NAME; ?> &bull; <?php echo get_role_label($user['role'] ?? ''); ?> Portal
          </p>
        </div>
        <div>
          <span class="badge badge-info" style="font-size: 0.85rem; padding: 0.4rem 0.85rem;">
            <i class="fas fa-calendar-alt me-1"></i> A.Y. <?php echo $_SESSION['academic_year'] ?? DEFAULT_ACADEMIC_YEAR; ?> (<?php echo $_SESSION['class_filter'] ?? 'TY'; ?>)
          </span>
        </div>
      </div>

      <!-- Flash Notification Container -->
      <?php $flash = get_flash(); if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
          <span><i class="fas fa-info-circle me-2"></i><?php echo sanitize($flash['message']); ?></span>
          <button type="button" style="background:none; border:none; color:inherit; cursor:pointer;" onclick="this.parentElement.remove();">&times;</button>
        </div>
      <?php endif; ?>
<?php endif; ?>
