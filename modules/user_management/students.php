<?php
// Student Directory View

$page_title = 'Students Directory';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_login();

$division = sanitize($_GET['division'] ?? 'Division C');

$sql = "SELECT u.*, b.batch_name 
        FROM users u 
        LEFT JOIN batches b ON u.division = b.division 
             AND CAST(SUBSTRING(u.student_roll_no, 3) AS UNSIGNED) >= CAST(SUBSTRING(b.start_roll, 3) AS UNSIGNED)
             AND CAST(SUBSTRING(u.student_roll_no, 3) AS UNSIGNED) <= CAST(SUBSTRING(b.end_roll, 3) AS UNSIGNED)
        WHERE u.role = 'student' AND u.division = ?
        ORDER BY u.student_roll_no ASC";

$stmt = execute_prepared($conn, $sql, "s", [$division]);
$result = $stmt ? mysqli_stmt_get_result($stmt) : false;

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Student Directory (<?php echo $division; ?>)</h2>
            <p style="font-size: 0.875rem; color: var(--text-muted);">Registered students mapped with roll numbers and auto-assigned batches</p>
        </div>
        <?php if (in_array(get_user_role(), ['admin', 'hod'])): ?>
            <a href="../../admin/add_user.php" class="btn btn-primary">➕ Register Student</a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th>Email Address</th>
                    <th>Division</th>
                    <th>Assigned Batch</th>
                    <th>Contact Phone</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><strong><?php echo sanitize($row['student_roll_no']); ?></strong></td>
                            <td><?php echo sanitize($row['full_name']); ?></td>
                            <td><?php echo sanitize($row['email']); ?></td>
                            <td><span class="badge badge-info"><?php echo sanitize($row['division']); ?></span></td>
                            <td><span class="badge badge-success"><?php echo sanitize($row['batch_name'] ?? 'Unassigned'); ?></span></td>
                            <td><?php echo sanitize($row['phone'] ?? '-'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted);">No student records found in this division.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
