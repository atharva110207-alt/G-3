<?php
// Practical History Redirect

require_once __DIR__ . '/../../config/auth.php';
require_login();
header('Location: ../dashboard/faculty_dashboard.php');
exit();
?>
