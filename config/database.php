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

// Ensure database selection
if (!mysqli_select_db($conn, $database)) {
    // Create database automatically if missing
    $create_db_sql = "CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
    if (mysqli_query($conn, $create_db_sql)) {
        mysqli_select_db($conn, $database);
        
        $sql_file = __DIR__ . '/../database.sql';
        if (file_exists($sql_file)) {
            $sql_contents = file_get_contents($sql_file);
            if (mysqli_multi_query($conn, $sql_contents)) {
                do {
                    if ($result = mysqli_store_result($conn)) {
                        mysqli_free_result($result);
                    }
                } while (mysqli_more_results($conn) && mysqli_next_result($conn));
            }
        }
    } else {
        die("Error selecting or creating database: " . mysqli_error($conn));
    }
} else {
    // Verify if schema is up to date (e.g. zprn column in users table)
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    $check_col = $check_table && mysqli_num_rows($check_table) > 0 ? mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'zprn'") : false;
    if (!$check_col || mysqli_num_rows($check_col) == 0) {
        $sql_file = __DIR__ . '/../database.sql';
        if (file_exists($sql_file)) {
            $sql_contents = file_get_contents($sql_file);
            if (mysqli_multi_query($conn, $sql_contents)) {
                do {
                    if ($result = mysqli_store_result($conn)) {
                        mysqli_free_result($result);
                    }
                } while (mysqli_more_results($conn) && mysqli_next_result($conn));
            }
        }
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
 * @return mysqli_stmt|bool Returns executed statement or false on failure
 */
function execute_prepared($conn, $sql, $types = "", $params = []) {
    if (!$conn) {
        error_log("Database connection object is invalid or null.");
        return false;
    }

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($conn) . " | SQL: " . $sql);
        return false;
    }
    
    if (!empty($types) && !empty($params)) {
        // Ensure parameters are passed by reference for bind_param compatibility
        $bind_params = [];
        $bind_params[] = &$types;
        foreach ($params as $key => $value) {
            $bind_params[] = &$params[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bind_params));
    }
    
    if (mysqli_stmt_execute($stmt)) {
        return $stmt;
    } else {
        error_log("Execute failed: " . mysqli_stmt_error($stmt) . " | SQL: " . $sql);
        mysqli_stmt_close($stmt);
        return false;
    }
}
?>