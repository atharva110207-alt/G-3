<?php
// Practical Assessment System
// AJAX Endpoint for fetching students by class and division
// Zeal College of Engineering & Research

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['admin', 'hod']);

$class = sanitize($_GET['class'] ?? '');
$division = sanitize($_GET['division'] ?? '');

if (empty($class) || empty($division)) {
    echo '<div class="alert alert-warning">Please select both Class and Division.</div>';
    exit();
}

$sql = "SELECT id, full_name, student_roll_no, zprn FROM users WHERE role = 'student' AND class = ? AND division = ? ORDER BY student_roll_no ASC";
$stmt = execute_prepared($conn, $sql, "ss", [$class, $division]);

if ($stmt) {
    $res = mysqli_stmt_get_result($stmt);
    $students = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $students[] = $row;
    }
    mysqli_stmt_close($stmt);
    
    if (empty($students)) {
        echo '<div class="alert alert-info">No students found for this class and division.</div>';
    } else {
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; max-height: 300px; overflow-y: auto; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; background: rgba(255,255,255,0.02);">';
        foreach ($students as $st) {
            $roll = sanitize($st['student_roll_no']);
            $name = sanitize($st['full_name']);
            $id = $st['id'];
            echo "<label class='student-checkbox-item' style='display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 5px;'>";
            echo "<input type='checkbox' name='student_ids[]' value='$id' checked class='form-check-input'>";
            echo "<span style='font-size: 0.85rem;'><strong>$roll</strong> - $name</span>";
            echo "</label>";
        }
        echo '</div>';
        echo '<div class="mt-2 text-end">';
        echo '<button type="button" class="btn btn-sm btn-secondary me-2" onclick="toggleAllStudents(true)">Select All</button>';
        echo '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllStudents(false)">Deselect All</button>';
        echo '</div>';
    }
} else {
    echo '<div class="alert alert-danger">Error fetching students.</div>';
}
?>
