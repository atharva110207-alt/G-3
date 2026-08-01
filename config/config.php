<?php
// Set default timezone for the application
date_default_timezone_set('Asia/Kolkata');

// Practical Assessment System - Configuration & Constants
// Zeal College of Engineering & Research - Department of Electronics & Computer Engineering

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Practical Assessment System');
}

if (!defined('COLLEGE_NAME')) {
    define('COLLEGE_NAME', 'ZEAL COLLEGE OF ENGINEERING & RESEARCH');
}

if (!defined('DEPARTMENT_NAME')) {
    define('DEPARTMENT_NAME', 'Department of Electronics & Computer Engineering');
}

if (!defined('DEFAULT_ACADEMIC_YEAR')) {
    define('DEFAULT_ACADEMIC_YEAR', '2026-2027');
}

if (!defined('BASE_URL')) {
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Strip subdirectories if loaded inside a module or admin subfolder
    $script_dir = preg_replace('#/(modules|admin|reports)(/.*)?$#i', '', $script_dir);
    $base_url = rtrim($script_dir, '/') . '/';
    define('BASE_URL', $base_url);
}

// Available Academic Years & Classes
$ACADEMIC_YEARS = ['2026-2027', '2025-2026'];
$CLASSES = ['FY', 'SY', 'TY', 'Final Year'];

// Evaluation Criteria Constants & Descriptions (Total: 25 Marks)
$EVALUATION_CRITERIA = [
    'regularity' => [
        'title' => 'Regularity (Max 5 Marks)',
        'max' => 5,
        'options' => [
            5 => 'Present on scheduled date (5 Marks)',
            0 => 'Absent on scheduled date (0 Marks)'
        ]
    ],
    'conduction' => [
        'title' => 'Practical Conduction (Max 10 Marks)',
        'max' => 10,
        'options' => [
            10 => 'Present & Performed on same day (10 Marks)',
            7  => 'Present & Not Performed (7 Marks)',
            5  => 'Absent on scheduled date & Performed Later (5 Marks)',
            0  => 'Absent & Not Performed (0 Marks)'
        ]
    ],
    'output' => [
        'title' => 'Program / Practical Output (Max 5 Marks)',
        'max' => 5,
        'options' => [
            5 => 'Present & Output Obtained (5 Marks)',
            3 => 'Present & Output Not Obtained (3 Marks)',
            2 => 'Absent & Performed Later (2 Marks)',
            0 => 'Absent & Not Performed (0 Marks)'
        ]
    ],
    'viva' => [
        'title' => 'Viva / Understanding (Max 5 Marks)',
        'max' => 5,
        'options' => [
            5 => 'Evaluated / Checked Same Day (5 Marks)',
            4 => 'Evaluated within 7 Days (4 Marks)',
            3 => 'Evaluated after 7 Days (3 Marks)',
            0 => 'Not Evaluated (0 Marks)'
        ]
    ]
];

/**
 * Helper to display role display label with "Subject Faculty" nomenclature
 */
function get_role_label($role) {
    switch (strtolower($role)) {
        case 'admin':
            return 'System Admin';
        case 'hod':
            return 'HOD';
        case 'gfm':
            return 'GFM';
        case 'class_teacher':
            return 'Class Teacher';
        case 'faculty':
            return 'Subject Faculty';
        case 'student':
            return 'Student';
        case 'parent':
            return 'Parent';
        default:
            return ucfirst($role);
    }
}
?>
