<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_role(['faculty', 'admin', 'hod']);

$delete_id = intval($_GET['id'] ?? 0);
if ($delete_id > 0) {
    $sql = "DELETE FROM practicals WHERE id = ?";
    $stmt = execute_prepared($conn, $sql, "i", [$delete_id]);
    if ($stmt) {
        mysqli_stmt_close($stmt);
        log_audit($conn, $user['id'], $user['role'], 'Delete Practical', 'practical_management', 'Deleted practical ID #' . $delete_id);
        set_flash('success', 'Practical experiment deleted successfully.');
    }
}
header('Location: ' . BASE_URL . 'modules/dashboard/faculty_dashboard.php');
exit();
?>
