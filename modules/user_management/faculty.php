<?php
// Faculty Directory View

$page_title = 'Faculty Directory';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_login();

$sql = "SELECT u.*, GROUP_CONCAT(DISTINCT fa.subject_name SEPARATOR ', ') as subjects 
        FROM users u 
        LEFT JOIN faculty_allocations fa ON u.id = fa.faculty_id 
        WHERE u.role = 'faculty' 
        GROUP BY u.id";
$result = mysqli_query($conn, $sql);

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Faculty Directory & Subject Allocations</h2>
            <p style="font-size: 0.875rem; color: var(--text-muted);">Assigned practical faculty members</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Faculty ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Division</th>
                    <th>Allocated Subjects</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><strong><?php echo sanitize($row['full_name']); ?></strong></td>
                            <td><?php echo sanitize($row['email']); ?></td>
                            <td><span class="badge badge-info"><?php echo sanitize($row['division'] ?? 'Division C'); ?></span></td>
                            <td><?php echo sanitize($row['subjects'] ?? 'None'); ?></td>
                            <td><?php echo sanitize($row['phone'] ?? '-'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted);">No faculty accounts found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
