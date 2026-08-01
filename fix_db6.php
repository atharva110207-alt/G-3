<?php
require 'config/database.php';
$res = mysqli_query($conn, "SHOW COLUMNS FROM `published_marksheets` LIKE 'subject_name'");
if (mysqli_num_rows($res) == 0) {
    if(!mysqli_query($conn, "ALTER TABLE `published_marksheets` ADD `subject_name` varchar(100) NOT NULL")) echo "Error: " . mysqli_error($conn) . "\n";
}
echo "subject_name added to published_marksheets!";
?>
