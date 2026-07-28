<?php
// Practical Assessment & Laboratory Performance Management System
// Core Utility & Evaluation Engine Functions

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
 * @param string $action
 * @param string $target_table
 * @param string $details
 * @return bool
 */
function log_audit($conn, $user_id, $action, $target_table, $details = '') {
    $sql = "INSERT INTO audit_logs (user_id, action_performed, target_table, details) VALUES (?, ?, ?, ?)";
    $stmt = execute_prepared($conn, $sql, "isss", [$user_id, $action, $target_table, $details]);
    if ($stmt) {
        mysqli_stmt_close($stmt);
        return true;
    }
    return false;
}

/**
 * AUTOMATED EVALUATION ENGINE (Max 25 Marks per Experiment)
 * Calculates scores for all 4 criteria based on conduction & viva criteria.
 * 
 * 1. Regularity (Max 5 Marks):
 *    - Present on scheduled date = 5
 *    - Absent = 0
 * 2. Practical Conduction (Max 10 Marks):
 *    - Present & Performed on same day = 10
 *    - Present & Not Performed = 7
 *    - Absent on scheduled date & Performed Later = 5
 *    - Absent & Not Performed = 0
 * 3. Program / Practical Output (Max 5 Marks):
 *    - Present & Output Obtained = 5
 *    - Present & Output Not Obtained = 3
 *    - Absent & Performed Later = 2
 *    - Absent & Not Performed = 0
 * 4. Viva / Understanding (Max 5 Marks):
 *    - Evaluated / Checked Same Day = 5
 *    - Evaluated within 7 Days = 4
 *    - Evaluated after 7 Days = 3
 *    - Not Evaluated = 0
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
 * Automated Batch Creation Tool:
 * Auto-generates batches for a division based on roll number ranges.
 * E.g., Division C, Roll EC1301 to EC1320, Batch Size 10 -> C1 (EC1301-EC1310), C2 (EC1311-EC1320)
 *
 * @param mysqli $conn
 * @param string $division
 * @param string $prefix (e.g. C)
 * @param int $start_num (e.g. 1301)
 * @param int $end_num (e.g. 1320)
 * @param int $batch_size (e.g. 10 or 20)
 * @param string $roll_prefix (e.g. EC)
 * @param string $academic_year
 * @return int Number of batches created
 */
function auto_generate_batches($conn, $division, $prefix, $start_num, $end_num, $batch_size, $roll_prefix, $academic_year) {
    $batches_created = 0;
    $current_start = $start_num;
    $batch_index = 1;
    
    while ($current_start <= $end_num) {
        $current_end = min($current_start + $batch_size - 1, $end_num);
        
        $b_name = $prefix . $batch_index;
        $start_roll = $roll_prefix . $current_start;
        $end_roll = $roll_prefix . $current_end;
        
        $sql = "INSERT INTO batches (batch_name, start_roll, end_roll, division, academic_year) VALUES (?, ?, ?, ?, ?)";
        $stmt = execute_prepared($conn, $sql, "sssss", [$b_name, $start_roll, $end_roll, $division, $academic_year]);
        
        if ($stmt) {
            mysqli_stmt_close($stmt);
            $batches_created++;
        }
        
        $current_start = $current_end + 1;
        $batch_index++;
    }
    
    return $batches_created;
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
