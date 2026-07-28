<?php
// Practical Assessment System - Edit Practical Controller
// Zeal College of Engineering & Research

$page_title = "Edit Practical Experiment";
require_once __DIR__ . '/../../includes/header.php';

require_role(['faculty', 'admin', 'hod']);

$edit_id = intval($_GET['id'] ?? 0);
if ($edit_id <= 0) {
    header('Location: ' . BASE_URL . 'modules/dashboard/faculty_dashboard.php');
    exit();
}

$error = '';

$sql = "SELECT * FROM practicals WHERE id = ?";
$stmt = execute_prepared($conn, $sql, "i", [$edit_id]);
$target_pract = null;
if ($stmt) {
    $res = mysqli_stmt_get_result($stmt);
    $target_pract = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

if (!$target_pract) {
    header('Location: ' . BASE_URL . 'modules/dashboard/faculty_dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = sanitize($_POST['subject_name'] ?? '');
    $exp_no = intval($_POST['exp_no'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $scheduled_date = sanitize($_POST['scheduled_date'] ?? ''); // Plan Date

    if (empty($subject_name) || $exp_no <= 0 || empty($title) || empty($scheduled_date)) {
        $error = "Subject Name, Exp #, Title, and Plan Date are required.";
    } else {
        $up_sql = "UPDATE practicals SET subject_name = ?, exp_no = ?, title = ?, scheduled_date = ? WHERE id = ?";
        $up_stmt = execute_prepared($conn, $up_sql, "sissi", [$subject_name, $exp_no, $title, $scheduled_date, $edit_id]);
        if ($up_stmt) {
            mysqli_stmt_close($up_stmt);
            log_audit($conn, $user['id'], $user['role'], 'Edit Practical', 'practical_management', 'Updated practical ID #' . $edit_id);
            set_flash('success', 'Practical experiment updated successfully!');
            header('Location: ' . BASE_URL . 'modules/dashboard/faculty_dashboard.php');
            exit();
        }
    }
}
?>

<div class="card" style="max-width: 700px; margin: 0 auto;">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-edit text-primary me-2"></i> Edit Practical Experiment (#<?php echo $target_pract['id']; ?>)</h3>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="form-group">
      <label for="subject_name" class="form-label">Subject Name</label>
      <input type="text" id="subject_name" name="subject_name" class="form-control" value="<?php echo sanitize($target_pract['subject_name']); ?>" required>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
      <div class="form-group">
        <label for="exp_no" class="form-label">Exp #</label>
        <input type="number" id="exp_no" name="exp_no" class="form-control" value="<?php echo $target_pract['exp_no']; ?>" required>
      </div>

      <div class="form-group">
        <label for="title" class="form-label">Experiment Title</label>
        <input type="text" id="title" name="title" class="form-control" value="<?php echo sanitize($target_pract['title']); ?>" required>
      </div>
    </div>

    <div class="form-group">
      <label for="scheduled_date" class="form-label">Plan Date</label>
      <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" value="<?php echo $target_pract['scheduled_date']; ?>" required>
    </div>

    <button type="submit" class="btn btn-primary">
      <i class="fas fa-save me-2"></i> Save Changes
    </button>
  </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
