<?php
// Practical Assessment System - Multi-Tier Rubric Assessment Evaluator
// Zeal College of Engineering & Research

$page_title = "Practical Conduction & Evaluation";
require_once __DIR__ . '/../../includes/header.php';

require_role(['faculty', 'admin', 'hod']);

$practical_id = intval($_GET['practical_id'] ?? 0);
$error = '';
$success = '';

// Save Assessment Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_assessment'])) {
    $practical_id = intval($_POST['practical_id'] ?? 0);
    $eval_data = $_POST['eval'] ?? []; // student_id => [regularity, conduction, output, viva, comments]

    if ($practical_id > 0 && !empty($eval_data)) {
        foreach ($eval_data as $student_id => $scores) {
            $student_id = intval($student_id);
            $reg = intval($scores['regularity'] ?? 0);
            $cond = intval($scores['conduction'] ?? 0);
            $out = intval($scores['output'] ?? 0);
            $viva = intval($scores['viva'] ?? 0);
            $comments = sanitize($scores['comments'] ?? '');

            $eval_result = evaluate_experiment($reg, $cond, $out, $viva);
            $total = $eval_result['total'];
            $today = date('Y-m-d');

            $sql = "INSERT INTO assessment (practical_id, student_id, faculty_id, regularity_score, conduction_score, output_score, viva_score, total_score, evaluation_date, comments) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    regularity_score = VALUES(regularity_score), 
                    conduction_score = VALUES(conduction_score), 
                    output_score = VALUES(output_score), 
                    viva_score = VALUES(viva_score), 
                    total_score = VALUES(total_score), 
                    evaluation_date = VALUES(evaluation_date), 
                    comments = VALUES(comments)";
            
            $stmt = execute_prepared($conn, $sql, "iiiiiisiss", [
                $practical_id, $student_id, $user['id'], 
                $eval_result['regularity'], $eval_result['conduction'], 
                $eval_result['output'], $eval_result['viva'], $total, 
                $today, $comments
            ]);
            if ($stmt) { mysqli_stmt_close($stmt); }
        }

        log_audit($conn, $user['id'], $user['role'], 'Evaluate Practicals', 'assessment', 'Saved 25-mark evaluation for practical ID #' . $practical_id);
        set_flash('success', 'Student practical evaluation scores saved successfully!');
        header('Location: practical_conduction.php?practical_id=' . $practical_id);
        exit();
    }
}

// Fetch Practicals Dropdown
if ($user['role'] === 'faculty') {
    $pract_sql = "SELECT p.*, b.batch_name FROM practicals p JOIN batches b ON p.batch_id = b.id WHERE p.faculty_id = ? ORDER BY p.scheduled_date DESC";
    $pract_stmt = execute_prepared($conn, $pract_sql, "i", [$user['id']]);
} else {
    $pract_sql = "SELECT p.*, b.batch_name FROM practicals p JOIN batches b ON p.batch_id = b.id ORDER BY p.scheduled_date DESC";
    $pract_stmt = execute_prepared($conn, $pract_sql, "", []);
}

$practicals_opt = [];
if ($pract_stmt) {
    $pract_res = mysqli_stmt_get_result($pract_stmt);
    while ($pr = mysqli_fetch_assoc($pract_res)) {
        $practicals_opt[] = $pr;
    }
    mysqli_stmt_close($pract_stmt);
}

// Selected Practical Details
$selected_pract = null;
$students_roster = [];
$existing_assessment = [];

