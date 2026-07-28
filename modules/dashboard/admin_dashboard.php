<?php
// Admin & HOD Master Dashboard

$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_role(['admin', 'hod']);

$msg = '';
$err = '';

// Handle Automated Batch Creation POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_create_batches'])) {
    $division = sanitize($_POST['division'] ?? 'Division C');
    $prefix = sanitize($_POST['prefix'] ?? 'C');
    $start_num = intval($_POST['start_num'] ?? 1301);
    $end_num = intval($_POST['end_num'] ?? 1320);
    $batch_size = intval($_POST['batch_size'] ?? 10);
    $roll_prefix = sanitize($_POST['roll_prefix'] ?? 'EC');
    $academic_year = sanitize($_POST['academic_year'] ?? ACADEMIC_YEAR);

    if ($start_num > 0 && $end_num >= $start_num && $batch_size > 0) {
        $count = auto_generate_batches($conn, $division, $prefix, $start_num, $end_num, $batch_size, $roll_prefix, $academic_year);
        log_audit($conn, $_SESSION['user_id'], 'Automated Batch Creation', 'batches', "Auto-created $count batches for $division ($roll_prefix$start_num to $roll_prefix$end_num)");
        set_flash('success', "Successfully generated $count batches for $division!");
        header('Location: admin_dashboard.php');
        exit();
    } else {
        $err = 'Invalid roll number range or batch size entered.';
    }
}

// Handle Faculty Allocation POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_allocate_faculty'])) {
    $faculty_id = intval($_POST['faculty_id'] ?? 0);
    $subject_name = sanitize($_POST['subject_name'] ?? '');
    $division = sanitize($_POST['division'] ?? 'Division C');
    $batch_id = intval($_POST['batch_id'] ?? 0);
    $academic_year = sanitize($_POST['academic_year'] ?? ACADEMIC_YEAR);

    if ($faculty_id && !empty($subject_name) && $batch_id) {
        $sql = "INSERT INTO faculty_allocations (faculty_id, subject_name, division, batch_id, academic_year) VALUES (?, ?, ?, ?, ?)";
        $stmt = execute_prepared($conn, $sql, "issis", [$faculty_id, $subject_name, $division, $batch_id, $academic_year]);
        if ($stmt) {
            mysqli_stmt_close($stmt);
            log_audit($conn, $_SESSION['user_id'], 'Allocated Faculty', 'faculty_allocations', "Allocated Faculty #$faculty_id to $subject_name ($division, Batch #$batch_id)");
            set_flash('success', 'Faculty practical allocation updated successfully!');
            header('Location: admin_dashboard.php');
            exit();
        }
    } else {
        $err = 'All allocation fields are required.';
    }
}

// Fetch System Counts
$user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users"))['cnt'] ?? 0;
$student_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users WHERE role = 'student'"))['cnt'] ?? 0;
$faculty_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users WHERE role = 'faculty'"))['cnt'] ?? 0;
$batch_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM batches"))['cnt'] ?? 0;
$pract_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM practicals"))['cnt'] ?? 0;
$assess_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM assessment"))['cnt'] ?? 0;

// Fetch Batches & Faculty for Forms
$batches_res = mysqli_query($conn, "SELECT * FROM batches ORDER BY batch_name ASC");
$faculty_res = mysqli_query($conn, "SELECT id, full_name FROM users WHERE role = 'faculty' ORDER BY full_name ASC");

include __DIR__ . '/../../includes/header.php';
?>

<!-- Statistics Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $user_count; ?></div>
            <div class="stat-label">Total Accounts</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🎓</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $student_count; ?></div>
            <div class="stat-label">Enrolled Students</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">👨‍🏫</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $faculty_count; ?></div>
            <div class="stat-label">Faculty Members</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🧪</div>
        <div class="stat-content">
            <div class="stat-value"><?php echo $pract_count; ?></div>
            <div class="stat-label">Scheduled Practicals</div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 1.5rem;">
    <!-- Automated Batch Creation Module -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">⚡ Automated Batch Generator</h3>
            <span class="badge badge-info">Auto-Split Division</span>
        </div>

        <form action="" method="POST">
            <input type="hidden" name="action_create_batches" value="1">
            
            <div class="form-group">
                <label class="form-label">Target Division *</label>
                <select name="division" class="form-select" required>
                    <option value="Division C" selected>Division C</option>
                    <option value="Division A">Division A</option>
                    <option value="Division B">Division B</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Roll Number Prefix</label>
                    <input type="text" name="roll_prefix" class="form-control" value="EC" required placeholder="e.g. EC">
                </div>
                <div class="form-group">
                    <label class="form-label">Batch Name Prefix</label>
                    <input type="text" name="prefix" class="form-control" value="C" required placeholder="e.g. C">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
                <div class="form-group">
                    <label class="form-label">Start Roll #</label>
                    <input type="number" name="start_num" class="form-control" value="1301" required>
                </div>
                <div class="form-group">
                    <label class="form-label">End Roll #</label>
                    <input type="number" name="end_num" class="form-control" value="1320" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Batch Size</label>
                    <input type="number" name="batch_size" class="form-control" value="10" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                🚀 Auto-Generate Division Batches
            </button>
        </form>
    </div>

    <!-- Practical Session Allocation Module -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📌 Faculty Subject & Batch Allocation</h3>
            <span class="badge badge-success">Academic Assignment</span>
        </div>

        <form action="" method="POST">
            <input type="hidden" name="action_allocate_faculty" value="1">

            <div class="form-group">
                <label class="form-label">Select Faculty Member *</label>
                <select name="faculty_id" class="form-select" required>
                    <option value="">-- Choose Faculty --</option>
                    <?php if ($faculty_res): while ($f = mysqli_fetch_assoc($faculty_res)): ?>
                        <option value="<?php echo $f['id']; ?>"><?php echo sanitize($f['full_name']); ?></option>
                    <?php endwhile; endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Practical Subject Name *</label>
                <input type="text" name="subject_name" class="form-control" placeholder="e.g. Microprocessors & Microcontrollers" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Division</label>
                    <select name="division" class="form-select" required>
                        <option value="Division C" selected>Division C</option>
                        <option value="Division A">Division A</option>
                        <option value="Division B">Division B</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Target Batch *</label>
                    <select name="batch_id" class="form-select" required>
                        <option value="">-- Select Batch --</option>
                        <?php if ($batches_res): while ($b = mysqli_fetch_assoc($batches_res)): ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo sanitize($b['batch_name'] . ' (' . $b['start_roll'] . ' - ' . $b['end_roll'] . ')'); ?></option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-accent" style="width: 100%; justify-content: center;">
                Assign Faculty Allocation
            </button>
        </form>
    </div>
</div>

<!-- Quick System Management Shortcuts -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quick Administration Tools</h3>
    </div>
    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="../../admin/manage_user.php" class="btn btn-secondary">👥 User Accounts Directory</a>
        <a href="../../modules/practical_management/create_practical.php" class="btn btn-secondary">🧪 Schedule Practical Sessions</a>
        <a href="../../admin/audit_logs.php" class="btn btn-secondary">📜 Audit Logs & Overrides</a>
        <a href="../../reports/final_marksheet.php" class="btn btn-primary">📋 Consolidated Term-Work Marksheet</a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
