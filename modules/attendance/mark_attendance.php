<?php
// Interactive Attendance Marker Module

$page_title = 'Mark Batch Attendance';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_role(['faculty', 'admin', 'hod']);

$practical_id = intval($_GET['practical_id'] ?? 0);

// Fetch practical details
$pract_sql = "SELECT p.*, b.batch_name, b.start_roll, b.end_roll 
              FROM practicals p 
              JOIN batches b ON p.batch_id = b.id 
              WHERE p.id = ? LIMIT 1";
$pract_stmt = execute_prepared($conn, $pract_sql, "i", [$practical_id]);
$pract = false;
if ($pract_stmt) {
    $res = mysqli_stmt_get_result($pract_stmt);
    $pract = mysqli_fetch_assoc($res);
    mysqli_stmt_close($pract_stmt);
}

// Fallback to select first available practical if ID not passed
if (!$pract) {
    $first_res = mysqli_query($conn, "SELECT id FROM practicals ORDER BY id DESC LIMIT 1");
    if ($first_res && $row = mysqli_fetch_assoc($first_res)) {
        header('Location: mark_attendance.php?practical_id=' . $row['id']);
        exit();
    }
}

// Handle Attendance Submission POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_attendance'])) {
    $attendance_data = $_POST['status'] ?? []; // array student_id => status

    foreach ($attendance_data as $student_id => $status) {
        $student_id = intval($student_id);
        $status = in_array($status, ['Present', 'Absent']) ? $status : 'Present';

        $ins_sql = "INSERT INTO attendance (practical_id, student_id, status) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE status = VALUES(status), date_marked = NOW()";
        $stmt = execute_prepared($conn, $ins_sql, "iis", [$practical_id, $student_id, $status]);
        if ($stmt) mysqli_stmt_close($stmt);
    }

    log_audit($conn, $_SESSION['user_id'], 'Marked Practical Attendance', 'attendance', "Marked attendance for Practical Exp #{$pract['exp_no']} (Batch {$pract['batch_name']})");
    set_flash('success', 'Attendance marked successfully!');
    header("Location: mark_attendance.php?practical_id=$practical_id");
    exit();
}

// Fetch Students in Batch
$students_sql = "SELECT u.id, u.full_name, u.student_roll_no, a.status 
                FROM users u 
                LEFT JOIN attendance a ON a.student_id = u.id AND a.practical_id = ?
                WHERE u.role = 'student' AND u.division = ?
                AND CAST(SUBSTRING(u.student_roll_no, 3) AS UNSIGNED) >= CAST(SUBSTRING(?, 3) AS UNSIGNED)
                AND CAST(SUBSTRING(u.student_roll_no, 3) AS UNSIGNED) <= CAST(SUBSTRING(?, 3) AS UNSIGNED)
                ORDER BY u.student_roll_no ASC";

$stmt = execute_prepared($conn, $students_sql, "isss", [$practical_id, $pract['division'], $pract['start_roll'], $pract['end_roll']]);
$students_res = $stmt ? mysqli_stmt_get_result($stmt) : false;

// Fetch all practicals for dropdown switcher
$all_pract_res = mysqli_query($conn, "SELECT p.id, p.exp_no, p.title, b.batch_name FROM practicals p JOIN batches b ON p.batch_id = b.id ORDER BY p.id DESC");

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Mark Attendance: Exp <?php echo $pract['exp_no']; ?> (<?php echo sanitize($pract['batch_name']); ?>)</h2>
            <p style="font-size: 0.875rem; color: var(--text-muted);">
                Subject: <strong><?php echo sanitize($pract['subject_name']); ?></strong> | Title: <strong><?php echo sanitize($pract['title']); ?></strong> | Date: <strong><?php echo format_date($pract['scheduled_date']); ?></strong>
            </p>
        </div>

        <!-- Switch Practical Dropdown -->
        <select class="form-select" style="width: auto;" onchange="location = 'mark_attendance.php?practical_id=' + this.value;">
            <?php if ($all_pract_res): while ($ap = mysqli_fetch_assoc($all_pract_res)): ?>
                <option value="<?php echo $ap['id']; ?>" <?php echo $ap['id'] == $practical_id ? 'selected' : ''; ?>>
                    Exp <?php echo $ap['exp_no']; ?> - <?php echo sanitize($ap['title']); ?> (<?php echo sanitize($ap['batch_name']); ?>)
                </option>
            <?php endwhile; endif; ?>
        </select>
    </div>

    <!-- Quick Action Bar & Counter Displays -->
    <div class="attendance-header-actions">
        <div style="display: flex; gap: 1rem; align-items: center;">
            <span class="badge badge-success" style="font-size: 0.875rem; padding: 0.5rem 0.875rem;">Present: <span id="presentCountDisplay">0</span></span>
            <span class="badge badge-danger" style="font-size: 0.875rem; padding: 0.5rem 0.875rem;">Absent: <span id="absentCountDisplay">0</span></span>
        </div>

        <div class="attendance-toggle-box">
            <button type="button" id="markAllPresent" class="btn btn-secondary btn-sm">✅ Mark All Present</button>
            <button type="button" id="markAllAbsent" class="btn btn-secondary btn-sm">❌ Mark All Absent</button>
        </div>
    </div>

    <form action="" method="POST">
        <input type="hidden" name="submit_attendance" value="1">

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Roll No</th>
                        <th>Student Name</th>
                        <th>Attendance Status Toggle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students_res && mysqli_num_rows($students_res) > 0): ?>
                        <?php while ($st = mysqli_fetch_assoc($students_res)): 
                            $curr_status = $st['status'] ?? 'Present';
                        ?>
                            <tr class="attendance-row">
                                <td><strong><?php echo sanitize($st['student_roll_no']); ?></strong></td>
                                <td><?php echo sanitize($st['full_name']); ?></td>
                                <td>
                                    <div class="status-radio-group">
                                        <label class="status-radio-label" style="color: var(--status-success-text);">
                                            <input type="radio" name="status[<?php echo $st['id']; ?>]" value="Present" <?php echo $curr_status === 'Present' ? 'checked' : ''; ?>>
                                            Present
                                        </label>
                                        <label class="status-radio-label" style="color: var(--status-danger-text);">
                                            <input type="radio" name="status[<?php echo $st['id']; ?>]" value="Absent" <?php echo $curr_status === 'Absent' ? 'checked' : ''; ?>>
                                            Absent
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted);">No students found in batch range.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Save Attendance Sheet</button>
        </div>
    </form>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/attendance.js"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
