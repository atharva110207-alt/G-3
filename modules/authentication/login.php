<?php
// Practical Assessment & Laboratory Performance Management System
// Authentication: Login Module

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . get_role_dashboard(get_user_role()));
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // Plaintext password as per requirement

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Query user with prepared statement
        $sql = "SELECT id, full_name, email, password, role, student_roll_no, division FROM users WHERE email = ? LIMIT 1";
        $stmt = execute_prepared($conn, $sql, "s", [$email]);

        if ($stmt) {
            $result = mysqli_stmt_get_result($stmt);
            if ($user = mysqli_fetch_assoc($result)) {
                // Verify PLAIN TEXT password per explicit prompt instruction
                if ($password === $user['password']) {
                    // Set Session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['student_roll_no'] = $user['student_roll_no'];
                    $_SESSION['division'] = $user['division'];

                    // Log login action
                    log_audit($conn, $user['id'], 'User Login Successful', 'users', 'Logged in as ' . $user['role']);

                    set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
                    header('Location: ' . get_role_dashboard($user['role']));
                    exit();
                } else {
                    $error = 'Invalid password. Please check your credentials.';
                }
            } else {
                $error = 'No account found with this email address.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = 'Database query failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/login.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">🎓</div>
            <h1 class="login-title">Lab Assessment Portal</h1>
            <p class="login-subtitle"><?php echo COLLEGE_NAME; ?></p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php 
        $flash = get_flash();
        if ($flash): 
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" id="loginForm">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="e.g. admin@zcoer.edu.in" required autofocus>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Sign In to Dashboard</button>
        </form>

        <div style="display: flex; justify-content: space-between; margin-top: 1rem; font-size: 0.8125rem;">
            <a href="forgot_password.php">Forgot Password?</a>
            <a href="register.php">Student Self-Register</a>
        </div>

        <details class="demo-credentials-box">
            <summary>🔑 Quick Demo Login Credentials (Click to Expand)</summary>
            <div style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.375rem;">
                <a href="#" class="fill-demo-account" data-email="admin@zcoer.edu.in" data-pass="Admin@123"><strong>Admin:</strong> admin@zcoer.edu.in / Admin@123</a>
                <a href="#" class="fill-demo-account" data-email="hod.extc@zcoer.edu.in" data-pass="hod123"><strong>HOD:</strong> hod.extc@zcoer.edu.in / hod123</a>
                <a href="#" class="fill-demo-account" data-email="faculty.smith@zcoer.edu.in" data-pass="faculty123"><strong>Faculty 1:</strong> faculty.smith@zcoer.edu.in / faculty123</a>
                <a href="#" class="fill-demo-account" data-email="gfm.divc@zcoer.edu.in" data-pass="gfm123"><strong>GFM Div C:</strong> gfm.divc@zcoer.edu.in / gfm123</a>
                <a href="#" class="fill-demo-account" data-email="ec1301@zcoer.edu.in" data-pass="student123"><strong>Student (EC1301):</strong> ec1301@zcoer.edu.in / student123</a>
                <a href="#" class="fill-demo-account" data-email="parent.ec1301@zcoer.edu.in" data-pass="parent123"><strong>Parent (EC1301):</strong> parent.ec1301@zcoer.edu.in / parent123</a>
            </div>
        </details>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/login.js"></script>
</body>
</html>
