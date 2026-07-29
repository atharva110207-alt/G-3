<?php
// Practical Assessment System - Top Navigation Bar
// Zeal College of Engineering & Research

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/config.php';

$logged_user = get_logged_user();
$selected_academic_year = $_GET['academic_year'] ?? ($_SESSION['academic_year'] ?? DEFAULT_ACADEMIC_YEAR);
$selected_class = $_GET['class_filter'] ?? ($_SESSION['class_filter'] ?? 'TY');

// Store selections in session
$_SESSION['academic_year'] = $selected_academic_year;
$_SESSION['class_filter'] = $selected_class;
?>

<header class="app-navbar">
  <div class="navbar-left">
    <button class="mobile-sidebar-toggle" id="sidebarToggleBtn" aria-label="Toggle Sidebar">
      <i class="fas fa-bars"></i>
    </button>
    <div class="navbar-branding" style="display: flex; align-items: center; gap: 10px;">
      <img src="<?php echo BASE_URL; ?>assets/images/logos/logo.png" alt="Logo" style="object-fit: contain; width: 45px; height: 45px;">
      <div>
        <h1 class="navbar-title"><?php echo APP_NAME; ?></h1>
      </div>
    </div>
  </div>

  <div class="navbar-right">
    <!-- Academic Year Selector -->
    <div class="selector-group">
      <label for="navAcademicYear" class="selector-label">A.Y.:</label>
      <select id="navAcademicYear" class="navbar-selector" onchange="updateGlobalFilter('academic_year', this.value)">
        <?php foreach ($ACADEMIC_YEARS as $ay): ?>
          <option value="<?php echo $ay; ?>" <?php echo $selected_academic_year === $ay ? 'selected' : ''; ?>><?php echo $ay; ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Class Selector -->
    <div class="selector-group">
      <label for="navClassFilter" class="selector-label">Class:</label>
      <select id="navClassFilter" class="navbar-selector" onchange="updateGlobalFilter('class_filter', this.value)">
        <?php foreach ($CLASSES as $c): ?>
          <option value="<?php echo $c; ?>" <?php echo $selected_class === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Dark Mode Toggle Button -->
    <button id="themeToggleBtn" class="theme-toggle-btn" title="Toggle Theme">
      <i class="fas fa-moon" id="themeIcon"></i>
    </button>

    <!-- User Profile Badge -->
    <div class="user-profile-badge">
      <div class="user-avatar">
        <i class="fas fa-user"></i>
      </div>
      <div class="user-info">
        <span class="user-name"><?php echo sanitize($logged_user['full_name'] ?? 'User'); ?></span>
        <span class="user-role-tag"><?php echo get_role_label($logged_user['role'] ?? ''); ?></span>
      </div>
    </div>
  </div>
</header>

<style>
.app-navbar {
  height: 70px;
  background: var(--bg-canvas);
  border-bottom: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.75rem;
  box-shadow: var(--shadow-sm);
  z-index: 100;
}

.navbar-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.mobile-sidebar-toggle {
  display: none;
  background: none;
  border: none;
  color: var(--text-primary);
  font-size: 1.25rem;
  cursor: pointer;
}

.navbar-title {
  font-size: 1.1rem;
  font-weight: 800;
  color: var(--text-primary);
  line-height: 1.2;
  letter-spacing: -0.01em;
}

.navbar-subtitle {
  font-size: 0.75rem;
  color: var(--text-secondary);
  font-weight: 500;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.selector-group {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.selector-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--text-secondary);
}

.theme-toggle-btn {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.theme-toggle-btn:hover {
  background: var(--primary-light);
  color: var(--primary-color);
}

.user-profile-badge {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--border-color);
  padding: 0.35rem 0.85rem;
  border-radius: 12px;
}

.user-avatar {
  width: 32px;
  height: 32px;
  background: var(--primary-color);
  color: #ffffff;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.1;
}

.user-role-tag {
  font-size: 0.7rem;
  color: var(--accent-color);
  font-weight: 600;
}

@media (max-width: 992px) {
  .mobile-sidebar-toggle {
    display: block;
  }
  .navbar-title {
    font-size: 0.95rem;
  }
  .navbar-subtitle {
    display: none;
  }
}
</style>

<script>
function updateGlobalFilter(key, value) {
  const url = new URL(window.location.href);
  url.searchParams.set(key, value);
  window.location.href = url.toString();
}
</script>
