<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$res2 = mysqli_query($conn, "SELECT DISTINCT subject_name FROM syllabi");
if ($res2) {
    while ($row = mysqli_fetch_row($res2)) {
        echo $row[0] . "\n";
    }
} else {
    echo "Error: " . mysqli_error($conn);
}
