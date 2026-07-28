<?php
// Calculate Marks Helper Endpoint

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

$regularity = intval($_GET['r'] ?? 0);
$conduction = intval($_GET['c'] ?? 0);
$output = intval($_GET['o'] ?? 0);
$viva = intval($_GET['v'] ?? 0);

$res = evaluate_experiment($regularity, $conduction, $output, $viva);

echo json_encode(['status' => 'success', 'data' => $res]);
exit();
?>
