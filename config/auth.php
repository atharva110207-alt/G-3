<?php
// Practical Assessment System - Authentication & Role-Based Authorization
// Zeal College of Engineering & Research

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/config.php';

/**
 * Ensure user is logged in, or redirect to login.php
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to access the system.');
        header('Location: ' . BASE_URL . 'modules/authentication/login.php');
        exit();
    }
}

/**
 * Require specific user role or list of roles.
 *
 * @param array|string $allowed_roles
 */
function require_role($allowed_roles) {
    require_login();
    $current_role = get_user_role();
    
    if (is_string($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    if (!in_array($current_role, $allowed_roles)) {
        set_flash('error', 'Unauthorized access! You do not have permission to view this page.');
        header('Location: ' . get_role_dashboard($current_role));
        exit();
    }
}

/**
 * Get dashboard URL corresponding to user role
 *
 * @param string $role
 * @return string
 */
function get_role_dashboard($role) {
    switch ($role) {
        case 'admin':
            return BASE_URL . 'modules/dashboard/admin_dashboard.php';
        case 'hod':
            return BASE_URL . 'modules/dashboard/hod_dashboard.php';
        case 'gfm':
            return BASE_URL . 'modules/dashboard/gfm_dashboard.php';
        case 'faculty':
            return BASE_URL . 'modules/dashboard/faculty_dashboard.php';
        case 'student':
            return BASE_URL . 'modules/dashboard/student_dashboard.php';
        case 'parent':
            return BASE_URL . 'modules/dashboard/parent_dashboard.php';
        default:
            return BASE_URL . 'modules/authentication/login.php';
    }
}
?>
