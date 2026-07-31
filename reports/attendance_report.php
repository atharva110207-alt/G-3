<?php
// Practical Assessment System - Attendance Report Generator
// Zeal College of Engineering & Research

$page_title = "Overall Practical Attendance Report";
require_once __DIR__ . '/../includes/header.php';

$division = sanitize($_GET['division'] ?? 'Division C');
$class = sanitize($_GET['class'] ?? ($_SESSION['class_filter'] ?? 'TY'));

// Fetch Students in Division
if ($user['role'] === 'student' || $user['role'] === 'parent') {
    $st_sql = "SELECT id, full_name, student_roll_no, zprn, class, division FROM users WHERE role = 'student' AND id = ?";
    $st_stmt = execute_prepared($conn, $st_sql, "i", [$user['id']]);
} else {
    $st_sql = "SELECT id, full_name, student_roll_no, zprn, class, division FROM users WHERE role = 'student' AND class = ? AND division = ? ORDER BY student_roll_no ASC";
    $st_stmt = execute_prepared($conn, $st_sql, "ss", [$class, $division]);
}

$students = [];
if ($st_stmt) {
    $res = mysqli_stmt_get_result($st_stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $students[] = $r;
    }
    mysqli_stmt_close($st_stmt);
}

// Attendance Calculation per Student
$att_summary = [];
$att_sql = "SELECT student_id, COUNT(*) as total_conducted, SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count FROM attendance GROUP BY student_id";
$att_res = mysqli_query($conn, $att_sql);
if ($att_res) {
    while ($ar = mysqli_fetch_assoc($att_res)) {
        $att_summary[$ar['student_id']] = $ar;
    }
}
?>

<div class="card mb-4">
  <div class="card-header">
    <div>
      <h3 class="card-title"><i class="fas fa-calendar-check text-primary me-2"></i> Overall Practical Attendance Summary</h3>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem;">
        <?php echo COLLEGE_NAME; ?> &bull; <?php echo DEPARTMENT_NAME; ?>
      </p>
    </div>
    <?php if ($user['role'] !== 'student' && $user['role'] !== 'parent'): ?>
    <form method="GET" action="" style="display: flex; gap: 0.5rem; align-items: center;">
      <select name="class" class="form-select" style="width: auto;" onchange="this.form.submit()">
        <?php foreach ($CLASSES as $c): ?>
          <option value="<?php echo $c; ?>" <?php echo $class === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
        <?php endforeach; ?>
      </select>
      <select name="division" class="form-select" style="width: auto;" onchange="this.form.submit()">
        <option value="Division A" <?php echo $division === 'Division A' ? 'selected' : ''; ?>>Division A</option>
        <option value="Division B" <?php echo $division === 'Division B' ? 'selected' : ''; ?>>Division B</option>
        <option value="Division C" <?php echo $division === 'Division C' ? 'selected' : ''; ?>>Division C</option>
      </select>
    </form>
    <?php endif; ?>
  </div>
</div>

<?php if ($user['role'] === 'student' || $user['role'] === 'parent'): ?>
  <?php
    $det_sql = "SELECT a.status, p.title, p.exp_no, p.subject_name, p.scheduled_date 
                FROM attendance a 
                JOIN practicals p ON a.practical_id = p.id 
                WHERE a.student_id = ? 
                ORDER BY p.subject_name ASC, p.exp_no ASC";
    $det_stmt = execute_prepared($conn, $det_sql, "i", [$user['id']]);
    $detailed_attendance = [];
    if ($det_stmt) {
        $res = mysqli_stmt_get_result($det_stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $detailed_attendance[$row['subject_name']][] = $row;
        }
        mysqli_stmt_close($det_stmt);
    }
  ?>
  
  <?php if (empty($detailed_attendance)): ?>
    <div class="card p-5 text-center text-muted">
      No practical attendance records found for you yet.
    </div>
  <?php else: ?>
    <?php foreach ($detailed_attendance as $subject => $records): ?>
      <div class="card mb-4">
        <div class="card-header bg-light">
          <h4 class="card-title m-0 text-primary"><i class="fas fa-book me-2"></i> <?php echo sanitize($subject); ?></h4>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 10%;">Exp #</th>
                <th style="width: 45%;">Experiment Title</th>
                <th style="width: 25%;">Scheduled Date</th>
                <th style="width: 20%;">Attendance Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($records as $rec): ?>
                <tr>
                  <td><strong><?php echo sanitize($rec['exp_no']); ?></strong></td>
                  <td><?php echo sanitize($rec['title']); ?></td>
                  <td><?php echo date('d M Y', strtotime($rec['scheduled_date'])); ?></td>
                  <td>
                    <?php if ($rec['status'] === 'Present'): ?>
                      <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Present</span>
                    <?php else: ?>
                      <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Absent</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

<?php else: ?>
<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Roll Number</th>
          <th>Student Name</th>
          <th>ZPRN</th>
          <th>Class & Div</th>
          <th>Total Practicals</th>
          <th>Present Count</th>
          <th>Overall Practical Attendance</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($students)): ?>
          <tr><td colspan="8" class="text-center" style="color: var(--text-muted); padding: 2rem;">No students found in selected division.</td></tr>
        <?php else: ?>
          <?php foreach ($students as $st): ?>
            <?php 
              $st_id = $st['id'];
              $total_c = $att_summary[$st_id]['total_conducted'] ?? 0;
              $pres_c = $att_summary[$st_id]['present_count'] ?? 0;
              $pct = $total_c > 0 ? round(($pres_c / $total_c) * 100, 1) : 100;
            ?>
            <tr>
              <td><strong class="badge badge-info" style="font-size: 0.85rem;"><?php echo sanitize($st['student_roll_no']); ?></strong></td>
              <td><strong style="color: var(--text-primary);"><?php echo sanitize($st['full_name']); ?></strong></td>
              <td><code><?php echo sanitize($st['zprn'] ?: '-'); ?></code></td>
              <td><?php echo sanitize($st['class'] . ' - ' . $st['division']); ?></td>
              <td><?php echo $total_c; ?> Sessions</td>
              <td><?php echo $pres_c; ?> Attended</td>
              <td><strong style="font-size: 1.05rem; color: <?php echo $pct >= 75 ? '#34d399' : '#f87171'; ?>;"><?php echo $pct; ?>%</strong></td>
              <td>
                <span class="badge badge-<?php echo $pct >= 75 ? 'success' : 'danger'; ?>">
                  <?php echo $pct >= 75 ? 'Satisfactory' : 'Defaulter (<75%)'; ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
