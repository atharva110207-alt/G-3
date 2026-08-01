<?php
require 'config/database.php';
$res = mysqli_query($conn, 'SELECT DISTINCT faculty_id FROM practicals');
while($row = mysqli_fetch_assoc($res)) { echo json_encode($row) . "\n"; }
?>
