<?php
// Practical Assessment System - Logout Controller
// Zeal College of Engineering & Research

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (is_logged_in()) {
    $user = get_logged_user();
    log_audit($conn, $user['id'], $user['role'], 'User Logout', 'authentication', 'Logged out of system.');
}

// Destroy session
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

session_start();
set_flash('info', 'You have been logged out safely.');
header('Location: ' . BASE_URL . 'modules/authentication/login.php');
exit();
?>
