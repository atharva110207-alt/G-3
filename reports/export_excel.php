<?php
// Practical Assessment System - Export Marksheet & Attendance to CSV (.excel)
// Zeal College of Engineering & Research

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/auth.php';

require_login();

$class_filter = sanitize($_GET['class'] ?? ($_SESSION['class_filter'] ?? 'TY'));
$division_filter = sanitize($_GET['division'] ?? 'Division C');
$subject_filter = sanitize($_GET['subject'] ?? 'Microprocessors & Microcontrollers');
$academic_year_filter = sanitize($_GET['academic_year'] ?? DEFAULT_ACADEMIC_YEAR);
$faculty_filter = sanitize($_GET['faculty_id'] ?? '');

// Verify if the marksheet is published before allowing download
$is_published = false;
$pub_chk = execute_prepared($conn, "SELECT id FROM published_marksheets WHERE subject_name = ? AND academic_year = ?", "ss", [$subject_filter, $academic_year_filter]);
if ($pub_chk) {
    if (mysqli_stmt_get_result($pub_chk)->num_rows > 0) $is_published = true;
    mysqli_stmt_close($pub_chk);
}

if (!$is_published) {
    die("Error: This marksheet is not published yet. You cannot download it.");
}

// Fetch Students
$st_sql = "SELECT id, full_name, student_roll_no, zprn, class, division FROM users WHERE role = 'student' AND class = ? AND division = ?";
$params = [$class_filter, $division_filter];
$types = "ss";

if ($_SESSION['role'] === 'student') {
    $st_sql .= " AND student_roll_no = ?";
    $params[] = $_SESSION['student_roll_no'];
    $types .= "s";
}
$st_sql .= " ORDER BY student_roll_no ASC";
$st_stmt = execute_prepared($conn, $st_sql, $types, $params);
$students = [];
if ($st_stmt) {
    $res = mysqli_stmt_get_result($st_stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $students[] = $r;
    }
    mysqli_stmt_close($st_stmt);
}

// Fetch Experiments
$exp_sql = "SELECT id, exp_no, title FROM practicals WHERE subject_name = ? AND division = ?";
$exp_params = [$subject_filter, $division_filter];
$exp_types = "ss";
if (!empty($faculty_filter)) {
    $exp_sql .= " AND faculty_id = ?";
    $exp_params[] = $faculty_filter;
    $exp_types .= "i";
}
$exp_sql .= " ORDER BY exp_no ASC";
$exp_stmt = execute_prepared($conn, $exp_sql, $exp_types, $exp_params);
$experiments = [];
if ($exp_stmt) {
    $res = mysqli_stmt_get_result($exp_stmt);
    $unique_experiments = [];
    while ($ex = mysqli_fetch_assoc($res)) {
        if (!isset($unique_experiments[$ex['exp_no']])) {
            $unique_experiments[$ex['exp_no']] = $ex;
        }
    }
    ksort($unique_experiments);
    $experiments = array_values($unique_experiments);
    mysqli_stmt_close($exp_stmt);
}

// Fetch Matrix
$matrix = [];
if (!empty($experiments)) {
    $ass_sql = "SELECT a.student_id, a.total_score, p.exp_no FROM assessment a JOIN practicals p ON a.practical_id = p.id WHERE p.subject_name = ?";
    $ass_stmt = execute_prepared($conn, $ass_sql, "s", [$subject_filter]);
    if ($ass_stmt) {
        $res = mysqli_stmt_get_result($ass_stmt);
        while ($ar = mysqli_fetch_assoc($res)) {
            $matrix[$ar['student_id']][$ar['exp_no']] = $ar['total_score'];
        }
        mysqli_stmt_close($ass_stmt);
    }
}

// Output headers for CSV download
$filename = "Marksheet_" . preg_replace('/[^A-Za-z0-9]/', '_', $subject_filter) . "_" . $division_filter . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Title Headers
fputcsv($output, [COLLEGE_NAME]);
fputcsv($output, [DEPARTMENT_NAME, APP_NAME]);
fputcsv($output, ['Subject: ' . $subject_filter, 'Division: ' . $division_filter, 'Generated On: ' . date('d M Y')]);
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
        $sc = $matrix[$st_id][$ex['exp_no']] ?? null;
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
