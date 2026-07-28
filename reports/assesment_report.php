<?php
// Detailed Practical Assessment Report

$page_title = 'Assessment Marks Report';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$sql = "SELECT u.student_roll_no, u.full_name, p.exp_no, p.title, p.subject_name,
        ass.regularity_score, ass.conduction_score, ass.output_score, ass.viva_score, ass.total_score, ass.evaluation_date
        FROM assessment ass
        JOIN users u ON ass.student_id = u.id
        JOIN practicals p ON ass.practical_id = p.id
        ORDER BY p.exp_no ASC, u.student_roll_no ASC";
$result = mysqli_query($conn, $sql);

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Detailed Experiment Assessment Breakdown (25 Marks)</h2>
        <button id="printReportBtn" class="btn btn-secondary btn-sm">🖨️ Print Report</button>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Roll No</th>
                    <th>Student Name</th>
                    <th>Exp #</th>
                    <th>Subject</th>
                    <th>Regularity (5)</th>
                    <th>Conduction (10)</th>
                    <th>Output (5)</th>
                    <th>Viva (5)</th>
                    <th>Total (25)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><strong><?php echo sanitize($r['student_roll_no']); ?></strong></td>
                            <td><?php echo sanitize($r['full_name']); ?></td>
                            <td>Exp <?php echo $r['exp_no']; ?></td>
                            <td><?php echo sanitize($r['subject_name']); ?></td>
                            <td><?php echo $r['regularity_score']; ?></td>
                            <td><?php echo $r['conduction_score']; ?></td>
                            <td><?php echo $r['output_score']; ?></td>
                            <td><?php echo $r['viva_score']; ?></td>
                            <td><strong><?php echo $r['total_score']; ?> / 25</strong></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" style="text-align: center; color: var(--text-muted);">No assessment records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
