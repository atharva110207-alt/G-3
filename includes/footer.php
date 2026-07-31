<?php
// Practical Assessment System - Page Footer Template
// Zeal College of Engineering & Research

$current_script = basename($_SERVER['PHP_SELF']);
$auth_pages = ['login.php', 'forgot_password.php', 'reset_password.php', 'register.php'];
?>

<?php if (!in_array($current_script, $auth_pages)): ?>
    </main>
  </div>
</div>
<?php endif; ?>

<!-- Core JavaScript Helpers -->
<script src="<?php echo BASE_URL; ?>assets/js/dashboard.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/validation.js"></script>

<script>
// Theme Toggle Switch Handling with localStorage persistence
document.addEventListener('DOMContentLoaded', () => {
  const themeToggleBtn = document.getElementById('themeToggleBtn');
  const themeIcon = document.getElementById('themeIcon');
  const htmlTag = document.documentElement;

  function updateIcon(theme) {
    if (themeIcon) {
      if (theme === 'dark') {
        themeIcon.className = 'fas fa-sun';
      } else {
        themeIcon.className = 'fas fa-moon';
      }
    }
  }

  const currentTheme = htmlTag.getAttribute('data-theme') || 'dark';
  updateIcon(currentTheme);

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
      const newTheme = htmlTag.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      htmlTag.setAttribute('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);
      updateIcon(newTheme);
    });
  }

  // Mobile Sidebar Toggle
  const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
  const sidebar = document.getElementById('appSidebar');
  if (sidebarToggleBtn && sidebar) {
    sidebarToggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('active');
    });
  }
});
</script>
</body>
</html>
<?php ob_end_flush(); ?>
