<?php
// Practical Assessment System - Batch-wise Attendance Report
// Zeal College of Engineering & Research

$page_title = "Publish Attendance";
require_once __DIR__ . '/../includes/header.php';

$subject_name = sanitize($_GET['subject'] ?? '');
$batch_id = intval($_GET['batch_id'] ?? 0);

if (empty($subject_name) || $batch_id <= 0) {
    echo "<div class='alert alert-danger m-4'>Invalid parameters. Please select a valid subject and batch.</div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit();
}

// Fetch Batch Details
$b_sql = "SELECT batch_name, class, division FROM batches WHERE id = ?";
$b_stmt = execute_prepared($conn, $b_sql, "i", [$batch_id]);
$batch_details = null;
if ($b_stmt) {
    $res = mysqli_stmt_get_result($b_stmt);
    if ($row = mysqli_fetch_assoc($res)) {
        $batch_details = $row;
    }
    mysqli_stmt_close($b_stmt);
}

// Fetch Students in Batch
$students = [];
$st_sql = "SELECT u.id, u.full_name, u.student_roll_no, u.zprn 
           FROM users u 
           JOIN batch_students bs ON u.id = bs.student_id 
           WHERE bs.batch_id = ? 
           ORDER BY u.student_roll_no ASC";
$st_stmt = execute_prepared($conn, $st_sql, "i", [$batch_id]);
if ($st_stmt) {
    $res = mysqli_stmt_get_result($st_stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $students[] = $r;
    }
    mysqli_stmt_close($st_stmt);
}

// Fallback: If no explicit students mapped in batch_students, pull by division
if (empty($students) && $batch_details) {
    $st_sql = "SELECT id, full_name, student_roll_no, zprn FROM users WHERE role = 'student' AND division = ? ORDER BY student_roll_no ASC";
    $st_stmt = execute_prepared($conn, $st_sql, "s", [$batch_details['division']]);
    if ($st_stmt) {
        $res = mysqli_stmt_get_result($st_stmt);
        while ($r = mysqli_fetch_assoc($res)) {
            $students[] = $r;
        }
        mysqli_stmt_close($st_stmt);
    }
}

// Fetch Total Practicals for this Subject & Batch
$p_sql = "SELECT id FROM practicals WHERE subject_name = ? AND batch_id = ?";
$p_stmt = execute_prepared($conn, $p_sql, "si", [$subject_name, $batch_id]);
$practicals_list = [];
if ($p_stmt) {
    $res = mysqli_stmt_get_result($p_stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $practicals_list[] = $r['id'];
    }
    mysqli_stmt_close($p_stmt);
}
$total_practicals = count($practicals_list);

// Fetch Attendance Data
$att_summary = [];
if ($total_practicals > 0) {
    $in_placeholders = implode(',', array_fill(0, count($practicals_list), '?'));
    $types = str_repeat('i', count($practicals_list));
    
    $att_sql = "SELECT student_id, SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count 
                FROM attendance 
                WHERE practical_id IN ($in_placeholders) 
                GROUP BY student_id";
    $att_stmt = execute_prepared($conn, $att_sql, $types, $practicals_list);
    if ($att_stmt) {
        $res = mysqli_stmt_get_result($att_stmt);
        while ($ar = mysqli_fetch_assoc($res)) {
            $att_summary[$ar['student_id']] = $ar['present_count'];
        }
        mysqli_stmt_close($att_stmt);
    }
}
?>

<div class="card mb-4">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
      <h3 class="card-title"><i class="fas fa-calendar-check text-primary me-2"></i> Batch-wise Published Attendance</h3>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem;">
        Subject: <strong style="color: var(--primary-color);"><?php echo sanitize($subject_name); ?></strong> &bull; 
        Batch: <strong><?php echo $batch_details ? sanitize($batch_details['batch_name']) : 'N/A'; ?></strong>
      </p>
    </div>
    <button onclick="window.print()" class="btn btn-secondary btn-sm">
      <i class="fas fa-print me-1"></i> Print Report
    </button>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Roll Number</th>
          <th>Student Name</th>
          <th>ZPRN</th>
          <th>Total Practicals</th>
          <th>Present Count</th>
          <th>Attendance %</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($students)): ?>
          <tr><td colspan="7" class="text-center" style="color: var(--text-muted); padding: 2rem;">No students found for this batch.</td></tr>
        <?php else: ?>
          <?php foreach ($students as $st): ?>
            <?php 
              $st_id = $st['id'];
              $pres_c = $att_summary[$st_id] ?? 0;
              $pct = $total_practicals > 0 ? round(($pres_c / $total_practicals) * 100, 1) : 100;
              $is_defaulter = $pct < 75;
            ?>
            <tr style="<?php echo $is_defaulter ? 'background-color: rgba(239, 68, 68, 0.05);' : ''; ?>">
              <td><strong class="badge badge-info" style="font-size: 0.85rem;"><?php echo sanitize($st['student_roll_no']); ?></strong></td>
              <td><strong style="color: var(--text-primary);"><?php echo sanitize($st['full_name']); ?></strong></td>
              <td><code><?php echo sanitize($st['zprn'] ?: '-'); ?></code></td>
              <td><?php echo $total_practicals; ?> Sessions</td>
              <td><?php echo $pres_c; ?> Attended</td>
              <td><strong style="font-size: 1.05rem; color: <?php echo !$is_defaulter ? '#34d399' : '#ef4444'; ?>;"><?php echo $pct; ?>%</strong></td>
              <td>
                <span class="badge badge-<?php echo !$is_defaulter ? 'success' : 'danger'; ?>">
                  <?php echo !$is_defaulter ? 'Satisfactory' : 'Defaulter (<75%)'; ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
