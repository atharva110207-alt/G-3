<?php
require 'config/database.php';
mysqli_query($conn, "DELETE FROM faculty_allocations WHERE subject_name = 'Microprocessors & Microcontrollers'");
echo "Deleted";
?>
