<?php
// Individual Student Report Redirect

require_once __DIR__ . '/../config/auth.php';
require_login();
header('Location: ../modules/dashboard/student_dashboard.php');
exit();
?>
