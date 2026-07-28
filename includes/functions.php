<?php
// Practical Assessment System - Core Utility & Evaluation Engine Functions
// Zeal College of Engineering & Research

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/session.php';

/**
 * XSS Sanitization wrapper
 *
 * @param mixed $data
 * @return mixed
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Write action entry into system audit_logs table
 *
 * @param mysqli $conn
 * @param int $user_id
 * @param string $user_role
 * @param string $action
 * @param string $target_module
 * @param string $details
 * @return bool
 */
function log_audit($conn, $user_id, $user_role, $action, $target_module, $details = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (empty($user_role)) {
        $user_role = get_user_role() ?? 'guest';
    }
    
    $sql = "INSERT INTO audit_logs (user_id, user_role, action_performed, target_module, IP_address, details) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = execute_prepared($conn, $sql, "isssss", [$user_id, $user_role, $action, $target_module, $ip, $details]);
    if ($stmt) {
        mysqli_stmt_close($stmt);
        return true;
    }
    return false;
}

/**
 * Get system setting from database
 *
 * @param mysqli $conn
 * @param string $key
 * @param string $default
 * @return string
 */
function get_system_setting($conn, $key, $default = '1') {
    $sql = "SELECT setting_value FROM system_settings WHERE setting_key = ?";
    $stmt = execute_prepared($conn, $sql, "s", [$key]);
    if ($stmt) {
        $res = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($res)) {
            mysqli_stmt_close($stmt);
            return $row['setting_value'];
        }
        mysqli_stmt_close($stmt);
    }
    return $default;
}

/**
 * Set system setting in database
 *
 * @param mysqli $conn
 * @param string $key
 * @param string $value
 * @return bool
 */
function set_system_setting($conn, $key, $value) {
    $sql = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    $stmt = execute_prepared($conn, $sql, "ss", [$key, $value]);
    if ($stmt) {
        mysqli_stmt_close($stmt);
        return true;
    }
    return false;
}

/**
 * AUTOMATED EVALUATION ENGINE (Max 25 Marks per Experiment)
 * Calculates scores for all 4 criteria:
 * 1. Regularity (Max 5 Marks)
 * 2. Practical Conduction (Max 10 Marks)
 * 3. Program / Practical Output (Max 5 Marks)
 * 4. Viva / Understanding (Max 5 Marks)
 *
 * @param int $regularity
 * @param int $conduction
 * @param int $output
 * @param int $viva
 * @return array ['regularity', 'conduction', 'output', 'viva', 'total']
 */
function evaluate_experiment($regularity, $conduction, $output, $viva) {
    $regularity = max(0, min(5, intval($regularity)));
    $conduction = max(0, min(10, intval($conduction)));
    $output = max(0, min(5, intval($output)));
    $viva = max(0, min(5, intval($viva)));
    
    $total = $regularity + $conduction + $output + $viva;
    
    return [
        'regularity' => $regularity,
        'conduction' => $conduction,
        'output' => $output,
        'viva' => $viva,
        'total' => $total
    ];
}

/**
 * Normalize score average for final term-work marksheets
 *
 * @param float $obtained_marks
 * @param float $out_of_marks
 * @param int $scale_target (e.g., 25 or 50)
 * @return float
 */
function normalize_termwork_marks($obtained_marks, $out_of_marks, $scale_target = 25) {
    if ($out_of_marks <= 0) return 0;
    $percentage = ($obtained_marks / $out_of_marks);
    return round($percentage * $scale_target, 2);
}

/**
 * Format Date nicely for UI
 * @param string $date_str
 * @return string
 */
function format_date($date_str) {
    if (empty($date_str)) return '-';
    return date('d M Y', strtotime($date_str));
}
?>
