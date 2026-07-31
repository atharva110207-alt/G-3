<?php
// Practical Assessment System - GFM (Guardian Faculty Member) Portal
// Zeal College of Engineering & Research

$page_title = "GFM Portal";
require_once __DIR__ . '/../../includes/header.php';

require_role(['gfm', 'admin', 'hod']);

$is_admin_or_hod = ($user['role'] === 'admin' || $user['role'] === 'hod');
$students = [];

if ($is_admin_or_hod) {
    $gfm_class = $_SESSION['class_filter'] ?? 'TY';
    $gfm_division = $user['division'] ?? 'Division C';
    $batch_name = 'All Batches (Admin View)';
    
    $sql = "SELECT id, full_name, email, student_roll_no, zprn, class, division, phone FROM users WHERE role = 'student' AND class = ? AND division = ? ORDER BY student_roll_no ASC";
    $stmt = execute_prepared($conn, $sql, "ss", [$gfm_class, $gfm_division]);
    if ($stmt) {
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $students[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
} else {
    $gfm_id = $user['id'];
    $b_sql = "SELECT b.id as batch_id, b.batch_name, b.class, b.division 
              FROM gfm_allocations ga 
              JOIN batches b ON ga.batch_id = b.id 
              WHERE ga.gfm_id = ? LIMIT 1";
    $b_stmt = execute_prepared($conn, $b_sql, "i", [$gfm_id]);
    $batch_info = null;
    if ($b_stmt) {
        $res = mysqli_stmt_get_result($b_stmt);
        $batch_info = mysqli_fetch_assoc($res);
        mysqli_stmt_close($b_stmt);
    }

    if ($batch_info) {
        $gfm_class = $batch_info['class'];
        $gfm_division = $batch_info['division'];
        $batch_name = $batch_info['batch_name'];
        $batch_id = $batch_info['batch_id'];

        $sql = "SELECT u.id, u.full_name, u.email, u.student_roll_no, u.zprn, u.class, u.division, u.phone 
                FROM batch_students bs 
                JOIN users u ON bs.student_id = u.id 
                WHERE bs.batch_id = ? ORDER BY u.student_roll_no ASC";
        $stmt = execute_prepared($conn, $sql, "i", [$batch_id]);
        if ($stmt) {
            $res = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($res)) {
                $students[] = $row;
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $gfm_class = 'N/A';
        $gfm_division = 'N/A';
        $batch_name = 'Unassigned';
    }
}
?>

<div class="card mb-4">
  <div class="card-header">
    <div>
      <h3 class="card-title"><i class="fas fa-users-class text-primary me-2"></i> GFM Monitor: <?php echo sanitize($gfm_class . ' - ' . $gfm_division); ?> (<?php echo sanitize($batch_name); ?>)</h3>
      <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem;">
        Guardian Faculty Member Portal for class student monitoring, attendance overview, and roll number directory.
      </p>
    </div>
    <span class="badge badge-info" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
      <?php echo count($students); ?> Enrolled Students
    </span>
  </div>

  <!-- Live Search Bar Filtering Student Profile Cards -->
  <div class="action-bar" style="margin-bottom: 0;">
    <div class="search-wrapper" style="max-width: 100%;">
      <i class="fas fa-search search-icon"></i>
      <input type="text" id="gfmStudentSearch" class="form-control search-input" placeholder="Live Search by Student Name, Roll Number, or ZPRN..." onkeyup="filterStudentCards()">
    </div>
  </div>
</div>

<!-- Student Profile Cards Grid -->
<div class="student-grid" id="studentCardContainer">
  <?php if (empty($students)): ?>
    <div class="card" style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 3rem;">
      <i class="fas fa-user-slash fa-3x mb-3"></i>
      <h4>No students found registered for <?php echo sanitize($batch_name . ' (' . $gfm_class . ' ' . $gfm_division . ')'); ?>.</h4>
    </div>
  <?php else: ?>
    <?php foreach ($students as $st): ?>
      <div class="student-card student-profile-item" data-search="<?php echo strtolower(sanitize($st['full_name'] . ' ' . $st['student_roll_no'] . ' ' . $st['zprn'])); ?>">
        <div class="student-card-header">
          <span class="student-roll"><i class="fas fa-id-card me-1"></i> <?php echo sanitize($st['student_roll_no']); ?></span>
          <span class="badge badge-secondary"><?php echo sanitize($st['class']); ?></span>
        </div>
        <h4 class="student-name"><?php echo sanitize($st['full_name']); ?></h4>
        <div class="student-meta">
          <div><i class="fas fa-barcode text-accent me-1"></i> ZPRN: <strong><?php echo sanitize($st['zprn'] ?: 'N/A'); ?></strong></div>
          <div><i class="fas fa-envelope text-muted me-1"></i> <?php echo sanitize($st['email']); ?></div>
          <div><i class="fas fa-phone text-muted me-1"></i> Mobile: <?php echo sanitize($st['phone'] ?: 'N/A'); ?></div>
        </div>
        <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between;">
          <a href="<?php echo BASE_URL; ?>reports/student_report.php?roll=<?php echo urlencode($st['student_roll_no']); ?>" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center;">
            <i class="fas fa-file-invoice me-1"></i> View Progress Card
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script>
function filterStudentCards() {
  const query = document.getElementById('gfmStudentSearch').value.toLowerCase().trim();
  const items = document.querySelectorAll('.student-profile-item');
  
  items.forEach(item => {
    const searchText = item.getAttribute('data-search') || '';
    if (searchText.includes(query)) {
      item.style.display = 'block';
    } else {
      item.style.display = 'none';
    }
  });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
