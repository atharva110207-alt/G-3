<?php
// Practical Assessment System - Subject Faculty Operations Dashboard
// Zeal College of Engineering & Research

$page_title = "Subject Faculty Dashboard";
require_once __DIR__ . '/../../includes/header.php';

require_role(['faculty', 'admin', 'hod']);

// Fetch Practicals Created by or Allocated to Subject Faculty
$fac_id = $user['id'];
$practicals_sql = "SELECT p.*, b.batch_name FROM practicals p JOIN batches b ON p.batch_id = b.id WHERE p.faculty_id = ? ORDER BY p.scheduled_date DESC LIMIT 10";
$stmt = execute_prepared($conn, $practicals_sql, "i", [$fac_id]);
$my_practicals = [];
if ($stmt) {
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $my_practicals[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Subject Faculty Allocations
$alloc_sql = "SELECT fa.*, b.batch_name FROM faculty_allocations fa LEFT JOIN batches b ON fa.batch_id = b.id WHERE fa.faculty_id = ?";
$alloc_stmt = execute_prepared($conn, $alloc_sql, "i", [$fac_id]);
$my_allocations = [];
if ($alloc_stmt) {
    $res = mysqli_stmt_get_result($alloc_stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $my_allocations[] = $row;
    }
    mysqli_stmt_close($alloc_stmt);
}
?>

<!-- Subject Faculty Banner -->
<div class="card mb-4" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 27, 75, 0.9)); border: 1px solid var(--primary-color);">
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h2 style="font-size: 1.5rem; font-weight: 800; color: #ffffff; margin-bottom: 0.25rem;">
        <i class="fas fa-chalkboard-teacher text-accent me-2"></i> Subject Faculty Workspace
      </h2>
      <p style="color: #9ca3af; font-size: 0.875rem;">
        Practical Experiment Planning, Interactive Attendance Logging & Multi-Tier Rubric Assessment
      </p>
    </div>
    <a href="<?php echo BASE_URL; ?>modules/practical_management/create_practical.php" class="btn btn-primary">
      <i class="fas fa-plus-circle me-1"></i> Create New Practical
    </a>
  </div>
</div>

<!-- Workflow Action Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-bottom: 1.75rem;">
  <a href="<?php echo BASE_URL; ?>modules/practical_management/create_practical.php" class="card" style="text-decoration: none; display: flex; align-items: center; gap: 1rem;">
    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(99, 102, 241, 0.2); color: #6366F1; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
      <i class="fas fa-flask"></i>
    </div>
    <div>
      <h4 style="font-weight: 700; color: var(--text-primary);">Create Practicals</h4>
      <p style="font-size: 0.8rem; color: var(--text-secondary);">Set Plan Date & Topics</p>
    </div>
  </a>

  <a href="<?php echo BASE_URL; ?>modules/attendance/mark_attendance.php" class="card" style="text-decoration: none; display: flex; align-items: center; gap: 1rem;">
    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(56, 189, 248, 0.2); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
      <i class="fas fa-calendar-check"></i>
    </div>
    <div>
      <h4 style="font-weight: 700; color: var(--text-primary);">Mark Attendance</h4>
      <p style="font-size: 0.8rem; color: var(--text-secondary);">Interactive Student Toggle</p>
    </div>
  </a>

  <a href="<?php echo BASE_URL; ?>modules/assessment/practical_conduction.php" class="card" style="text-decoration: none; display: flex; align-items: center; gap: 1rem;">
    <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(16, 185, 129, 0.2); color: #34d399; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
      <i class="fas fa-pen-nib"></i>
    </div>
    <div>
      <h4 style="font-weight: 700; color: var(--text-primary);">Evaluate Students</h4>
      <p style="font-size: 0.8rem; color: var(--text-secondary);">Multi-Tier (25 Marks)</p>
    </div>
  </a>
</div>

<!-- Scheduled Practicals Table -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-list text-primary me-2"></i> Scheduled Experiments & Practicals</h3>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Exp #</th>
          <th>Subject Name</th>
          <th>Title / Experiment Topic</th>
          <th>Batch & Division</th>
          <th>Plan Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($my_practicals)): ?>
          <tr><td colspan="6" class="text-center" style="color: var(--text-muted); padding: 2rem;">No practical experiments scheduled yet.</td></tr>
        <?php else: ?>
          <?php foreach ($my_practicals as $p): ?>
            <tr>
              <td><span class="badge badge-info">Exp #<?php echo $p['exp_no']; ?></span></td>
              <td><strong><?php echo sanitize($p['subject_name']); ?></strong></td>
              <td><?php echo sanitize($p['title']); ?></td>
              <td><?php echo sanitize('Batch ' . $p['batch_name'] . ' (' . $p['division'] . ')'); ?></td>
              <td><span class="badge badge-warning"><i class="fas fa-calendar me-1"></i> Plan Date: <?php echo format_date($p['scheduled_date']); ?></span></td>
              <td>
                <a href="<?php echo BASE_URL; ?>modules/attendance/mark_attendance.php?practical_id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm" title="Mark Attendance"><i class="fas fa-user-check"></i> Attendance</a>
                <a href="<?php echo BASE_URL; ?>modules/assessment/practical_conduction.php?practical_id=<?php echo $p['id']; ?>" class="btn btn-primary btn-sm" title="Evaluate Marks"><i class="fas fa-pen"></i> Evaluate</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
