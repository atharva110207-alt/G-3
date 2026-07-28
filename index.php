<?php
// Practical Assessment System - Root Dispatcher
// Zeal College of Engineering & Research

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/auth.php';

if (is_logged_in()) {
    header('Location: ' . get_role_dashboard(get_user_role()));
    exit();
} else {
    header('Location: ' . BASE_URL . 'modules/authentication/login.php');
    exit();
}
?>
