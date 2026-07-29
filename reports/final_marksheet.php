<?php
// Practical Assessment System - Term-Work Final Marksheet Generator
// Zeal College of Engineering & Research

$page_title = "Final Termwork Marksheet";
require_once __DIR__ . '/../includes/header.php';

$class_filter = $_SESSION['class_filter'] ?? 'TY';
$division_filter = $_GET['division'] ?? 'Division C';
$subject_filter = $_GET['subject'] ?? 'Microprocessors & Microcontrollers';

// Fetch Students in Division & Class
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

// Fetch Experiments for Subject
$exp_sql = "SELECT id, exp_no, title FROM practicals WHERE subject_name = ? AND division = ? ORDER BY exp_no ASC";
$exp_stmt = execute_prepared($conn, $exp_sql, "ss", [$subject_filter, $division_filter]);
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
    $ass_sql = "SELECT a.student_id, a.practical_id, a.total_score FROM assessment a JOIN practicals p ON a.practical_id = p.id WHERE p.subject_name = ?";
    $ass_stmt = execute_prepared($conn, $ass_sql, "s", [$subject_filter]);
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
  <div class="card-header">
    <div>
      <h3 class="card-title"><i class="fas fa-file-invoice text-primary me-2"></i> Final Term-Work Marksheet & Evaluation Summary</h3>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem;">
        <?php echo COLLEGE_NAME; ?> &bull; <?php echo DEPARTMENT_NAME; ?>
      </p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
      <a href="<?php echo BASE_URL; ?>reports/export_excel.php?subject=<?php echo urlencode($subject_filter); ?>&division=<?php echo urlencode($division_filter); ?>" class="btn btn-accent btn-sm">
        <i class="fas fa-file-excel me-1"></i> Export to Excel (.csv)
      </a>
      <a href="<?php echo BASE_URL; ?>reports/export_pdf.php?subject=<?php echo urlencode($subject_filter); ?>&division=<?php echo urlencode($division_filter); ?>" target="_blank" class="btn btn-primary btn-sm">
        <i class="fas fa-file-pdf me-1"></i> Download PDF
      </a>
    </div>
  </div>

  <form method="GET" action="" class="action-bar" style="margin-bottom: 0;">
    <div style="display: flex; gap: 1rem; width: 100%; flex-wrap: wrap;">
      <div style="flex: 1; min-width: 220px;">
        <label for="subject" class="form-label">Subject</label>
        <select id="subject" name="subject" class="form-select" onchange="this.form.submit()">
          <option value="Microprocessors & Microcontrollers" <?php echo $subject_filter === 'Microprocessors & Microcontrollers' ? 'selected' : ''; ?>>Microprocessors & Microcontrollers</option>
          <option value="Digital Signal Processing" <?php echo $subject_filter === 'Digital Signal Processing' ? 'selected' : ''; ?>>Digital Signal Processing</option>
          <option value="VLSI Design & Embedded Systems" <?php echo $subject_filter === 'VLSI Design & Embedded Systems' ? 'selected' : ''; ?>>VLSI Design & Embedded Systems</option>
        </select>
      </div>

      <div style="width: 180px;">
        <label for="division" class="form-label">Division</label>
        <select id="division" name="division" class="form-select" onchange="this.form.submit()">
          <option value="Division A" <?php echo $division_filter === 'Division A' ? 'selected' : ''; ?>>Division A</option>
          <option value="Division B" <?php echo $division_filter === 'Division B' ? 'selected' : ''; ?>>Division B</option>
          <option value="Division C" <?php echo $division_filter === 'Division C' ? 'selected' : ''; ?>>Division C</option>
        </select>
      </div>
    </div>
  </form>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table" style="font-size: 0.825rem;">
      <thead>
        <tr>
          <th>Roll No</th>
          <th>Student Name</th>
          <th>ZPRN</th>
          <?php foreach ($experiments as $ex): ?>
            <th class="text-center" title="<?php echo sanitize($ex['title']); ?>">Exp #<?php echo $ex['exp_no']; ?></th>
          <?php endforeach; ?>
          <th class="text-center">Total (Max)</th>
          <th class="text-center">Normalized (25)</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($students)): ?>
          <tr><td colspan="<?php echo count($experiments) + 5; ?>" class="text-center" style="padding: 2rem; color: var(--text-muted);">No student records found.</td></tr>
        <?php else: ?>
          <?php foreach ($students as $st): ?>
            <?php 
              $st_id = $st['id'];
              $row_total = 0;
              $row_max = count($experiments) * 25;
            ?>
            <tr>
              <td><strong class="badge badge-info" style="font-size: 0.85rem;"><?php echo sanitize($st['student_roll_no']); ?></strong></td>
              <td><strong style="color: var(--text-primary);"><?php echo sanitize($st['full_name']); ?></strong></td>
              <td><code><?php echo sanitize($st['zprn'] ?: '-'); ?></code></td>
              
              <?php foreach ($experiments as $ex): ?>
                <?php 
                  $score = $assessment_matrix[$st_id][$ex['id']] ?? null;
                  if ($score !== null) { $row_total += $score; }
                ?>
                <td class="text-center">
                  <?php if ($score !== null): ?>
                    <span style="font-weight: 700; color: <?php echo $score >= 20 ? '#34d399' : ($score >= 12 ? '#fbbf24' : '#f87171'); ?>;">
                      <?php echo $score; ?>
                    </span>
                  <?php else: ?>
                    <span style="color: var(--text-muted);">-</span>
                  <?php endif; ?>
                </td>
              <?php endforeach; ?>

              <td class="text-center"><strong style="color: var(--text-primary);"><?php echo $row_total; ?> / <?php echo $row_max ?: 25; ?></strong></td>
              <td class="text-center">
                <span class="badge badge-success" style="font-size: 0.9rem;">
                  <?php echo normalize_termwork_marks($row_total, $row_max, 25); ?> / 25
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
