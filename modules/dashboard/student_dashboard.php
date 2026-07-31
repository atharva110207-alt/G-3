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
            WHERE a.student_id = ? AND EXISTS (SELECT 1 FROM published_marksheets pm WHERE pm.subject_name = p.subject_name)
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

// Subject-Wise Attendance Stats
$subj_att_sql = "SELECT p.subject_name, COUNT(a.id) as total, SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present 
                 FROM attendance a 
                 JOIN practicals p ON a.practical_id = p.id 
                 WHERE a.student_id = ? 
                 GROUP BY p.subject_name";
$subj_att_stmt = execute_prepared($conn, $subj_att_sql, "i", [$student_id]);
$subject_attendance = [];
if ($subj_att_stmt) {
    $res = mysqli_stmt_get_result($subj_att_stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $pct = $r['total'] > 0 ? round(($r['present'] / $r['total']) * 100) : 100;
        $subject_attendance[] = [
            'subject' => $r['subject_name'],
            'total' => $r['total'],
            'present' => $r['present'],
            'percentage' => $pct
        ];
    }
    mysqli_stmt_close($subj_att_stmt);
}
?>

<style>
.circular-progress {
  position: relative;
  width: 70px;
  height: 70px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  background: var(--border-color);
}
.circular-progress::before {
  content: "";
  position: absolute;
  inset: 6px;
  border-radius: 50%;
  background: var(--bg-card);
}
.progress-value {
  position: relative;
  font-weight: 700;
  font-size: 0.95rem;
  color: var(--text-primary);
}
</style>

<!-- Interaction Cards -->
<h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1.5rem;">Overview</h2>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
  <div class="card" style="display: flex; flex-direction: row; align-items: center; gap: 1.25rem; padding: 1.5rem;">
    <div style="width: 60px; height: 60px; border-radius: 14px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 1.75rem;">
      <i class="fas fa-calendar-check"></i>
    </div>
    <div>
      <h3 style="font-weight: 800; font-size: 1.2rem; color: var(--text-primary); margin-bottom: 0.25rem;">Practical Attendance</h3>
      <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0;">Overall Score: <strong style="color: #38bdf8;"><?php echo $att_percentage; ?>%</strong></p>
    </div>
  </div>

  <?php if ($release_status === '1'): ?>
    <div class="card" style="display: flex; flex-direction: row; align-items: center; gap: 1.25rem; padding: 1.5rem;">
      <div style="width: 60px; height: 60px; border-radius: 14px; background: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center; font-size: 1.75rem;">
        <i class="fas fa-file-signature"></i>
      </div>
      <div>
        <h3 style="font-weight: 800; font-size: 1.2rem; color: var(--text-primary); margin-bottom: 0.25rem;">Practical Assessment</h3>
        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0;">Total Marks: <strong style="color: #34d399;"><?php echo $total_obtained; ?> / <?php echo $total_max ?: 25; ?></strong></p>
      </div>
    </div>
  <?php else: ?>
    <div class="card" style="display: flex; flex-direction: row; align-items: center; gap: 1.25rem; padding: 1.5rem; opacity: 0.7; cursor: not-allowed;">
      <div style="width: 60px; height: 60px; border-radius: 14px; background: rgba(100, 116, 139, 0.15); color: #64748b; display: flex; align-items: center; justify-content: center; font-size: 1.75rem;">
        <i class="fas fa-lock"></i>
      </div>
      <div>
        <h3 style="font-weight: 800; font-size: 1.2rem; color: var(--text-muted); margin-bottom: 0.25rem;">Practical Assessment</h3>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0;">Reports locked by Department HOD.</p>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Subject-Wise Attendance Visualizers -->
<h3 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem;"><i class="fas fa-chart-pie text-accent me-2"></i> Subject-Wise Attendance</h3>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
  <?php if(empty($subject_attendance)): ?>
    <div class="alert alert-secondary w-100">No attendance data recorded yet.</div>
  <?php else: ?>
    <?php foreach ($subject_attendance as $sa): 
      $color = $sa['percentage'] >= 75 ? '#34d399' : ($sa['percentage'] >= 50 ? '#fbbf24' : '#f87171');
    ?>
      <div class="card" style="display: flex; flex-direction: row; align-items: center; gap: 1rem; padding: 1rem;">
        <div class="circular-progress" style="background: conic-gradient(<?php echo $color; ?> <?php echo $sa['percentage']; ?>%, var(--border-color) 0);">
          <span class="progress-value"><?php echo $sa['percentage']; ?>%</span>
        </div>
        <div style="flex: 1; min-width: 0;">
          <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo sanitize($sa['subject']); ?>">
            <?php echo sanitize($sa['subject']); ?>
          </h4>
          <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0;">
            <?php echo $sa['present']; ?> / <?php echo $sa['total']; ?> Present
          </p>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Evaluation Marks Breakdown -->
<?php if ($release_status === '1'): ?>
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
            <th>Performance (10)</th>
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
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
