<?php
// Charts Data API / JSON Endpoint

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

require_login();

header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'experiments' => ['Exp 1', 'Exp 2', 'Exp 3'],
    'averages' => [23.5, 22.0, 24.1]
]);
exit();
?>
