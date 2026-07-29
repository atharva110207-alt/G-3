<?php
ob_start();
// Practical Assessment System - Edit Batches Module
// Zeal College of Engineering & Research

$page_title = "Edit Existing Batches";
require_once __DIR__ . '/../includes/header.php';

// Restricted to Admin & HOD
require_role(['admin', 'hod']);

$error = '';
$success = '';

// Handle Delete
if (isset($_GET['delete_batch'])) {
    $del_id = intval($_GET['delete_batch']);
    $sql = "DELETE FROM batches WHERE id = ?";
    $stmt = execute_prepared($conn, $sql, "i", [$del_id]);
    if ($stmt) {
        log_audit($conn, $user['id'], $user['role'], 'Delete Batch', 'batch_management', 'Deleted batch ID ' . $del_id);
        set_flash('success', 'Batch deleted successfully.');
        header('Location: edit_batches.php');
        exit();
    } else {
        $error = "Failed to delete batch.";
    }
}

// Fetch existing batches
$batches_sql = "SELECT b.*, COUNT(bs.student_id) as student_count FROM batches b LEFT JOIN batch_students bs ON b.id = bs.batch_id GROUP BY b.id ORDER BY b.academic_year DESC, b.class ASC, b.division ASC, b.batch_name ASC";
$batches_res = mysqli_query($conn, $batches_sql);
$batches_list = [];
if ($batches_res) {
    while ($b = mysqli_fetch_assoc($batches_res)) {
        $batches_list[] = $b;
    }
}
?>

<div style="max-width: 1000px; margin: 0 auto;">
  <div class="card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-list text-primary me-2"></i> Active Batches Directory</h3>
      <a href="create_batches.php" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Create New Batch</a>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <?php $flash = get_flash(); if ($flash): ?>
      <div class="alert alert-<?php echo $flash['type']; ?>"><i class="fas fa-info-circle me-2"></i> <?php echo sanitize($flash['message']); ?></div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Batch</th>
            <th>Class / Div</th>
            <th>Students</th>
            <th>Assigned Subject</th>
            <th>A.Y.</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($batches_list)): ?>
            <tr><td colspan="6" class="text-center" style="color: var(--text-muted);">No batches created yet.</td></tr>
          <?php else: ?>
            <?php foreach ($batches_list as $b): ?>
              <tr>
                <td><strong style="color: var(--accent-color); font-size: 1rem;"><?php echo sanitize($b['batch_name']); ?></strong></td>
                <td><?php echo sanitize($b['class'] . ' - ' . $b['division']); ?></td>
                <td><span class="badge badge-info"><?php echo $b['student_count']; ?> Students</span></td>
                <td><?php echo sanitize($b['subject_assigned'] ?: '-'); ?></td>
                <td><?php echo sanitize($b['academic_year']); ?></td>
                <td>
                  <a href="#" class="btn btn-sm btn-outline-primary me-1" title="Edit Batch (Coming Soon)">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="?delete_batch=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this batch? This will unassign all students in this batch.');" title="Delete Batch">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php ob_end_flush(); ?>
