<?php
// Practical Assessment System - Batch-wise Marksheet Report
// Zeal College of Engineering & Research

$page_title = "Publish Marksheet";
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

// Fetch Experiments for this Subject & Batch
$exp_sql = "SELECT id, exp_no, title FROM practicals WHERE subject_name = ? AND batch_id = ? ORDER BY exp_no ASC";
$exp_stmt = execute_prepared($conn, $exp_sql, "si", [$subject_name, $batch_id]);
$experiments = [];
if ($exp_stmt) {
    $res = mysqli_stmt_get_result($exp_stmt);
    while ($ex = mysqli_fetch_assoc($res)) {
        $experiments[] = $ex;
    }
    mysqli_stmt_close($exp_stmt);
}

// Fetch Assessment Matrix
$assessment_matrix = [];
if (!empty($experiments)) {
    $in_placeholders = implode(',', array_fill(0, count($experiments), '?'));
    $types = str_repeat('i', count($experiments));
    $exp_ids = array_column($experiments, 'id');
    
    $ass_sql = "SELECT student_id, practical_id, total_score FROM assessment WHERE practical_id IN ($in_placeholders)";
    $ass_stmt = execute_prepared($conn, $ass_sql, $types, $exp_ids);
    if ($ass_stmt) {
        $res = mysqli_stmt_get_result($ass_stmt);
        while ($ar = mysqli_fetch_assoc($res)) {
            $assessment_matrix[$ar['student_id']][$ar['practical_id']] = $ar['total_score'];
        }
        mysqli_stmt_close($ass_stmt);
    }
}
?>

<div class="card mb-4">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
      <h3 class="card-title"><i class="fas fa-file-alt text-primary me-2"></i> Batch-wise Published Marksheet</h3>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem;">
        Subject: <strong style="color: var(--primary-color);"><?php echo sanitize($subject_name); ?></strong> &bull; 
        Batch: <strong><?php echo $batch_details ? sanitize($batch_details['batch_name']) : 'N/A'; ?></strong>
      </p>
    </div>
    <button onclick="window.print()" class="btn btn-secondary btn-sm">
      <i class="fas fa-print me-1"></i> Print Marksheet
    </button>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-bordered" style="white-space: nowrap;">
      <thead>
        <tr>
          <th>Roll Number</th>
          <th>Student Name</th>
          <?php foreach ($experiments as $ex): ?>
            <th class="text-center" title="<?php echo sanitize($ex['title']); ?>">
              Exp #<?php echo $ex['exp_no']; ?><br>
              <small class="text-muted">(15)</small>
            </th>
          <?php endforeach; ?>
          <th class="text-center" style="background-color: rgba(99, 102, 241, 0.1);">Total</th>
          <th class="text-center" style="background-color: rgba(16, 185, 129, 0.1);">Scaled (/25)</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($students)): ?>
          <tr><td colspan="<?php echo count($experiments) + 4; ?>" class="text-center" style="color: var(--text-muted); padding: 2rem;">No students found for this batch.</td></tr>
        <?php else: ?>
          <?php foreach ($students as $st): ?>
            <?php 
              $st_id = $st['id'];
              $total_obtained = 0;
              $max_possible = count($experiments) * 15;
            ?>
            <tr>
              <td><strong class="badge badge-info"><?php echo sanitize($st['student_roll_no']); ?></strong></td>
              <td><strong style="color: var(--text-primary);"><?php echo sanitize($st['full_name']); ?></strong></td>
              <?php foreach ($experiments as $ex): ?>
                <?php 
                  $score = $assessment_matrix[$st_id][$ex['id']] ?? '-'; 
                  if ($score !== '-') {
                      $total_obtained += $score;
                  }
                ?>
                <td class="text-center fw-bold <?php echo $score === '-' ? 'text-muted' : 'text-primary'; ?>">
                  <?php echo $score; ?>
                </td>
              <?php endforeach; ?>
              <td class="text-center fw-bold" style="background-color: rgba(99, 102, 241, 0.05); color: #4338ca;">
                <?php echo $total_obtained; ?> <small class="text-muted">/ <?php echo $max_possible; ?></small>
              </td>
              <td class="text-center fw-bold" style="background-color: rgba(16, 185, 129, 0.05); color: #059669; font-size: 1.1rem;">
                <?php echo $max_possible > 0 ? round(($total_obtained / $max_possible) * 25) : 0; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
