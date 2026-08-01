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
    $academic_year = sanitize($_POST['academic_year'] ?? DEFAULT_ACADEMIC_YEAR);
    
    $batch_selections = $_POST['batch_select'] ?? []; // batch_id => "1"
    $plan_dates = $_POST['plan_date'] ?? []; // batch_id => "YYYY-MM-DD"

    if (empty($subject_name) || $exp_no <= 0 || empty($title)) {
        $error = "Subject Name, Experiment #, and Title are required.";
    } elseif (empty($batch_selections)) {
        $error = "Please select at least one allocated batch to schedule the experiment.";
    } else {
        $success_count = 0;
        foreach ($batch_selections as $b_id => $val) {
            $b_id = intval($b_id);
            $p_date = sanitize($plan_dates[$b_id] ?? date('Y-m-d'));
            
            // Fetch class and div for this batch
            $b_sql = "SELECT class, division FROM batches WHERE id = ?";
            $b_stmt = execute_prepared($conn, $b_sql, "i", [$b_id]);
            if ($b_stmt) {
                $res = mysqli_stmt_get_result($b_stmt);
                if ($r = mysqli_fetch_assoc($res)) {
                    $class = $r['class'];
                    $div = $r['division'];
                    
                    $sql = "INSERT INTO practicals (subject_name, exp_no, title, class, division, batch_id, faculty_id, scheduled_date, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = execute_prepared($conn, $sql, "sisssiiss", [$subject_name, $exp_no, $title, $class, $div, $b_id, $user['id'], $p_date, $academic_year]);
                    if ($stmt) {
                        $success_count++;
                        mysqli_stmt_close($stmt);
                    }
                }
                mysqli_stmt_close($b_stmt);
            }
        }

        if ($success_count > 0) {
            log_audit($conn, $user['id'], $user['role'], 'Create Practical', 'practical_management', 'Created Exp #' . $exp_no . ' (' . $title . ') for ' . $success_count . ' batches.');
            set_flash('success', 'Practical Exp #' . $exp_no . ' successfully scheduled for ' . $success_count . ' batch(es).');
            header('Location: ' . BASE_URL . 'modules/dashboard/faculty_dashboard.php');
            exit();
        } else {
            $error = "Failed to save practical experiment records.";
        }
    }
}

// Fetch Batches Allocated to this Subject Faculty
$fac_id = $user['id'];
$alloc_sql = "SELECT fa.batch_id, fa.subject_name, fa.class, fa.division, b.batch_name 
              FROM faculty_allocations fa 
              JOIN batches b ON fa.batch_id = b.id 
              WHERE fa.faculty_id = ? ORDER BY fa.subject_name ASC, b.batch_name ASC";
$alloc_stmt = execute_prepared($conn, $alloc_sql, "i", [$fac_id]);
$my_allocations = [];
if ($alloc_stmt) {
    $res = mysqli_stmt_get_result($alloc_stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $my_allocations[] = $row;
    }
    mysqli_stmt_close($alloc_stmt);
}
?>

<div class="card" style="max-width: 900px; margin: 0 auto;">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-flask text-primary me-2"></i> Create Practical Experiment</h3>
    <a href="<?php echo BASE_URL; ?>modules/dashboard/faculty_dashboard.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i> <?php echo sanitize($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
      <div class="form-group">
        <label for="subject_name" class="form-label">Subject Name <span class="text-danger">*</span></label>
        <select id="subject_name" name="subject_name" class="form-select" required onchange="filterBatches()">
          <option value="">-- Select Subject --</option>
          <?php 
            $unique_subjects = array_unique(array_column($my_allocations, 'subject_name'));
            foreach ($unique_subjects as $subj): 
          ?>
            <option value="<?php echo sanitize($subj); ?>" <?php echo (isset($_GET['subject']) && $_GET['subject'] === $subj) ? 'selected' : ''; ?>><?php echo sanitize($subj); ?></option>
          <?php endforeach; ?>
        </select>
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

    <div class="form-group mt-4">
      <label class="form-label"><i class="fas fa-users me-1"></i> Multi-Batch Target Assignment <span class="text-danger">*</span></label>
      <div class="alert alert-secondary mb-2" style="font-size: 0.85rem;">Select the allocated batches you wish to schedule this experiment for. You can set a unique Plan Date for each batch.</div>
      
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th style="width: 50px;">Select</th>
              <th>Batch Name</th>
              <th>Class & Div</th>
              <th>Plan Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($my_allocations)): ?>
              <tr><td colspan="4" class="text-center">No batches allocated to you. Contact Admin.</td></tr>
            <?php else: ?>
              <?php foreach ($my_allocations as $alloc): ?>
                <tr class="batch-row" data-subject="<?php echo sanitize($alloc['subject_name']); ?>">
                  <td class="text-center" style="vertical-align: middle;">
                    <label class="custom-checkbox" style="justify-content: center; margin: 0;">
                      <input type="checkbox" name="batch_select[<?php echo $alloc['batch_id']; ?>]" value="1" class="form-check-input">
                      <span class="checkmark"></span>
                    </label>
                  </td>
                  <td><strong><?php echo sanitize($alloc['batch_name']); ?></strong></td>
                  <td><?php echo sanitize($alloc['class'] . ' - ' . $alloc['division']); ?></td>
                  <td>
                    <input type="date" name="plan_date[<?php echo $alloc['batch_id']; ?>]" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg mt-2">
      <i class="fas fa-save me-2"></i> Save & Schedule Practical Experiment
    </button>
  </form>
</div>

<script>
function filterBatches() {
    const subject = document.getElementById('subject_name').value;
    const rows = document.querySelectorAll('.batch-row');
    rows.forEach(row => {
        if (subject === '' || row.getAttribute('data-subject') === subject) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
            // Uncheck if hidden
            row.querySelector('input[type="checkbox"]').checked = false;
        }
    });
}
document.addEventListener('DOMContentLoaded', filterBatches);
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
