<?php
// Attendance History Redirect

require_once __DIR__ . '/../../config/auth.php';
require_login();
header('Location: attendance_report.php');
exit();
?>
