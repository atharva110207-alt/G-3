<?php
// Practical Assessment System - Admin Control Dashboard
// Zeal College of Engineering & Research

$page_title = "Admin Dashboard";
require_once __DIR__ . '/../../includes/header.php';

require_role('admin');

// Metrics Queries
$total_users = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users");
if ($res && $r = mysqli_fetch_assoc($res)) { $total_users = $r['cnt']; }

$total_batches = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM batches");
if ($res && $r = mysqli_fetch_assoc($res)) { $total_batches = $r['cnt']; }

$total_allocations = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM faculty_allocations");
if ($res && $r = mysqli_fetch_assoc($res)) { $total_allocations = $r['cnt']; }

$total_logs = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM audit_logs");
if ($res && $r = mysqli_fetch_assoc($res)) { $total_logs = $r['cnt']; }

// Fetch Recent Audit Logs
$recent_logs = [];
$log_sql = "SELECT a.*, u.full_name FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.timestamp DESC LIMIT 8";
$log_res = mysqli_query($conn, $log_sql);
if ($log_res) {
    while ($row = mysqli_fetch_assoc($log_res)) {
        $recent_logs[] = $row;
    }
}
?>

<!-- Glassmorphic Stat Cards Grid -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon"><i class="fas fa-users"></i></div>
    <div class="stat-info">
      <h3><?php echo $total_users; ?></h3>
      <p>Registered Users</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;"><i class="fas fa-layer-group"></i></div>
    <div class="stat-info">
      <h3><?php echo $total_batches; ?></h3>
      <p>Manual Batches</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #34d399;"><i class="fas fa-tasks"></i></div>
    <div class="stat-info">
      <h3><?php echo $total_allocations; ?></h3>
      <p>Batch Allocations</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;"><i class="fas fa-shield-alt"></i></div>
    <div class="stat-info">
      <h3><?php echo $total_logs; ?></h3>
      <p>Audit Events</p>
    </div>
  </div>
</div>

<!-- Admin Quick Action Tools -->
<div class="card mb-4">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-tools text-primary me-2"></i> System Administration Control Panel</h3>
  </div>
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
    <a href="<?php echo BASE_URL; ?>admin/manage_user.php" class="btn btn-secondary" style="justify-content: center; padding: 1rem;">
      <i class="fas fa-users-cog fa-lg me-2 text-primary"></i> Manage User Accounts
    </a>
    <a href="<?php echo BASE_URL; ?>admin/create_batches.php" class="btn btn-secondary" style="justify-content: center; padding: 1rem;">
      <i class="fas fa-layer-group fa-lg me-2 text-accent"></i> Manual Batch Creation
    </a>
    <a href="<?php echo BASE_URL; ?>admin/allocations.php" class="btn btn-secondary" style="justify-content: center; padding: 1rem;">
      <i class="fas fa-tasks fa-lg me-2 style-success"></i> Batch Allocation
    </a>
    <a href="<?php echo BASE_URL; ?>admin/audit_logs.php" class="btn btn-secondary" style="justify-content: center; padding: 1rem;">
      <i class="fas fa-shield-alt fa-lg me-2 style-warning"></i> View System Audit Logs
    </a>
    <a href="<?php echo BASE_URL; ?>admin/backup.php?download=pdf" target="_blank" class="btn btn-primary" style="justify-content: center; padding: 1rem;">
      <i class="fas fa-file-pdf fa-lg me-2"></i> 1-Click Database PDF Backup
    </a>
  </div>
</div>

<!-- Recent System Audit Logs Table -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-history text-primary me-2"></i> Recent System Activity Audit Logs</h3>
    <a href="<?php echo BASE_URL; ?>admin/audit_logs.php" class="btn btn-secondary btn-sm">View All Logs</a>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Timestamp</th>
          <th>User</th>
          <th>Role</th>
          <th>Action</th>
          <th>Module</th>
          <th>IP Address</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent_logs as $log): ?>
          <tr>
            <td><?php echo date('d M Y, H:i:s', strtotime($log['timestamp'])); ?></td>
            <td><strong><?php echo sanitize($log['full_name'] ?: 'System'); ?></strong></td>
            <td><span class="badge badge-info"><?php echo get_role_label($log['user_role']); ?></span></td>
            <td><?php echo sanitize($log['action_performed']); ?></td>
            <td><span class="badge badge-secondary"><?php echo sanitize($log['target_module']); ?></span></td>
            <td><code><?php echo sanitize($log['IP_address']); ?></code></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
