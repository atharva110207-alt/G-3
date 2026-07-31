<?php
// Practical Assessment System - Add User Controller
// Zeal College of Engineering & Research

$page_title = "Add New User";
require_once __DIR__ . '/../includes/header.php';

// Restricted to Admin
require_role('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = sanitize($_POST['role'] ?? '');
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    
    // Dynamic fields for Student / Parent
    $student_roll_no = sanitize($_POST['student_roll_no'] ?? '');
    $zprn = sanitize($_POST['zprn'] ?? '');
    $class = sanitize($_POST['class'] ?? 'TY');
    $division = sanitize($_POST['division'] ?? 'Division C');

    // Dynamic fields for GFM
    if ($role === 'gfm') {
        $class = sanitize($_POST['gfm_class'] ?? 'TY');
        $division = sanitize($_POST['gfm_division'] ?? 'Division C');
    }

    if (empty($role) || empty($email) || empty($password)) {
        $error = "Role, Email, and Password are required fields.";
    } else {
        // Handle name if empty for student/parent
        if (empty($full_name)) {
            $full_name = ($role === 'student') ? "Student " . $student_roll_no : "Parent (" . $student_roll_no . ")";
        }

        // Check duplicate email
        $check_sql = "SELECT id FROM users WHERE email = ? AND role = ?";
        $check_stmt = execute_prepared($conn, $check_sql, "ss", [$email, $role]);
        if ($check_stmt) {
            $res = mysqli_stmt_get_result($check_stmt);
            if (mysqli_num_rows($res) > 0) {
                $error = "A user account with this email address and role already exists.";
            }
            mysqli_stmt_close($check_stmt);
        }

        if (empty($error)) {
            $insert_sql = "INSERT INTO users (full_name, email, password, role, student_roll_no, zprn, class, division, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = execute_prepared($conn, $insert_sql, "sssssssss", [
                $full_name, $email, $password, $role, 
                (!empty($student_roll_no) ? $student_roll_no : null),
                (!empty($zprn) ? $zprn : null),
                $class, $division, $phone
            ]);

            if ($stmt) {
                $new_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                log_audit($conn, $user['id'], $user['role'], 'Create User', 'user_management', 'Created new ' . get_role_label($role) . ': ' . $email);
                set_flash('success', get_role_label($role) . ' account created successfully!');
                header('Location: manage_user.php');
                exit();
            } else {
                $error = "Failed to create user record in database.";
            }
        }
    }
}
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-user-plus text-primary me-2"></i> Add New User Account</h3>
    <a href="manage_user.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Users</a>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="" id="addUserForm">
    <div class="form-group">
      <label for="role" class="form-label">System Role <span class="text-danger">*</span></label>
      <select id="role" name="role" class="form-select" required onchange="toggleRoleFields(this.value)">
        <option value="">-- Select System Role --</option>
        <option value="admin">System Administrator</option>
        <option value="hod">HOD (Head of Department)</option>
        <option value="gfm">GFM (Guardian Faculty Member)</option>
        <option value="class_teacher">Class Teacher</option>
        <option value="faculty">Subject Faculty</option>
        <option value="student">Student</option>
        <option value="parent">Parent</option>
      </select>
    </div>

    <!-- General Staff Fields (Admin/HOD/GFM/Subject Faculty) -->
    <div id="staffFieldsGroup">
      <div class="form-group">
        <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
        <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. Prof. John Smith">
      </div>
    </div>

    <!-- Student / Parent Specific Dynamic Toggle Fields -->
    <div id="studentParentFieldsGroup" style="display: none;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label for="student_roll_no" class="form-label">Roll Number <span class="text-danger">*</span></label>
          <input type="text" id="student_roll_no" name="student_roll_no" class="form-control" placeholder="e.g. EC1301">
        </div>

        <div class="form-group">
          <label for="zprn" class="form-label">ZPRN (Zeal PRN Number)</label>
          <input type="text" id="zprn" name="zprn" class="form-control" placeholder="e.g. ZPRN20261301">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label for="class" class="form-label">Academic Class</label>
          <select id="class" name="class" class="form-select">
            <option value="FY">FY (First Year)</option>
            <option value="SY">SY (Second Year)</option>
            <option value="TY" selected>TY (Third Year)</option>
            <option value="BY">BY (B.E. Final Year)</option>
          </select>
        </div>

        <div class="form-group">
          <label for="division" class="form-label">Division</label>
          <select id="division" name="division" class="form-select">
            <option value="Division A">Division A</option>
            <option value="Division B">Division B</option>
            <option value="Division C" selected>Division C</option>
            <option value="Division D">Division D</option>
          </select>
        </div>
      </div>
    </div>

    <!-- GFM Specific Allocation -->
    <div id="gfmFieldsGroup" style="display: none;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label for="gfm_class" class="form-label">Assigned Class</label>
          <select id="gfm_class" name="gfm_class" class="form-select">
            <option value="FY">FY</option>
            <option value="SY">SY</option>
            <option value="TY" selected>TY</option>
            <option value="BY">BY</option>
          </select>
        </div>
        <div class="form-group">
          <label for="gfm_division" class="form-label">Assigned Division</label>
          <select id="gfm_division" name="gfm_division" class="form-select">
            <option value="Division A">Division A</option>
            <option value="Division B">Division B</option>
            <option value="Division C" selected>Division C</option>
            <option value="Division D">Division D</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Common Login & Contact Fields -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
      <div class="form-group">
        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
        <input type="email" id="email" name="email" class="form-control" placeholder="e.g. user@zcoer.edu.in" required>
      </div>

      <div class="form-group">
        <label for="phone" class="form-label">Mobile Number</label>
        <input type="text" id="phone" name="phone" class="form-control" placeholder="e.g. 9876543210">
      </div>
    </div>

    <div class="form-group">
      <label for="password" class="form-label">Account Password (PLAIN TEXT) <span class="text-danger">*</span></label>
      <input type="password" id="password" name="password" class="form-control" placeholder="Enter user password" required>
    </div>

    <button type="submit" class="btn btn-primary">
      <i class="fas fa-check-circle me-2"></i> Save & Create User Account
    </button>
  </form>
</div>

<script>
function toggleRoleFields(role) {
  const staffGroup = document.getElementById('staffFieldsGroup');
  const studentParentGroup = document.getElementById('studentParentFieldsGroup');
  const gfmGroup = document.getElementById('gfmFieldsGroup');
  
  if (role === 'student' || role === 'parent') {
    studentParentGroup.style.display = 'block';
    staffGroup.style.display = 'block'; // Keep optional name field
    if(gfmGroup) gfmGroup.style.display = 'none';
  } else if (role === 'gfm') {
    studentParentGroup.style.display = 'none';
    staffGroup.style.display = 'block';
    if(gfmGroup) gfmGroup.style.display = 'block';
  } else {
    studentParentGroup.style.display = 'none';
    staffGroup.style.display = 'block';
    if(gfmGroup) gfmGroup.style.display = 'none';
  }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
