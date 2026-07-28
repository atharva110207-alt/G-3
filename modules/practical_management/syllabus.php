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
            $sql = "INSERT INTO syllabi (class, division, subject_name, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?)";
            $stmt = execute_prepared($conn, $sql, "ssssi", [$class, $division, $subject_name, $rel_path, $user['id']]);
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

// Fetch Syllabi List
$syl_sql = "SELECT s.*, u.full_name as uploader FROM syllabi s JOIN users u ON s.uploaded_by = u.id ORDER BY s.upload_date DESC";
$syl_res = mysqli_query($conn, $syl_sql);
$syllabi_list = [];
if ($syl_res) {
    while ($row = mysqli_fetch_assoc($syl_res)) {
        $syllabi_list[] = $row;
    }
}
?>

<div style="display: grid; grid-template-columns: <?php echo $can_upload ? '1fr 1.5fr' : '1fr'; ?>; gap: 1.5rem;">
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
        <div class="form-group">
          <label for="subject_name" class="form-label">Subject Name <span class="text-danger">*</span></label>
          <select id="subject_name" name="subject_name" class="form-select" required>
            <option value="Microprocessors & Microcontrollers">Microprocessors & Microcontrollers</option>
            <option value="Digital Signal Processing">Digital Signal Processing</option>
            <option value="VLSI Design & Embedded Systems">VLSI Design & Embedded Systems</option>
            <option value="Computer Networks & Security">Computer Networks & Security</option>
          </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label for="class" class="form-label">Class</label>
            <select id="class" name="class" class="form-select">
              <?php foreach ($CLASSES as $c): ?>
                <option value="<?php echo $c; ?>" <?php echo $c === 'TY' ? 'selected' : ''; ?>><?php echo $c; ?></option>
              <?php endforeach; ?>
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

        <div class="form-group">
          <label for="syllabus_file" class="form-label">Syllabus PDF / Doc File <span class="text-danger">*</span></label>
          <input type="file" id="syllabus_file" name="syllabus_file" class="form-control" accept=".pdf,.doc,.docx" required>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fas fa-cloud-upload-alt me-2"></i> Upload Syllabus Document
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
            <th>Class / Div</th>
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
                <td><?php echo sanitize($s['class'] . ' - ' . $s['division']); ?></td>
                <td><?php echo sanitize($s['uploader']); ?></td>
                <td><?php echo format_date($s['upload_date']); ?></td>
                <td>
                  <a href="<?php echo BASE_URL . sanitize($s['file_path']); ?>" target="_blank" class="btn btn-accent btn-sm">
                    <i class="fas fa-download me-1"></i> View PDF
                  </a>
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
