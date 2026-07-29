<?php
// Practical Assessment System - Subject Faculty Allocations Module
// Zeal College of Engineering & Research

$page_title = "Batch Allocation";
require_once __DIR__ . '/../includes/header.php';

// Restricted to Admin & HOD
require_role(['admin', 'hod']);

$error = '';
$success = '';

// Handle Delete Allocation Action
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $del_id = intval($_GET['id'] ?? 0);
    if ($del_id > 0) {
        $del_sql = "DELETE FROM faculty_allocations WHERE id = ?";
        $del_stmt = execute_prepared($conn, $del_sql, "i", [$del_id]);
        if ($del_stmt) {
            mysqli_stmt_close($del_stmt);
            log_audit($conn, $user['id'], $user['role'], 'Delete Allocation', 'allocation_management', 'Deleted allocation ID #' . $del_id);
            set_flash('success', 'Allocation deleted successfully.');
        }
    }
    header('Location: allocations.php');
    exit();
}

// Handle Add/Edit Allocation Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alloc_id = intval($_POST['alloc_id'] ?? 0);
    $faculty_id = intval($_POST['faculty_id'] ?? 0);
    $subject_name = sanitize($_POST['subject_name'] ?? '');
    $class = sanitize($_POST['class'] ?? 'TY');
    $division = sanitize($_POST['division'] ?? 'Division C');
    $batch_id = intval($_POST['batch_id'] ?? 0);
    $academic_year = sanitize($_POST['academic_year'] ?? DEFAULT_ACADEMIC_YEAR);

    if ($faculty_id <= 0 || empty($subject_name) || empty($division)) {
        $error = "Subject Faculty, Subject Name, and Division are required.";
    } else {
        if ($alloc_id > 0) {
            // Update
            $sql = "UPDATE faculty_allocations SET faculty_id = ?, subject_name = ?, class = ?, division = ?, batch_id = ?, academic_year = ? WHERE id = ?";
            $b_param = $batch_id > 0 ? $batch_id : null;
            $stmt = execute_prepared($conn, $sql, "isssisi", [$faculty_id, $subject_name, $class, $division, $b_param, $academic_year, $alloc_id]);
            if ($stmt) {
                mysqli_stmt_close($stmt);
                log_audit($conn, $user['id'], $user['role'], 'Edit Allocation', 'allocation_management', 'Updated allocation ID #' . $alloc_id);
                set_flash('success', 'Allocation updated successfully!');
                header('Location: allocations.php');
                exit();
            }
        } else {
            // Create
            $sql = "INSERT INTO faculty_allocations (faculty_id, subject_name, class, division, batch_id, academic_year) VALUES (?, ?, ?, ?, ?, ?)";
            $b_param = $batch_id > 0 ? $batch_id : null;
            $stmt = execute_prepared($conn, $sql, "isssis", [$faculty_id, $subject_name, $class, $division, $b_param, $academic_year]);
            if ($stmt) {
                mysqli_stmt_close($stmt);
                log_audit($conn, $user['id'], $user['role'], 'Create Allocation', 'allocation_management', 'Allocated ' . $subject_name . ' to Subject Faculty ID #' . $faculty_id);
                set_flash('success', 'Subject Faculty allocation created successfully!');
                header('Location: allocations.php');
                exit();
            }
        }
        $error = "Database operation failed.";
    }
}

// Fetch all Subject Faculty accounts for dropdown
$fac_sql = "SELECT id, full_name, email FROM users WHERE role = 'faculty' ORDER BY full_name ASC";
$fac_res = mysqli_query($conn, $fac_sql);
$faculty_members = [];
if ($fac_res) {
    while ($f = mysqli_fetch_assoc($fac_res)) {
        $faculty_members[] = $f;
    }
}

// Fetch Batches for dropdown
$batch_sql = "SELECT id, batch_name, class, division FROM batches ORDER BY batch_name ASC";
$batch_res = mysqli_query($conn, $batch_sql);
$batches_opt = [];
if ($batch_res) {
    while ($b = mysqli_fetch_assoc($batch_res)) {
        $batches_opt[] = $b;
    }
}

// Fetch Existing Allocations
$alloc_sql = "SELECT fa.*, u.full_name as faculty_name, u.email as faculty_email, b.batch_name 
              FROM faculty_allocations fa 
              JOIN users u ON fa.faculty_id = u.id 
              LEFT JOIN batches b ON fa.batch_id = b.id 
              ORDER BY fa.academic_year DESC, fa.class ASC, fa.division ASC";
$alloc_res = mysqli_query($conn, $alloc_sql);
$allocations_list = [];
if ($alloc_res) {
    while ($row = mysqli_fetch_assoc($alloc_res)) {
        $allocations_list[] = $row;
    }
}

// Pre-fill for Edit if requested
$edit_alloc = null;
if (isset($_GET['edit_id'])) {
    $e_id = intval($_GET['edit_id']);
    foreach ($allocations_list as $a) {
        if ($a['id'] == $e_id) {
            $edit_alloc = $a;
            break;
        }
    }
}
?>

<div class="alert alert-info mb-4">
  <i class="fas fa-info-circle me-2"></i> 
  <strong>Role Notice:</strong> Admin and HOD perform Subject & Batch Allocation ONLY. Practical creation, experiment scheduling, and evaluation are strictly conducted by assigned <strong>Subject Faculty</strong>.
</div>

