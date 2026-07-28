<?php
// Student Personal Practical Performance & Attendance Portal

$page_title = 'Student Portal';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_role(['student', 'admin']);

$student_id = $_SESSION['user_id'];
$roll_no = $_SESSION['student_roll_no'] ?? '';

// Calculate Attendance %
$att_sql = "SELECT 
            COUNT(*) as total_practicals,
            SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count
            FROM attendance WHERE student_id = ?";
$att_stmt = execute_prepared($conn, $att_sql, "i", [$student_id]);
$att_res = $att_stmt ? mysqli_fetch_assoc(mysqli_stmt_get_result($att_stmt)) : ['total_practicals' => 0, 'present_count' => 0];

$total_practicals = intval($att_res['total_practicals'] ?? 0);
$present_count = intval($att_res['present_count'] ?? 0);
$attendance_pct = $total_practicals > 0 ? round(($present_count / $total_practicals) * 100, 1) : 100;

// Fetch Experiment Score Breakdown
$score_sql = "SELECT ass.*, p.exp_no, p.title, p.subject_name, p.scheduled_date, u.full_name as faculty_name
              FROM assessment ass
              JOIN practicals p ON ass.practical_id = p.id
              LEFT JOIN users u ON ass.faculty_id = u.id
              WHERE ass.student_id = ?
              ORDER BY p.exp_no ASC";
$score_stmt = execute_prepared($conn, $score_sql, "i", [$student_id]);
$score_res = $score_stmt ? mysqli_stmt_get_result($score_stmt) : false;

// Cumulative Average calculation
$total_obtained = 0;
$exp_count = 0;

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Student Performance Dashboard</h2>
            <p style="font-size: 0.875rem; color: var(--text-muted);">Roll Number: <strong><?php echo sanitize($roll_no); ?></strong> | Division: <strong><?php echo sanitize($_SESSION['division'] ?? 'Division C'); ?></strong></p>
        </div>
        <span class="badge badge-info">Term 2025-2026</span>
    </div>

    <!-- Overview Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $attendance_pct; ?>%</div>
                <div class="stat-label">Practical Attendance</div>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: <?php echo $attendance_pct; ?>%; background-color: <?php echo $attendance_pct >= 75 ? '#16a34a' : '#dc2626'; ?>;"></div>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🧪</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $present_count; ?> / <?php echo $total_practicals; ?></div>
                <div class="stat-label">Sessions Attended</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-content">
                <div id="cumulAverageDisplay" class="stat-value">--</div>
                <div class="stat-label">Cumulative Score (out of 25)</div>
            </div>
        </div>
    </div>

    <!-- Detailed Experiment Breakdown Cards -->
    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 1rem;">Experiment-by-Experiment Score Breakdown (25 Marks Max)</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem;">
        <?php if ($score_res && mysqli_num_rows($score_res) > 0): ?>
            <?php while ($s = mysqli_fetch_assoc($score_res)): 
                $total_obtained += $s['total_score'];
                $exp_count++;
            ?>
                <div style="background-color: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                        <div>
                            <span class="badge badge-info">Exp <?php echo $s['exp_no']; ?></span>
                            <h4 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-top: 0.25rem;"><?php echo sanitize($s['title']); ?></h4>
                            <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo sanitize($s['subject_name']); ?></span>
                        </div>
                        <div class="total-score-badge <?php echo $s['total_score'] >= 22 ? 'score-perfect' : ($s['total_score'] >= 15 ? 'score-warning' : 'score-danger'); ?>">
                            <?php echo $s['total_score']; ?> / 25
                        </div>
                    </div>

                    <!-- 4 Evaluation Criteria Breakdown -->
                    <div style="font-size: 0.8125rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin: 1rem 0; background-color: var(--bg-primary); padding: 0.75rem; border-radius: var(--radius-sm);">
                        <div>Regularity: <strong><?php echo $s['regularity_score']; ?>/5</strong></div>
                        <div>Conduction: <strong><?php echo $s['conduction_score']; ?>/10</strong></div>
                        <div>Output: <strong><?php echo $s['output_score']; ?>/5</strong></div>
                        <div>Viva / Concept: <strong><?php echo $s['viva_score']; ?>/5</strong></div>
                    </div>

                    <?php if (!empty($s['comments'])): ?>
                        <div style="font-size: 0.8125rem; color: var(--text-secondary); font-style: italic; border-left: 3px solid var(--accent-color); padding-left: 0.5rem;">
                            💬 Feedback: "<?php echo sanitize($s['comments']); ?>"
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: var(--text-muted);">
                No practical evaluations recorded yet for your account.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$cumul_avg = $exp_count > 0 ? round($total_obtained / $exp_count, 1) : 0;
?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cumulDisplay = document.getElementById('cumulAverageDisplay');
        if (cumulDisplay) {
            cumulDisplay.textContent = '<?php echo $cumul_avg; ?> / 25';
        }
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