if ($practical_id > 0) {
    foreach ($practicals_opt as $p) {
        if ($p['id'] == $practical_id) {
            $selected_pract = $p;
            break;
        }
    }

    if ($selected_pract) {
        $b_id = $selected_pract['batch_id'];
        
        $st_sql = "SELECT u.id, u.full_name, u.student_roll_no, u.zprn 
                   FROM users u 
                   JOIN batch_students bs ON u.id = bs.student_id 
                   WHERE bs.batch_id = ? 
                   ORDER BY u.student_roll_no ASC";
        $st_stmt = execute_prepared($conn, $st_sql, "i", [$b_id]);

        if ($st_stmt) {
            $res = mysqli_stmt_get_result($st_stmt);
            while ($st = mysqli_fetch_assoc($res)) {
                $students_roster[] = $st;
            }
            mysqli_stmt_close($st_stmt);
        }

        // Fallback if batch has no explicit students (legacy behavior)
        if (empty($students_roster)) {
            $st_sql = "SELECT id, full_name, student_roll_no, zprn FROM users WHERE role = 'student' AND division = ? ORDER BY student_roll_no ASC";
            $st_stmt = execute_prepared($conn, $st_sql, "s", [$selected_pract['division']]);
            if ($st_stmt) {
                $res = mysqli_stmt_get_result($st_stmt);
                while ($st = mysqli_fetch_assoc($res)) {
                    $students_roster[] = $st;
                }
                mysqli_stmt_close($st_stmt);
            }
        }

        // Fetch Existing Assessment Scores
        $ass_sql = "SELECT * FROM assessment WHERE practical_id = ?";
        $ass_stmt = execute_prepared($conn, $ass_sql, "i", [$practical_id]);
        if ($ass_stmt) {
            $res = mysqli_stmt_get_result($ass_stmt);
            while ($ar = mysqli_fetch_assoc($res)) {
                $existing_assessment[$ar['student_id']] = $ar;
            }
            mysqli_stmt_close($ass_stmt);
        }

        // Fetch Attendance for Regularity Auto-Fill
        $att_sql = "SELECT student_id, status FROM attendance WHERE practical_id = ?";
        $att_stmt = execute_prepared($conn, $att_sql, "i", [$practical_id]);
        $attendance_data = [];
        if ($att_stmt) {
            $res = mysqli_stmt_get_result($att_stmt);
            while ($ar = mysqli_fetch_assoc($res)) {
                $attendance_data[$ar['student_id']] = $ar['status'];
            }
            mysqli_stmt_close($att_stmt);
        }
    }
}
?>

