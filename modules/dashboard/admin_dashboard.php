<?php
// Practical Assessment System - Admin Control Dashboard
// Zeal College of Engineering & Research

$page_title = "Admin Dashboard";
require_once __DIR__ . '/../../includes/header.php';

require_role('admin');

$search_query = sanitize($_GET['search_user'] ?? '');
$searched_user = null;
$user_batches = [];
$user_allocations = [];

if (!empty($search_query)) {
    // Search user by ZPRN, email, or username (full_name)
    $sql = "SELECT * FROM users WHERE zprn = ? OR email = ? OR full_name LIKE ? LIMIT 1";
    $like_query = "%" . $search_query . "%";
    $stmt = execute_prepared($conn, $sql, "sss", [$search_query, $search_query, $like_query]);
    if ($stmt) {
        $res = mysqli_stmt_get_result($stmt);
        if ($user_db = mysqli_fetch_assoc($res)) {
            $searched_user = $user_db;
            
            // If Student, fetch their batches
            if ($user_db['role'] === 'student') {
                $b_sql = "SELECT b.batch_name, b.subject_assigned, b.academic_year 
                          FROM batch_students bs 
                          JOIN batches b ON bs.batch_id = b.id 
                          WHERE bs.student_id = ?";
                $b_stmt = execute_prepared($conn, $b_sql, "i", [$user_db['id']]);
                if ($b_stmt) {
                    $b_res = mysqli_stmt_get_result($b_stmt);
                    while ($row = mysqli_fetch_assoc($b_res)) {
                        $user_batches[] = $row;
                    }
                    mysqli_stmt_close($b_stmt);
                }
            }
            
            // If Faculty, fetch their allocations
            if ($user_db['role'] === 'faculty') {
                $a_sql = "SELECT subject_name, class, division, academic_year 
                          FROM faculty_allocations 
                          WHERE faculty_id = ?";
                $a_stmt = execute_prepared($conn, $a_sql, "i", [$user_db['id']]);
                if ($a_stmt) {
                    $a_res = mysqli_stmt_get_result($a_stmt);
                    while ($row = mysqli_fetch_assoc($a_res)) {
                        $user_allocations[] = $row;
                    }
                    mysqli_stmt_close($a_stmt);
                }
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!-- Top Action Bar -->
<div style="display: flex; justify-content: flex-end; margin-bottom: 2rem;">
  <a href="<?php echo BASE_URL; ?>admin/add_user.php" class="btn btn-primary btn-lg" style="box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);">
    <i class="fas fa-user-plus me-2"></i> Add New User
  </a>
</div>

<!-- Massive Centralized Search Bar -->
<div class="card" style="padding: 3rem 2rem; border-radius: 20px; text-align: center; background: linear-gradient(145deg, var(--bg-card), var(--bg-canvas)); border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
  <h2 style="font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem;">User Directory Search</h2>
  <p style="color: var(--text-secondary); margin-bottom: 2rem;">Enter a ZPRN, Email Address, or exact Username to pull up their complete profile.</p>
  
  <form method="GET" action="" style="max-width: 700px; margin: 0 auto; position: relative;">
    <input type="text" name="search_user" class="form-control" placeholder="Search by ZPRN, Email, or Name..." value="<?php echo sanitize($search_query); ?>" required autofocus style="padding: 1.25rem 1.5rem; padding-right: 150px; font-size: 1.2rem; border-radius: 50px; border: 2px solid var(--primary-color); background: rgba(0,0,0,0.2); color: var(--text-primary); box-shadow: inset 0 2px 5px rgba(0,0,0,0.2);">
    
    <button type="submit" class="btn btn-primary" style="position: absolute; right: 8px; top: 8px; bottom: 8px; border-radius: 40px; padding: 0 2rem; font-weight: 700; font-size: 1.1rem;">
      <i class="fas fa-search me-2"></i> Search
    </button>
  </form>
</div>

<!-- Dynamic Search Results Profile View -->
<?php if (!empty($search_query)): ?>
  <?php if ($searched_user): ?>
    <div class="card mt-4" style="border-radius: 15px; overflow: hidden; border: 1px solid var(--border-color);">
      <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(16, 185, 129, 0.1)); padding: 2rem; display: flex; align-items: center; gap: 2rem; border-bottom: 1px solid var(--border-color);">
        <div style="width: 80px; height: 80px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800;">
          <?php echo strtoupper(substr($searched_user['full_name'], 0, 1)); ?>
        </div>
        <div>
          <h2 style="margin: 0; font-size: 1.8rem; color: var(--text-primary); font-weight: 800;"><?php echo sanitize($searched_user['full_name']); ?></h2>
          <div style="display: flex; gap: 1rem; margin-top: 0.5rem; align-items: center;">
            <span class="badge badge-info" style="font-size: 0.9rem; padding: 0.4rem 0.8rem;"><?php echo get_role_label($searched_user['role']); ?></span>
            <span style="color: var(--text-secondary); font-size: 0.95rem;"><i class="fas fa-envelope me-1"></i> <?php echo sanitize($searched_user['email']); ?></span>
            <?php if (!empty($searched_user['phone'])): ?>
              <span style="color: var(--text-secondary); font-size: 0.95rem;"><i class="fas fa-phone me-1"></i> <?php echo sanitize($searched_user['phone']); ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div style="padding: 2rem; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Base Info -->
        <div>
          <h4 style="color: var(--text-primary); font-weight: 700; margin-bottom: 1rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem;">Academic Details</h4>
          <table class="table" style="background: transparent;">
            <tbody>
              <tr><td style="color: var(--text-secondary); font-weight: 600; border-top: none; width: 40%;">ZPRN</td><td style="border-top: none;"><strong><?php echo sanitize($searched_user['zprn'] ?: 'N/A'); ?></strong></td></tr>
              <tr><td style="color: var(--text-secondary); font-weight: 600;">Roll Number</td><td><strong><?php echo sanitize($searched_user['student_roll_no'] ?: 'N/A'); ?></strong></td></tr>
              <tr><td style="color: var(--text-secondary); font-weight: 600;">Class & Division</td><td><strong><?php echo sanitize(($searched_user['class'] ?? 'TY') . ' - ' . ($searched_user['division'] ?? 'N/A')); ?></strong></td></tr>
              <tr><td style="color: var(--text-secondary); font-weight: 600;">Account Created</td><td><?php echo date('d M Y', strtotime($searched_user['created_at'])); ?></td></tr>
            </tbody>
          </table>
        </div>

        <!-- Role Specific Info -->
        <div>
          <?php if ($searched_user['role'] === 'student'): ?>
            <h4 style="color: var(--text-primary); font-weight: 700; margin-bottom: 1rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem;">Assigned Batches</h4>
            <?php if (empty($user_batches)): ?>
              <div class="alert alert-secondary text-center">No batches assigned to this student.</div>
            <?php else: ?>
              <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                <?php foreach ($user_batches as $b): ?>
                  <li style="background: rgba(255,255,255,0.05); padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600; color: var(--text-primary);"><?php echo sanitize($b['batch_name']); ?></span>
                    <span class="badge badge-warning"><?php echo sanitize($b['academic_year']); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          <?php elseif ($searched_user['role'] === 'faculty'): ?>
            <h4 style="color: var(--text-primary); font-weight: 700; margin-bottom: 1rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem;">Subject Allocations</h4>
            <?php if (empty($user_allocations)): ?>
              <div class="alert alert-secondary text-center">No subjects allocated to this faculty.</div>
            <?php else: ?>
              <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                <?php foreach ($user_allocations as $a): ?>
                  <li style="background: rgba(255,255,255,0.05); padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;"><?php echo sanitize($a['subject_name']); ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); display: flex; justify-content: space-between;">
                      <span><?php echo sanitize($a['class'] . ' - ' . $a['division']); ?></span>
                      <span class="badge badge-warning"><?php echo sanitize($a['academic_year']); ?></span>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          <?php else: ?>
            <h4 style="color: var(--text-primary); font-weight: 700; margin-bottom: 1rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem;">Access Level</h4>
            <div class="alert alert-info text-center">
              <i class="fas fa-shield-alt fa-2x mb-2"></i><br>
              This user possesses administrative or oversight privileges.
            </div>
          <?php endif; ?>
        </div>
      </div>
      
      <div style="background: rgba(0,0,0,0.1); padding: 1rem 2rem; border-top: 1px solid var(--border-color); text-align: right;">
        <a href="<?php echo BASE_URL; ?>admin/edit_user.php?id=<?php echo $searched_user['id']; ?>" class="btn btn-secondary"><i class="fas fa-edit me-2"></i> Edit Profile</a>
        <a href="<?php echo BASE_URL; ?>admin/manage_user.php?search=<?php echo urlencode($searched_user['email']); ?>" class="btn btn-accent ms-2"><i class="fas fa-cogs me-2"></i> Manage User</a>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-danger mt-4 text-center" style="padding: 2rem;">
      <i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>
      No user found matching "<strong><?php echo sanitize($search_query); ?></strong>". Please verify the ZPRN, Email Address, or Name.
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
