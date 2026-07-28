<?php
// Practical Assessment System - Auth Check Middleware & Helpers
// Zeal College of Engineering & Research

require_once __DIR__ . '/auth.php';

/**
 * Validate and verify credentials against database
 *
 * @param mysqli $conn
 * @param string $identity
 * @param string $password
 * @param string $role
 * @return array|bool User data array if verified, false otherwise
 */
function verify_credentials($conn, $identity, $password, $role = '') {
    $identity = trim($identity);
    $password = trim($password);
    $role = trim($role);

    if (empty($identity) || empty($password)) {
        return false;
    }

    $user = null;

    if (!empty($role)) {
        $sql = "SELECT id, full_name, email, password, role, student_roll_no, zprn, class, division FROM users 
                WHERE (email = ? OR student_roll_no = ? OR zprn = ? OR full_name = ?) AND role = ?";
        $stmt = execute_prepared($conn, $sql, "sssss", [$identity, $identity, $identity, $identity, $role]);
        if ($stmt) {
            $res = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);
        }
    }

    if (!$user) {
        $sql = "SELECT id, full_name, email, password, role, student_roll_no, zprn, class, division FROM users 
                WHERE (email = ? OR student_roll_no = ? OR zprn = ? OR full_name = ?)";
        $stmt = execute_prepared($conn, $sql, "ssss", [$identity, $identity, $identity, $identity]);
        if ($stmt) {
            $res = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);
        }
    }

    if ($user && $password === $user['password']) {
        return $user;
    }

    return false;
}
?>
