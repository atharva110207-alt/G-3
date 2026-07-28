<?php
// Practical Assessment System - Database Connection & Prepared Statements Helper
// Zeal College of Engineering & Research

$host = "localhost";
$username = "root";
$password = "";
$database = "practical_assessment_db";

// Connect to MySQL server
$conn = mysqli_connect($host, $username, $password);

if (!$conn) {
    die("MySQL Connection Failed: " . mysqli_connect_error());
}

// Select database, or create database if not existing
$db_selected = mysqli_select_db($conn, $database);
if (!$db_selected) {
    // Create database automatically if missing
    $create_db_sql = "CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
    if (mysqli_query($conn, $create_db_sql)) {
        mysqli_select_db($conn, $database);
        
        $sql_file = __DIR__ . '/../database.sql';
        if (file_exists($sql_file)) {
            $sql_contents = file_get_contents($sql_file);
            mysqli_multi_query($conn, $sql_contents);
            while (mysqli_next_result($conn)) {;} // Flush multi queries
        }
    } else {
        die("Error selecting or creating database: " . mysqli_error($conn));
    }
}

// Set charset to utf8mb4
mysqli_set_charset($conn, "utf8mb4");

/**
 * Execute a parameterized prepared statement safely using MySQLi.
 *
 * @param mysqli $conn
 * @param string $sql
 * @param string $types
 * @param array $params
 * @return mysqli_stmt|bool
 */
function execute_prepared($conn, $sql, $types = "", $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn) . " SQL: " . $sql);
        return false;
    }
    
    if (!empty($types) && !empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        return $stmt;
    } else {
        error_log("Execute failed: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
}
?>