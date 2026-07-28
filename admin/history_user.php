<?php
// User Activity History Redirect / View

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

require_role(['admin', 'hod']);

header('Location: audit_logs.php');
exit();
?>
