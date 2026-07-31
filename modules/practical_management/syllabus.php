<?php
// Practical Assessment System - Syllabus Upload & Viewer Module
// Zeal College of Engineering & Research

$page_title = "Syllabus Repository";
require_once __DIR__ . '/../../includes/header.php';

$can_upload = in_array($user['role'], ['admin', 'hod']);
$error = '';
$success = '';

// Upload Syllabus Action
if ($can_upload && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = sanitize($_POST['subject_name'] ?? '');
    $class = sanitize($_POST['class'] ?? 'TY');
    $division = sanitize($_POST['division'] ?? 'Division C');
    $semester = sanitize($_POST['semester'] ?? '');

    if (empty($subject_name) || empty($_FILES['syllabus_file']['name'])) {
        $error = "Subject Name and File are required.";
    } else {
        $target_dir = __DIR__ . '/../../uploads/syllabi/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . '_' . basename($_FILES['syllabus_file']['name']);
        $target_path = $target_dir . $file_name;
        $rel_path = 'uploads/syllabi/' . $file_name;

        if (move_uploaded_file($_FILES['syllabus_file']['tmp_name'], $target_path)) {
            $sql = "INSERT INTO syllabi (class, division, semester, subject_name, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = execute_prepared($conn, $sql, "sssssi", [$class, $division, $semester, $subject_name, $rel_path, $user['id']]);
            if ($stmt) {
                mysqli_stmt_close($stmt);
                log_audit($conn, $user['id'], $user['role'], 'Upload Syllabus', 'practical_management', 'Uploaded syllabus for ' . $subject_name);
                set_flash('success', 'Syllabus document uploaded successfully!');
                header('Location: syllabus.php');
                exit();
            }
        } else {
            $error = "Failed to save file on server.";
        }
    }
}

// Delete Syllabus Action
if ($can_upload && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_syllabus_id'])) {
    $del_id = intval($_POST['delete_syllabus_id']);
    
    // First fetch the file path so we can delete the actual file
    $fetch_sql = "SELECT file_path, subject_name FROM syllabi WHERE id = ?";
    $fetch_stmt = execute_prepared($conn, $fetch_sql, "i", [$del_id]);
    if ($fetch_stmt) {
        $res = mysqli_stmt_get_result($fetch_stmt);
        if ($row = mysqli_fetch_assoc($res)) {
            $file_to_delete = __DIR__ . '/../../' . $row['file_path'];
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
            }
            
            // Delete from database
            $del_sql = "DELETE FROM syllabi WHERE id = ?";
            $del_stmt = execute_prepared($conn, $del_sql, "i", [$del_id]);
            if ($del_stmt) {
                mysqli_stmt_close($del_stmt);
                log_audit($conn, $user['id'], $user['role'], 'Delete Syllabus', 'practical_management', 'Deleted syllabus for ' . $row['subject_name']);
                set_flash('success', 'Syllabus document deleted successfully!');
                header('Location: syllabus.php');
                exit();
            }
        }
        mysqli_stmt_close($fetch_stmt);
    }
}

// Fetch Syllabi List
if ($user['role'] === 'faculty') {
    $syl_sql = "SELECT s.*, u.full_name as uploader FROM syllabi s JOIN users u ON s.uploaded_by = u.id 
                WHERE s.subject_name IN (SELECT subject_name FROM faculty_allocations WHERE faculty_id = ?)
                ORDER BY s.upload_date DESC";
    $syl_stmt = execute_prepared($conn, $syl_sql, "i", [$user['id']]);
    $syllabi_list = [];
    if ($syl_stmt) {
        $res = mysqli_stmt_get_result($syl_stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $syllabi_list[] = $row;
        }
        mysqli_stmt_close($syl_stmt);
    }
} else {
    $syl_sql = "SELECT s.*, u.full_name as uploader FROM syllabi s JOIN users u ON s.uploaded_by = u.id ORDER BY s.upload_date DESC";
    $syl_res = mysqli_query($conn, $syl_sql);
    $syllabi_list = [];
    if ($syl_res) {
        while ($row = mysqli_fetch_assoc($syl_res)) {
            $syllabi_list[] = $row;
        }
    }
}
?>

