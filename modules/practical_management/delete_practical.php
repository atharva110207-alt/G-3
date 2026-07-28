<?php
// Delete Practical Experiment Handler

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_role(['faculty', 'admin', 'hod']);

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $sql = "DELETE FROM practicals WHERE id = ?";
    $stmt = execute_prepared($conn, $sql, "i", [$id]);
    if ($stmt) {
        mysqli_stmt_close($stmt);
        log_audit($conn, $_SESSION['user_id'], 'Deleted Practical', 'practicals', "Deleted practical #$id");
        set_flash('success', 'Practical deleted successfully.');
    }
}
header('Location: ../dashboard/faculty_dashboard.php');
exit();
?>
