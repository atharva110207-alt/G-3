<?php
require_once __DIR__ . '/config/database.php';

// 1. Create system_settings table
$sql1 = "CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
if(!mysqli_query($conn, $sql1)) echo "Error: " . mysqli_error($conn) . "\n";

// Insert default 'release_reports' if it doesn't exist
$sql_insert = "INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`) VALUES ('release_reports', '0')";
if(!mysqli_query($conn, $sql_insert)) echo "Error: " . mysqli_error($conn) . "\n";

// 2. Modify practicals table
$res = mysqli_query($conn, "SHOW COLUMNS FROM `practicals` LIKE 'experiment_number'");
if (mysqli_num_rows($res) > 0) {
    if(!mysqli_query($conn, "ALTER TABLE `practicals` CHANGE `experiment_number` `exp_no` INT(11) NOT NULL")) echo "Error: " . mysqli_error($conn) . "\n";
}

$res2 = mysqli_query($conn, "SHOW COLUMNS FROM `practicals` LIKE 'faculty_id'");
if (mysqli_num_rows($res2) == 0) {
    if(!mysqli_query($conn, "ALTER TABLE `practicals` ADD `faculty_id` INT(11) NULL AFTER `batch_id`")) echo "Error: " . mysqli_error($conn) . "\n";
    if(!mysqli_query($conn, "ALTER TABLE `practicals` ADD CONSTRAINT `fk_practicals_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE")) echo "Error: " . mysqli_error($conn) . "\n";
}

// 3. Modify syllabi table
$res3 = mysqli_query($conn, "SHOW COLUMNS FROM `syllabi` LIKE 'division'");
if (mysqli_num_rows($res3) == 0) {
    if(!mysqli_query($conn, "ALTER TABLE `syllabi` ADD `division` VARCHAR(50) NULL AFTER `class`")) echo "Error: " . mysqli_error($conn) . "\n";
}
$res4 = mysqli_query($conn, "SHOW COLUMNS FROM `syllabi` LIKE 'semester'");
if (mysqli_num_rows($res4) == 0) {
    if(!mysqli_query($conn, "ALTER TABLE `syllabi` ADD `semester` VARCHAR(50) NULL AFTER `division`")) echo "Error: " . mysqli_error($conn) . "\n";
}

echo "Database fixed successfully!";
?>
