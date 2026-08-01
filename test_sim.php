<?php
require 'config/database.php';
// find user id of somesh naik
$res = mysqli_query($conn, "SELECT id, full_name, role FROM users WHERE full_name LIKE '%Somesh Naik%'");
$user = mysqli_fetch_assoc($res);
if (!$user) { echo "User not found\n"; exit; }
echo "User: " . $user['full_name'] . " (ID: " . $user['id'] . ")\n";

$subj_sql = "SELECT DISTINCT fa.subject_name, s.semester FROM faculty_allocations fa LEFT JOIN syllabi s ON fa.subject_name = s.subject_name WHERE fa.faculty_id = ? ORDER BY s.semester ASC, fa.subject_name ASC";
$subj_stmt = mysqli_prepare($conn, $subj_sql);
mysqli_stmt_bind_param($subj_stmt, "i", $user['id']);
mysqli_stmt_execute($subj_stmt);
$subj_res = mysqli_stmt_get_result($subj_stmt);
$subject_options = [];
while ($r = mysqli_fetch_assoc($subj_res)) {
    $sem = $r['semester'] ?: 'Other';
    $subject_options[$sem][] = $r['subject_name'];
}
echo "Subjects: \n";
print_r($subject_options);
?>
