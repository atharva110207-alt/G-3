<?php
// Mark Override & Mandatory Audit Logging Module

$page_title = 'Override Assessment Marks';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_role(['faculty', 'admin', 'hod']);

$error = '';
$practical_id = intval($_GET['practical_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p_id = intval($_POST['practical_id'] ?? 0);
    $student_id = intval($_POST['student_id'] ?? 0);
    $new_regularity = intval($_POST['regularity_score'] ?? 0);
    $new_conduction = intval($_POST['conduction_score'] ?? 0);
    $new_output = intval($_POST['output_score'] ?? 0);
    $new_viva = intval($_POST['viva_score'] ?? 0);
    $override_reason = sanitize($_POST['override_reason'] ?? '');

    if (empty($override_reason)) {
        $error = 'A mandatory justification reason must be provided for auditing mark overrides.';
    } else {
        $eval = evaluate_experiment($new_regularity, $new_conduction, $new_output, $new_viva);
        $total = $eval['total'];

        $sql = "UPDATE assessment SET 
                regularity_score = ?, conduction_score = ?, output_score = ?, viva_score = ?, total_score = ?, comments = ?
                WHERE practical_id = ? AND student_id = ?";
        $stmt = execute_prepared($conn, $sql, "iiiiisii", [
            $eval['regularity'], $eval['conduction'], $eval['output'], $eval['viva'],
            $total, "OVERRIDDEN: " . $override_reason, $p_id, $student_id
        ]);

        if ($stmt) {
            mysqli_stmt_close($stmt);

            // Log MANDATORY Audit Trail
            log_audit($conn, $_SESSION['user_id'], 'Assessment Mark Override', 'assessment', 
                "Overrode marks for Student #$student_id in Practical #$p_id. New Total: $total/25. Reason: $override_reason");

            set_flash('success', "Marks overridden successfully! Action recorded in audit logs.");
            header("Location: practical_conduction.php?practical_id=$p_id");
            exit();
        } else {
            $error = 'Failed to override assessment marks.';
        }
    }
}

// Fetch practicals & students
$practicals_res = mysqli_query($conn, "SELECT p.id, p.exp_no, p.title, b.batch_name FROM practicals p JOIN batches b ON p.batch_id = b.id ORDER BY p.id DESC");
$students_res = mysqli_query($conn, "SELECT id, full_name, student_roll_no FROM users WHERE role = 'student' ORDER BY student_roll_no ASC");

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <h2 class="card-title">🛠️ Override Student Marks with Mandatory Audit Log</h2>
            <p style="font-size: 0.875rem; color: var(--text-muted);">Modify previously assigned experiment scores with full traceability</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Target Practical Experiment *</label>
            <select name="practical_id" class="form-select" required>
                <option value="">-- Choose Practical --</option>
                <?php if ($practicals_res): while ($p = mysqli_fetch_assoc($practicals_res)): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo $p['id'] == $practical_id ? 'selected' : ''; ?>>
                        Exp <?php echo $p['exp_no']; ?>: <?php echo sanitize($p['title']); ?> (<?php echo sanitize($p['batch_name']); ?>)
                    </option>
                <?php endwhile; endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Target Student *</label>
            <select name="student_id" class="form-select" required>
                <option value="">-- Choose Student --</option>
                <?php if ($students_res): while ($s = mysqli_fetch_assoc($students_res)): ?>
                    <option value="<?php echo $s['id']; ?>">
                        <?php echo sanitize($s['student_roll_no'] . ' - ' . $s['full_name']); ?>
                    </option>
                <?php endwhile; endif; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Regularity Score (0-5)</label>
                <input type="number" name="regularity_score" class="form-control" min="0" max="5" value="5" required>
            </div>
            <div class="form-group">
                <label class="form-label">Conduction Score (0-10)</label>
                <input type="number" name="conduction_score" class="form-control" min="0" max="10" value="10" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Output Score (0-5)</label>
                <input type="number" name="output_score" class="form-control" min="0" max="5" value="5" required>
            </div>
            <div class="form-group">
                <label class="form-label">Viva Score (0-5)</label>
                <input type="number" name="viva_score" class="form-control" min="0" max="5" value="5" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Mandatory Audit Justification / Reason *</label>
            <textarea name="override_reason" class="form-control" rows="3" required placeholder="State exact reason for mark correction (e.g. Late submission verified by HOD, Re-evaluation after viva re-check)..."></textarea>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-danger" style="flex: 1; justify-content: center;">Confirm & Log Mark Override</button>
            <a href="practical_conduction.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
