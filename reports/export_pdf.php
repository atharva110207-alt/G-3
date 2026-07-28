<?php
// PDF / Printable Export Redirect

require_once __DIR__ . '/../config/auth.php';
require_login();
header('Location: final_marksheet.php');
exit();
?>
