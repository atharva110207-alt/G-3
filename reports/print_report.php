<?php
// Dedicated Clean Print Report Viewer

require_once __DIR__ . '/../config/auth.php';
require_login();
header('Location: final_marksheet.php');
exit();
?>
