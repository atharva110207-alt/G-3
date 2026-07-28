<?php
// Student Self-Registration Module

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $student_roll_no = sanitize($_POST['student_roll_no'] ?? '');
    $division = sanitize($_POST['division'] ?? 'Division C');
    $phone = sanitize($_POST['phone'] ?? '');

    if (empty($full_name) || empty($email) || empty($password) || empty($student_roll_no)) {
        $error = 'All required fields must be completed.';
    } else {
        // Check duplicate email
        $chk_sql = "SELECT id FROM users WHERE email = ? OR student_roll_no = ?";
        $chk_stmt = execute_prepared($conn, $chk_sql, "ss", [$email, $student_roll_no]);
        if ($chk_stmt && mysqli_stmt_fetch($chk_stmt)) {
            $error = 'An account with this email or Roll Number already exists.';
            mysqli_stmt_close($chk_stmt);
        } else {
            if ($chk_stmt) mysqli_stmt_close($chk_stmt);

            $ins_sql = "INSERT INTO users (full_name, email, password, role, student_roll_no, division, phone) VALUES (?, ?, ?, 'student', ?, ?, ?)";
            $ins_stmt = execute_prepared($conn, $ins_sql, "ssssss", [$full_name, $email, $password, $student_roll_no, $division, $phone]);
            if ($ins_stmt) {
                mysqli_stmt_close($ins_stmt);
                set_flash('success', 'Registration successful! You can now log in.');
                header('Location: login.php');
                exit();
            } else {
                $error = 'Failed to register user. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
</head>
<body class="login-body">
    <div class="login-card" style="max-width: 500px;">
        <div class="login-header">
            <h1 class="login-title">Student Registration</h1>
            <p class="login-subtitle">Join the Practical Assessment System</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" required placeholder="e.g. Aarav Sharma">
            </div>

            <div class="form-group">
                <label class="form-label">Roll Number *</label>
                <input type="text" name="student_roll_no" class="form-control" required placeholder="e.g. EC1321">
            </div>

            <div class="form-group">
                <label class="form-label">Division *</label>
                <select name="division" class="form-select">
                    <option value="Division C" selected>Division C</option>
                    <option value="Division A">Division A</option>
                    <option value="Division B">Division B</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" required placeholder="e.g. ec1321@zcoer.edu.in">
            </div>

            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" required placeholder="Choose a password">
            </div>

            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="10-digit phone number">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Register Student</button>
        </form>

        <div style="text-align: center; margin-top: 1rem; font-size: 0.875rem;">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>
</body>
</html>
