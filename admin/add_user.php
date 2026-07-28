<?php
// Add New User Account

$page_title = 'Add New User';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['admin', 'hod']);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // Plaintext password per requirement
    $role = sanitize($_POST['role'] ?? 'student');
    $student_roll_no = sanitize($_POST['student_roll_no'] ?? '');
    $division = sanitize($_POST['division'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    if (empty($full_name) || empty($email) || empty($password) || empty($role)) {
        $error = 'Please complete all required fields.';
    } else {
        // Check duplicate email
        $chk_sql = "SELECT id FROM users WHERE email = ? LIMIT 1";
        $chk_stmt = execute_prepared($conn, $chk_sql, "s", [$email]);
        if ($chk_stmt && mysqli_stmt_fetch($chk_stmt)) {
            $error = 'An account with this email address already exists.';
            mysqli_stmt_close($chk_stmt);
        } else {
            if ($chk_stmt) mysqli_stmt_close($chk_stmt);

            $ins_sql = "INSERT INTO users (full_name, email, password, role, student_roll_no, division, phone) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $ins_stmt = execute_prepared($conn, $ins_sql, "sssssss", [
                $full_name, $email, $password, $role,
                !empty($student_roll_no) ? $student_roll_no : null,
                !empty($division) ? $division : null,
                !empty($phone) ? $phone : null
            ]);

            if ($ins_stmt) {
                $new_id = mysqli_insert_id($conn);
                mysqli_stmt_close($ins_stmt);

                log_audit($conn, $_SESSION['user_id'], 'Created User Account', 'users', "Created $role account for $full_name ($email)");
                set_flash('success', "User account for $full_name ($role) created successfully!");
                header('Location: manage_user.php');
                exit();
            } else {
                $error = 'Failed to create user account. Please try again.';
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Add New System User</h2>
        <a href="manage_user.php" class="btn btn-secondary btn-sm">⬅️ Back to Users</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="full_name" class="form-control" required placeholder="e.g. Prof. R. K. Sharma">
        </div>

        <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-control" required placeholder="e.g. user@zcoer.edu.in">
        </div>

        <div class="form-group">
            <label class="form-label">Password * (Plain Text per System Rules)</label>
            <input type="text" name="password" class="form-control" required placeholder="Assign initial password">
        </div>

        <div class="form-group">
            <label class="form-label">Institutional Role *</label>
            <select name="role" class="form-select" required>
                <option value="student">Student</option>
                <option value="faculty">Faculty</option>
                <option value="gfm">GFM (Group Faculty Mentor)</option>
                <option value="hod">HOD (Head of Department)</option>
                <option value="parent">Parent</option>
                <option value="admin">Administrator</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Student Roll Number (If Student/Parent)</label>
            <input type="text" name="student_roll_no" class="form-control" placeholder="e.g. EC1301">
        </div>

        <div class="form-group">
            <label class="form-label">Division (If Student/GFM)</label>
            <select name="division" class="form-select">
                <option value="">-- Select Division --</option>
                <option value="Division C" selected>Division C</option>
                <option value="Division A">Division A</option>
                <option value="Division B">Division B</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" placeholder="10-digit mobile number">
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">Save User Account</button>
            <a href="manage_user.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
