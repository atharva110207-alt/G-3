<?php
// Practical Assessment & Laboratory Performance Management System
// Dynamic Role-Based Sidebar Navigation

$user_role = get_user_role();
?>
<aside class="sidebar" style="width: 260px; background-color: var(--bg-sidebar); min-height: 100vh; display: flex; flex-direction: column; flex-shrink: 0; transition: width 0.3s ease;">
    <div style="padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 0.75rem;">
        <div style="font-size: 1.75rem;">⚡</div>
        <div>
            <div style="color: #ffffff; font-weight: 800; font-size: 1.125rem; letter-spacing: -0.02em;">LAB ASSESS</div>
            <div style="color: var(--sidebar-text); font-size: 0.75rem; font-weight: 600;">Performance Portal</div>
        </div>
    </div>

    <div style="padding: 1rem 0; flex: 1;" class="sidebar-menu">
        <div style="padding: 0.5rem 1.5rem; font-size: 0.6875rem; font-weight: 700; color: var(--sidebar-text); text-transform: uppercase; letter-spacing: 0.08em;">
            Main Navigation
        </div>

        <?php if ($user_role === 'admin' || $user_role === 'hod'): ?>
            <a href="<?php echo BASE_URL; ?>modules/dashboard/admin_dashboard.php" class="sidebar-link">
                📊 Admin Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>admin/manage_user.php" class="sidebar-link">
                👥 User Management
            </a>
            <a href="<?php echo BASE_URL; ?>modules/practical_management/create_practical.php" class="sidebar-link">
                🧪 Schedule Practicals
            </a>
            <a href="<?php echo BASE_URL; ?>admin/audit_logs.php" class="sidebar-link">
                📜 System Audit Logs
            </a>
            <a href="<?php echo BASE_URL; ?>admin/backup.php" class="sidebar-link">
                💾 Database Backup
            </a>
            <a href="<?php echo BASE_URL; ?>reports/final_marksheet.php" class="sidebar-link">
                📋 Final Marksheet Report
            </a>
        <?php endif; ?>

        <?php if ($user_role === 'gfm'): ?>
            <a href="<?php echo BASE_URL; ?>modules/dashboard/gfm_dashboarrd.php" class="sidebar-link">
                📊 GFM Overview Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>modules/user_management/students.php" class="sidebar-link">
                🎓 Division C Students
            </a>
            <a href="<?php echo BASE_URL; ?>reports/attendance_report.php" class="sidebar-link">
                📅 Class Attendance Summary
            </a>
            <a href="<?php echo BASE_URL; ?>reports/final_marksheet.php" class="sidebar-link">
                📑 Consolidated Term-Work
            </a>
        <?php endif; ?>

        <?php if ($user_role === 'faculty'): ?>
            <a href="<?php echo BASE_URL; ?>modules/dashboard/faculty_dashboard.php" class="sidebar-link">
                📊 Faculty Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>modules/practical_management/create_practical.php" class="sidebar-link">
                ➕ Setup New Experiment
            </a>
            <a href="<?php echo BASE_URL; ?>modules/attendance/mark_attendance.php" class="sidebar-link">
                ✍️ Mark Batch Attendance
            </a>
            <a href="<?php echo BASE_URL; ?>modules/assessment/practical_conduction.php" class="sidebar-link">
                📝 25-Mark Assessment Grid
            </a>
            <a href="<?php echo BASE_URL; ?>modules/assessment/override_marks.php" class="sidebar-link">
                🛠️ Override Marks Log
            </a>
            <a href="<?php echo BASE_URL; ?>reports/assesment_report.php" class="sidebar-link">
                📑 Batch Marksheet Report
            </a>
        <?php endif; ?>

        <?php if ($user_role === 'student'): ?>
            <a href="<?php echo BASE_URL; ?>modules/dashboard/student_dashboard.php" class="sidebar-link">
                🎓 My Performance Portal
            </a>
            <a href="<?php echo BASE_URL; ?>reports/student_report.php" class="sidebar-link">
                📈 Practical Score History
            </a>
        <?php endif; ?>

        <?php if ($user_role === 'parent'): ?>
            <a href="<?php echo BASE_URL; ?>modules/dashboard/parent_dashboard.php" class="sidebar-link">
                👨‍👩‍👧 Child Performance Portal
            </a>
            <a href="<?php echo BASE_URL; ?>reports/student_report.php" class="sidebar-link">
                📑 Detailed Term-Work Report
            </a>
        <?php endif; ?>
    </div>

    <div style="padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); font-size: 0.75rem; color: var(--sidebar-text);">
        System v2.5 &bull; Production Ready
    </div>
</aside>

<style>
.sidebar-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    color: var(--sidebar-text);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
}
.sidebar-link:hover {
    background-color: var(--sidebar-hover);
    color: var(--sidebar-text-active);
}
</style>
