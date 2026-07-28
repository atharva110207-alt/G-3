<?php
// Practical Assessment System - Create Practical Experiment
// Zeal College of Engineering & Research

$page_title = "Create Practical Experiment";
require_once __DIR__ . '/../../includes/header.php';

// Restricted strictly to Subject Faculty
require_role(['faculty', 'admin']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = sanitize($_POST['subject_name'] ?? '');
    $exp_no = intval($_POST['exp_no'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $class = sanitize($_POST['class'] ?? 'TY');
    $division = sanitize($_POST['division'] ?? 'Division C');
    $batch_id = intval($_POST['batch_id'] ?? 0);
    $scheduled_date = sanitize($_POST['scheduled_date'] ?? ''); // Plan Date
    $academic_year = sanitize($_POST['academic_year'] ?? DEFAULT_ACADEMIC_YEAR);

    if (empty($subject_name) || $exp_no <= 0 || empty($title) || $batch_id <= 0 || empty($scheduled_date)) {
        $error = "Subject Name, Experiment #, Title, Batch, and Plan Date are required.";
    } else {
        $sql = "INSERT INTO practicals (subject_name, exp_no, title, class, division, batch_id, faculty_id, scheduled_date, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = execute_prepared($conn, $sql, "sisssiiss", [$subject_name, $exp_no, $title, $class, $division, $batch_id, $user['id'], $scheduled_date, $academic_year]);

        if ($stmt) {
            $pract_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);

            log_audit($conn, $user['id'], $user['role'], 'Create Practical', 'practical_management', 'Created Exp #' . $exp_no . ' (' . $title . ') for ' . $subject_name);
            set_flash('success', 'Practical Exp #' . $exp_no . ' created successfully!');
            header('Location: ' . BASE_URL . 'modules/dashboard/faculty_dashboard.php');
            exit();
        } else {
            $error = "Failed to save practical experiment record.";
        }
    }
}

// Fetch Batches for Subject Faculty
$batch_sql = "SELECT id, batch_name, class, division FROM batches ORDER BY batch_name ASC";
$batch_res = mysqli_query($conn, $batch_sql);
$batches = [];
if ($batch_res) {
    while ($b = mysqli_fetch_assoc($batch_res)) {
        $batches[] = $b;
    }
}
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-flask text-primary me-2"></i> Create Practical Experiment</h3>
    <a href="<?php echo BASE_URL; ?>modules/dashboard/faculty_dashboard.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="form-group">
      <label for="subject_name" class="form-label">Subject Name <span class="text-danger">*</span></label>
      <select id="subject_name" name="subject_name" class="form-select" required>
        <option value="Microprocessors & Microcontrollers">Microprocessors & Microcontrollers</option>
        <option value="Digital Signal Processing">Digital Signal Processing</option>
        <option value="VLSI Design & Embedded Systems">VLSI Design & Embedded Systems</option>
        <option value="Computer Networks & Security">Computer Networks & Security</option>
      </select>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
      <div class="form-group">
        <label for="exp_no" class="form-label">Experiment No # <span class="text-danger">*</span></label>
        <input type="number" id="exp_no" name="exp_no" class="form-control" min="1" max="25" placeholder="e.g. 1" required>
      </div>

      <div class="form-group">
        <label for="title" class="form-label">Experiment Title / Topic <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control" placeholder="e.g. 8086 Assembly Language Programming" required>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
      <div class="form-group">
        <label for="class" class="form-label">Class</label>
        <select id="class" name="class" class="form-select">
          <?php foreach ($CLASSES as $c): ?>
            <option value="<?php echo $c; ?>" <?php echo $c === 'TY' ? 'selected' : ''; ?>><?php echo $c; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="division" class="form-label">Division <span class="text-danger">*</span></label>
        <select id="division" name="division" class="form-select">
          <option value="Division A">Division A</option>
          <option value="Division B">Division B</option>
          <option value="Division C" selected>Division C</option>
          <option value="Division D">Division D</option>
        </select>
      </div>

      <div class="form-group">
        <label for="batch_id" class="form-label">Target Batch <span class="text-danger">*</span></label>
        <select id="batch_id" name="batch_id" class="form-select" required>
          <option value="">-- Select Batch --</option>
          <?php foreach ($batches as $b): ?>
            <option value="<?php echo $b['id']; ?>">Batch <?php echo sanitize($b['batch_name'] . ' (' . $b['class'] . '-' . $b['division'] . ')'); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
      <!-- Renamed Field Label per requirements: Plan Date -->
      <div class="form-group">
        <label for="scheduled_date" class="form-label">Plan Date <span class="text-danger">*</span></label>
        <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
      </div>

      <div class="form-group">
        <label for="academic_year" class="form-label">Academic Year</label>
        <select id="academic_year" name="academic_year" class="form-select">
          <?php foreach ($ACADEMIC_YEARS as $ay): ?>
            <option value="<?php echo $ay; ?>"><?php echo $ay; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">
      <i class="fas fa-check-circle me-2"></i> Schedule Practical Experiment
    </button>
  </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
