<?php
// Export Consolidated Marksheet to Excel (CSV Stream)

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

$division = sanitize($_GET['division'] ?? 'Division C');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=TermWork_Marksheet_' . urlencode($division) . '_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, ['Sr No', 'Roll Number', 'Student Name', 'Division', 'Experiments Checked', 'Sum Obtained', 'Average (out of 25)', 'Term-Work Score (out of 50)']);

$sql = "SELECT u.student_roll_no, u.full_name, u.division,
        COUNT(ass.id) as exp_evaluated,
        SUM(ass.total_score) as sum_obtained,
        AVG(ass.total_score) as avg_score_25
        FROM users u 
        LEFT JOIN assessment ass ON ass.student_id = u.id
        WHERE u.role = 'student' AND u.division = ?
        GROUP BY u.id
        ORDER BY u.student_roll_no ASC";

$stmt = execute_prepared($conn, $sql, "s", [$division]);
if ($stmt) {
    $res = mysqli_stmt_get_result($stmt);
    $sr = 1;
    while ($row = mysqli_fetch_assoc($res)) {
        $avg_25 = $row['avg_score_25'] !== null ? round($row['avg_score_25'], 2) : 0;
        $norm_50 = round($avg_25 * 2, 2);

        fputcsv($output, [
            $sr++,
            $row['student_roll_no'],
            $row['full_name'],
            $row['division'],
            $row['exp_evaluated'],
            $row['sum_obtained'] ?? 0,
            $avg_25,
            $norm_50
        ]);
    }
    mysqli_stmt_close($stmt);
}

fclose($output);
exit();
?>
