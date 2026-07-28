<?php
// Assessment Update Handler Redirect

require_once __DIR__ . '/../../config/auth.php';
require_login();
header('Location: practical_conduction.php');
exit();
?>
