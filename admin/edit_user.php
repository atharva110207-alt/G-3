<?php
// Practical Assessment System - Edit User Controller
// Zeal College of Engineering & Research

$page_title = "Edit User Account";
require_once __DIR__ . '/../includes/header.php';

require_role('admin');

$edit_id = intval($_GET['id'] ?? 0);
if ($edit_id <= 0) {
    header('Location: manage_user.php');
    exit();
}

$error = '';
$success = '';

// Fetch user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = execute_prepared($conn, $sql, "i", [$edit_id]);
$target_user = null;
if ($stmt) {
    $res = mysqli_stmt_get_result($stmt);
    $target_user = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

if (!$target_user) {
    set_flash('error', 'Target user account not found.');
    header('Location: manage_user.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = sanitize($_POST['role'] ?? '');
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $student_roll_no = sanitize($_POST['student_roll_no'] ?? '');
    $zprn = sanitize($_POST['zprn'] ?? '');
    $class = sanitize($_POST['class'] ?? 'TY');
    $division = sanitize($_POST['division'] ?? 'Division C');

    if (empty($role) || empty($email) || empty($password)) {
        $error = "Role, Email, and Password are required fields.";
    } else {
        // Check duplicate email for the same role
        $check_sql = "SELECT id FROM users WHERE email = ? AND role = ? AND id != ?";
        $check_stmt = execute_prepared($conn, $check_sql, "ssi", [$email, $role, $edit_id]);
        if ($check_stmt) {
            $res = mysqli_stmt_get_result($check_stmt);
            if (mysqli_num_rows($res) > 0) {
                $error = "A user account with this email address and role already exists.";
            }
            mysqli_stmt_close($check_stmt);
        }

        if (empty($error)) {
            $update_sql = "UPDATE users SET full_name = ?, email = ?, password = ?, role = ?, student_roll_no = ?, zprn = ?, class = ?, division = ?, phone = ? WHERE id = ?";
            $up_stmt = execute_prepared($conn, $update_sql, "sssssssssi", [
                $full_name, $email, $password, $role,
                (!empty($student_roll_no) ? $student_roll_no : null),
                (!empty($zprn) ? $zprn : null),
                $class, $division, $phone, $edit_id
            ]);

            if ($up_stmt) {
                mysqli_stmt_close($up_stmt);
                log_audit($conn, $user['id'], $user['role'], 'Edit User', 'user_management', 'Updated user ID #' . $edit_id . ' (' . $email . ')');
                set_flash('success', 'User account updated successfully!');
                header('Location: manage_user.php');
                exit();
            } else {
                $error = "Failed to update user account. Ensure fields are not identical or try again.";
            }
        }
    }
}
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-user-edit text-primary me-2"></i> Edit User Account (#<?php echo $target_user['id']; ?>)</h3>
    <a href="manage_user.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Users</a>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="form-group">
      <label for="role" class="form-label">System Role <span class="text-danger">*</span></label>
      <select id="role" name="role" class="form-select" required>
        <option value="admin" <?php echo $target_user['role'] === 'admin' ? 'selected' : ''; ?>>System Administrator</option>
        <option value="hod" <?php echo $target_user['role'] === 'hod' ? 'selected' : ''; ?>>HOD (Head of Department)</option>
        <option value="gfm" <?php echo $target_user['role'] === 'gfm' ? 'selected' : ''; ?>>GFM (Guardian Faculty Member)</option>
        <option value="class_teacher" <?php echo $target_user['role'] === 'class_teacher' ? 'selected' : ''; ?>>Class Teacher</option>
        <option value="faculty" <?php echo $target_user['role'] === 'faculty' ? 'selected' : ''; ?>>Subject Faculty</option>
        <option value="student" <?php echo $target_user['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
        <option value="parent" <?php echo $target_user['role'] === 'parent' ? 'selected' : ''; ?>>Parent</option>
      </select>
    </div>

    <div class="form-group">
      <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
      <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo sanitize($target_user['full_name']); ?>" required>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
      <div class="form-group">
        <label for="student_roll_no" class="form-label">Roll Number</label>
        <input type="text" id="student_roll_no" name="student_roll_no" class="form-control" value="<?php echo sanitize($target_user['student_roll_no'] ?? ''); ?>">
      </div>

      <div class="form-group">
        <label for="zprn" class="form-label">ZPRN Number</label>
        <input type="text" id="zprn" name="zprn" class="form-control" value="<?php echo sanitize($target_user['zprn'] ?? ''); ?>">
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
      <div class="form-group">
        <label for="class" class="form-label">Academic Class</label>
        <select id="class" name="class" class="form-select">
          <option value="FY" <?php echo ($target_user['class'] ?? '') === 'FY' ? 'selected' : ''; ?>>FY</option>
          <option value="SY" <?php echo ($target_user['class'] ?? '') === 'SY' ? 'selected' : ''; ?>>SY</option>
          <option value="TY" <?php echo ($target_user['class'] ?? '') === 'TY' ? 'selected' : ''; ?>>TY</option>
          <option value="BY" <?php echo ($target_user['class'] ?? '') === 'BY' ? 'selected' : ''; ?>>BY</option>
        </select>
      </div>

      <div class="form-group">
        <label for="division" class="form-label">Division</label>
        <select id="division" name="division" class="form-select">
          <option value="Division A" <?php echo ($target_user['division'] ?? '') === 'Division A' ? 'selected' : ''; ?>>Division A</option>
          <option value="Division B" <?php echo ($target_user['division'] ?? '') === 'Division B' ? 'selected' : ''; ?>>Division B</option>
          <option value="Division C" <?php echo ($target_user['division'] ?? '') === 'Division C' ? 'selected' : ''; ?>>Division C</option>
          <option value="Division D" <?php echo ($target_user['division'] ?? '') === 'Division D' ? 'selected' : ''; ?>>Division D</option>
        </select>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
      <div class="form-group">
        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email" class="form-control" value="<?php echo sanitize($target_user['email']); ?>" required>
      </div>

      <div class="form-group">
        <label for="phone" class="form-label">Mobile Number</label>
        <input type="text" id="phone" name="phone" class="form-control" value="<?php echo sanitize($target_user['phone'] ?? ''); ?>">
      </div>
    </div>

    <div class="form-group">
      <label for="password" class="form-label">Password (PLAIN TEXT) <span class="text-danger">*</span></label>
      <input type="text" id="password" name="password" class="form-control" value="<?php echo sanitize($target_user['password']); ?>" required>
    </div>

    <button type="submit" class="btn btn-primary">
      <i class="fas fa-save me-2"></i> Update User Details
    </button>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
