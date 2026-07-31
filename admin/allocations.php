<?php
ob_start();
// Practical Assessment System - Allocation Module
// Zeal College of Engineering & Research

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Restricted to Admin & HOD
require_role(['admin', 'hod']);

$error = '';
$success = '';

// Handle Delete Actions
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $del_id = intval($_GET['id'] ?? 0);
    $type = $_GET['type'] ?? '';
    if ($del_id > 0) {
        if ($type === 'faculty') {
            execute_prepared($conn, "DELETE FROM faculty_allocations WHERE id = ?", "i", [$del_id]);
            log_audit($conn, $user['id'], $user['role'], 'Delete Faculty Allocation', 'allocation_management', 'Deleted allocation ID #' . $del_id);
        } elseif ($type === 'gfm') {
            execute_prepared($conn, "DELETE FROM gfm_allocations WHERE id = ?", "i", [$del_id]);
            log_audit($conn, $user['id'], $user['role'], 'Delete GFM Allocation', 'allocation_management', 'Deleted GFM allocation ID #' . $del_id);
        } elseif ($type === 'class_teacher') {
            execute_prepared($conn, "DELETE FROM class_teacher_allocations WHERE id = ?", "i", [$del_id]);
            log_audit($conn, $user['id'], $user['role'], 'Delete CT Allocation', 'allocation_management', 'Deleted CT allocation ID #' . $del_id);
        }
        set_flash('success', 'Allocation deleted successfully.');
    }
    header('Location: allocations.php');
    exit();
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allocation_type = $_POST['allocation_type'] ?? '';
    
    if ($allocation_type === 'faculty') {
        $faculty_id = intval($_POST['faculty_id'] ?? 0);
        $subject_name = sanitize($_POST['subject_name'] ?? '');
        $class = sanitize($_POST['class'] ?? 'TY');
        $division = sanitize($_POST['division'] ?? 'Division C');
        $batch_id = intval($_POST['batch_id'] ?? 0);
        $academic_year = sanitize($_POST['academic_year'] ?? DEFAULT_ACADEMIC_YEAR);

        if ($faculty_id <= 0 || empty($subject_name) || empty($division)) {
            $error = "Subject Faculty, Subject Name, and Division are required.";
        } else {
            $b_param = $batch_id > 0 ? $batch_id : null;
            $sql = "INSERT INTO faculty_allocations (faculty_id, subject_name, class, division, batch_id, academic_year) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = execute_prepared($conn, $sql, "isssis", [$faculty_id, $subject_name, $class, $division, $b_param, $academic_year]);
            if ($stmt) {
                mysqli_stmt_close($stmt);
                log_audit($conn, $user['id'], $user['role'], 'Create Faculty Allocation', 'allocation_management', 'Allocated ' . $subject_name . ' to Faculty ID #' . $faculty_id);
                set_flash('success', 'Subject Faculty allocated successfully!');
                header('Location: allocations.php');
                exit();
            } else {
                $error = "Failed to allocate subject faculty.";
            }
        }
    } elseif ($allocation_type === 'gfm') {
        $gfm_id = intval($_POST['gfm_id'] ?? 0);
        $batch_id = intval($_POST['batch_id'] ?? 0);
        $academic_year = sanitize($_POST['academic_year'] ?? DEFAULT_ACADEMIC_YEAR);
        
        if ($gfm_id <= 0 || $batch_id <= 0) {
            $error = "GFM and Assigned Batch are required.";
        } else {
            $sql = "INSERT INTO gfm_allocations (gfm_id, batch_id, academic_year) VALUES (?, ?, ?)";
            $stmt = execute_prepared($conn, $sql, "iis", [$gfm_id, $batch_id, $academic_year]);
            if ($stmt) {
                mysqli_stmt_close($stmt);
                log_audit($conn, $user['id'], $user['role'], 'Create GFM Allocation', 'allocation_management', 'Allocated GFM ID #' . $gfm_id . ' to Batch ID #' . $batch_id);
                set_flash('success', 'GFM allocated successfully!');
                header('Location: allocations.php');
                exit();
            } else {
                $error = "Failed to allocate GFM.";
            }
        }
    } elseif ($allocation_type === 'class_teacher') {
        $class_teacher_id = intval($_POST['class_teacher_id'] ?? 0);
        $class = sanitize($_POST['class'] ?? 'TY');
        $division = sanitize($_POST['division'] ?? 'Division C');
        $academic_year = sanitize($_POST['academic_year'] ?? DEFAULT_ACADEMIC_YEAR);
        
        if ($class_teacher_id <= 0 || empty($division)) {
            $error = "Class Teacher and Division are required.";
        } else {
            $sql = "INSERT INTO class_teacher_allocations (class_teacher_id, class, division, academic_year) VALUES (?, ?, ?, ?)";
            $stmt = execute_prepared($conn, $sql, "isss", [$class_teacher_id, $class, $division, $academic_year]);
            if ($stmt) {
                mysqli_stmt_close($stmt);
                log_audit($conn, $user['id'], $user['role'], 'Create CT Allocation', 'allocation_management', 'Allocated CT ID #' . $class_teacher_id . ' to ' . $division);
                set_flash('success', 'Class Teacher allocated successfully!');
                header('Location: allocations.php');
                exit();
            } else {
                $error = "Failed to allocate Class Teacher.";
            }
        }
    }
}

