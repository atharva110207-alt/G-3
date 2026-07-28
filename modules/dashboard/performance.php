<?php
// Performance Redirect

require_once __DIR__ . '/../../config/auth.php';
require_login();

header('Location: analytics.php');
exit();
?>
