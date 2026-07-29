<?php
// Practical Assessment System - HOD Dashboard & Department Monitor
// Zeal College of Engineering & Research

$page_title = "HOD Dashboard";
require_once __DIR__ . '/../../includes/header.php';

require_role(['hod', 'admin']);

// Handle Toggle Release Reports Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_release_reports'])) {
    $current_state = get_system_setting($conn, 'release_reports_student_view', '1');
    $new_state = ($current_state === '1') ? '0' : '1';
    set_system_setting($conn, 'release_reports_student_view', $new_state);
    log_audit($conn, $user['id'], 'hod', 'Toggle Release Reports', 'system', 'Set student view release toggle to ' . $new_state);
    set_flash('success', 'Student marksheet release status updated to: ' . ($new_state === '1' ? 'PUBLISHED' : 'HIDDEN'));
    header('Location: hod_dashboard.php');
    exit();
}

$release_status = get_system_setting($conn, 'release_reports_student_view', '1');

// Department Metrics
$total_students = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users WHERE role = 'student'");
if ($res && $r = mysqli_fetch_assoc($res)) { $total_students = $r['cnt']; }

$total_practicals = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM practicals");
if ($res && $r = mysqli_fetch_assoc($res)) { $total_practicals = $r['cnt']; }

$total_evaluations = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM assessment");
if ($res && $r = mysqli_fetch_assoc($res)) { $total_evaluations = $r['cnt']; }

// Allocations List
$alloc_sql = "SELECT fa.*, u.full_name as faculty_name, u.email as faculty_email, b.batch_name 
              FROM faculty_allocations fa 
              JOIN users u ON fa.faculty_id = u.id 
              LEFT JOIN batches b ON fa.batch_id = b.id 
              ORDER BY fa.academic_year DESC, fa.class ASC";
$alloc_res = mysqli_query($conn, $alloc_sql);
$allocations = [];
if ($alloc_res) {
    while ($row = mysqli_fetch_assoc($alloc_res)) {
        $allocations[] = $row;
    }
}
?>

<!-- HOD Dashboard Banner & Control Switch -->
<div class="card mb-4" style="background: linear-gradient(135deg, rgba(30, 27, 75, 0.9), rgba(15, 23, 42, 0.9)); border: 1px solid var(--primary-color);">
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h2 style="font-size: 1.5rem; font-weight: 800; color: #ffffff; margin-bottom: 0.25rem;">
        <i class="fas fa-user-shield text-accent me-2"></i> HOD Department Operations & Control Panel
      </h2>
      <p style="color: #9ca3af; font-size: 0.875rem;">
        Monitoring Electronics & Computer Engineering Practical Evaluation & Term-work Progress
      </p>
    </div>

    <!-- Toggle Switch: Release Reports for Student View -->
    <form method="POST" action="" style="display: flex; align-items: center; gap: 1rem; background: rgba(255,255,255,0.08); padding: 0.75rem 1.25rem; border-radius: 12px;">
      <input type="hidden" name="toggle_release_reports" value="1">
      <div style="text-align: right;">
        <span style="display: block; font-weight: 700; font-size: 0.85rem; color: #ffffff;">Release Reports to Students</span>
        <span style="font-size: 0.75rem; color: <?php echo $release_status === '1' ? '#34d399' : '#f87171'; ?>; font-weight: 600;">
          Status: <?php echo $release_status === '1' ? 'PUBLISHED & VISIBLE' : 'LOCKED / HIDDEN'; ?>
        </span>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" <?php echo $release_status === '1' ? 'checked' : ''; ?> onchange="this.form.submit()">
        <span class="toggle-slider"></span>
      </label>
    </form>
  </div>
</div>

<!-- Department Monitoring Stats -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
    <div class="stat-info">
      <h3><?php echo $total_students; ?></h3>
      <p>Department Students</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;"><i class="fas fa-flask"></i></div>
    <div class="stat-info">
      <h3><?php echo $total_practicals; ?></h3>
      <p>Scheduled Practicals</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;"><i class="fas fa-clipboard-check"></i></div>
    <div class="stat-info">
      <h3><?php echo $total_evaluations; ?></h3>
      <p>Total Evaluations Completed</p>
    </div>
  </div>
</div>

<!-- Batch Allocations Management for HOD -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-tasks text-success me-2"></i> Active Batch Allocations</h3>
    <a href="<?php echo BASE_URL; ?>admin/allocations.php" class="btn btn-primary btn-sm">
      <i class="fas fa-edit me-1"></i> Edit & Allocate Subjects
    </a>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Subject Faculty</th>
          <th>Subject Name</th>
          <th>Class & Division</th>
          <th>Assigned Batch</th>
          <th>Academic Year</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($allocations)): ?>
          <tr><td colspan="6" class="text-center" style="color: var(--text-muted);">No allocations setup yet.</td></tr>
        <?php else: ?>
          <?php foreach ($allocations as $al): ?>
            <tr>
              <td><strong><?php echo sanitize($al['faculty_name']); ?></strong></td>
              <td><span class="badge badge-info"><?php echo sanitize($al['subject_name']); ?></span></td>
              <td><?php echo sanitize($al['class'] . ' - ' . $al['division']); ?></td>
              <td><?php echo sanitize($al['batch_name'] ?: 'All Batches'); ?></td>
              <td><?php echo sanitize($al['academic_year']); ?></td>
              <td>
                <a href="<?php echo BASE_URL; ?>admin/allocations.php?edit_id=<?php echo $al['id']; ?>" class="btn btn-secondary btn-sm">
                  <i class="fas fa-edit me-1"></i> Edit
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
