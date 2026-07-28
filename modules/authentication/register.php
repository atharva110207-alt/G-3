<?php
// Practical Assessment System - Self-Registration Disabled Notice
// Zeal College of Engineering & Research

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/config.php';

set_flash('warning', 'Self-registration is disabled per institution policy. Please contact System Administrator or HOD for account creation.');
header('Location: ' . BASE_URL . 'modules/authentication/login.php');
exit();
?>
