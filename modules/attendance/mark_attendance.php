<?php
// Practical Assessment System - Interactive Student Attendance Marker
// Zeal College of Engineering & Research

$page_title = "Mark Practical Attendance";
require_once __DIR__ . '/../../includes/header.php';

require_role(['faculty', 'admin', 'hod']);

$practical_id = intval($_GET['practical_id'] ?? 0);
$error = '';
$success = '';

// Handle Attendance Save Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $practical_id = intval($_POST['practical_id'] ?? 0);
    $attendance_data = $_POST['attendance_status'] ?? []; // student_id => 'Present' or 'Absent'

    if ($practical_id > 0 && !empty($attendance_data)) {
        foreach ($attendance_data as $student_id => $status) {
            $student_id = intval($student_id);
            $status = ($status === 'Present') ? 'Present' : 'Absent';
            
            $sql = "INSERT INTO attendance (practical_id, student_id, status, date_marked) VALUES (?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE status = VALUES(status), date_marked = NOW()";
            $stmt = execute_prepared($conn, $sql, "iis", [$practical_id, $student_id, $status]);
            if ($stmt) { mysqli_stmt_close($stmt); }
        }
        log_audit($conn, $user['id'], $user['role'], 'Mark Attendance', 'attendance', 'Logged attendance for practical ID #' . $practical_id);
        set_flash('success', 'Practical attendance recorded successfully!');
        header('Location: mark_attendance.php?practical_id=' . $practical_id);
        exit();
    } else {
        $error = "Please select a practical experiment and mark attendance status.";
    }
}

// Fetch Practicals List for Dropdown
$pract_sql = "SELECT p.*, b.batch_name FROM practicals p JOIN batches b ON p.batch_id = b.id ORDER BY p.scheduled_date DESC";
$pract_res = mysqli_query($conn, $pract_sql);
$practicals_opt = [];
if ($pract_res) {
    while ($pr = mysqli_fetch_assoc($pract_res)) {
        $practicals_opt[] = $pr;
    }
}

// Selected Practical Details
$selected_pract = null;
$students_in_batch = [];
$existing_attendance = [];

if ($practical_id > 0) {
    foreach ($practicals_opt as $p) {
        if ($p['id'] == $practical_id) {
            $selected_pract = $p;
            break;
        }
    }

    if ($selected_pract) {
        // Fetch Students belonging to the batch or division
        $b_id = $selected_pract['batch_id'];
        
        // Fetch batch details to get roll range if available
        $b_sql = "SELECT * FROM batches WHERE id = ?";
        $b_stmt = execute_prepared($conn, $b_sql, "i", [$b_id]);
        $batch_info = null;
        if ($b_stmt) {
            $res = mysqli_stmt_get_result($b_stmt);
            $batch_info = mysqli_fetch_assoc($res);
            mysqli_stmt_close($b_stmt);
        }

        if ($batch_info && !empty($batch_info['start_roll']) && !empty($batch_info['end_roll'])) {
            $st_sql = "SELECT id, full_name, student_roll_no, zprn, division FROM users WHERE role = 'student' AND student_roll_no >= ? AND student_roll_no <= ? ORDER BY student_roll_no ASC";
            $st_stmt = execute_prepared($conn, $st_sql, "ss", [$batch_info['start_roll'], $batch_info['end_roll']]);
        } else {
            $st_sql = "SELECT id, full_name, student_roll_no, zprn, division FROM users WHERE role = 'student' AND division = ? ORDER BY student_roll_no ASC";
            $st_stmt = execute_prepared($conn, $st_sql, "s", [$selected_pract['division']]);
        }

        if ($st_stmt) {
            $res = mysqli_stmt_get_result($st_stmt);
            while ($st = mysqli_fetch_assoc($res)) {
                $students_in_batch[] = $st;
            }
            mysqli_stmt_close($st_stmt);
        }

        // Existing Attendance Records
        $att_sql = "SELECT student_id, status FROM attendance WHERE practical_id = ?";
        $att_stmt = execute_prepared($conn, $att_sql, "i", [$practical_id]);
        if ($att_stmt) {
            $res = mysqli_stmt_get_result($att_stmt);
            while ($ar = mysqli_fetch_assoc($res)) {
                $existing_attendance[$ar['student_id']] = $ar['status'];
            }
            mysqli_stmt_close($att_stmt);
        }
    }
}
?>

