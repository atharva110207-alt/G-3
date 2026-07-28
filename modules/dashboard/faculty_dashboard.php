<?php
// Faculty Practical Conduction & Assessment Dashboard

$page_title = 'Faculty Dashboard';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_role(['faculty', 'admin']);

$faculty_id = $_SESSION['user_id'];

// Fetch Faculty Allocations
$alloc_sql = "SELECT fa.*, b.batch_name, b.start_roll, b.end_roll 
              FROM faculty_allocations fa 
              LEFT JOIN batches b ON fa.batch_id = b.id 
              WHERE fa.faculty_id = ?";
$alloc_stmt = execute_prepared($conn, $alloc_sql, "i", [$faculty_id]);
$alloc_res = $alloc_stmt ? mysqli_stmt_get_result($alloc_stmt) : false;

// Fetch Faculty Practicals
$pract_sql = "SELECT p.*, b.batch_name, 
              (SELECT COUNT(*) FROM attendance a WHERE a.practical_id = p.id) as att_marked,
              (SELECT COUNT(*) FROM assessment ass WHERE ass.practical_id = p.id) as ass_marked
              FROM practicals p 
              JOIN batches b ON p.batch_id = b.id 
              WHERE p.faculty_id = ? 
              ORDER BY p.scheduled_date DESC";
$pract_stmt = execute_prepared($conn, $pract_sql, "i", [$faculty_id]);
$pract_res = $pract_stmt ? mysqli_stmt_get_result($pract_stmt) : false;

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Welcome, <?php echo sanitize($_SESSION['full_name']); ?></h2>
            <p style="font-size: 0.875rem; color: var(--text-muted);">Manage your assigned practical experiments, attendance, and student evaluations</p>
        </div>
        <a href="../practical_management/create_practical.php" class="btn btn-primary">➕ Schedule New Experiment</a>
    </div>

    <!-- Allocated Subjects & Batches -->
    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 1rem;">My Allocated Subjects & Batches</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <?php if ($alloc_res && mysqli_num_rows($alloc_res) > 0): ?>
            <?php while ($alloc = mysqli_fetch_assoc($alloc_res)): ?>
                <div style="background-color: var(--bg-primary); border: 1px solid var(--border-color); padding: 1.25rem; border-radius: var(--radius-md);">
                    <span class="badge badge-info" style="margin-bottom: 0.5rem;"><?php echo sanitize($alloc['division']); ?> - Batch <?php echo sanitize($alloc['batch_name'] ?? 'All'); ?></span>
                    <h4 style="font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;"><?php echo sanitize($alloc['subject_name']); ?></h4>
                    <p style="font-size: 0.8125rem; color: var(--text-muted);">Roll Range: <?php echo sanitize($alloc['start_roll'] ?? '-') . ' to ' . sanitize($alloc['end_roll'] ?? '-'); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: var(--text-muted);">No faculty allocations assigned yet.</p>
        <?php endif; ?>
    </div>

    <!-- Practical Experiment Sessions List -->
    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 1rem;">Practical Experiment Sessions</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Exp #</th>
                    <th>Subject Name</th>
                    <th>Experiment Title</th>
                    <th>Batch</th>
                    <th>Date Scheduled</th>
                    <th>Attendance Status</th>
                    <th>Evaluation Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pract_res && mysqli_num_rows($pract_res) > 0): ?>
                    <?php while ($p = mysqli_fetch_assoc($pract_res)): ?>
                        <tr>
                            <td><strong>Exp <?php echo $p['exp_no']; ?></strong></td>
                            <td><?php echo sanitize($p['subject_name']); ?></td>
                            <td><?php echo sanitize($p['title']); ?></td>
                            <td><span class="badge badge-info">Batch <?php echo sanitize($p['batch_name']); ?></span></td>
                            <td><?php echo format_date($p['scheduled_date']); ?></td>
                            <td>
                                <?php if ($p['att_marked'] > 0): ?>
                                    <span class="badge badge-success">Marked (<?php echo $p['att_marked']; ?>)</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['ass_marked'] > 0): ?>
                                    <span class="badge badge-success">Evaluated (<?php echo $p['ass_marked']; ?>)</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Pending Evaluation</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.375rem;">
                                    <a href="../attendance/mark_attendance.php?practical_id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm">✍️ Attendance</a>
                                    <a href="../assessment/practical_conduction.php?practical_id=<?php echo $p['id']; ?>" class="btn btn-primary btn-sm">📝 Grade (0-25)</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">No practical experiments scheduled yet. Click "Schedule New Experiment" above to get started.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
