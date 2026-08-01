<?php
require 'config/database.php';
$res = mysqli_query($conn, 'SELECT id, full_name, class, division FROM users WHERE role = "student"');
while($row = mysqli_fetch_assoc($res)) { echo $row['full_name'] . ' - Class: ' . $row['class'] . ' - Div: ' . $row['division'] . "\n"; }
?>
