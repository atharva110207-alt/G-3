<?php
// Practical Assessment System - Term-Work Final Marksheet Generator
// Zeal College of Engineering & Research

$page_title = "Practical Marksheet (Batch-wise & Division-wise)";
require_once __DIR__ . '/../includes/header.php';

// Fetch dynamic dropdown options
$ay_options = $ACADEMIC_YEARS ?? [DEFAULT_ACADEMIC_YEAR];
if ($_SESSION['role'] === 'faculty') {
    $subj_sql = "SELECT DISTINCT fa.subject_name, s.semester FROM faculty_allocations fa INNER JOIN syllabi s ON fa.subject_name = s.subject_name WHERE fa.faculty_id = ? ORDER BY s.semester ASC, fa.subject_name ASC";
    $subj_stmt = execute_prepared($conn, $subj_sql, "i", [$_SESSION['user_id']]);
    $subj_res = $subj_stmt ? mysqli_stmt_get_result($subj_stmt) : false;
} else {
    $subj_sql = "SELECT DISTINCT fa.subject_name, s.semester FROM faculty_allocations fa INNER JOIN syllabi s ON fa.subject_name = s.subject_name ORDER BY s.semester ASC, fa.subject_name ASC";
    $subj_res = mysqli_query($conn, $subj_sql);
}
$subject_options = [];
if ($subj_res) {
    while ($r = mysqli_fetch_assoc($subj_res)) {
        $sem = $r['semester'] ?: 'Other';
        $subject_options[$sem][] = $r['subject_name'];
    }
}

$class_filter = $_GET['class'] ?? ($_SESSION['class_filter'] ?? 'TY');
$division_filter = $_GET['division'] ?? 'Division C';
$subject_filter = $_GET['subject'] ?? '';
if (empty($subject_filter) && !empty($subject_options)) {
    $first_group = reset($subject_options);
    $subject_filter = $first_group[0] ?? 'Microprocessors & Microcontrollers';
}
$academic_year_filter = $_GET['academic_year'] ?? DEFAULT_ACADEMIC_YEAR;
$faculty_filter = $_GET['faculty_id'] ?? '';
if ($_SESSION['role'] === 'faculty') {
    $faculty_filter = $_SESSION['user_id'];
}

$faculty_options = [];

// Fetch dynamic divisions based on subject and faculty
$div_sql = "SELECT DISTINCT division FROM faculty_allocations WHERE subject_name = ?";
$div_params = [$subject_filter];
$div_types = "s";

$div_sql .= " ORDER BY division ASC";
$div_stmt = execute_prepared($conn, $div_sql, $div_types, $div_params);
$division_options = [];
if ($div_stmt) {
    $res = mysqli_stmt_get_result($div_stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $division_options[] = $r['division'];
    }
    mysqli_stmt_close($div_stmt);
}

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
$exp_sql = "SELECT id, exp_no, title FROM practicals WHERE subject_name = ? AND division = ?";
$exp_params = [$subject_filter, $division_filter];
$exp_types = "ss";

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

// Fetch Assessment Matrix
$assessment_matrix = [];
if (!empty($experiments)) {
    $ass_sql = "SELECT a.student_id, a.total_score, p.exp_no FROM assessment a JOIN practicals p ON a.practical_id = p.id WHERE p.subject_name = ?";
    $ass_stmt = execute_prepared($conn, $ass_sql, "s", [$subject_filter]);
    if ($ass_stmt) {
        $res = mysqli_stmt_get_result($ass_stmt);
        while ($ar = mysqli_fetch_assoc($res)) {
            $assessment_matrix[$ar['student_id']][$ar['exp_no']] = $ar['total_score'];
        }
        mysqli_stmt_close($ass_stmt);
    }
}
$is_published = false;
$pub_chk = execute_prepared($conn, "SELECT id FROM published_marksheets WHERE subject_name = ? AND academic_year = ?", "ss", [$subject_filter, $academic_year_filter]);
if ($pub_chk) {
    if (mysqli_stmt_get_result($pub_chk)->num_rows > 0) $is_published = true;
    mysqli_stmt_close($pub_chk);
}
?>

