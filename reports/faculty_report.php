<?php
// Practical Assessment System - Subject Faculty Activity Report
// Zeal College of Engineering & Research

$page_title = "Subject Faculty Activity Report";
require_once __DIR__ . '/../includes/header.php';

require_role(['admin', 'hod']);

// Fetch Subject Faculty Conducted Statistics
$fac_sql = "SELECT u.id, u.full_name, u.email, COUNT(DISTINCT p.id) as total_practicals, COUNT(DISTINCT a.id) as total_evaluations 
            FROM users u 
            LEFT JOIN practicals p ON u.id = p.faculty_id 
            LEFT JOIN assessment a ON u.id = a.faculty_id 
            WHERE u.role = 'faculty' 
            GROUP BY u.id";
$fac_res = mysqli_query($conn, $fac_sql);
$faculty_stats = [];
if ($fac_res) {
    while ($r = mysqli_fetch_assoc($fac_res)) {
        $faculty_stats[] = $r;
    }
}
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-chalkboard-teacher text-primary me-2"></i> Subject Faculty Workload & Conduction Statistics</h3>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Subject Faculty Name</th>
          <th>Email Address</th>
          <th>Total Practicals Scheduled</th>
          <th>Total Evaluations Completed</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($faculty_stats as $fs): ?>
          <tr>
            <td><strong><?php echo sanitize($fs['full_name']); ?></strong></td>
            <td><?php echo sanitize($fs['email']); ?></td>
            <td><span class="badge badge-info"><?php echo $fs['total_practicals']; ?> Practicals</span></td>
            <td><span class="badge badge-success"><?php echo $fs['total_evaluations']; ?> Evaluations</span></td>
            <td><span class="badge badge-success">Active</span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
