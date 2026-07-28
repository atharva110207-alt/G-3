<?php
// Practical Assessment System - System Audit Logs Viewer
// Zeal College of Engineering & Research

$page_title = "System Audit Logs";
require_once __DIR__ . '/../includes/header.php';

// Restricted to Admin & HOD
require_role(['admin', 'hod']);

$module_filter = sanitize($_GET['target_module'] ?? '');
$search_query = sanitize($_GET['search'] ?? '');

$sql = "SELECT a.*, u.full_name, u.email FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id WHERE 1=1";
$params = [];
$types = "";

if (!empty($module_filter)) {
    $sql .= " AND a.target_module = ?";
    $params[] = $module_filter;
    $types .= "s";
}

if (!empty($search_query)) {
    $sql .= " AND (a.action_performed LIKE ? OR a.details LIKE ? OR u.full_name LIKE ? OR a.IP_address LIKE ?)";
    $like = "%" . $search_query . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "ssss";
}

$sql .= " ORDER BY a.timestamp DESC LIMIT 100";
$stmt = execute_prepared($conn, $sql, $types, $params);
$logs = [];
if ($stmt) {
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $logs[] = $r;
    }
    mysqli_stmt_close($stmt);
}
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-shield-alt text-primary me-2"></i> System Audit & Activity Logs</h3>
  </div>

  <form method="GET" action="" class="action-bar">
    <div class="search-wrapper">
      <i class="fas fa-search search-icon"></i>
      <input type="text" name="search" class="form-control search-input" placeholder="Search action, user, IP..." value="<?php echo sanitize($search_query); ?>">
    </div>

    <div style="display: flex; gap: 0.5rem;">
      <select name="target_module" class="form-select" style="width: auto;" onchange="this.form.submit()">
        <option value="">All Modules</option>
        <option value="authentication" <?php echo $module_filter === 'authentication' ? 'selected' : ''; ?>>Authentication</option>
        <option value="user_management" <?php echo $module_filter === 'user_management' ? 'selected' : ''; ?>>User Management</option>
        <option value="batch_management" <?php echo $module_filter === 'batch_management' ? 'selected' : ''; ?>>Batch Management</option>
        <option value="allocation_management" <?php echo $module_filter === 'allocation_management' ? 'selected' : ''; ?>>Allocations</option>
        <option value="practical_management" <?php echo $module_filter === 'practical_management' ? 'selected' : ''; ?>>Practicals</option>
        <option value="attendance" <?php echo $module_filter === 'attendance' ? 'selected' : ''; ?>>Attendance</option>
        <option value="assessment" <?php echo $module_filter === 'assessment' ? 'selected' : ''; ?>>Assessment</option>
        <option value="system" <?php echo $module_filter === 'system' ? 'selected' : ''; ?>>System</option>
      </select>
      <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Timestamp</th>
          <th>User</th>
          <th>Role</th>
          <th>Action Performed</th>
          <th>Target Module</th>
          <th>IP Address</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($logs)): ?>
          <tr><td colspan="7" class="text-center" style="color: var(--text-muted);">No audit log entries found.</td></tr>
        <?php else: ?>
          <?php foreach ($logs as $l): ?>
            <tr>
              <td style="white-space: nowrap; font-size: 0.8rem;"><?php echo date('d M Y, H:i:s', strtotime($l['timestamp'])); ?></td>
              <td><strong><?php echo sanitize($l['full_name'] ?: 'System / Unknown'); ?></strong></td>
              <td><span class="badge badge-info"><?php echo get_role_label($l['user_role']); ?></span></td>
              <td><strong style="color: var(--text-primary);"><?php echo sanitize($l['action_performed']); ?></strong></td>
              <td><span class="badge badge-secondary"><?php echo sanitize($l['target_module']); ?></span></td>
              <td><code><?php echo sanitize($l['IP_address']); ?></code></td>
              <td style="font-size: 0.8125rem; color: var(--text-secondary);"><?php echo sanitize($l['details']); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