// Fetch Dropdowns
$faculty_res = mysqli_query($conn, "SELECT id, full_name, email FROM users WHERE role = 'faculty' ORDER BY full_name ASC");
$faculty_members = $faculty_res ? mysqli_fetch_all($faculty_res, MYSQLI_ASSOC) : [];

$gfm_res = mysqli_query($conn, "SELECT id, full_name, email FROM users WHERE role = 'faculty' ORDER BY full_name ASC");
$gfm_members = $gfm_res ? mysqli_fetch_all($gfm_res, MYSQLI_ASSOC) : [];

$subject_res = mysqli_query($conn, "SELECT DISTINCT subject_name FROM syllabi ORDER BY subject_name ASC");
$subjects = $subject_res ? mysqli_fetch_all($subject_res, MYSQLI_ASSOC) : [];

$ct_res = mysqli_query($conn, "SELECT id, full_name, email FROM users WHERE role IN ('class_teacher', 'faculty') ORDER BY full_name ASC");
$class_teachers = $ct_res ? mysqli_fetch_all($ct_res, MYSQLI_ASSOC) : [];

$batch_res = mysqli_query($conn, "SELECT id, batch_name, class, division FROM batches ORDER BY batch_name ASC");
$batches_opt = $batch_res ? mysqli_fetch_all($batch_res, MYSQLI_ASSOC) : [];

