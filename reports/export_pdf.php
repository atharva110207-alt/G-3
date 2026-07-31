<?php
// Practical Assessment System - Export Marksheet to Printable PDF
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
?>
<!DOCTYPE html>
<html>
<head>
  <title>Termwork Marksheet PDF - <?php echo COLLEGE_NAME; ?></title>
  <style>
    body { font-family: Arial, sans-serif; margin: 1.5rem; color: #0f172a; line-height: 1.4; font-size: 12px; }
    .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 0.75rem; margin-bottom: 1rem; }
    .header h1 { margin: 0; color: #1e3a8a; font-size: 16px; display: none; }
    .header h2 { margin: 2px 0; font-size: 13px; color: #475569; display: none; }
    .print-banner { width: 100%; max-width: 100%; height: auto; display: block; margin-bottom: 10px; }
    .meta { display: flex; justify-content: space-between; margin-bottom: 1rem; background: #f8fafc; padding: 8px 12px; border-radius: 6px; font-weight: bold; border: 1px solid #e2e8f0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
    th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
    th { background: #1e3a8a; color: white; text-align: center; }
    td.center { text-align: center; }
    tr:nth-child(even) { background: #f8fafc; }
    .sign-section { display: flex; justify-content: space-between; margin-top: 3rem; text-align: center; font-weight: bold; }
    @media print { .no-print { display: none; } }
  </style>
</head>
<body>
  <div class="no-print" style="margin-bottom: 1rem; text-align: right;">
    <button onclick="window.print();" style="padding: 8px 16px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
      Print / Export PDF
    </button>
  </div>

  <div class="header">
    <div style="font-size: 24px; font-weight: bold; color: #1e3a8a; margin-bottom: 5px; text-transform: uppercase;">Zeal College of Engineering & Research</div>
    <div style="font-size: 16px; color: #475569; margin-bottom: 15px;">Department of Electronics & Computer Engineering</div>
    <h3>OFFICIAL TERMWORK MARKSHEET SUMMARY</h3>
  </div>

  <div class="meta">
    <div>Subject: <?php echo sanitize($subject_filter); ?></div>
    <div>Class & Div: <?php echo sanitize($class_filter . ' ' . $division_filter); ?></div>
    <div>Date: <?php echo date('d M Y'); ?></div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Roll No</th>
        <th>Student Name</th>
        <th>ZPRN</th>
        <?php foreach ($experiments as $ex): ?>
          <th>Exp #<?php echo $ex['exp_no']; ?></th>
        <?php endforeach; ?>
        <th>Total</th>
        <th>Normalized (25)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($students as $st): ?>
        <?php 
          $st_id = $st['id'];
          $row_total = 0;
          $row_max = count($experiments) * 25;
        ?>
        <tr>
          <td class="center"><strong><?php echo sanitize($st['student_roll_no']); ?></strong></td>
          <td><?php echo sanitize($st['full_name']); ?></td>
          <td class="center"><?php echo sanitize($st['zprn'] ?: '-'); ?></td>
          <?php foreach ($experiments as $ex): ?>
            <?php 
              $sc = $matrix[$st_id][$ex['exp_no']] ?? null;
              if ($sc !== null) { $row_total += $sc; }
            ?>
            <td class="center"><?php echo $sc !== null ? $sc : '-'; ?></td>
          <?php endforeach; ?>
          <td class="center"><strong><?php echo $row_total; ?> / <?php echo $row_max ?: 25; ?></strong></td>
          <td class="center" style="font-weight: bold; color: #1e3a8a;">
            <?php echo normalize_termwork_marks($row_total, $row_max, 25); ?> / 25
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="sign-section">
    <div>Subject Faculty Signature</div>
    <div>GFM Signature</div>
    <div>HOD Signature & Stamp</div>
  </div>
</body>
</html>
