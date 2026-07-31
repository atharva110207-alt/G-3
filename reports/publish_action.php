<?php
// Practical Assessment System - Publish Action Handler
// Zeal College of Engineering & Research

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/auth.php';

require_role(['admin', 'hod']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = sanitize($_POST['subject_name'] ?? '');
    $academic_year = sanitize($_POST['academic_year'] ?? '');

    if (empty($subject_name) || empty($academic_year)) {
        set_flash('error', 'Invalid subject or academic year for publishing.');
        header("Location: final_marksheet.php");
        exit();
    }

    $published_by = $_SESSION['user_id'];

    $sql = "INSERT IGNORE INTO published_marksheets (subject_name, academic_year, published_by, published_at) VALUES (?, ?, ?, NOW())";
    $stmt = execute_prepared($conn, $sql, "ssi", [$subject_name, $academic_year, $published_by]);

    if ($stmt) {
        mysqli_stmt_close($stmt);
        log_audit($conn, $published_by, $_SESSION['role'], 'Publish Marksheet', 'marksheet', "Published marksheet for $subject_name ($academic_year)");
        set_flash('success', "Marksheet for $subject_name has been officially published!");
    } else {
        set_flash('error', 'Failed to publish marksheet. Please try again.');
    }

    header("Location: final_marksheet.php?subject=" . urlencode($subject_name) . "&academic_year=" . urlencode($academic_year));
    exit();
}
?>
