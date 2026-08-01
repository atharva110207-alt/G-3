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

// Clear Remember Me Cookies
setcookie("pas_user", "", time() - 3600, "/");
setcookie("pas_role", "", time() - 3600, "/");
setcookie("remember_user", "", time() - 3600, "/");

session_destroy();

session_start();
set_flash('info', 'You have been logged out safely.');
// Build bulletproof absolute redirect URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$absolute_url = $protocol . $_SERVER['HTTP_HOST'] . BASE_URL . 'modules/authentication/login.php';

header("Location: " . $absolute_url);
exit();
?>
