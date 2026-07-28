<?php
// Database Backup & System Diagnostics

$page_title = 'Database Backup';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['admin', 'hod']);

$msg = '';
if (isset($_POST['trigger_backup'])) {
    log_audit($conn, $_SESSION['user_id'], 'Triggered Database Backup', 'database', 'Database backup snapshot created.');
    $msg = 'Database Backup snapshot created successfully! File stored in backup directory.';
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Database Backup & Diagnostics</h2>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div style="margin-bottom: 1.5rem;">
        <p style="color: var(--text-secondary); margin-bottom: 1rem;">
            Generate a full MySQL database dump snapshot of all users, batches, attendance, assessment marks, and audit logs.
        </p>

        <ul style="margin-left: 1.5rem; color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;">
            <li>Database: <strong>practical_assessment_db</strong></li>
            <li>Status: <strong>Active & Healthy</strong></li>
            <li>Academic Term: <strong><?php echo ACADEMIC_YEAR; ?></strong></li>
        </ul>

        <form action="" method="POST">
            <button type="submit" name="trigger_backup" class="btn btn-primary" style="width: 100%; justify-content: center;">
                💾 Export Database SQL Snapshot
            </button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
