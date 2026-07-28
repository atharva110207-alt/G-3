<?php
// Faculty Evaluation Progress Report

$page_title = 'Faculty Evaluation Report';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$sql = "SELECT u.full_name, COUNT(DISTINCT p.id) as practicals_scheduled, COUNT(DISTINCT a.id) as evaluations_done
        FROM users u 
        LEFT JOIN practicals p ON u.id = p.faculty_id
        LEFT JOIN assessment a ON u.id = a.faculty_id
        WHERE u.role = 'faculty'
        GROUP BY u.id";
$result = mysqli_query($conn, $sql);

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Faculty Evaluation Progress Report</h2>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Faculty Member</th>
                    <th>Practicals Scheduled</th>
                    <th>Total Evaluations Logged</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><strong><?php echo sanitize($r['full_name']); ?></strong></td>
                            <td><?php echo $r['practicals_scheduled']; ?></td>
                            <td><span class="badge badge-info"><?php echo $r['evaluations_done']; ?> Records</span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">No faculty statistics.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
