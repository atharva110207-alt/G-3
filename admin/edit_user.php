<?php
// Edit Existing User Account

$page_title = 'Edit User';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['admin', 'hod']);

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: manage_user.php');
    exit();
}

$error = '';

// Fetch existing user data
$sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
$stmt = execute_prepared($conn, $sql, "i", [$id]);
$user_data = false;
if ($stmt) {
    $res = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

if (!$user_data) {
    set_flash('error', 'User not found.');
    header('Location: manage_user.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize($_POST['role'] ?? '');
    $student_roll_no = sanitize($_POST['student_roll_no'] ?? '');
    $division = sanitize($_POST['division'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    if (empty($full_name) || empty($email) || empty($role)) {
        $error = 'Full Name, Email, and Role are required.';
    } else {
        $upd_sql = "UPDATE users SET full_name = ?, email = ?, password = ?, role = ?, student_roll_no = ?, division = ?, phone = ? WHERE id = ?";
        $upd_stmt = execute_prepared($conn, $upd_sql, "sssssssi", [
            $full_name, $email, $password, $role,
            !empty($student_roll_no) ? $student_roll_no : null,
            !empty($division) ? $division : null,
            !empty($phone) ? $phone : null,
            $id
        ]);

        if ($upd_stmt) {
            mysqli_stmt_close($upd_stmt);
            log_audit($conn, $_SESSION['user_id'], 'Updated User Account', 'users', "Updated details for user #$id ($full_name)");
            set_flash('success', "User details for $full_name updated successfully!");
            header('Location: manage_user.php');
            exit();
        } else {
            $error = 'Failed to update user details.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Edit User Account (#<?php echo $user_data['id']; ?>)</h2>
        <a href="manage_user.php" class="btn btn-secondary btn-sm">⬅️ Back to Users</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="full_name" class="form-control" required value="<?php echo sanitize($user_data['full_name']); ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-control" required value="<?php echo sanitize($user_data['email']); ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Password * (Plain Text per System Rules)</label>
            <input type="text" name="password" class="form-control" required value="<?php echo sanitize($user_data['password']); ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Institutional Role *</label>
            <select name="role" class="form-select" required>
                <?php 
                $roles = ['student', 'faculty', 'gfm', 'hod', 'parent', 'admin'];
                foreach ($roles as $r):
                ?>
                    <option value="<?php echo $r; ?>" <?php echo $user_data['role'] === $r ? 'selected' : ''; ?>><?php echo strtoupper($r); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Student Roll Number (If Student/Parent)</label>
            <input type="text" name="student_roll_no" class="form-control" value="<?php echo sanitize($user_data['student_roll_no'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Division (If Student/GFM)</label>
            <select name="division" class="form-select">
                <option value="">-- Select Division --</option>
                <option value="Division C" <?php echo $user_data['division'] === 'Division C' ? 'selected' : ''; ?>>Division C</option>
                <option value="Division A" <?php echo $user_data['division'] === 'Division A' ? 'selected' : ''; ?>>Division A</option>
                <option value="Division B" <?php echo $user_data['division'] === 'Division B' ? 'selected' : ''; ?>>Division B</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" value="<?php echo sanitize($user_data['phone'] ?? ''); ?>">
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">Update User Account</button>
            <a href="manage_user.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
