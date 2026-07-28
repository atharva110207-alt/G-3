<?php
// Database & System Health Check Endpoint

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';

if ($conn && mysqli_ping($conn)) {
    echo "<h1>Database Connected Successfully!</h1>";
    echo "<p>Database Name: <strong>" . $database . "</strong></p>";
    echo "<p>System: <strong>" . APP_NAME . "</strong></p>";
    echo "<p><a href='index.php'>Go to System Login</a></p>";
} else {
    echo "<h1>Database Connection Failed</h1>";
}
?>