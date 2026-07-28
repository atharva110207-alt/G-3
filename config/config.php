<?php
// Practical Assessment & Laboratory Performance Management System
// Global Configuration & Constants

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Practical Assessment & Lab Performance Management System');
}

if (!defined('COLLEGE_NAME')) {
    define('COLLEGE_NAME', 'Zalawad College of Engineering & Research (ZCOER)');
}

if (!defined('ACADEMIC_YEAR')) {
    define('ACADEMIC_YEAR', '2025-2026');
}

if (!defined('BASE_URL')) {
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Strip subdirectories if loaded inside a module or admin subfolder
    $script_dir = preg_replace('#/(modules|admin|reports)(/.*)?$#i', '', $script_dir);
    $base_url = rtrim($script_dir, '/') . '/';
    define('BASE_URL', $base_url);
}

// Evaluation Criteria Constants & Descriptions
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
?>
