<?php
// Parents Directory View

$page_title = 'Parents Directory';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_login();

$sql = "SELECT p.*, s.full_name as student_name 
        FROM users p 
        LEFT JOIN users s ON p.student_roll_no = s.student_roll_no AND s.role = 'student'
        WHERE p.role = 'parent'
        ORDER BY p.student_roll_no ASC";
$result = mysqli_query($conn, $sql);

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Parent Directory</h2>
            <p style="font-size: 0.875rem; color: var(--text-muted);">Parents linked with student roll numbers</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Linked Roll No</th>
                    <th>Child / Student Name</th>
                    <th>Parent Name</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><strong><?php echo sanitize($row['student_roll_no']); ?></strong></td>
                            <td><?php echo sanitize($row['student_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo sanitize($row['full_name']); ?></td>
                            <td><?php echo sanitize($row['email']); ?></td>
                            <td><?php echo sanitize($row['phone'] ?? '-'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted);">No parent records registered.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
