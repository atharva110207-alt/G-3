<?php
// System Audit Logs Viewer

$page_title = 'Audit Logs';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['admin', 'hod']);

$sql = "SELECT a.*, u.full_name, u.role FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.id DESC LIMIT 100";
$result = mysqli_query($conn, $sql);

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">System Audit Logs & Override History</h2>
            <p style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">Complete security trail of user actions, assessment overrides, and data updates</p>
        </div>
        <button class="btn btn-secondary btn-sm" onclick="location.reload();">🔄 Refresh Logs</button>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Timestamp</th>
                    <th>User / Performer</th>
                    <th>Action Performed</th>
                    <th>Target Table</th>
                    <th>Details / Justification</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($row['timestamp'])); ?></td>
                            <td>
                                <strong><?php echo sanitize($row['full_name'] ?? 'System / Unknown'); ?></strong>
                                <?php if ($row['role']): ?>
                                    <span class="badge badge-info"><?php echo strtoupper($row['role']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-warning"><?php echo sanitize($row['action_performed']); ?></span></td>
                            <td><code><?php echo sanitize($row['target_table']); ?></code></td>
                            <td style="max-width: 300px;"><?php echo sanitize($row['details']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted);">No audit logs recorded yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
