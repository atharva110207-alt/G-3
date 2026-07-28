<?php
// Attendance Summary Report

$page_title = 'Attendance Report';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_login();

$sql = "SELECT u.student_roll_no, u.full_name, u.division,
        COUNT(a.id) as total_sessions,
        SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present_count
        FROM users u 
        LEFT JOIN attendance a ON u.id = a.student_id
        WHERE u.role = 'student' AND u.division = 'Division C'
        GROUP BY u.id
        ORDER BY u.student_roll_no ASC";
$result = mysqli_query($conn, $sql);

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Class Practical Attendance Summary</h2>
        <button id="printReportBtn" class="btn btn-secondary btn-sm">🖨️ Print Report</button>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th>Division</th>
                    <th>Total Sessions</th>
                    <th>Attended</th>
                    <th>Attendance %</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($result)): 
                        $total = intval($r['total_sessions']);
                        $present = intval($r['present_count']);
                        $pct = $total > 0 ? round(($present / $total) * 100, 1) : 100;
                    ?>
                        <tr>
                            <td><strong><?php echo sanitize($r['student_roll_no']); ?></strong></td>
                            <td><?php echo sanitize($r['full_name']); ?></td>
                            <td><?php echo sanitize($r['division']); ?></td>
                            <td><?php echo $total; ?></td>
                            <td><?php echo $present; ?></td>
                            <td><span class="badge badge-<?php echo $pct >= 75 ? 'success' : 'danger'; ?>"><?php echo $pct; ?>%</span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No attendance summary available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
