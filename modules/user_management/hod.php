<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/config.php';
header('Location: ' . BASE_URL . 'admin/manage_user.php?role=hod');
exit();
?>
