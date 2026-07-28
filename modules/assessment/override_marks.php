<?php
// Practical Assessment System - Override Marks Action
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_role(['admin', 'hod']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = intval($_POST['assessment_id'] ?? 0);
    $override_score = intval($_POST['override_score'] ?? 0);
    $reason = sanitize($_POST['reason'] ?? 'HOD Override');

    if ($assessment_id > 0 && $override_score >= 0 && $override_score <= 25) {
        $sql = "UPDATE assessment SET total_score = ?, comments = CONCAT(IFNULL(comments, ''), ' [HOD Override: ', ?, ']') WHERE id = ?";
        $stmt = execute_prepared($conn, $sql, "isi", [$override_score, $reason, $assessment_id]);
        if ($stmt) {
            mysqli_stmt_close($stmt);
            log_audit($conn, $user['id'], $user['role'], 'Override Marks', 'assessment', 'Overrode assessment ID #' . $assessment_id . ' to score ' . $override_score);
            set_flash('success', 'Marks overridden successfully.');
        }
    }
}
header('Location: ' . BASE_URL . 'reports/final_marksheet.php');
exit();
?>
