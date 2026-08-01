<?php
require 'config/database.php';
$res = mysqli_query($conn, 'SHOW COLUMNS FROM practicals');
while($row = mysqli_fetch_assoc($res)) { echo $row['Field'] . "\n"; }
?>
