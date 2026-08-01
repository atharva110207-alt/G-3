<?php
require 'config/database.php';
$res = mysqli_query($conn, 'SELECT role FROM users WHERE id = 184');
$row = mysqli_fetch_assoc($res);
echo "Role: " . $row['role'] . "\n";
?>
