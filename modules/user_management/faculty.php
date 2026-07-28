<?php
// Practical Assessment System - Subject Faculty Registry View
// Zeal College of Engineering & Research

$page_title = "Subject Faculty Registry";
require_once __DIR__ . '/../../includes/header.php';

header('Location: ' . BASE_URL . 'admin/manage_user.php?role=faculty');
exit();
?>
