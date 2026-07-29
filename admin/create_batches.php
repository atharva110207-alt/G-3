<?php
// Practical Assessment System - Manual Batch Creation Module
// Zeal College of Engineering & Research

$page_title = "Manual Batch Creation";
require_once __DIR__ . '/../includes/header.php';

// Restricted to Admin & HOD
require_role(['admin', 'hod']);

$error = '';
$success = '';

if (isset($_GET['delete_batch'])) {
    $del_id = intval($_GET['delete_batch']);
    $sql = "DELETE FROM batches WHERE id = ?";
    $stmt = execute_prepared($conn, $sql, "i", [$del_id]);
    if ($stmt) {
        log_audit($conn, $user['id'], $user['role'], 'Delete Batch', 'batch_management', 'Deleted batch ID ' . $del_id);
        set_flash('success', 'Batch deleted successfully.');
        header('Location: create_batches.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $batch_name = sanitize($_POST['batch_name'] ?? '');
    $class = sanitize($_POST['class'] ?? 'TY');
    $division = sanitize($_POST['division'] ?? 'Division C');
    $subject_assigned = sanitize($_POST['subject_assigned'] ?? '');
    $academic_year = sanitize($_POST['academic_year'] ?? DEFAULT_ACADEMIC_YEAR);
    $student_ids = $_POST['student_ids'] ?? [];

    if (empty($batch_name) || empty($division)) {
        $error = "Batch Name and Division are required.";
    } elseif (empty($student_ids)) {
        $error = "Please select at least one student to form the batch.";
    } else {
        $sql = "INSERT INTO batches (batch_name, class, division, subject_assigned, academic_year) VALUES (?, ?, ?, ?, ?)";
        $stmt = execute_prepared($conn, $sql, "sssss", [$batch_name, $class, $division, $subject_assigned, $academic_year]);

        if ($stmt) {
            $batch_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            
            // Insert into batch_students
            $bs_sql = "INSERT INTO batch_students (batch_id, student_id) VALUES (?, ?)";
            foreach ($student_ids as $sid) {
                $bs_stmt = execute_prepared($conn, $bs_sql, "ii", [$batch_id, intval($sid)]);
                if ($bs_stmt) mysqli_stmt_close($bs_stmt);
            }

            log_audit($conn, $user['id'], $user['role'], 'Create Batch', 'batch_management', 'Created batch ' . $batch_name . ' with ' . count($student_ids) . ' students');
            set_flash('success', 'Batch ' . $batch_name . ' created successfully!');
            header('Location: create_batches.php');
            exit();
        } else {
            $error = "Failed to create batch in database.";
        }
    }
}
?>

<div style="max-width: 800px; margin: 0 auto;">
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
          <label for="class" class="form-label">Class</label>
          <select id="class" name="class" class="form-select" onchange="loadStudents()">
            <?php foreach ($CLASSES as $c): ?>
              <option value="<?php echo $c; ?>" <?php echo $c === 'TY' ? 'selected' : ''; ?>><?php echo $c; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="division" class="form-label">Division <span class="text-danger">*</span></label>
          <select id="division" name="division" class="form-select" onchange="loadStudents()">
            <option value="Division A">Division A</option>
            <option value="Division B">Division B</option>
            <option value="Division C" selected>Division C</option>
            <option value="Division D">Division D</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Select Students for Batch <span class="text-danger">*</span></label>
        <div id="studentsContainer">
          <div class="alert alert-secondary text-center">Loading students...</div>
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
</div>

<script>
function loadStudents() {
  const cls = document.getElementById('class').value;
  const div = document.getElementById('division').value;
  const container = document.getElementById('studentsContainer');
  
  if(cls && div) {
      container.innerHTML = '<div class="alert alert-secondary text-center">Loading...</div>';
      fetch(`ajax_get_students.php?class=${cls}&division=${div}`)
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(err => {
            container.innerHTML = '<div class="alert alert-danger">Failed to fetch students.</div>';
        });
  }
}

function toggleAllStudents(status) {
    const checkboxes = document.querySelectorAll('#studentsContainer input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = status);
}

document.addEventListener('DOMContentLoaded', loadStudents);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