<div class="card mb-4">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
    <h3 class="card-title"><i class="fas fa-pen-nib text-primary me-2"></i> Practical Assessment</h3>
    <?php if ($selected_pract): ?>
      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
          <a href="<?php echo BASE_URL; ?>reports/batch_marksheet_report.php?subject=<?php echo urlencode($selected_pract['subject_name']); ?>&batch_id=<?php echo $selected_pract['batch_id']; ?>" target="_blank" class="btn btn-primary btn-sm">
            <i class="fas fa-file-alt me-1"></i> Publish Marksheet
          </a>
          <a href="<?php echo BASE_URL; ?>reports/defaulter_list.php?subject=<?php echo urlencode($selected_pract['subject_name']); ?>&batch_id=<?php echo $selected_pract['batch_id']; ?>" target="_blank" class="btn btn-danger btn-sm">
            <i class="fas fa-exclamation-triangle me-1"></i> Generate Defaulter List
          </a>
      </div>
    <?php endif; ?>
  </div>

  <div style="padding: 1.5rem;">
    <h4 style="font-size: 1rem; color: var(--text-secondary); margin-bottom: 1rem;">Select Scheduled Practical Experiment <span class="text-danger">*</span></h4>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
      <?php if(empty($practicals_opt)): ?>
        <div class="alert alert-secondary w-100">No scheduled practical experiments found.</div>
      <?php else: ?>
        <?php foreach ($practicals_opt as $po): ?>
          <a href="?practical_id=<?php echo $po['id']; ?>" class="card" style="text-decoration: none; padding: 1rem; border: 1px solid <?php echo $practical_id == $po['id'] ? 'var(--primary-color)' : 'var(--border-color)'; ?>; background: <?php echo $practical_id == $po['id'] ? 'rgba(99, 102, 241, 0.05)' : 'transparent'; ?>;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
              <span class="badge badge-info">Exp #<?php echo $po['exp_no']; ?></span>
              <span style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-calendar-alt me-1"></i><?php echo format_date($po['scheduled_date']); ?></span>
            </div>
            <h5 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo sanitize($po['title']); ?>">
              <?php echo sanitize($po['title']); ?>
            </h5>
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0;">
              Batch: <strong><?php echo sanitize($po['batch_name']); ?></strong> 
            </p>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($selected_pract): ?>
  <div class="card">
    <div class="card-header">
      <div>
        <h3 class="card-title">
          <i class="fas fa-award text-accent me-2"></i> 
          Assessment Form: Exp #<?php echo $selected_pract['exp_no']; ?> - <?php echo sanitize($selected_pract['title']); ?>
        </h3>
        <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem;">
          Plan Date: <strong><?php echo format_date($selected_pract['scheduled_date']); ?></strong> &bull; Criteria: Regularity (5) + Performance (10) + Output (5) + Viva (5) = Total (25 Marks)
        </p>
      </div>
    </div>

    <form method="POST" action="">
      <input type="hidden" name="save_assessment" value="1">
      <input type="hidden" name="practical_id" value="<?php echo $selected_pract['id']; ?>">

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Roll Number</th>
              <th>Student Name</th>
              <th>Regularity (5)</th>
              <th>Performance (10)</th>
              <th>Output (5)</th>
              <th>Viva (5)</th>
              <th>Total (25)</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($students_roster)): ?>
              <tr><td colspan="8" class="text-center" style="color: var(--text-muted); padding: 2rem;">No students found in batch roster.</td></tr>
            <?php else: ?>
              <?php foreach ($students_roster as $st): ?>
                <?php 
                  $ex = $existing_assessment[$st['id']] ?? null;
                  $status = $attendance_data[$st['id']] ?? 'Absent';
                  
                  // Auto-fill Regularity based on Attendance
                  $r_val = ($status === 'Present') ? 5 : 0;
                  
                  $c_val = $ex['conduction_score'] ?? 10;
                  $o_val = $ex['output_score'] ?? 5;
                  $v_val = $ex['viva_score'] ?? 5;
                  $t_val = $r_val + $c_val + $o_val + $v_val;
                ?>
                <tr class="eval-row">
                  <td><strong class="badge badge-info" style="font-size: 0.85rem;"><?php echo sanitize($st['student_roll_no']); ?></strong></td>
                  <td><strong style="color: var(--text-primary);"><?php echo sanitize($st['full_name']); ?></strong></td>
                  
                  <td>
                    <input type="hidden" name="eval[<?php echo $st['id']; ?>][regularity]" value="<?php echo $r_val; ?>" class="score-reg">
                    <span class="badge <?php echo $r_val == 5 ? 'badge-success' : 'badge-danger'; ?>" style="font-size: 0.9rem; padding: 0.4rem 0.6rem;"><?php echo $r_val; ?> / 5</span>
                  </td>

                  <td>
                    <select name="eval[<?php echo $st['id']; ?>][conduction]" class="form-select score-input score-cond" style="width: 75px;" onchange="calculateRowTotal(this)">
                      <option value="10" <?php echo $c_val == 10 ? 'selected' : ''; ?>>10</option>
                      <option value="7" <?php echo $c_val == 7 ? 'selected' : ''; ?>>7</option>
                      <option value="5" <?php echo $c_val == 5 ? 'selected' : ''; ?>>5</option>
                      <option value="0" <?php echo $c_val == 0 ? 'selected' : ''; ?>>0</option>
                    </select>
                  </td>

                  <td>
                    <select name="eval[<?php echo $st['id']; ?>][output]" class="form-select score-input score-out" style="width: 70px;" onchange="calculateRowTotal(this)">
                      <option value="5" <?php echo $o_val == 5 ? 'selected' : ''; ?>>5</option>
                      <option value="3" <?php echo $o_val == 3 ? 'selected' : ''; ?>>3</option>
                      <option value="2" <?php echo $o_val == 2 ? 'selected' : ''; ?>>2</option>
                      <option value="0" <?php echo $o_val == 0 ? 'selected' : ''; ?>>0</option>
                    </select>
                  </td>

                  <td>
                    <select name="eval[<?php echo $st['id']; ?>][viva]" class="form-select score-input score-viva" style="width: 70px;" onchange="calculateRowTotal(this)">
                      <option value="5" <?php echo $v_val == 5 ? 'selected' : ''; ?>>5</option>
                      <option value="4" <?php echo $v_val == 4 ? 'selected' : ''; ?>>4</option>
                      <option value="3" <?php echo $v_val == 3 ? 'selected' : ''; ?>>3</option>
                      <option value="0" <?php echo $v_val == 0 ? 'selected' : ''; ?>>0</option>
                    </select>
                  </td>

                  <td>
                    <span class="row-total-badge badge badge-success" style="font-size: 1rem; font-weight: 800; min-width: 50px; text-align: center;">
                      <?php echo $t_val; ?> / 25
                    </span>
                  </td>

                  <td>
                    <input type="text" name="eval[<?php echo $st['id']; ?>][comments]" class="form-control" placeholder="Comments..." value="<?php echo sanitize($ex['comments'] ?? ''); ?>" style="font-size: 0.8rem;">
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if (!empty($students_roster)): ?>
        <div style="padding: 1.5rem; border-top: 1px solid var(--border-color); text-align: right;">
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save me-2"></i> Save & Lock Evaluation Scores
          </button>
        </div>
      <?php endif; ?>
    </form>
  </div>
<?php endif; ?>

<script>
function calculateRowTotal(selectElem) {
  const row = selectElem.closest('.eval-row');
  const reg = parseInt(row.querySelector('.score-reg').value) || 0;
  const cond = parseInt(row.querySelector('.score-cond').value) || 0;
  const out = parseInt(row.querySelector('.score-out').value) || 0;
  const viva = parseInt(row.querySelector('.score-viva').value) || 0;

  const total = reg + cond + out + viva;
  const totalBadge = row.querySelector('.row-total-badge');
  totalBadge.innerText = total + " / 25";
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