<div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.5rem;">
  <!-- Allocation Form -->
  <div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-plus text-primary me-2"></i> 
        <?php echo $edit_alloc ? 'Edit Batch Allocation' : 'Assign Batch to Faculty'; ?>
        </h3>
      <?php if ($edit_alloc): ?>
        <a href="allocations.php" class="btn btn-secondary btn-sm">Cancel Edit</a>
      <?php endif; ?>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="hidden" name="alloc_id" value="<?php echo $edit_alloc['id'] ?? 0; ?>">

      <div class="form-group">
        <label for="faculty_id" class="form-label">Subject Faculty <span class="text-danger">*</span></label>
        <select id="faculty_id" name="faculty_id" class="form-select" required>
          <option value="">-- Select Subject Faculty Member --</option>
          <?php foreach ($faculty_members as $fm): ?>
            <option value="<?php echo $fm['id']; ?>" <?php echo ($edit_alloc['faculty_id'] ?? 0) == $fm['id'] ? 'selected' : ''; ?>>
              <?php echo sanitize($fm['full_name'] . ' (' . $fm['email'] . ')'); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="subject_name" class="form-label">Subject Name <span class="text-danger">*</span></label>
        <select id="subject_name" name="subject_name" class="form-select" required>
          <option value="Microprocessors & Microcontrollers" <?php echo ($edit_alloc['subject_name'] ?? '') === 'Microprocessors & Microcontrollers' ? 'selected' : ''; ?>>Microprocessors & Microcontrollers</option>
          <option value="Digital Signal Processing" <?php echo ($edit_alloc['subject_name'] ?? '') === 'Digital Signal Processing' ? 'selected' : ''; ?>>Digital Signal Processing</option>
          <option value="VLSI Design & Embedded Systems" <?php echo ($edit_alloc['subject_name'] ?? '') === 'VLSI Design & Embedded Systems' ? 'selected' : ''; ?>>VLSI Design & Embedded Systems</option>
          <option value="Computer Networks & Security" <?php echo ($edit_alloc['subject_name'] ?? '') === 'Computer Networks & Security' ? 'selected' : ''; ?>>Computer Networks & Security</option>
          <option value="Analog Electronics & Circuits" <?php echo ($edit_alloc['subject_name'] ?? '') === 'Analog Electronics & Circuits' ? 'selected' : ''; ?>>Analog Electronics & Circuits</option>
        </select>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label for="class" class="form-label">Class</label>
          <select id="class" name="class" class="form-select">
            <?php foreach ($CLASSES as $c): ?>
              <option value="<?php echo $c; ?>" <?php echo ($edit_alloc['class'] ?? 'TY') === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="division" class="form-label">Division <span class="text-danger">*</span></label>
          <select id="division" name="division" class="form-select">
            <option value="Division A" <?php echo ($edit_alloc['division'] ?? '') === 'Division A' ? 'selected' : ''; ?>>Division A</option>
            <option value="Division B" <?php echo ($edit_alloc['division'] ?? '') === 'Division B' ? 'selected' : ''; ?>>Division B</option>
            <option value="Division C" <?php echo ($edit_alloc['division'] ?? 'Division C') === 'Division C' ? 'selected' : ''; ?>>Division C</option>
            <option value="Division D" <?php echo ($edit_alloc['division'] ?? '') === 'Division D' ? 'selected' : ''; ?>>Division D</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="batch_id" class="form-label">Assigned Batch (Optional)</label>
        <select id="batch_id" name="batch_id" class="form-select">
          <option value="0">All Batches in Division</option>
          <?php foreach ($batches_opt as $bo): ?>
            <option value="<?php echo $bo['id']; ?>" <?php echo ($edit_alloc['batch_id'] ?? 0) == $bo['id'] ? 'selected' : ''; ?>>
              Batch <?php echo sanitize($bo['batch_name'] . ' (' . $bo['class'] . ' - ' . $bo['division'] . ')'); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="academic_year" class="form-label">Academic Year</label>
        <select id="academic_year" name="academic_year" class="form-select">
          <?php foreach ($ACADEMIC_YEARS as $ay): ?>
            <option value="<?php echo $ay; ?>" <?php echo ($edit_alloc['academic_year'] ?? DEFAULT_ACADEMIC_YEAR) === $ay ? 'selected' : ''; ?>><?php echo $ay; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-2"></i> <?php echo $edit_alloc ? 'Update Allocation' : 'Save Allocation'; ?>
      </button>
    </form>
  </div>

  <!-- Allocations Table -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-list text-primary me-2"></i> Active Allocations</h3>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Subject Faculty</th>
            <th>Subject Name</th>
            <th>Class / Div</th>
            <th>Batch</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($allocations_list)): ?>
            <tr><td colspan="5" class="text-center" style="color: var(--text-muted);">No faculty allocations recorded yet.</td></tr>
          <?php else: ?>
            <?php foreach ($allocations_list as $al): ?>
              <tr>
                <td>
                  <strong style="color: var(--text-primary);"><?php echo sanitize($al['faculty_name']); ?></strong>
                  <br><span style="font-size: 0.75rem; color: var(--text-secondary);"><?php echo sanitize($al['faculty_email']); ?></span>
                </td>
                <td><span class="badge badge-info"><?php echo sanitize($al['subject_name']); ?></span></td>
                <td><?php echo sanitize($al['class'] . ' - ' . $al['division']); ?></td>
                <td><?php echo sanitize($al['batch_name'] ?: 'All Batches'); ?></td>
                <td>
                  <a href="allocations.php?edit_id=<?php echo $al['id']; ?>" class="btn btn-secondary btn-sm" title="Edit Allocation"><i class="fas fa-edit"></i></a>
                  <a href="allocations.php?action=delete&id=<?php echo $al['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete allocation for <?php echo sanitize($al['faculty_name']); ?>?');" title="Delete Allocation"><i class="fas fa-trash"></i></a>
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
