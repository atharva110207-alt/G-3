<?php
require 'config/database.php';
$res = mysqli_query($conn, 'SELECT id, full_name, role FROM users WHERE id IN (180, 181)');
while($row = mysqli_fetch_assoc($res)) { echo json_encode($row) . "\n"; }
?>
