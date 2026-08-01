<?php
// Practical Assessment System - Parent Portal
// Zeal College of Engineering & Research

$page_title = "Parent Dashboard";
require_once __DIR__ . '/../../includes/header.php';

require_role(['parent', 'admin', 'hod']);

$student_roll = $user['student_roll_no'] ?? '';
$parent_zprn = $user['zprn'] ?? '';
$student_info = null;
$my_evaluations = [];
$att_percentage = 100;

if (!empty($student_roll) && !empty($parent_zprn)) {
    // Fetch Linked Student Record based on both Roll Number and ZPRN
    $st_sql = "SELECT id, full_name, email, student_roll_no, zprn, class, division FROM users WHERE student_roll_no = ? AND zprn = ? AND role = 'student'";
    $st_stmt = execute_prepared($conn, $st_sql, "ss", [$student_roll, $parent_zprn]);
    if ($st_stmt) {
        $res = mysqli_stmt_get_result($st_stmt);
        $student_info = mysqli_fetch_assoc($res);
        mysqli_stmt_close($st_stmt);
    }

    if ($student_info) {
        $student_id = $student_info['id'];
        
        // Fetch Published Subjects
        $published_subjects = [];
        $pub_sql = "SELECT subject_name FROM published_marksheets WHERE academic_year = ?";
        $pub_stmt = execute_prepared($conn, $pub_sql, "s", [DEFAULT_ACADEMIC_YEAR]);
        if ($pub_stmt) {
            $res = mysqli_stmt_get_result($pub_stmt);
            while ($row = mysqli_fetch_assoc($res)) {
                $published_subjects[] = $row['subject_name'];
            }
            mysqli_stmt_close($pub_stmt);
        }

        // Fetch Evaluations (Only for Published Subjects)
        $eval_sql = "SELECT a.*, p.title as exp_title, p.exp_no, p.subject_name 
                    FROM assessment a 
                    JOIN practicals p ON a.practical_id = p.id 
                    WHERE a.student_id = ? 
                    ORDER BY p.exp_no ASC";
        $eval_stmt = execute_prepared($conn, $eval_sql, "i", [$student_id]);
        if ($eval_stmt) {
            $res = mysqli_stmt_get_result($eval_stmt);
            while ($row = mysqli_fetch_assoc($res)) {
                // Only show marks if the subject is published
                if (in_array($row['subject_name'], $published_subjects)) {
                    $my_evaluations[] = $row;
                }
            }
            mysqli_stmt_close($eval_stmt);
        }

        // Subject-wise Attendance
        $subject_attendance = [];
        $subj_att_sql = "SELECT p.subject_name, COUNT(a.id) as total, SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present 
                         FROM attendance a 
                         JOIN practicals p ON a.practical_id = p.id 
                         WHERE a.student_id = ? 
                         GROUP BY p.subject_name
                         ORDER BY p.subject_name ASC";
        $subj_att_stmt = execute_prepared($conn, $subj_att_sql, "i", [$student_id]);
        if ($subj_att_stmt) {
            $res = mysqli_stmt_get_result($subj_att_stmt);
            while ($row = mysqli_fetch_assoc($res)) {
                $subject_attendance[] = $row;
            }
            mysqli_stmt_close($subj_att_stmt);
        }

        // Attendance Percentage
        $att_sql = "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present FROM attendance WHERE student_id = ?";
        $att_stmt = execute_prepared($conn, $att_sql, "i", [$student_id]);
        if ($att_stmt) {
            $res = mysqli_stmt_get_result($att_stmt);
            if ($r = mysqli_fetch_assoc($res)) {
                $att_percentage = $r['total'] > 0 ? round(($r['present'] / $r['total']) * 100, 1) : 100;
            }
            mysqli_stmt_close($att_stmt);
        }
    }
}
?>

<div class="card mb-4">
  <div class="card-header">
    <div>
      <h3 class="card-title"><i class="fas fa-user-friends text-primary me-2"></i> Parent Performance Monitoring Portal</h3>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem;">
        Monitoring Wards Academic Conduction, Attendance, and Multi-Tier Practical Rubric Marks
      </p>
    </div>
    <span class="badge badge-info" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
      Linked Roll No: <?php echo sanitize($student_roll ?: 'N/A'); ?>
    </span>
  </div>
</div>

<?php if ($student_info): ?>
  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
      <div class="stat-info">
        <h3><?php echo sanitize($student_info['full_name']); ?></h3>
        <p>Ward Name &bull; <?php echo sanitize($student_info['class'] . ' ' . $student_info['division']); ?></p>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;"><i class="fas fa-calendar-check"></i></div>
      <div class="stat-info">
        <h3><?php echo $att_percentage; ?>%</h3>
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

  <div class="card mb-4">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-calendar-check text-success me-2"></i> Subject-wise Practical Attendance</h3>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Subject</th>
            <th>Total Practicals</th>
            <th>Attended</th>
            <th>Attendance %</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($subject_attendance)): ?>
            <tr><td colspan="4" class="text-center" style="color: var(--text-muted); padding: 2rem;">No practical attendance recorded yet.</td></tr>
          <?php else: ?>
            <?php foreach ($subject_attendance as $att): 
              $pct = $att['total'] > 0 ? round(($att['present'] / $att['total']) * 100, 1) : 100;
              $color = $pct >= 75 ? 'var(--status-success-text)' : ($pct >= 50 ? 'var(--status-warning-text)' : 'var(--status-danger-text)');
            ?>
              <tr>
                <td><strong><?php echo sanitize($att['subject_name']); ?></strong></td>
                <td><?php echo $att['total']; ?></td>
                <td><?php echo $att['present']; ?></td>
                <td><strong style="color: <?php echo $color; ?>;"><?php echo $pct; ?>%</strong></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-list-ol text-primary me-2"></i> Practical Experiment Performance Breakdown (Published Only)</h3>
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
            <tr><td colspan="8" class="text-center" style="color: var(--text-muted); padding: 2rem;">No published marksheet records available for your ward at this time.</td></tr>
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
<?php else: ?>
  <div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i> No active student account is currently linked to roll number <strong><?php echo sanitize($student_roll); ?></strong> and ZPRN <strong><?php echo sanitize($parent_zprn); ?></strong>. Please contact Administrator.
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
