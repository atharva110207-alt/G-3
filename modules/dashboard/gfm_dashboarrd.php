<?php
// GFM (Group Faculty Mentor) Division Dashboard

$page_title = 'GFM Dashboard';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_role(['gfm', 'admin', 'hod']);

$division = $_SESSION['division'] ?? 'Division C';

// Fetch Division C Students & Attendance Summary
$students_sql = "SELECT u.id, u.student_roll_no, u.full_name, u.email, u.phone,
                (SELECT COUNT(*) FROM attendance a WHERE a.student_id = u.id) as total_sessions,
                (SELECT SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) FROM attendance a WHERE a.student_id = u.id) as present_sessions,
                (SELECT AVG(total_score) FROM assessment ass WHERE ass.student_id = u.id) as avg_score
                FROM users u
                WHERE u.role = 'student' AND u.division = ?
                ORDER BY u.student_roll_no ASC";
$stmt = execute_prepared($conn, $students_sql, "s", [$division]);
$students_res = $stmt ? mysqli_stmt_get_result($stmt) : false;

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">GFM Class Performance Dashboard (<?php echo sanitize($division); ?>)</h2>
            <p style="font-size: 0.875rem; color: var(--text-muted);">Class-wide attendance tracking, defaulter list monitoring, and submission analytics</p>
        </div>
        <a href="../../reports/final_marksheet.php" class="btn btn-primary">📑 View Term-Work Sheets</a>
    </div>

    <!-- Student Attendance & Defaulter Tracking Table -->
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th>Sessions Attended</th>
                    <th>Attendance %</th>
                    <th>Average Score (0-25)</th>
                    <th>Status Flag</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students_res && mysqli_num_rows($students_res) > 0): ?>
                    <?php while ($st = mysqli_fetch_assoc($students_res)): 
                        $total = intval($st['total_sessions'] ?? 0);
                        $present = intval($st['present_sessions'] ?? 0);
                        $pct = $total > 0 ? round(($present / $total) * 100, 1) : 100;
                        $avg = $st['avg_score'] !== null ? round($st['avg_score'], 1) : '--';
                        $is_defaulter = $pct < 75;
                    ?>
                        <tr style="<?php echo $is_defaulter ? 'background-color: rgba(220, 38, 38, 0.05);' : ''; ?>">
                            <td><strong><?php echo sanitize($st['student_roll_no']); ?></strong></td>
                            <td><?php echo sanitize($st['full_name']); ?></td>
                            <td><?php echo $present; ?> / <?php echo $total; ?></td>
                            <td>
                                <strong><?php echo $pct; ?>%</strong>
                            </td>
                            <td><strong><?php echo $avg; ?></strong></td>
                            <td>
                                <?php if ($is_defaulter): ?>
                                    <span class="badge badge-danger">⚠️ Defaulter (&lt;75%)</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Good Standing</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted);">No student records found in <?php echo $division; ?>.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
