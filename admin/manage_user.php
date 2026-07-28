<?php
// Practical Assessment System - User Management & CRUD Dashboard
// Zeal College of Engineering & Research

$page_title = "User Management";
require_once __DIR__ . '/../includes/header.php';

// Restricted to Admin
require_role('admin');

$role_filter = sanitize($_GET['role'] ?? '');
$search_query = sanitize($_GET['search'] ?? '');

$sql = "SELECT id, full_name, email, password, role, student_roll_no, zprn, class, division, phone, created_at FROM users WHERE 1=1";
$params = [];
$types = "";

if (!empty($role_filter)) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

if (!empty($search_query)) {
    $sql .= " AND (full_name LIKE ? OR email LIKE ? OR student_roll_no LIKE ? OR zprn LIKE ?)";
    $like = "%" . $search_query . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "ssss";
}

$sql .= " ORDER BY id DESC";
$stmt = execute_prepared($conn, $sql, $types, $params);
$users_list = [];
if ($stmt) {
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $users_list[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-users-cog text-primary me-2"></i> User Accounts Registry</h3>
    <div>
      <a href="add_user.php" class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-1"></i> Add New User</a>
      <a href="create_batches.php" class="btn btn-accent btn-sm ms-2"><i class="fas fa-layer-group me-1"></i> Create Batches</a>
    </div>
  </div>

  <!-- Search & Role Filter Bar -->
  <form method="GET" action="" class="action-bar">
    <div class="search-wrapper">
      <i class="fas fa-search search-icon"></i>
      <input type="text" name="search" class="form-control search-input" placeholder="Search name, email, roll no, zprn..." value="<?php echo sanitize($search_query); ?>">
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: center;">
      <select name="role" class="form-select" style="width: auto;" onchange="this.form.submit()">
        <option value="">All Roles</option>
        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>System Admin</option>
        <option value="hod" <?php echo $role_filter === 'hod' ? 'selected' : ''; ?>>HOD</option>
        <option value="gfm" <?php echo $role_filter === 'gfm' ? 'selected' : ''; ?>>GFM</option>
        <option value="faculty" <?php echo $role_filter === 'faculty' ? 'selected' : ''; ?>>Subject Faculty</option>
        <option value="student" <?php echo $role_filter === 'student' ? 'selected' : ''; ?>>Student</option>
        <option value="parent" <?php echo $role_filter === 'parent' ? 'selected' : ''; ?>>Parent</option>
      </select>
      <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>#ID</th>
          <th>Full Name / Roll No</th>
          <th>Role</th>
          <th>Email</th>
          <th>Password (Plain)</th>
          <th>Class & Div</th>
          <th>ZPRN</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users_list)): ?>
          <tr>
            <td colspan="8" class="text-center" style="padding: 2rem; color: var(--text-muted);">
              <i class="fas fa-folder-open fa-2x mb-2"></i><br>No user accounts matched the filter criteria.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($users_list as $u): ?>
            <tr>
              <td>#<?php echo $u['id']; ?></td>
              <td>
                <strong style="color: var(--text-primary);"><?php echo sanitize($u['full_name']); ?></strong>
                <?php if (!empty($u['student_roll_no'])): ?>
                  <br><span class="badge badge-info"><?php echo sanitize($u['student_roll_no']); ?></span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge badge-<?php 
                  echo ($u['role'] === 'admin' ? 'danger' : 
                       ($u['role'] === 'hod' ? 'warning' : 
                       ($u['role'] === 'faculty' ? 'success' : 'info'))); 
                ?>">
                  <?php echo get_role_label($u['role']); ?>
                </span>
              </td>
              <td><?php echo sanitize($u['email']); ?></td>
              <td><code style="background: rgba(0,0,0,0.3); padding: 0.2rem 0.5rem; border-radius: 4px; color: #38bdf8;"><?php echo sanitize($u['password']); ?></code></td>
              <td><?php echo sanitize(($u['class'] ?? 'TY') . ' - ' . ($u['division'] ?? '-')); ?></td>
              <td><?php echo sanitize($u['zprn'] ?? '-'); ?></td>
              <td>
                <a href="edit_user.php?id=<?php echo $u['id']; ?>" class="btn btn-secondary btn-sm" title="Edit User"><i class="fas fa-edit"></i></a>
                <?php if ($u['id'] != $user['id']): ?>
                  <a href="delete_user.php?id=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete user <?php echo sanitize($u['full_name']); ?>?');" title="Delete User"><i class="fas fa-trash"></i></a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
