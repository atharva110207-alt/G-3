<?php
require 'config/database.php';
$res = mysqli_query($conn, 'SELECT * FROM faculty_allocations');
while($row = mysqli_fetch_assoc($res)) { echo json_encode($row) . "\n"; }
?>