<div class="card mb-4">
  <div class="card-header">
    <div>
      <h3 class="card-title"><i class="fas fa-file-invoice text-primary me-2"></i> <?php echo ($_SESSION['role'] === 'student') ? 'Personal Practical Marksheet' : 'Practical Marksheet (Batch-wise & Division-wise)'; ?>
      <?php if ($is_published): ?><span class="badge bg-success" style="font-size: 0.75rem; vertical-align: middle;">Published</span><?php endif; ?>
      </h3>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem;">
        <?php echo COLLEGE_NAME; ?> &bull; <?php echo DEPARTMENT_NAME; ?>
      </p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
      <?php if (!$is_published && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'hod')): ?>
      <form method="POST" action="publish_action.php" style="margin:0;">
        <input type="hidden" name="subject_name" value="<?php echo htmlspecialchars($subject_filter); ?>">
        <input type="hidden" name="academic_year" value="<?php echo htmlspecialchars($academic_year_filter); ?>">
        <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Officially publish this marksheet? Students will immediately see their marks for this subject.');">
          <i class="fas fa-bullhorn me-1"></i> Publish
        </button>
      </form>
      <?php endif; ?>
      <?php if ($is_published): ?>
      <a href="<?php echo BASE_URL; ?>reports/export_excel.php?subject=<?php echo urlencode($subject_filter); ?>&division=<?php echo urlencode($division_filter); ?>&class=<?php echo urlencode($class_filter); ?>" class="btn btn-accent btn-sm">
        <i class="fas fa-file-excel me-1"></i> Export to Excel (.csv)
      </a>
      <a href="<?php echo BASE_URL; ?>reports/export_pdf.php?subject=<?php echo urlencode($subject_filter); ?>&division=<?php echo urlencode($division_filter); ?>&class=<?php echo urlencode($class_filter); ?>" target="_blank" class="btn btn-primary btn-sm">
        <i class="fas fa-file-pdf me-1"></i> Download PDF
      </a>
      <?php else: ?>
      <button class="btn btn-secondary btn-sm" disabled title="Marksheet must be published first" style="cursor: not-allowed; opacity: 0.6;">
        <i class="fas fa-file-excel me-1"></i> Export to Excel (.csv)
      </button>
      <button class="btn btn-secondary btn-sm" disabled title="Marksheet must be published first" style="cursor: not-allowed; opacity: 0.6;">
        <i class="fas fa-file-pdf me-1"></i> Download PDF
      </button>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($_SESSION['role'] !== 'student' && $_SESSION['role'] !== 'parent'): ?>
  <form method="GET" action="" class="action-bar" style="margin-bottom: 0;">
    <div style="display: flex; gap: 1rem; width: 100%; flex-wrap: wrap;">
      
      <div style="flex: 1; min-width: 150px;">
        <label for="academic_year" class="form-label">Academic Year</label>
        <select id="academic_year" name="academic_year" class="form-select" onchange="this.form.submit()">
          <?php foreach ($ay_options as $ay): ?>
            <option value="<?php echo $ay; ?>" <?php echo $academic_year_filter === $ay ? 'selected' : ''; ?>><?php echo $ay; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="flex: 2; min-width: 220px;">
        <label for="subject" class="form-label">Subject</label>
        <select id="subject" name="subject" class="form-select" onchange="this.form.submit()">
          <?php foreach ($subject_options as $sem => $subjects): ?>
            <optgroup label="<?php echo sanitize($sem); ?>">
              <?php foreach ($subjects as $subj): ?>
                <option value="<?php echo sanitize($subj); ?>" <?php echo $subject_filter === $subj ? 'selected' : ''; ?>><?php echo sanitize($subj); ?></option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="flex: 1; min-width: 150px;">
        <label for="class" class="form-label">Class</label>
        <select id="class" name="class" class="form-select" onchange="this.form.submit()">
          <?php foreach ($CLASSES as $c): ?>
            <option value="<?php echo $c; ?>" <?php echo $class_filter === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
          <?php endforeach; ?>
        </select>
      </div>



      <div style="flex: 1; min-width: 150px;">
        <label for="division" class="form-label">Division</label>
        <select id="division" name="division" class="form-select" onchange="this.form.submit()">
          <?php foreach ($division_options as $div): ?>
            <option value="<?php echo sanitize($div); ?>" <?php echo $division_filter === $div ? 'selected' : ''; ?>><?php echo sanitize($div); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </form>
  <?php endif; ?>
</div>

<div class="card">
  <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-color);">
    <input type="text" id="tableSearch" class="form-control" placeholder="Search by ZPRN, Roll No, or Name..." style="max-width: 400px;">
  </div>
  <div class="table-responsive">
    <table class="table" id="marksheetTable" style="font-size: 0.825rem;">
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
        <?php if (($_SESSION['role'] === 'student' || $_SESSION['role'] === 'parent') && !$is_published): ?>
          <tr><td colspan="<?php echo count($experiments) + 5; ?>" class="text-center" style="padding: 2.5rem;">
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-muted);">
              <i class="fas fa-lock" style="font-size: 2rem; margin-bottom: 1rem; color: #fbbf24;"></i>
              <h4 style="margin: 0; color: var(--text-primary);">Marksheet Not Published</h4>
              <p style="margin-top: 0.5rem; max-width: 400px; text-align: center;">This marksheet is pending final review. It will be visible here once the HOD or Administrator officially publishes it.</p>
            </div>
          </td></tr>
        <?php elseif (empty($students)): ?>
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
                  $score = $assessment_matrix[$st_id][$ex['exp_no']] ?? null;
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('tableSearch');
    const tableBody = document.querySelector('#marksheetTable tbody');
    
    if (searchInput && tableBody) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = tableBody.getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                // Skip the "No student records found" row if present
                if (rows[i].cells.length === 1) continue;
                
                const textContent = rows[i].textContent.toLowerCase();
                if (textContent.indexOf(filter) > -1) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
