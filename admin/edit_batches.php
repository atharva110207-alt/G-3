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

// Handle Add Student to Batch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student_id'])) {
    $edit_batch_id = intval($_POST['edit_batch_id']);
    $student_id = intval($_POST['add_student_id']);
    if ($edit_batch_id > 0 && $student_id > 0) {
        $sql = "INSERT IGNORE INTO batch_students (batch_id, student_id) VALUES (?, ?)";
        execute_prepared($conn, $sql, "ii", [$edit_batch_id, $student_id]);
        set_flash('success', 'Student added to batch successfully.');
        header('Location: edit_batches.php?edit_id=' . $edit_batch_id);
        exit();
    }
}

// Handle Remove Student from Batch
if (isset($_GET['remove_student_id']) && isset($_GET['from_batch'])) {
    $student_id = intval($_GET['remove_student_id']);
    $edit_batch_id = intval($_GET['from_batch']);
    if ($edit_batch_id > 0 && $student_id > 0) {
        $sql = "DELETE FROM batch_students WHERE batch_id = ? AND student_id = ?";
        execute_prepared($conn, $sql, "ii", [$edit_batch_id, $student_id]);
        set_flash('success', 'Student removed from batch.');
        header('Location: edit_batches.php?edit_id=' . $edit_batch_id);
        exit();
    }
}

// If in Edit Mode
$edit_mode = false;
$edit_batch = null;
$batch_students = [];
$available_students = [];

if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $edit_batch_id = intval($_GET['edit_id']);
    
    // Fetch Batch Details
    $b_sql = "SELECT * FROM batches WHERE id = ?";
    $b_stmt = execute_prepared($conn, $b_sql, "i", [$edit_batch_id]);
    if ($b_stmt) {
        $res = mysqli_stmt_get_result($b_stmt);
        if ($row = mysqli_fetch_assoc($res)) {
            $edit_batch = $row;
        }
        mysqli_stmt_close($b_stmt);
    }
    
    if ($edit_batch) {
        // Fetch Current Students in this Batch
        $bs_sql = "SELECT u.id, u.full_name, u.student_roll_no, u.zprn FROM batch_students bs JOIN users u ON bs.student_id = u.id WHERE bs.batch_id = ? ORDER BY u.student_roll_no ASC";
        $bs_stmt = execute_prepared($conn, $bs_sql, "i", [$edit_batch_id]);
        if ($bs_stmt) {
            $res = mysqli_stmt_get_result($bs_stmt);
            while ($row = mysqli_fetch_assoc($res)) {
                $batch_students[] = $row;
            }
            mysqli_stmt_close($bs_stmt);
        }
        
        // Fetch Available Students in same Class/Div not already in this batch
        $as_sql = "SELECT id, full_name, student_roll_no, zprn FROM users WHERE role = 'student' AND class = ? AND division = ? AND id NOT IN (SELECT student_id FROM batch_students WHERE batch_id = ?) ORDER BY student_roll_no ASC";
        $as_stmt = execute_prepared($conn, $as_sql, "ssi", [$edit_batch['class'], $edit_batch['division'], $edit_batch_id]);
        if ($as_stmt) {
            $res = mysqli_stmt_get_result($as_stmt);
            while ($row = mysqli_fetch_assoc($res)) {
                $available_students[] = $row;
            }
            mysqli_stmt_close($as_stmt);
        }
    }
} else {
    // Normal List Mode
    // Filters
    $filter_ay = sanitize($_GET['ay'] ?? '');
    $filter_sem = sanitize($_GET['sem'] ?? '');
    $filter_class = sanitize($_GET['class'] ?? '');
    $filter_div = sanitize($_GET['division'] ?? '');
    $filter_name = sanitize($_GET['batch_name'] ?? '');

    $batches_sql = "SELECT b.*, COUNT(bs.student_id) as student_count FROM batches b LEFT JOIN batch_students bs ON b.id = bs.batch_id WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($filter_ay)) { $batches_sql .= " AND b.academic_year = ?"; $params[] = $filter_ay; $types .= "s"; }
    // if (!empty($filter_sem)) { ... } // Mock semester, not in DB
    if (!empty($filter_class)) { $batches_sql .= " AND b.class = ?"; $params[] = $filter_class; $types .= "s"; }
    if (!empty($filter_div)) { $batches_sql .= " AND b.division = ?"; $params[] = $filter_div; $types .= "s"; }
    if (!empty($filter_name)) { $batches_sql .= " AND b.batch_name LIKE ?"; $params[] = "%".$filter_name."%"; $types .= "s"; }

    $batches_sql .= " GROUP BY b.id ORDER BY b.academic_year DESC, b.class ASC, b.division ASC, b.batch_name ASC";
    
    $batches_stmt = execute_prepared($conn, $batches_sql, $types, $params);
    $batches_list = [];
    if ($batches_stmt) {
        $batches_res = mysqli_stmt_get_result($batches_stmt);
        while ($b = mysqli_fetch_assoc($batches_res)) {
            $batches_list[] = $b;
        }
    }
}
?>

