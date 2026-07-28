<?php
// Email Verification Handler

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/session.php';

set_flash('info', 'Email verified successfully. You can now access your account.');
header('Location: login.php');
exit();
?>
