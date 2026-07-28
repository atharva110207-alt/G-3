<?php
// Practical Assessment System - Delete User Action
// Zeal College of Engineering & Research

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role('admin');

$delete_id = intval($_GET['id'] ?? 0);
$current_user = get_logged_user();

if ($delete_id > 0 && $delete_id != $current_user['id']) {
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = execute_prepared($conn, $sql, "i", [$delete_id]);
    if ($stmt) {
        mysqli_stmt_close($stmt);
        log_audit($conn, $current_user['id'], $current_user['role'], 'Delete User', 'user_management', 'Deleted user ID #' . $delete_id);
        set_flash('success', 'User account deleted successfully.');
    } else {
        set_flash('error', 'Failed to delete user account.');
    }
} else {
    set_flash('error', 'Invalid operation or cannot delete your own active session account.');
}

header('Location: manage_user.php');
exit();
?>
