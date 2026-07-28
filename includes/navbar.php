<?php
// Practical Assessment & Laboratory Performance Management System
// Top Navigation Bar

$user = get_logged_user();
?>
<nav style="background-color: var(--bg-card); border-bottom: 1px solid var(--border-color); padding: 1rem 1.75rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <button id="sidebarToggle" class="btn btn-secondary btn-sm" style="display: none;">☰ Menu</button>
        <div>
            <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0;"><?php echo COLLEGE_NAME; ?></h3>
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Academic Term: <?php echo ACADEMIC_YEAR; ?></span>
        </div>
    </div>

    <div style="display: flex; align-items: center; gap: 1rem;">
        <!-- Dynamic Theme Switcher Toggle Button -->
        <button id="themeToggleBtn" class="btn btn-secondary btn-sm" title="Toggle Light/Dark Theme">
            🌙 Theme Switcher
        </button>

        <?php if ($user): ?>
            <div style="display: flex; align-items: center; gap: 0.75rem; padding-left: 1rem; border-left: 1px solid var(--border-color);">
                <div style="text-align: right;">
                    <div style="font-weight: 700; font-size: 0.875rem; color: var(--text-primary);"><?php echo sanitize($user['full_name']); ?></div>
                    <span class="badge badge-info"><?php echo strtoupper(sanitize($user['role'])); ?></span>
                </div>
                <a href="<?php echo BASE_URL; ?>modules/authentication/logout.php" class="btn btn-danger btn-sm" title="Sign Out">Logout</a>
            </div>
        <?php endif; ?>
    </div>
</nav>