<div style="max-width: 1000px; margin: 0 auto;">
  <?php if ($edit_mode && $edit_batch): ?>
    <!-- EDIT BATCH INTERFACE -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-edit text-primary me-2"></i> Edit Batch: <?php echo sanitize($edit_batch['batch_name']); ?></h3>
        <a href="edit_batches.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Batches</a>
      </div>

      <?php $flash = get_flash(); if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><i class="fas fa-info-circle me-2"></i> <?php echo sanitize($flash['message']); ?></div>
      <?php endif; ?>

      <div class="alert alert-info">
        <strong>Constraint Notice:</strong> Adding or removing students will automatically update their assessment portal. This does <strong>NOT</strong> break or alter the batch's faculty assignment.
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Current Students -->
        <div>
          <h4 style="font-size: 1.1rem; color: var(--text-primary); margin-bottom: 1rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem;">Current Students (<?php echo count($batch_students); ?>)</h4>
          <?php if (empty($batch_students)): ?>
            <div class="alert alert-secondary">No students currently in this batch.</div>
          <?php else: ?>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem; max-height: 400px; overflow-y: auto;">
              <?php foreach ($batch_students as $s): ?>
                <li style="background: rgba(255,255,255,0.05); padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                  <div>
                    <strong style="display: block; color: var(--text-primary); font-size: 0.9rem;"><?php echo sanitize($s['full_name']); ?></strong>
                    <span style="font-size: 0.8rem; color: var(--text-secondary);">Roll No: <?php echo sanitize($s['student_roll_no']); ?> | ZPRN: <?php echo sanitize($s['zprn']); ?></span>
                  </div>
                  <a href="?remove_student_id=<?php echo $s['id']; ?>&from_batch=<?php echo $edit_batch_id; ?>" class="btn btn-sm btn-outline-danger" title="Remove" onclick="return confirm('Remove student from this batch?');">
                    <i class="fas fa-minus"></i>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <!-- Add New Students -->
        <div>
          <h4 style="font-size: 1.1rem; color: var(--text-primary); margin-bottom: 1rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem;">Add Students</h4>
          <form method="POST" action="">
            <input type="hidden" name="edit_batch_id" value="<?php echo $edit_batch_id; ?>">
            <div class="form-group">
              <label for="add_student_id" class="form-label">Select Student</label>
              <select name="add_student_id" id="add_student_id" class="form-select" required>
                <option value="">-- Choose Student --</option>
                <?php foreach ($available_students as $as): ?>
                  <option value="<?php echo $as['id']; ?>"><?php echo sanitize($as['student_roll_no'] . ' - ' . $as['full_name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn btn-success w-100"><i class="fas fa-plus me-2"></i> Add Student to Batch</button>
          </form>
        </div>
      </div>
    </div>

  <?php else: ?>
    <!-- BATCH LIST MODE -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list text-primary me-2"></i> Active Batches Directory</h3>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?></div>
      <?php endif; ?>

      <?php $flash = get_flash(); if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"><i class="fas fa-info-circle me-2"></i> <?php echo sanitize($flash['message']); ?></div>
      <?php endif; ?>

      <!-- Top Filter Form -->
      <form method="GET" action="" style="background: rgba(0,0,0,0.1); padding: 1rem; border-radius: 10px; margin-bottom: 1rem; border: 1px solid var(--border-color); display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        
        <select name="ay" class="form-select" style="width: auto;">
          <option value="">All Academic Years</option>
          <?php foreach ($ACADEMIC_YEARS as $ay): ?>
            <option value="<?php echo $ay; ?>" <?php echo $filter_ay === $ay ? 'selected' : ''; ?>><?php echo $ay; ?></option>
          <?php endforeach; ?>
        </select>
        
        <select name="sem" class="form-select" style="width: auto;">
          <option value="">All Semesters</option>
          <option value="SEM 1" <?php echo $filter_sem === 'SEM 1' ? 'selected' : ''; ?>>SEM 1</option>
          <option value="SEM 2" <?php echo $filter_sem === 'SEM 2' ? 'selected' : ''; ?>>SEM 2</option>
          <option value="SEM 3" <?php echo $filter_sem === 'SEM 3' ? 'selected' : ''; ?>>SEM 3</option>
          <option value="SEM 4" <?php echo $filter_sem === 'SEM 4' ? 'selected' : ''; ?>>SEM 4</option>
          <option value="SEM 5" <?php echo $filter_sem === 'SEM 5' ? 'selected' : ''; ?>>SEM 5</option>
          <option value="SEM 6" <?php echo $filter_sem === 'SEM 6' ? 'selected' : ''; ?>>SEM 6</option>
          <option value="SEM 7" <?php echo $filter_sem === 'SEM 7' ? 'selected' : ''; ?>>SEM 7</option>
          <option value="SEM 8" <?php echo $filter_sem === 'SEM 8' ? 'selected' : ''; ?>>SEM 8</option>
        </select>

        <select name="class" class="form-select" style="width: auto;">
          <option value="">All Classes</option>
          <?php foreach ($CLASSES as $c): ?>
            <option value="<?php echo $c; ?>" <?php echo $filter_class === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
          <?php endforeach; ?>
        </select>

        <select name="division" class="form-select" style="width: auto;">
          <option value="">All Divisions</option>
          <option value="Division A" <?php echo $filter_div === 'Division A' ? 'selected' : ''; ?>>Division A</option>
          <option value="Division B" <?php echo $filter_div === 'Division B' ? 'selected' : ''; ?>>Division B</option>
          <option value="Division C" <?php echo $filter_div === 'Division C' ? 'selected' : ''; ?>>Division C</option>
        </select>

        <input type="text" name="batch_name" class="form-control" placeholder="Batch Name" value="<?php echo sanitize($filter_name); ?>" style="width: 150px;">
        
        <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
        <a href="edit_batches.php" class="btn btn-outline-secondary btn-sm">Clear</a>
      </form>

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Batch</th>
              <th>Class / Div</th>
              <th>Students</th>
              <th>A.Y.</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($batches_list)): ?>
              <tr><td colspan="5" class="text-center" style="color: var(--text-muted);">No batches matched the filter.</td></tr>
            <?php else: ?>
              <?php foreach ($batches_list as $b): ?>
                <tr>
                  <td><strong style="color: var(--accent-color); font-size: 1rem;"><?php echo sanitize($b['batch_name']); ?></strong></td>
                  <td><?php echo sanitize($b['class'] . ' - ' . $b['division']); ?></td>
                  <td><span class="badge badge-info"><?php echo $b['student_count']; ?> Students</span></td>
                  <td><?php echo sanitize($b['academic_year']); ?></td>
                  <td>
                    <a href="?edit_id=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit Batch Students">
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
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php ob_end_flush(); ?>