<div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
  <?php if ($can_upload): ?>
    <!-- Upload Form for Admin / HOD -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-upload text-primary me-2"></i> Upload Course Syllabus</h3>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?></div>
      <?php endif; ?>

      <form method="POST" action="" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
          <div class="form-group" style="margin: 0;">
            <label for="academic_year" class="form-label">Academic Year (AY)</label>
            <select id="academic_year" name="academic_year" class="form-select">
              <?php foreach ($ACADEMIC_YEARS as $ay): ?>
                  <option value="<?php echo $ay; ?>" <?php echo $ay === DEFAULT_ACADEMIC_YEAR ? 'selected' : ''; ?>><?php echo $ay; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group" style="margin: 0;">
            <label for="department" class="form-label">Department</label>
            <select id="department" name="department" class="form-select">
              <option value="AI & Data Science">AI & Data Science</option>
              <option value="AI & Machine Learning">AI & Machine Learning</option>
              <option value="Civil Engineering">Civil Engineering</option>
              <option value="Computer Engineering">Computer Engineering</option>
              <option value="Electronics & Computer Engineering">Electronics & Computer Engineering</option>
              <option value="E & TC Engineering" selected>E & TC Engineering</option>
              <option value="Electrical Engineering">Electrical Engineering</option>
              <option value="Information Technology">Information Technology</option>
              <option value="Mechanical Engineering">Mechanical Engineering</option>
              <option value="Robotics & Automation">Robotics & Automation</option>
            </select>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
          <div class="form-group" style="margin: 0;">
            <label for="class" class="form-label">Class</label>
            <select id="class" name="class" class="form-select">
              <?php foreach ($CLASSES as $c): ?>
                <option value="<?php echo $c; ?>" <?php echo $c === 'TY' ? 'selected' : ''; ?>><?php echo $c; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group" style="margin: 0;">
            <label for="semester" class="form-label">Semester</label>
            <select id="semester" name="semester" class="form-select">
              <option value="SEM 1">SEM 1 (FY)</option>
              <option value="SEM 2">SEM 2 (FY)</option>
              <option value="SEM 3">SEM 3 (SY)</option>
              <option value="SEM 4">SEM 4 (SY)</option>
              <option value="SEM 5">SEM 5 (TY)</option>
              <option value="SEM 6">SEM 6 (TY)</option>
              <option value="SEM 7">SEM 7 (Final Year)</option>
              <option value="SEM 8">SEM 8 (Final Year)</option>
            </select>
          </div>

          <div class="form-group" style="margin: 0;">
            <label for="subject_name" class="form-label">Subject Name <span class="text-danger">*</span></label>
            <input type="text" id="subject_name" name="subject_name" class="form-control" placeholder="Subject Name" required>
          </div>
        </div>

        <div class="form-group">
          <label for="syllabus_file" class="form-label">Syllabus PDF / Doc File <span class="text-danger">*</span></label>
          <input type="file" id="syllabus_file" name="syllabus_file" class="form-control" accept=".pdf,.doc,.docx" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">
          <i class="fas fa-cloud-upload-alt me-2"></i> UPLOAD SYLLABUS
        </button>
      </form>
    </div>
  <?php endif; ?>

  <!-- Syllabus Repository Listing -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-book text-primary me-2"></i> Course Syllabus Repository</h3>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Subject Name</th>
            <th>Class / Sem</th>
            <th>Uploaded By</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($syllabi_list)): ?>
            <tr><td colspan="5" class="text-center" style="color: var(--text-muted); padding: 2rem;">No syllabus documents uploaded yet.</td></tr>
          <?php else: ?>
            <?php foreach ($syllabi_list as $s): ?>
              <tr>
                <td><strong style="color: var(--text-primary);"><?php echo sanitize($s['subject_name']); ?></strong></td>
                <td><?php echo sanitize($s['class'] . (!empty($s['semester']) ? ' - ' . $s['semester'] : '')); ?></td>
                <td><?php echo sanitize($s['uploader']); ?></td>
                <td><?php echo format_date($s['upload_date']); ?></td>
                <td style="white-space: nowrap;">
                  <a href="<?php echo BASE_URL . sanitize($s['file_path']); ?>" target="_blank" class="btn btn-accent btn-sm me-1">
                    <i class="fas fa-download me-1"></i> View PDF
                  </a>
                  <button onclick="let printWindow = window.open('<?php echo BASE_URL . sanitize($s['file_path']); ?>', '_blank'); printWindow.onload = function() { printWindow.print(); };" class="btn btn-secondary btn-sm">
                    <i class="fas fa-print me-1"></i> Print
                  </button>
                  <?php if ($can_upload): ?>
                  <form method="POST" action="" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this syllabus document? This action cannot be undone.');">
                    <input type="hidden" name="delete_syllabus_id" value="<?php echo $s['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm ms-1">
                      <i class="fas fa-trash-alt"></i> Delete
                    </button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
