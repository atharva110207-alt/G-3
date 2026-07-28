<?php
// Practical Assessment System - Manual Batch Creation Module
// Zeal College of Engineering & Research

$page_title = "Manual Batch Creation";
require_once __DIR__ . '/../includes/header.php';

// Restricted to Admin & HOD
require_role(['admin', 'hod']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $batch_name = sanitize($_POST['batch_name'] ?? '');
    $start_roll = sanitize($_POST['start_roll'] ?? '');
    $end_roll = sanitize($_POST['end_roll'] ?? '');
    $class = sanitize($_POST['class'] ?? 'TY');
    $division = sanitize($_POST['division'] ?? 'Division C');
    $subject_assigned = sanitize($_POST['subject_assigned'] ?? '');
    $academic_year = sanitize($_POST['academic_year'] ?? DEFAULT_ACADEMIC_YEAR);

    if (empty($batch_name) || empty($start_roll) || empty($end_roll) || empty($division)) {
        $error = "Batch Name, Start Roll Number, End Roll Number, and Division are required.";
    } else {
        $sql = "INSERT INTO batches (batch_name, start_roll, end_roll, class, division, subject_assigned, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = execute_prepared($conn, $sql, "sssssss", [$batch_name, $start_roll, $end_roll, $class, $division, $subject_assigned, $academic_year]);

        if ($stmt) {
            $batch_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            log_audit($conn, $user['id'], $user['role'], 'Create Batch', 'batch_management', 'Created batch ' . $batch_name . ' (' . $start_roll . '-' . $end_roll . ')');
            set_flash('success', 'Batch ' . $batch_name . ' created successfully!');
            header('Location: create_batches.php');
            exit();
        } else {
            $error = "Failed to create batch in database.";
        }
    }
}

// Fetch existing batches
$batches_sql = "SELECT * FROM batches ORDER BY academic_year DESC, class ASC, division ASC, batch_name ASC";
$batches_res = mysqli_query($conn, $batches_sql);
$batches_list = [];
if ($batches_res) {
    while ($b = mysqli_fetch_assoc($batches_res)) {
        $batches_list[] = $b;
    }
}
?>

<div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.5rem;">
  <!-- Batch Creation Form -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-layer-group text-primary me-2"></i> Manual Batch Creation</h3>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="batch_name" class="form-label">Batch Name (e.g. C1, C2, A1) <span class="text-danger">*</span></label>
        <input type="text" id="batch_name" name="batch_name" class="form-control" placeholder="e.g. C1" required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label for="start_roll" class="form-label">Start Roll No <span class="text-danger">*</span></label>
          <input type="text" id="start_roll" name="start_roll" class="form-control" placeholder="e.g. EC1301" required>
        </div>

        <div class="form-group">
          <label for="end_roll" class="form-label">End Roll No <span class="text-danger">*</span></label>
          <input type="text" id="end_roll" name="end_roll" class="form-control" placeholder="e.g. EC1310" required>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label for="class" class="form-label">Class</label>
          <select id="class" name="class" class="form-select">
            <?php foreach ($CLASSES as $c): ?>
              <option value="<?php echo $c; ?>" <?php echo $c === 'TY' ? 'selected' : ''; ?>><?php echo $c; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="division" class="form-label">Division <span class="text-danger">*</span></label>
          <select id="division" name="division" class="form-select">
            <option value="Division A">Division A</option>
            <option value="Division B">Division B</option>
            <option value="Division C" selected>Division C</option>
            <option value="Division D">Division D</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="subject_assigned" class="form-label">Assigned Subject</label>
        <input type="text" id="subject_assigned" name="subject_assigned" class="form-control" placeholder="e.g. Microprocessors & Microcontrollers">
      </div>

      <div class="form-group">
        <label for="academic_year" class="form-label">Academic Year</label>
        <select id="academic_year" name="academic_year" class="form-select">
          <?php foreach ($ACADEMIC_YEARS as $ay): ?>
            <option value="<?php echo $ay; ?>"><?php echo $ay; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="fas fa-plus-circle me-2"></i> Create Batch
      </button>
    </form>
  </div>

  <!-- Existing Batches Table -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-list text-primary me-2"></i> Active Batches Directory</h3>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Batch</th>
            <th>Class / Div</th>
            <th>Roll Range</th>
            <th>Assigned Subject</th>
            <th>A.Y.</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($batches_list)): ?>
            <tr><td colspan="5" class="text-center" style="color: var(--text-muted);">No batches created yet.</td></tr>
          <?php else: ?>
            <?php foreach ($batches_list as $b): ?>
              <tr>
                <td><strong style="color: var(--accent-color); font-size: 1rem;"><?php echo sanitize($b['batch_name']); ?></strong></td>
                <td><?php echo sanitize($b['class'] . ' - ' . $b['division']); ?></td>
                <td><span class="badge badge-info"><?php echo sanitize($b['start_roll'] . ' to ' . $b['end_roll']); ?></span></td>
                <td><?php echo sanitize($b['subject_assigned'] ?: '-'); ?></td>
                <td><?php echo sanitize($b['academic_year']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
