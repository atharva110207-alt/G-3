<?php
// Practical Assessment System - Student Workspace Dashboard
// Zeal College of Engineering & Research

$page_title = "Student Dashboard";
require_once __DIR__ . '/../../includes/header.php';

require_role(['student', 'admin', 'hod']);

$student_id = $user['id'];
$release_status = get_system_setting($conn, 'release_reports_student_view', '1');

// Fetch Student Evaluation Marks
$eval_sql = "SELECT a.*, p.title as exp_title, p.exp_no, p.subject_name, p.scheduled_date 
            FROM assessment a 
            JOIN practicals p ON a.practical_id = p.id 
            WHERE a.student_id = ? 
            ORDER BY p.exp_no ASC";
$eval_stmt = execute_prepared($conn, $eval_sql, "i", [$student_id]);
$my_evaluations = [];
$total_obtained = 0;
$total_max = 0;
if ($eval_stmt) {
    $res = mysqli_stmt_get_result($eval_stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $my_evaluations[] = $row;
        $total_obtained += $row['total_score'];
        $total_max += 25;
    }
    mysqli_stmt_close($eval_stmt);
}

// Attendance stats
$att_sql = "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present FROM attendance WHERE student_id = ?";
$att_stmt = execute_prepared($conn, $att_sql, "i", [$student_id]);
$att_total = 0;
$att_present = 0;
if ($att_stmt) {
    $res = mysqli_stmt_get_result($att_stmt);
    if ($r = mysqli_fetch_assoc($res)) {
        $att_total = $r['total'];
        $att_present = $r['present'];
    }
    mysqli_stmt_close($att_stmt);
}
$att_percentage = $att_total > 0 ? round(($att_present / $att_total) * 100, 1) : 100;
?>

<?php if ($release_status !== '1'): ?>
  <div class="alert alert-warning">
    <i class="fas fa-lock me-2"></i> <strong>Report Release Notice:</strong> Official termwork marksheets are currently being evaluated and prepared by the Department HOD. Preliminary scores are shown below.
  </div>
<?php endif; ?>

<!-- Performance Overview Cards -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon"><i class="fas fa-id-badge"></i></div>
    <div class="stat-info">
      <h3><?php echo sanitize($user['student_roll_no'] ?: 'EC-STD'); ?></h3>
      <p>Roll No &bull; <?php echo sanitize($user['class'] . ' ' . $user['division']); ?></p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;"><i class="fas fa-user-check"></i></div>
    <div class="stat-info">
      <h3><?php echo $att_percentage; ?>%</h3>
      <p>Overall Practical Attendance</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;"><i class="fas fa-award"></i></div>
    <div class="stat-info">
      <h3><?php echo $total_obtained; ?> / <?php echo $total_max ?: 25; ?></h3>
      <p>Total Practical Marks</p>
    </div>
  </div>
</div>

<!-- Evaluation Marks Breakdown -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-chart-bar text-primary me-2"></i> Practical Evaluation Performance Breakdown</h3>
    <a href="<?php echo BASE_URL; ?>reports/final_marksheet.php" class="btn btn-primary btn-sm"><i class="fas fa-file-pdf me-1"></i> Final Marksheet</a>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Exp #</th>
          <th>Subject</th>
          <th>Experiment Title</th>
          <th>Regularity (5)</th>
          <th>Conduction (10)</th>
          <th>Output (5)</th>
          <th>Viva (5)</th>
          <th>Total Marks (25)</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($my_evaluations)): ?>
          <tr><td colspan="8" class="text-center" style="color: var(--text-muted); padding: 2rem;">No evaluated experiments recorded yet.</td></tr>
        <?php else: ?>
          <?php foreach ($my_evaluations as $ev): ?>
            <tr>
              <td><span class="badge badge-info">Exp #<?php echo $ev['exp_no']; ?></span></td>
              <td><strong><?php echo sanitize($ev['subject_name']); ?></strong></td>
              <td><?php echo sanitize($ev['exp_title']); ?></td>
              <td><?php echo $ev['regularity_score']; ?> / 5</td>
              <td><?php echo $ev['conduction_score']; ?> / 10</td>
              <td><?php echo $ev['output_score']; ?> / 5</td>
              <td><?php echo $ev['viva_score']; ?> / 5</td>
              <td><strong style="color: #38bdf8; font-size: 1.05rem;"><?php echo $ev['total_score']; ?> / 25</strong></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
