<?php
// Practical Assessment System - Sidebar Navigation Component
// Zeal College of Engineering & Research

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/config.php';

$current_user = get_logged_user();
$user_role = $current_user['role'] ?? 'guest';
$current_page = basename($_SERVER['PHP_SELF']);

/**
 * Helper to check active page
 */
function is_active($page_names) {
    global $current_page;
    if (is_string($page_names)) {
        $page_names = [$page_names];
    }
    return in_array($current_page, $page_names) ? 'active' : '';
}
?>

<aside class="app-sidebar" id="sidebar">
  <div class="sidebar-brand">
    <img src="/G-3/assets/images/logos/logo.png" alt="ZEAL Logo" onerror="this.onerror=null; this.src='/G-3/assets/images/logos/Logo.png';" style="object-fit: contain; width: 40px; height: 40px; flex-shrink: 0;">
    <div class="brand-text">
      <span class="brand-title">ZEAL PALPMS</span>
      <span class="brand-sub">ECE Department</span>
    </div>
  </div>

  <nav class="sidebar-menu">
    <!-- SECTION: OVERVIEW -->
    <div class="menu-section-label">OVERVIEW</div>
    <a href="<?php echo get_role_dashboard($user_role); ?>" class="menu-item <?php echo is_active(['admin_dashboard.php', 'hod_dashboard.php', 'gfm_dashboard.php', 'faculty_dashboard.php', 'student_dashboard.php', 'parent_dashboard.php', 'index.php']); ?>">
      <i class="fas fa-chart-line menu-icon"></i>
      <span>Dashboard</span>
    </a>

    <!-- SECTION: ASSESSMENT -->
    <div class="menu-section-label">ASSESSMENT</div>
    
    <?php if ($user_role === 'admin'): ?>
      <a href="<?php echo BASE_URL; ?>admin/manage_user.php" class="menu-item <?php echo is_active(['manage_user.php', 'add_user.php', 'edit_user.php']); ?>">
        <i class="fas fa-users-cog menu-icon"></i>
        <span>User Management</span>
      </a>
      <a href="<?php echo BASE_URL; ?>admin/create_batches.php" class="menu-item <?php echo is_active(['create_batches.php']); ?>">
        <i class="fas fa-layer-group menu-icon"></i>
        <span>Manual Batches</span>
      </a>
      <a href="<?php echo BASE_URL; ?>admin/edit_batches.php" class="menu-item <?php echo is_active(['edit_batches.php']); ?>">
        <i class="fas fa-list menu-icon"></i>
        <span>Edit Existing Batches</span>
      </a>
      <a href="<?php echo BASE_URL; ?>admin/allocations.php" class="menu-item <?php echo is_active(['allocations.php']); ?>">
        <i class="fas fa-tasks menu-icon"></i>
        <span>Batch Allocation</span>
      </a>
    <?php endif; ?>

    <?php if ($user_role === 'hod'): ?>
      <a href="<?php echo BASE_URL; ?>admin/allocations.php" class="menu-item <?php echo is_active(['allocations.php']); ?>">
        <i class="fas fa-tasks menu-icon"></i>
        <span>Batch Allocation</span>
      </a>
      <a href="<?php echo BASE_URL; ?>reports/final_marksheet.php" class="menu-item <?php echo is_active(['final_marksheet.php', 'assesment_report.php']); ?>">
        <i class="fas fa-clipboard-check menu-icon"></i>
        <span>Department Reports</span>
      </a>
    <?php endif; ?>

    <?php if ($user_role === 'faculty'): ?>
      <a href="<?php echo BASE_URL; ?>modules/practical_management/create_practical.php" class="menu-item <?php echo is_active(['create_practical.php', 'edit_practical.php']); ?>">
        <i class="fas fa-flask menu-icon"></i>
        <span>Create Practicals</span>
      </a>
      <a href="<?php echo BASE_URL; ?>modules/attendance/mark_attendance.php" class="menu-item <?php echo is_active(['mark_attendance.php']); ?>">
        <i class="fas fa-calendar-check menu-icon"></i>
        <span>Mark Attendance</span>
      </a>
      <a href="<?php echo BASE_URL; ?>modules/assessment/practical_conduction.php" class="menu-item <?php echo is_active(['practical_conduction.php']); ?>">
        <i class="fas fa-pen-nib menu-icon"></i>
        <span>Evaluate Students</span>
      </a>
    <?php endif; ?>

    <?php if ($user_role === 'gfm'): ?>
      <a href="<?php echo BASE_URL; ?>reports/attendance_report.php" class="menu-item <?php echo is_active(['attendance_report.php']); ?>">
        <i class="fas fa-user-check menu-icon"></i>
        <span>Class Attendance</span>
      </a>
      <a href="<?php echo BASE_URL; ?>reports/assesment_report.php" class="menu-item <?php echo is_active(['assesment_report.php']); ?>">
        <i class="fas fa-poll-h menu-icon"></i>
        <span>Class Performance</span>
      </a>
    <?php endif; ?>

    <?php if ($user_role === 'student' || $user_role === 'parent'): ?>
      <a href="<?php echo BASE_URL; ?>reports/final_marksheet.php" class="menu-item <?php echo is_active(['final_marksheet.php']); ?>">
        <i class="fas fa-award menu-icon"></i>
        <span>My Termwork Marks</span>
      </a>
    <?php endif; ?>

    <!-- SECTION: RESOURCES -->
    <div class="menu-section-label">RESOURCES</div>
    <a href="<?php echo BASE_URL; ?>modules/practical_management/syllabus.php" class="menu-item <?php echo is_active(['syllabus.php']); ?>">
      <i class="fas fa-book-open menu-icon"></i>
      <span><?php echo ($user_role === 'admin' || $user_role === 'hod') ? 'Syllabus Upload' : 'View Syllabus'; ?></span>
    </a>
    <a href="<?php echo BASE_URL; ?>reports/attendance_report.php" class="menu-item <?php echo is_active(['attendance_report.php']); ?>">
      <i class="fas fa-calendar-check menu-icon"></i>
      <span>Practical Attendance</span>
    </a>
    <a href="<?php echo BASE_URL; ?>reports/final_marksheet.php" class="menu-item <?php echo is_active(['final_marksheet.php', 'assesment_report.php']); ?>">
      <i class="fas fa-file-alt menu-icon"></i>
      <span>Practical Marksheet</span>
    </a>

    <!-- SECTION: ACCOUNT -->
    <div class="menu-section-label">ACCOUNT</div>
    <a href="<?php echo BASE_URL; ?>modules/authentication/reset_password.php" class="menu-item <?php echo is_active(['reset_password.php']); ?>">
      <i class="fas fa-key menu-icon"></i>
      <span>Reset Password</span>
    </a>
    
    <?php if ($user_role === 'admin' || $user_role === 'hod'): ?>
      <a href="<?php echo BASE_URL; ?>admin/audit_logs.php" class="menu-item <?php echo is_active(['audit_logs.php']); ?>">
        <i class="fas fa-shield-alt menu-icon"></i>
        <span>System Audit Logs</span>
      </a>
    <?php endif; ?>

    <?php if ($user_role === 'admin'): ?>
      <a href="<?php echo BASE_URL; ?>admin/backup.php" class="menu-item <?php echo is_active(['backup.php']); ?>">
        <i class="fas fa-database menu-icon"></i>
        <span>Database Backup</span>
      </a>
    <?php endif; ?>

    <a href="/G-3/modules/authentication/logout.php" class="nav-link text-danger menu-item">
      <i class="fas fa-sign-out-alt menu-icon"></i>
      <span>Logout</span>
    </a>
  </nav>
