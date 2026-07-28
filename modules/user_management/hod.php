<?php
// HOD Management Redirect

require_once __DIR__ . '/../../config/auth.php';
require_login();
header('Location: ../../admin/manage_user.php?role=hod');
exit();
?>
