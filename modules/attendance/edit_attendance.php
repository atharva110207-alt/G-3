<?php
// Edit Attendance Record Redirect

require_once __DIR__ . '/../../config/auth.php';
require_login();

$practical_id = intval($_GET['practical_id'] ?? 0);
header("Location: mark_attendance.php?practical_id=$practical_id");
exit();
?>
