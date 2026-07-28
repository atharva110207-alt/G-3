<?php
// Experiment Completion Status Report

$page_title = 'Experiment Status Report';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

header('Location: assesment_report.php');
exit();
?>
