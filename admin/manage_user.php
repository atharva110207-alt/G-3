<?php
// User Management Center - Admin & HOD Access

$page_title = 'User Management';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['admin', 'hod']);

$role_filter = sanitize($_GET['role'] ?? '');
$search = sanitize($_GET['search'] ?? '');

$sql = "SELECT id, full_name, email, role, student_roll_no, division, phone, created_at FROM users WHERE 1=1";
$params = [];
$types = "";

if (!empty($role_filter)) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

if (!empty($search)) {
    $sql .= " AND (full_name LIKE ? OR email LIKE ? OR student_roll_no LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

$sql .= " ORDER BY id DESC";

$stmt = execute_prepared($conn, $sql, $types, $params);
$users_result = $stmt ? mysqli_stmt_get_result($stmt) : false;

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">User Management Directory</h2>
            <p style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">View and manage system accounts across all institutional roles</p>
        </div>
        <a href="add_user.php" class="btn btn-primary">➕ Create New User Account</a>
    </div>

    <!-- Filter Tabs & Search Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="manage_user.php" class="btn btn-sm <?php echo empty($role_filter) ? 'btn-primary' : 'btn-secondary'; ?>">All Users</a>
            <a href="manage_user.php?role=student" class="btn btn-sm <?php echo $role_filter === 'student' ? 'btn-primary' : 'btn-secondary'; ?>">Students</a>
            <a href="manage_user.php?role=faculty" class="btn btn-sm <?php echo $role_filter === 'faculty' ? 'btn-primary' : 'btn-secondary'; ?>">Faculty</a>
            <a href="manage_user.php?role=gfm" class="btn btn-sm <?php echo $role_filter === 'gfm' ? 'btn-primary' : 'btn-secondary'; ?>">GFMs</a>
            <a href="manage_user.php?role=hod" class="btn btn-sm <?php echo $role_filter === 'hod' ? 'btn-primary' : 'btn-secondary'; ?>">HODs</a>
            <a href="manage_user.php?role=parent" class="btn btn-sm <?php echo $role_filter === 'parent' ? 'btn-primary' : 'btn-secondary'; ?>">Parents</a>
        </div>

        <form action="" method="GET" style="display: flex; gap: 0.5rem;">
            <?php if ($role_filter): ?>
                <input type="hidden" name="role" value="<?php echo $role_filter; ?>">
            <?php endif; ?>
            <input type="text" name="search" class="form-control" placeholder="Search by name, email, roll..." value="<?php echo $search; ?>" style="width: 240px;">
            <button type="submit" class="btn btn-secondary">Search</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Role</th>
                    <th>Roll Number</th>
                    <th>Division</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users_result && mysqli_num_rows($users_result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($users_result)): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><strong><?php echo sanitize($row['full_name']); ?></strong></td>
                            <td><?php echo sanitize($row['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    switch($row['role']) {
                                        case 'admin': echo 'danger'; break;
                                        case 'hod': echo 'warning'; break;
                                        case 'faculty': echo 'info'; break;
                                        case 'gfm': echo 'info'; break;
                                        case 'student': echo 'success'; break;
                                        default: echo 'secondary';
                                    }
                                ?>">
                                    <?php echo strtoupper($row['role']); ?>
                                </span>
                            </td>
                            <td><?php echo $row['student_roll_no'] ? sanitize($row['student_roll_no']) : '-'; ?></td>
                            <td><?php echo $row['division'] ? sanitize($row['division']) : '-'; ?></td>
                            <td><?php echo $row['phone'] ? sanitize($row['phone']) : '-'; ?></td>
                            <td>
                                <div style="display: flex; gap: 0.375rem;">
                                    <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-muted);">No user accounts found matching your query.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
