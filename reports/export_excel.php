<?php
// Practical Assessment System - Export Marksheet & Attendance to CSV (.excel)
// Zeal College of Engineering & Research

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$division = sanitize($_GET['division'] ?? 'Division C');
$subject = sanitize($_GET['subject'] ?? 'Microprocessors & Microcontrollers');
$class = $_SESSION['class_filter'] ?? 'TY';

// Fetch Students
$st_sql = "SELECT id, full_name, student_roll_no, zprn, class, division FROM users WHERE role = 'student' AND class = ? AND division = ? ORDER BY student_roll_no ASC";
$st_stmt = execute_prepared($conn, $st_sql, "ss", [$class, $division]);
$students = [];
if ($st_stmt) {
    $res = mysqli_stmt_get_result($st_stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $students[] = $r;
    }
    mysqli_stmt_close($st_stmt);
}

// Fetch Experiments
$exp_sql = "SELECT id, exp_no, title FROM practicals WHERE subject_name = ? AND division = ? ORDER BY exp_no ASC";
$exp_stmt = execute_prepared($conn, $exp_sql, "ss", [$subject, $division]);
$experiments = [];
if ($exp_stmt) {
    $res = mysqli_stmt_get_result($exp_stmt);
    while ($ex = mysqli_fetch_assoc($res)) {
        $experiments[] = $ex;
    }
    mysqli_stmt_close($exp_stmt);
}

// Fetch Matrix
$matrix = [];
if (!empty($experiments)) {
    $ass_sql = "SELECT a.student_id, a.practical_id, a.total_score FROM assessment a JOIN practicals p ON a.practical_id = p.id WHERE p.subject_name = ?";
    $ass_stmt = execute_prepared($conn, $ass_sql, "s", [$subject]);
    if ($ass_stmt) {
        $res = mysqli_stmt_get_result($ass_stmt);
        while ($ar = mysqli_fetch_assoc($res)) {
            $matrix[$ar['student_id']][$ar['practical_id']] = $ar['total_score'];
        }
        mysqli_stmt_close($ass_stmt);
    }
}

// Output headers for CSV download
$filename = "Marksheet_" . preg_replace('/[^A-Za-z0-9]/', '_', $subject) . "_" . $division . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Title Headers
fputcsv($output, [COLLEGE_NAME]);
fputcsv($output, [DEPARTMENT_NAME, APP_NAME]);
fputcsv($output, ['Subject: ' . $subject, 'Division: ' . $division, 'Generated On: ' . date('d M Y')]);
fputcsv($output, []); // Empty row

// Table Column Headers
$headers = ['Roll Number', 'Student Name', 'ZPRN', 'Class', 'Division'];
foreach ($experiments as $ex) {
    $headers[] = 'Exp #' . $ex['exp_no'];
}
$headers[] = 'Total Score';
$headers[] = 'Normalized (25)';
fputcsv($output, $headers);

// Rows
foreach ($students as $st) {
    $st_id = $st['id'];
    $row_total = 0;
    $row_max = count($experiments) * 25;

    $row = [
        $st['student_roll_no'],
        $st['full_name'],
        $st['zprn'] ?: '-',
        $st['class'],
        $st['division']
    ];

    foreach ($experiments as $ex) {
        $sc = $matrix[$st_id][$ex['id']] ?? null;
        if ($sc !== null) {
            $row_total += $sc;
            $row[] = $sc;
        } else {
            $row[] = '-';
        }
    }

    $row[] = $row_total . ' / ' . ($row_max ?: 25);
    $row[] = normalize_termwork_marks($row_total, $row_max, 25) . ' / 25';
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
