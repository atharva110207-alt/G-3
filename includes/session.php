<?php
// Practical Assessment System - Session Management & Flash Notifications
// Zeal College of Engineering & Research

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is currently logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get currently logged-in user array
 * @return array|null
 */
function get_logged_user() {
    if (is_logged_in()) {
        return [
            'id' => $_SESSION['user_id'],
            'full_name' => $_SESSION['full_name'] ?? 'User',
            'email' => $_SESSION['email'] ?? '',
            'role' => $_SESSION['role'] ?? '',
            'student_roll_no' => $_SESSION['student_roll_no'] ?? null,
            'zprn' => $_SESSION['zprn'] ?? null,
            'class' => $_SESSION['class'] ?? 'TY',
            'division' => $_SESSION['division'] ?? 'Division C'
        ];
    }
    return null;
}

/**
 * Get current user role
 * @return string|null
 */
function get_user_role() {
    return $_SESSION['role'] ?? null;
}

/**
 * Set session flash message
 *
 * @param string $type ('success', 'error', 'warning', 'info')
 * @param string $message
 */
function set_flash($type, $message) {
    $_SESSION['flash_msg'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear session flash message
 * @return array|null
 */
function get_flash() {
    if (isset($_SESSION['flash_msg'])) {
        $msg = $_SESSION['flash_msg'];
        unset($_SESSION['flash_msg']);
        return $msg;
    }
    return null;
}
?>
