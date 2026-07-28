<?php
// Create & Schedule Practical Experiment

$page_title = 'Schedule Practical Experiment';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_role(['faculty', 'admin', 'hod']);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = sanitize($_POST['subject_name'] ?? '');
    $exp_no = intval($_POST['exp_no'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $division = sanitize($_POST['division'] ?? 'Division C');
    $batch_id = intval($_POST['batch_id'] ?? 0);
    $faculty_id = $_SESSION['user_id'];
    $scheduled_date = sanitize($_POST['scheduled_date'] ?? date('Y-m-d'));

    if (empty($subject_name) || $exp_no <= 0 || empty($title) || !$batch_id) {
        $error = 'Please fill in all experiment details and choose a target batch.';
    } else {
        $sql = "INSERT INTO practicals (subject_name, exp_no, title, division, batch_id, faculty_id, scheduled_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = execute_prepared($conn, $sql, "sissiis", [$subject_name, $exp_no, $title, $division, $batch_id, $faculty_id, $scheduled_date]);

        if ($stmt) {
            $new_pract_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);

            log_audit($conn, $faculty_id, 'Created Practical Experiment', 'practicals', "Scheduled Exp #$exp_no ($title) for Batch #$batch_id");
            set_flash('success', "Practical Experiment #$exp_no created successfully!");
            header('Location: ../dashboard/faculty_dashboard.php');
            exit();
        } else {
            $error = 'Failed to schedule practical experiment.';
        }
    }
}

// Fetch available batches
$batches_res = mysqli_query($conn, "SELECT * FROM batches ORDER BY batch_name ASC");

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Setup & Schedule Practical Experiment</h2>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Subject Name *</label>
            <input type="text" name="subject_name" class="form-control" required placeholder="e.g. Microprocessors & Microcontrollers" value="Microprocessors & Microcontrollers">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Experiment # *</label>
                <input type="number" name="exp_no" class="form-control" required min="1" max="20" placeholder="e.g. 1" value="4">
            </div>
            <div class="form-group">
                <label class="form-label">Scheduled Date *</label>
                <input type="date" name="scheduled_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Experiment Title / Topic *</label>
            <input type="text" name="title" class="form-control" required placeholder="e.g. Interfacing ADC 0809 with 8086 Microprocessor">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Division *</label>
                <select name="division" class="form-select" required>
                    <option value="Division C" selected>Division C</option>
                    <option value="Division A">Division A</option>
                    <option value="Division B">Division B</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Target Batch *</label>
                <select name="batch_id" class="form-select" required>
                    <option value="">-- Choose Batch --</option>
                    <?php if ($batches_res): while ($b = mysqli_fetch_assoc($batches_res)): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo sanitize($b['batch_name'] . ' (' . $b['start_roll'] . ' - ' . $b['end_roll'] . ')'); ?></option>
                    <?php endwhile; endif; ?>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">Save & Schedule Experiment</button>
            <a href="../dashboard/faculty_dashboard.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
