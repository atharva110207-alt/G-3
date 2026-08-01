<?php
require 'config/database.php';
mysqli_query($conn, "ALTER TABLE assessment MODIFY evaluated_by int(11) NULL");
echo "Constraint fixed!";
?>
