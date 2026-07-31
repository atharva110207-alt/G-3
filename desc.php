<?php
require 'config/config.php';
require 'config/database.php';
$res = mysqli_query($conn, 'DESCRIBE syllabi');
while ($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
