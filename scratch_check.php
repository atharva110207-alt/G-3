<?php
require_once __DIR__ . '/config/database.php';
$res = mysqli_query($conn, "SELECT role, COUNT(*) as cnt FROM users GROUP BY role");
while ($r = mysqli_fetch_assoc($res)) {
    echo $r['role'] . ": " . $r['cnt'] . "\n";
}
$b = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM batches"))['c'];
$p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM practicals"))['c'];
$a = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM assessment"))['c'];
echo "Batches: $b\nPracticals: $p\nAssessment Records: $a\n";
?>
