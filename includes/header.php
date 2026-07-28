<?php
// Practical Assessment & Laboratory Performance Management System
// Header Include

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/functions.php';

$user = get_logged_user();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? sanitize($page_title) . ' - ' . APP_NAME : APP_NAME; ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- System CSS Assets -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/attendance.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/assessment.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/report.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/responsive.css">

    <!-- Theme Initializer -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>
<body>
<div class="app-layout">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/navbar.php'; ?>
        <div class="content-wrapper">
            <?php 
            $flash = get_flash();
            if ($flash): 
            ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <span><?php echo $flash['message']; ?></span>
                    <button onclick="this.parentElement.remove()" style="background:none; border:none; color:inherit; font-weight:bold; cursor:pointer;">&times;</button>
                </div>
            <?php endif; ?>
