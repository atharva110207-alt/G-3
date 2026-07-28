<?php
// Practical Assessment System - Individual Student Report Card
// Zeal College of Engineering & Research

$page_title = "Student Progress Report Card";
require_once __DIR__ . '/../includes/header.php';

$roll_no = sanitize($_GET['roll'] ?? '');

$student_info = null;
if (!empty($roll_no)) {
    $st_sql = "SELECT id, full_name, email, student_roll_no, zprn, class, division, phone FROM users WHERE student_roll_no = ? AND role = 'student'";
    $st_stmt = execute_prepared($conn, $st_sql, "s", [$roll_no]);
    if ($st_stmt) {
        $res = mysqli_stmt_get_result($st_stmt);
        $student_info = mysqli_fetch_assoc($res);
        mysqli_stmt_close($st_stmt);
    }
}

$evaluations = [];
$att_pct = 100;

if ($student_info) {
    $student_id = $student_info['id'];
    
    // Fetch Assessments
    $ass_sql = "SELECT a.*, p.title as exp_title, p.exp_no, p.subject_name, p.scheduled_date 
                FROM assessment a 
                JOIN practicals p ON a.practical_id = p.id 
                WHERE a.student_id = ? 
                ORDER BY p.exp_no ASC";
    $ass_stmt = execute_prepared($conn, $ass_sql, "i", [$student_id]);
    if ($ass_stmt) {
        $res = mysqli_stmt_get_result($ass_stmt);
        while ($r = mysqli_fetch_assoc($res)) {
            $evaluations[] = $r;
        }
        mysqli_stmt_close($ass_stmt);
    }

    // Attendance
    $att_sql = "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present FROM attendance WHERE student_id = ?";
    $att_stmt = execute_prepared($conn, $att_sql, "i", [$student_id]);
    if ($att_stmt) {
        $res = mysqli_stmt_get_result($att_stmt);
        if ($ar = mysqli_fetch_assoc($res)) {
            $att_pct = $ar['total'] > 0 ? round(($ar['present'] / $ar['total']) * 100, 1) : 100;
        }
        mysqli_stmt_close($att_stmt);
    }
}
?>

<div class="card mb-4">
  <div class="card-header">
    <div>
      <h3 class="card-title"><i class="fas fa-id-card text-primary me-2"></i> Student Performance & Evaluation Card</h3>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem;">
        <?php echo COLLEGE_NAME; ?> &bull; <?php echo DEPARTMENT_NAME; ?>
      </p>
    </div>
    <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i> Print Report Card</button>
  </div>
</div>

<?php if ($student_info): ?>
  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
      <div class="stat-info">
        <h3><?php echo sanitize($student_info['full_name']); ?></h3>
        <p>Roll No: <strong><?php echo sanitize($student_info['student_roll_no']); ?></strong> &bull; <?php echo sanitize($student_info['class'] . ' ' . $student_info['division']); ?></p>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;"><i class="fas fa-user-check"></i></div>
      <div class="stat-info">
        <h3><?php echo $att_pct; ?>%</h3>
        <p>Overall Practical Attendance</p>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;"><i class="fas fa-barcode"></i></div>
      <div class="stat-info">
        <h3><?php echo sanitize($student_info['zprn'] ?: 'ZPRN-PENDING'); ?></h3>
        <p>Zeal PRN Number</p>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-list-ol text-primary me-2"></i> Practical Experiment Evaluation Records</h3>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Exp #</th>
            <th>Subject Name</th>
            <th>Title</th>
            <th>Regularity (5)</th>
            <th>Conduction (10)</th>
            <th>Output (5)</th>
            <th>Viva (5)</th>
            <th>Total Marks (25)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($evaluations)): ?>
            <tr><td colspan="8" class="text-center" style="color: var(--text-muted); padding: 2rem;">No evaluated records.</td></tr>
          <?php else: ?>
            <?php foreach ($evaluations as $ev): ?>
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
<?php else: ?>
  <div class="alert alert-warning">Please specify a valid student roll number.</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
