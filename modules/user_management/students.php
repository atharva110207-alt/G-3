<?php
// Practical Assessment System - Student Registry View
// Zeal College of Engineering & Research

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/config.php';
header('Location: ' . BASE_URL . 'admin/manage_user.php?role=student');
exit();
?>