</aside>

<style>
.app-sidebar {
  width: 260px;
  background-color: var(--bg-sidebar);
  color: var(--sidebar-text);
  display: flex;
  flex-direction: column;
  border-right: 1px solid rgba(99, 102, 241, 0.2);
  flex-shrink: 0;
  transition: all 0.3s ease;
}

.sidebar-brand {
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.875rem;
  border-bottom: 1px solid rgba(99, 102, 241, 0.15);
}

.brand-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #6366F1, #38bdf8);
  color: #ffffff;
  border-radius: 10px;
  /* display: flex; */
  /* align-items: center; */
  /* justify-content: center; */
  /* font-weight: 900; */
  /* font-size: 1.25rem; */
  /* box-shadow: 0 0 12px rgba(99, 102, 241, 0.4); */
}

.brand-title {
  display: block;
  font-weight: 800;
  font-size: 1.1rem;
  color: #1e1b4b;
  letter-spacing: 0.05em;
}

.brand-sub {
  display: block;
  font-size: 0.725rem;
  color: #4338ca;
  font-weight: 600;
}

.sidebar-menu {
  padding: 1.25rem 0.75rem;
  overflow-y: auto;
  flex: 1;
}

.menu-section-label {
  font-size: 0.7rem;
  font-weight: 800;
  color: #4338ca;
  letter-spacing: 0.08em;
  padding: 1rem 0.75rem 0.5rem 0.75rem;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.75rem 1rem;
  color: var(--sidebar-text);
  font-weight: 600;
  font-size: 0.875rem;
  border-radius: 10px;
  margin-bottom: 0.25rem;
  transition: all 0.2s ease;
  text-decoration: none;
}

.menu-item:hover {
  background-color: var(--sidebar-hover);
  color: #1e1b4b;
}

.menu-item.active {
  background-color: var(--sidebar-active-bg) !important;
  color: #ffffff !important;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
}

.menu-icon {
  font-size: 1.1rem;
  width: 20px;
  text-align: center;
}

.text-danger {
  color: #dc2626 !important;
}

.text-danger:hover {
  background-color: #fee2e2 !important;
}

/* Collapsed State Styles */
.app-sidebar.collapsed {
  width: 80px;
}
.app-sidebar.collapsed .brand-text,
.app-sidebar.collapsed .menu-item span,
.app-sidebar.collapsed .menu-section-label {
  display: none;
}
.app-sidebar.collapsed .sidebar-brand {
  justify-content: center;
  padding: 1.5rem 0;
}
.app-sidebar.collapsed .menu-item {
  justify-content: center;
  padding: 0.75rem;
}
.app-sidebar.collapsed .menu-icon {
  margin: 0;
}
</style>
