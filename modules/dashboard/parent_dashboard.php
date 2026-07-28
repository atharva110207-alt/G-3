<?php
// Parent Read-Only Performance Portal

$page_title = 'Parent Portal';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_role(['parent', 'admin']);

$roll_no = $_SESSION['student_roll_no'] ?? '';

// Fetch child student user
$student_sql = "SELECT id, full_name, email, division, phone FROM users WHERE student_roll_no = ? AND role = 'student' LIMIT 1";
$student_stmt = execute_prepared($conn, $student_sql, "s", [$roll_no]);
$student = false;
if ($student_stmt) {
    $res = mysqli_stmt_get_result($student_stmt);
    $student = mysqli_fetch_assoc($res);
    mysqli_stmt_close($student_stmt);
}

$attendance_pct = 0;
$present_count = 0;
$total_practicals = 0;
$score_res = false;

if ($student) {
    // Attendance stats
    $att_sql = "SELECT 
                COUNT(*) as total_practicals,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count
                FROM attendance WHERE student_id = ?";
    $att_stmt = execute_prepared($conn, $att_sql, "i", [$student['id']]);
    $att_data = mysqli_fetch_assoc(mysqli_stmt_get_result($att_stmt));
    $total_practicals = intval($att_data['total_practicals'] ?? 0);
    $present_count = intval($att_data['present_count'] ?? 0);
    $attendance_pct = $total_practicals > 0 ? round(($present_count / $total_practicals) * 100, 1) : 100;

    // Evaluations
    $score_sql = "SELECT ass.*, p.exp_no, p.title, p.subject_name
                  FROM assessment ass
                  JOIN practicals p ON ass.practical_id = p.id
                  WHERE ass.student_id = ?
                  ORDER BY p.exp_no ASC";
    $score_stmt = execute_prepared($conn, $score_sql, "i", [$student['id']]);
    $score_res = mysqli_stmt_get_result($score_stmt);
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Parent Monitoring Portal</h2>
            <p style="font-size: 0.875rem; color: var(--text-muted);">
                Child: <strong><?php echo sanitize($student['full_name'] ?? 'Student'); ?></strong> (Roll: <?php echo sanitize($roll_no); ?>)
            </p>
        </div>
        <span class="badge badge-info">Read-Only Parent Mode</span>
    </div>

    <!-- Attendance Alert Banner -->
    <?php if ($attendance_pct < 75): ?>
        <div class="alert alert-danger">
            ⚠️ <strong>Attendance Warning Alert:</strong> Your child's current lab attendance is <strong><?php echo $attendance_pct; ?>%</strong> (Below the 75% mandatory requirement).
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            ✅ <strong>Good Attendance Standing:</strong> Current lab attendance is <strong><?php echo $attendance_pct; ?>%</strong>.
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $attendance_pct; ?>%</div>
                <div class="stat-label">Lab Attendance Status</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🧪</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $present_count; ?> / <?php echo $total_practicals; ?></div>
                <div class="stat-label">Sessions Attended</div>
            </div>
        </div>
    </div>

    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 1rem;">Experiment Performance & Scores</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Exp #</th>
                    <th>Subject</th>
                    <th>Experiment Title</th>
                    <th>Total Score (0-25)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($score_res && mysqli_num_rows($score_res) > 0): ?>
                    <?php while ($s = mysqli_fetch_assoc($score_res)): ?>
                        <tr>
                            <td>Exp <?php echo $s['exp_no']; ?></td>
                            <td><?php echo sanitize($s['subject_name']); ?></td>
                            <td><?php echo sanitize($s['title']); ?></td>
                            <td><strong><?php echo $s['total_score']; ?> / 25</strong></td>
                            <td>
                                <span class="badge badge-<?php echo $s['total_score'] >= 15 ? 'success' : 'danger'; ?>">
                                    <?php echo $s['total_score'] >= 15 ? 'Passed / Satisfactory' : 'Needs Improvement'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted);">No experiment marks evaluated yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
