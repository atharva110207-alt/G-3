<?php
// Practical Assessment System - Consolidated Student SEM Report
// Zeal College of Engineering & Research

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/auth.php';

require_role(['student']);

$student_id = $_SESSION['user_id'];
$academic_year = DEFAULT_ACADEMIC_YEAR;

// Fetch student details
$st_sql = "SELECT id, full_name, student_roll_no, zprn, class, division FROM users WHERE id = ?";
$st_stmt = execute_prepared($conn, $st_sql, "i", [$student_id]);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($st_stmt));
mysqli_stmt_close($st_stmt);

// Fetch all subjects published for this academic year
$pub_sql = "SELECT subject_name FROM published_marksheets WHERE academic_year = ?";
$pub_stmt = execute_prepared($conn, $pub_sql, "s", [$academic_year]);
$published_subjects = [];
if ($pub_stmt) {
    $res = mysqli_stmt_get_result($pub_stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $published_subjects[] = $r['subject_name'];
    }
    mysqli_stmt_close($pub_stmt);
}

// Fetch Assessment Matrix for the student
$assessment_matrix = [];
$total_obtained_overall = 0;
$total_max_overall = 0;

if (!empty($published_subjects)) {
    // We just fetch all assessments for the student and group by subject
    $eval_sql = "SELECT a.*, p.title as exp_title, p.exp_no, p.subject_name 
                 FROM assessment a 
                 JOIN practicals p ON a.practical_id = p.id 
                 WHERE a.student_id = ? 
                 ORDER BY p.subject_name ASC, p.exp_no ASC";
    $eval_stmt = execute_prepared($conn, $eval_sql, "i", [$student_id]);
    
    if ($eval_stmt) {
        $res = mysqli_stmt_get_result($eval_stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            // Only include published subjects
            if (in_array($row['subject_name'], $published_subjects)) {
                $assessment_matrix[$row['subject_name']][] = $row;
            }
        }
        mysqli_stmt_close($eval_stmt);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Consolidated SEM Report - <?php echo sanitize($student['full_name']); ?></title>
  <style>
    body { font-family: Arial, sans-serif; margin: 1.5rem; color: #0f172a; line-height: 1.4; font-size: 12px; }
    .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 0.75rem; margin-bottom: 1rem; }
    .header h1 { font-size: 1.1rem; color: #1e3a8a; margin: 0 0 0.25rem 0; text-transform: uppercase; }
    .header h2 { font-size: 0.9rem; color: #475569; margin: 0; font-weight: 500; }
    .meta-table { width: 100%; margin-bottom: 1.5rem; border-collapse: collapse; }
    .meta-table td { padding: 0.25rem 0; }
    .data-table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 11px; }
    .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 0.35rem 0.5rem; text-align: left; }
    .data-table th { background-color: #f1f5f9; color: #334155; font-weight: bold; }
    .text-center { text-align: center !important; }
    .text-right { text-align: right !important; }
    @media print {
      body { margin: 0; padding: 10mm; -webkit-print-color-adjust: exact; }
      .no-print { display: none !important; }
    }
    .btn-print { background: #3b82f6; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold; }
  </style>
</head>
<body>

  <div class="no-print" style="text-align: right; margin-bottom: 1rem;">
    <button class="btn-print" onclick="window.print()">Print / Save PDF</button>
  </div>

  <div class="header">
    <h1><?php echo COLLEGE_NAME; ?></h1>
    <h2><?php echo DEPARTMENT_NAME; ?></h2>
    <h3 style="margin-top: 0.5rem; font-size: 1rem; color: #0f172a;">CONSOLIDATED PRACTICAL ASSESSMENT REPORT (SEM-WISE)</h3>
    <h4 style="margin: 0.25rem 0 0 0; color: #475569; font-weight: 400;">Academic Year: <?php echo $academic_year; ?></h4>
  </div>

  <table class="meta-table">
    <tr>
      <td><strong>Student Name:</strong> <?php echo sanitize($student['full_name']); ?></td>
      <td><strong>Roll Number:</strong> <?php echo sanitize($student['student_roll_no']); ?></td>
      <td><strong>ZPRN:</strong> <?php echo sanitize($student['zprn'] ?: 'N/A'); ?></td>
    </tr>
    <tr>
      <td><strong>Class:</strong> <?php echo sanitize($student['class']); ?></td>
      <td><strong>Division:</strong> <?php echo sanitize($student['division']); ?></td>
      <td><strong>Date Generated:</strong> <?php echo date('d M Y'); ?></td>
    </tr>
  </table>

  <?php if (empty($assessment_matrix)): ?>
    <div style="text-align: center; padding: 2rem; border: 1px dashed #cbd5e1; color: #64748b;">
      No officially published marksheets available yet for your enrolled subjects.
    </div>
  <?php else: ?>
    <?php foreach ($assessment_matrix as $subject => $practicals): ?>
      <?php 
        $subject_total = 0;
        $subject_max = count($practicals) * 25;
      ?>
      <h4 style="margin: 0 0 0.5rem 0; color: #1e3a8a; border-left: 3px solid #3b82f6; padding-left: 0.5rem;"><?php echo sanitize($subject); ?></h4>
      <table class="data-table">
        <thead>
          <tr>
            <th style="width: 10%;">Exp No</th>
            <th style="width: 50%;">Experiment Title</th>
            <th class="text-center" style="width: 10%;">A/R/P (05)</th>
            <th class="text-center" style="width: 10%;">Conduction (10)</th>
            <th class="text-center" style="width: 10%;">Journal (10)</th>
            <th class="text-center" style="width: 10%;">Total (25)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($practicals as $p): ?>
            <?php $subject_total += $p['total_score']; ?>
            <tr>
              <td><?php echo sanitize($p['exp_no']); ?></td>
              <td><?php echo sanitize($p['exp_title']); ?></td>
              <td class="text-center"><?php echo $p['arp_score']; ?></td>
              <td class="text-center"><?php echo $p['conduction_score']; ?></td>
              <td class="text-center"><?php echo $p['journal_score']; ?></td>
              <td class="text-center" style="font-weight: bold;"><?php echo $p['total_score']; ?></td>
            </tr>
          <?php endforeach; ?>
          <tr style="background-color: #f8fafc; font-weight: bold;">
            <td colspan="5" class="text-right">Subject Term-Work Total:</td>
            <td class="text-center" style="color: #16a34a;"><?php echo $subject_total; ?> / <?php echo $subject_max; ?></td>
          </tr>
        </tbody>
      </table>
      <?php 
        $total_obtained_overall += $subject_total;
        $total_max_overall += $subject_max;
      ?>
    <?php endforeach; ?>

    <div style="margin-top: 2rem; border-top: 2px solid #cbd5e1; padding-top: 1rem; display: flex; justify-content: space-between; font-size: 13px;">
      <div><strong>Final Overall Term-Work Score:</strong> <span style="font-size: 16px; color: #1e3a8a; font-weight: bold; margin-left: 0.5rem;"><?php echo $total_obtained_overall; ?> / <?php echo $total_max_overall; ?></span></div>
      <div><strong>Percentage:</strong> <?php echo $total_max_overall > 0 ? round(($total_obtained_overall / $total_max_overall) * 100, 2) : 0; ?>%</div>
    </div>
  <?php endif; ?>

  <div style="margin-top: 4rem; display: flex; justify-content: space-between; font-weight: bold; text-align: center;">
    <div style="border-top: 1px solid #94a3b8; padding-top: 0.5rem; width: 150px;">Student Signature</div>
    <div style="border-top: 1px solid #94a3b8; padding-top: 0.5rem; width: 150px;">Head of Department</div>
  </div>

</body>
</html>
