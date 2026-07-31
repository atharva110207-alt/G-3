<?php
require 'config/config.php';
require 'config/database.php';

$tables = [
    'users' => 'class',
    'batches' => 'class',
    'syllabi' => 'class',
    'class_teacher_allocations' => 'class'
];

foreach ($tables as $table => $col) {
    // First, change the ENUM definition to include Final Year
    $sql = "ALTER TABLE `$table` MODIFY `$col` ENUM('FY','SY','TY','BY','Final Year') NULL DEFAULT NULL";
    if (mysqli_query($conn, $sql)) {
        echo "Updated ENUM definition for $table\n";
        // Now update any 'BY' to 'Final Year'
        $update_sql = "UPDATE `$table` SET `$col` = 'Final Year' WHERE `$col` = 'BY'";
        mysqli_query($conn, $update_sql);
        // Finally, remove 'BY' from ENUM
        $final_sql = "ALTER TABLE `$table` MODIFY `$col` ENUM('FY','SY','TY','Final Year') NULL DEFAULT NULL";
        mysqli_query($conn, $final_sql);
        echo "Removed BY and set to Final Year in $table\n";
    } else {
        echo "Error on $table: " . mysqli_error($conn) . "\n";
    }
}

// Add semester to syllabi
$sql_sem = "ALTER TABLE `syllabi` ADD `semester` VARCHAR(20) NULL DEFAULT NULL AFTER `division`";
if (mysqli_query($conn, $sql_sem)) {
    echo "Added semester to syllabi\n";
} else {
    echo "Error adding semester: " . mysqli_error($conn) . "\n";
}
