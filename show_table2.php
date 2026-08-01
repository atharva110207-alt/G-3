<?php
require 'config/database.php';
$res = mysqli_query($conn, 'SHOW CREATE TABLE published_marksheets');
if($res) { 
    $row = mysqli_fetch_row($res); 
    echo $row[1]; 
} else { 
    echo mysqli_error($conn); 
}
?>
