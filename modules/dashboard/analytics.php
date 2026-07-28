<?php
// Practical Assessment System - Analytics Redirect
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/config.php';
header('Location: ' . BASE_URL . 'reports/final_marksheet.php');
exit();
?>
