<?php
require 'config/database.php';

// Check if Somesh has allocations
$res = mysqli_query($conn, "SELECT COUNT(*) as c FROM faculty_allocations WHERE faculty_id = 184");
$row = mysqli_fetch_assoc($res);
if ($row['c'] == 0) {
    // Assign him Engineering Physics for FY Division C
    mysqli_query($conn, "INSERT INTO faculty_allocations (faculty_id, subject_name, class, division, batch_id, academic_year) VALUES (184, 'Engineering Physics', 'FY', 'Division C', 3, '2025-2026')");
    
    // Assign him Microprocessors & Microcontrollers for SY Division A
    mysqli_query($conn, "INSERT INTO faculty_allocations (faculty_id, subject_name, class, division, batch_id, academic_year) VALUES (184, 'Microprocessors & Microcontrollers', 'SY', 'Division A', 10, '2025-2026')");

    // Also update practicals table so he owns the practicals for Engineering Physics (which are currently under ID 180)
    mysqli_query($conn, "UPDATE practicals SET faculty_id = 184 WHERE subject_name = 'Engineering Physics'");
    
    echo "Allocated subjects to Prof. Somesh Naik!";
} else {
    echo "Already allocated.";
}
?>