$page_title = "Role & Batch Allocations";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 1400px; margin: 0 auto;">
    
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 1.5rem;">
        
        <!-- CARD 1: Assign Subject to Faculty -->
        <div class="card h-100">
            <div class="card-header border-primary" style="border-bottom: 2px solid;">
                <h4 class="card-title text-primary m-0"><i class="fas fa-chalkboard-teacher me-2"></i> Assign Subject to Faculty</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="allocation_type" value="faculty">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year" class="form-select">
                            <?php foreach ($ACADEMIC_YEARS as $ay): ?>
                                <option value="<?php echo $ay; ?>" <?php echo $ay === DEFAULT_ACADEMIC_YEAR ? 'selected' : ''; ?>><?php echo $ay; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Subject Faculty <span class="text-danger">*</span></label>
                        <select name="faculty_id" class="form-select" required>
                            <option value="">-- Select Faculty --</option>
                            <?php foreach ($faculty_members as $fm): ?>
                                <option value="<?php echo $fm['id']; ?>"><?php echo sanitize($fm['full_name'] . ' (' . $fm['email'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                        <select name="subject_name" class="form-select" required>
                            <option value="">-- Select Subject --</option>
                            <?php foreach ($subjects as $sub): ?>
                                <option value="<?php echo sanitize($sub['subject_name']); ?>"><?php echo sanitize($sub['subject_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Class</label>
                            <select name="class" class="form-select">
                                <?php foreach ($CLASSES as $c): ?>
                                    <option value="<?php echo $c; ?>" <?php echo $c === 'TY' ? 'selected' : ''; ?>><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Division <span class="text-danger">*</span></label>
                            <select name="division" class="form-select">
                                <option value="Division A">Division A</option>
                                <option value="Division B">Division B</option>
                                <option value="Division C" selected>Division C</option>
                                <option value="Division D">Division D</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Assigned Batch (Optional)</label>
                        <select name="batch_id" class="form-select">
                            <option value="0">All Batches in Division</option>
                            <?php foreach ($batches_opt as $bo): ?>
                                <option value="<?php echo $bo['id']; ?>">Batch <?php echo sanitize($bo['batch_name'] . ' (' . $bo['class'] . '-' . $bo['division'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-check-circle me-2"></i> Assign Subject</button>
                </form>
            </div>
        </div>

        <!-- CARD 2: Assign Batch to GFM -->
        <div class="card h-100">
            <div class="card-header border-warning" style="border-bottom: 2px solid;">
                <h4 class="card-title text-warning m-0"><i class="fas fa-users-cog me-2"></i> Assign Batch to GFM</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="allocation_type" value="gfm">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year" class="form-select">
                            <?php foreach ($ACADEMIC_YEARS as $ay): ?>
                                <option value="<?php echo $ay; ?>" <?php echo $ay === DEFAULT_ACADEMIC_YEAR ? 'selected' : ''; ?>><?php echo $ay; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Select GFM <span class="text-danger">*</span></label>
                        <select name="gfm_id" class="form-select" required>
                            <option value="">-- Select GFM --</option>
                            <?php foreach ($gfm_members as $gm): ?>
                                <option value="<?php echo $gm['id']; ?>"><?php echo sanitize($gm['full_name'] . ' (' . $gm['email'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Assigned Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" class="form-select" required>
                            <option value="">-- Select Batch --</option>
                            <?php foreach ($batches_opt as $bo): ?>
                                <option value="<?php echo $bo['id']; ?>">Batch <?php echo sanitize($bo['batch_name'] . ' (' . $bo['class'] . '-' . $bo['division'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-warning text-dark w-100"><i class="fas fa-check-circle me-2"></i> Allocate Batch</button>
                </form>
            </div>
        </div>

        <!-- CARD 3: Assign Division to Class Teacher -->
        <div class="card h-100">
            <div class="card-header border-success" style="border-bottom: 2px solid;">
                <h4 class="card-title text-success m-0"><i class="fas fa-user-tie me-2"></i> Assign Division to Class Teacher</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="allocation_type" value="class_teacher">
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year" class="form-select">
                            <?php foreach ($ACADEMIC_YEARS as $ay): ?>
                                <option value="<?php echo $ay; ?>" <?php echo $ay === DEFAULT_ACADEMIC_YEAR ? 'selected' : ''; ?>><?php echo $ay; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Select Class Teacher <span class="text-danger">*</span></label>
                        <select name="class_teacher_id" class="form-select" required>
                            <option value="">-- Select Class Teacher --</option>
                            <?php foreach ($class_teachers as $ct): ?>
                                <option value="<?php echo $ct['id']; ?>"><?php echo sanitize($ct['full_name'] . ' (' . $ct['email'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 form-group">
                            <label class="form-label">Class</label>
                            <select name="class" class="form-select">
                                <?php foreach ($CLASSES as $c): ?>
                                    <option value="<?php echo $c; ?>" <?php echo $c === 'TY' ? 'selected' : ''; ?>><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">Division <span class="text-danger">*</span></label>
                            <select name="division" class="form-select">
                                <option value="Division A">Division A</option>
                                <option value="Division B">Division B</option>
                                <option value="Division C" selected>Division C</option>
                                <option value="Division D">Division D</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-check-circle me-2"></i> Assign Division</button>
                </form>
            </div>
        </div>

    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
