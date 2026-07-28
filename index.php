<?php
// Practical Assessment & Laboratory Performance Management System
// Root Entry Router

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/auth.php';

if (is_logged_in()) {
    $role = get_user_role();
    header('Location: ' . get_role_dashboard($role));
    exit();
} else {
    header('Location: ' . BASE_URL . 'modules/authentication/login.php');
    exit();
}
?>
