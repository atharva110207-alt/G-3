<?php
require 'config/database.php';
$res = mysqli_query($conn, 'SHOW CREATE TABLE assessment');
$row = mysqli_fetch_row($res);
echo $row[1];
?>
