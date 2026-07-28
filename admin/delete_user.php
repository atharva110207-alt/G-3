<?php
// Delete User Handler

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['admin', 'hod']);

$id = intval($_GET['id'] ?? 0);

if ($id) {
    // Prevent self deletion
    if ($id === $_SESSION['user_id']) {
        set_flash('error', 'You cannot delete your own active account!');
    } else {
        $del_sql = "DELETE FROM users WHERE id = ?";
        $stmt = execute_prepared($conn, $del_sql, "i", [$id]);
        if ($stmt) {
            mysqli_stmt_close($stmt);
            log_audit($conn, $_SESSION['user_id'], 'Deleted User Account', 'users', "Deleted user ID #$id");
            set_flash('success', "User account #$id deleted successfully.");
        } else {
            set_flash('error', 'Failed to delete user.');
        }
    }
}

header('Location: manage_user.php');
exit();
?>
