<?php
// Edit Practical Experiment

$page_title = 'Edit Practical';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_role(['faculty', 'admin', 'hod']);

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ../dashboard/faculty_dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $scheduled_date = sanitize($_POST['scheduled_date'] ?? date('Y-m-d'));

    $sql = "UPDATE practicals SET title = ?, scheduled_date = ? WHERE id = ?";
    $stmt = execute_prepared($conn, $sql, "ssi", [$title, $scheduled_date, $id]);
    if ($stmt) {
        mysqli_stmt_close($stmt);
        log_audit($conn, $_SESSION['user_id'], 'Updated Practical', 'practicals', "Updated Exp #$id title to $title");
        set_flash('success', 'Practical experiment updated.');
        header('Location: ../dashboard/faculty_dashboard.php');
        exit();
    }
}

// Fetch practical
$p_sql = "SELECT * FROM practicals WHERE id = ? LIMIT 1";
$p_stmt = execute_prepared($conn, $p_sql, "i", [$id]);
$pract = false;
if ($p_stmt) {
    $pract = mysqli_fetch_assoc(mysqli_stmt_get_result($p_stmt));
    mysqli_stmt_close($p_stmt);
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Edit Practical Experiment</h2>
    </div>

    <form action="" method="POST">
        <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required value="<?php echo sanitize($pract['title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Scheduled Date</label>
            <input type="date" name="scheduled_date" class="form-control" required value="<?php echo sanitize($pract['scheduled_date'] ?? ''); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Update Experiment</button>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
