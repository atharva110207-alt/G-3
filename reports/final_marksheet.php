<?php
// Consolidated Term-Work Final Marksheet Report (Out of 25 / 50)

$page_title = 'Final Term-Work Marksheet';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$division = sanitize($_GET['division'] ?? 'Division C');

// Query all students in Division C with experiment score averages
$sql = "SELECT u.id, u.student_roll_no, u.full_name, b.batch_name,
        COUNT(ass.id) as exp_evaluated,
        SUM(ass.total_score) as sum_obtained,
        AVG(ass.total_score) as avg_score_25
        FROM users u 
        LEFT JOIN batches b ON u.division = b.division 
             AND CAST(SUBSTRING(u.student_roll_no, 3) AS UNSIGNED) >= CAST(SUBSTRING(b.start_roll, 3) AS UNSIGNED)
             AND CAST(SUBSTRING(u.student_roll_no, 3) AS UNSIGNED) <= CAST(SUBSTRING(b.end_roll, 3) AS UNSIGNED)
        LEFT JOIN assessment ass ON ass.student_id = u.id
        WHERE u.role = 'student' AND u.division = ?
        GROUP BY u.id
        ORDER BY u.student_roll_no ASC";

$stmt = execute_prepared($conn, $sql, "s", [$division]);
$result = $stmt ? mysqli_stmt_get_result($stmt) : false;

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header no-print">
        <div>
            <h2 class="card-title">Consolidated Term-Work Marksheet (<?php echo sanitize($division); ?>)</h2>
            <p style="font-size: 0.875rem; color: var(--text-muted);">Normalized Term-Work Scores out of 25 & 50 Marks for Academic Submission</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="export_excel.php?division=<?php echo urlencode($division); ?>" class="btn btn-secondary btn-sm">📊 Export Excel (CSV)</a>
            <button id="printReportBtn" class="btn btn-primary btn-sm">🖨️ Print Final Sheet</button>
        </div>
    </div>

    <!-- Academic Header for Print Sheet -->
    <div class="report-header">
        <h2 class="report-title"><?php echo COLLEGE_NAME; ?></h2>
        <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin-top: 0.25rem;">DEPARTMENT OF ELECTRONICS & TELECOMMUNICATION ENGINEERING</h3>
        <p style="font-weight: 600; color: var(--text-secondary); margin-top: 0.25rem;">CONTINUOUS PRACTICAL ASSESSMENT & TERM-WORK MARKSHEET - <?php echo ACADEMIC_YEAR; ?></p>
        <span class="badge badge-info" style="margin-top: 0.5rem;"><?php echo sanitize($division); ?></span>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Sr #</th>
                    <th>Roll No</th>
                    <th>Student Full Name</th>
                    <th>Batch</th>
                    <th>Exp Checked</th>
                    <th>Raw Score Sum</th>
                    <th>Avg Marks (out of 25)</th>
                    <th>Normalized Term-Work (out of 50)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): $sr = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): 
                        $exp_cnt = intval($row['exp_evaluated']);
                        $raw_sum = intval($row['sum_obtained'] ?? 0);
                        $avg_25 = $row['avg_score_25'] !== null ? round($row['avg_score_25'], 2) : 0;
                        $norm_50 = round($avg_25 * 2, 2);
                        $is_passed = $avg_25 >= 10;
                    ?>
                        <tr>
                            <td><?php echo $sr++; ?></td>
                            <td><strong><?php echo sanitize($row['student_roll_no']); ?></strong></td>
                            <td><?php echo sanitize($row['full_name']); ?></td>
                            <td><span class="badge badge-info"><?php echo sanitize($row['batch_name'] ?? 'C1'); ?></span></td>
                            <td><?php echo $exp_cnt; ?></td>
                            <td><?php echo $raw_sum; ?></td>
                            <td><strong style="color: var(--primary-color); font-size: 1rem;"><?php echo $avg_25; ?> / 25</strong></td>
                            <td><strong><?php echo $norm_50; ?> / 50</strong></td>
                            <td>
                                <span class="badge badge-<?php echo $is_passed ? 'success' : 'danger'; ?>">
                                    <?php echo $is_passed ? 'ACCEPTED / PASSED' : 'DEFICIENT'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-muted);">No student term-work entries found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Official Institutional Signature Block -->
    <div class="signature-block">
        <div class="signature-line">
            Subject Faculty In-Charge
        </div>
        <div class="signature-line">
            Group Faculty Mentor (GFM)
        </div>
        <div class="signature-line">
            Head of Department (HOD)
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