<div class="card mb-4">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-calendar-check text-primary me-2"></i> Interactive Student Attendance Toggle</h3>
  </div>

  <form method="GET" action="" class="action-bar" style="margin-bottom: 0;">
    <div style="flex: 1;">
      <label for="practical_id" class="form-label">Select Scheduled Practical Experiment <span class="text-danger">*</span></label>
      <select id="practical_id" name="practical_id" class="form-select" onchange="this.form.submit()">
        <option value="">-- Choose Experiment --</option>
        <?php foreach ($practicals_opt as $po): ?>
          <option value="<?php echo $po['id']; ?>" <?php echo $practical_id == $po['id'] ? 'selected' : ''; ?>>
            Exp #<?php echo $po['exp_no']; ?>: <?php echo sanitize($po['title']); ?> (Batch <?php echo sanitize($po['batch_name'] . ' - Plan Date: ' . format_date($po['scheduled_date'])); ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</div>

<?php if ($selected_pract): ?>
  <div class="card">
    <div class="card-header">
      <div>
        <h3 class="card-title">
          <i class="fas fa-users text-accent me-2"></i> 
          Attendance Roster for Exp #<?php echo $selected_pract['exp_no']; ?>: <?php echo sanitize($selected_pract['title']); ?>
        </h3>
        <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem;">
          Plan Date: <strong><?php echo format_date($selected_pract['scheduled_date']); ?></strong> &bull; Batch: <strong><?php echo sanitize($selected_pract['batch_name']); ?></strong>
        </p>
      </div>
      <div style="display: flex; gap: 0.5rem;">
        <button type="button" class="btn btn-secondary btn-sm" onclick="setAllAttendance('Present')"><i class="fas fa-check-double me-1"></i> Mark All Present</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="setAllAttendance('Absent')"><i class="fas fa-times me-1"></i> Mark All Absent</button>
      </div>
    </div>

    <form method="POST" action="">
      <input type="hidden" name="save_attendance" value="1">
      <input type="hidden" name="practical_id" value="<?php echo $selected_pract['id']; ?>">

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Roll Number</th>
              <th>Student Name</th>
              <th>ZPRN</th>
              <th>Attendance Status Toggle</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($students_in_batch)): ?>
              <tr><td colspan="4" class="text-center" style="color: var(--text-muted); padding: 2rem;">No students found in selected batch roster.</td></tr>
            <?php else: ?>
              <?php foreach ($students_in_batch as $st): ?>
                <?php 
                  $current_status = $existing_attendance[$st['id']] ?? 'Present'; 
                ?>
                <tr>
                  <td><strong class="badge badge-info" style="font-size: 0.85rem;"><?php echo sanitize($st['student_roll_no']); ?></strong></td>
                  <td><strong style="color: var(--text-primary);"><?php echo sanitize($st['full_name']); ?></strong></td>
                  <td><?php echo sanitize($st['zprn'] ?: '-'); ?></td>
                  <td>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                      <label class="toggle-switch">
                        <input type="checkbox" name="attendance_status[<?php echo $st['id']; ?>]" value="Present" class="att-toggle-checkbox" <?php echo $current_status === 'Present' ? 'checked' : ''; ?> onchange="updateToggleLabel(this)">
                        <span class="toggle-slider"></span>
                      </label>
                      <span class="att-status-label badge <?php echo $current_status === 'Present' ? 'badge-success' : 'badge-danger'; ?>">
                        <?php echo $current_status; ?>
                      </span>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if (!empty($students_in_batch)): ?>
        <div style="padding: 1.5rem; border-top: 1px solid var(--border-color); text-align: right;">
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save me-2"></i> Save Attendance Records
          </button>
        </div>
      <?php endif; ?>
    </form>
  </div>
<?php endif; ?>

<script>
function updateToggleLabel(checkbox) {
  const label = checkbox.parentElement.nextElementSibling;
  if (checkbox.checked) {
    checkbox.value = "Present";
    label.className = "att-status-label badge badge-success";
    label.innerText = "Present";
  } else {
    checkbox.value = "Absent";
    label.className = "att-status-label badge badge-danger";
    label.innerText = "Absent";
  }
}

function setAllAttendance(status) {
  const checkboxes = document.querySelectorAll('.att-toggle-checkbox');
  checkboxes.forEach(cb => {
    cb.checked = (status === 'Present');
    updateToggleLabel(cb);
  });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
