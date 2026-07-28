-- Practical Assessment System
-- Database Schema & Complete Seed Data Creation Script
-- Institution: ZEAL COLLEGE OF ENGINEERING & RESEARCH
-- Department: Department of Electronics & Computer Engineering

CREATE DATABASE IF NOT EXISTS `practical_assessment_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `practical_assessment_db`;

-- --------------------------------------------------------
-- Drop Tables if Exist (Order respects foreign keys)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `system_settings`;
DROP TABLE IF EXISTS `syllabi`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `assessment`;
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `practicals`;
DROP TABLE IF EXISTS `faculty_allocations`;
DROP TABLE IF EXISTS `batches`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL, -- Stored as Plain Text per requirements specification
  `role` ENUM('admin', 'hod', 'gfm', 'faculty', 'student', 'parent') NOT NULL,
  `student_roll_no` VARCHAR(20) DEFAULT NULL,
  `zprn` VARCHAR(50) DEFAULT NULL,
  `class` ENUM('FY', 'SY', 'TY', 'BY') DEFAULT 'TY',
  `division` VARCHAR(10) DEFAULT 'Division C',
  `phone` VARCHAR(20) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_role` (`role`),
  INDEX `idx_roll` (`student_roll_no`),
  INDEX `idx_class_div` (`class`, `division`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: batches
-- --------------------------------------------------------
CREATE TABLE `batches` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `batch_name` VARCHAR(50) NOT NULL,
  `start_roll` VARCHAR(20) NOT NULL,
  `end_roll` VARCHAR(20) NOT NULL,
  `class` ENUM('FY', 'SY', 'TY', 'BY') NOT NULL DEFAULT 'TY',
  `division` VARCHAR(10) NOT NULL,
  `subject_assigned` VARCHAR(100) DEFAULT NULL,
  `academic_year` VARCHAR(20) NOT NULL DEFAULT '2026-2027',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_batch_div` (`class`, `division`, `batch_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: faculty_allocations
-- --------------------------------------------------------
CREATE TABLE `faculty_allocations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `faculty_id` INT NOT NULL,
  `subject_name` VARCHAR(100) NOT NULL,
  `class` ENUM('FY', 'SY', 'TY', 'BY') NOT NULL DEFAULT 'TY',
  `division` VARCHAR(10) NOT NULL,
  `batch_id` INT DEFAULT NULL,
  `academic_year` VARCHAR(20) NOT NULL DEFAULT '2026-2027',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_alloc_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_alloc_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: practicals
-- --------------------------------------------------------
CREATE TABLE `practicals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `subject_name` VARCHAR(100) NOT NULL,
  `exp_no` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `class` ENUM('FY', 'SY', 'TY', 'BY') NOT NULL DEFAULT 'TY',
  `division` VARCHAR(10) NOT NULL,
  `batch_id` INT NOT NULL,
  `faculty_id` INT NOT NULL,
  `scheduled_date` DATE NOT NULL, -- Plan Date
  `academic_year` VARCHAR(20) NOT NULL DEFAULT '2026-2027',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pract_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pract_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX `idx_exp_subj` (`subject_name`, `exp_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: attendance
-- --------------------------------------------------------
CREATE TABLE `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `practical_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `status` ENUM('Present', 'Absent') NOT NULL DEFAULT 'Present',
  `date_marked` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_att` (`practical_id`, `student_id`),
  CONSTRAINT `fk_att_practical` FOREIGN KEY (`practical_id`) REFERENCES `practicals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_att_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: assessment
-- --------------------------------------------------------
CREATE TABLE `assessment` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `practical_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `faculty_id` INT NOT NULL,
  `regularity_score` INT NOT NULL DEFAULT 0,
  `conduction_score` INT NOT NULL DEFAULT 0,
  `output_score` INT NOT NULL DEFAULT 0,
  `viva_score` INT NOT NULL DEFAULT 0,
  `total_score` INT NOT NULL DEFAULT 0,
  `evaluation_date` DATE NOT NULL,
  `comments` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_assess` (`practical_id`, `student_id`),
  CONSTRAINT `fk_ass_practical` FOREIGN KEY (`practical_id`) REFERENCES `practicals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ass_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ass_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: syllabi
-- --------------------------------------------------------
CREATE TABLE `syllabi` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `class` ENUM('FY', 'SY', 'TY', 'BY') NOT NULL,
  `division` VARCHAR(10) NOT NULL,
  `subject_name` VARCHAR(100) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `uploaded_by` INT NOT NULL,
  `upload_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_syllabi_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: audit_logs
-- --------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `user_role` VARCHAR(50) DEFAULT 'admin',
  `action_performed` VARCHAR(255) NOT NULL,
  `target_module` VARCHAR(100) NOT NULL,
  `IP_address` VARCHAR(45) DEFAULT '127.0.0.1',
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `details` TEXT DEFAULT NULL,
  INDEX `idx_audit_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: system_settings
-- --------------------------------------------------------
CREATE TABLE `system_settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- SEED DATA INSERTS
-- --------------------------------------------------------

-- 1. System Settings Initial State
INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('release_reports_student_view', '1')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- 2. Admin Account (admin@zcoer.edu.in / Admin@123)
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `student_roll_no`, `zprn`, `class`, `division`, `phone`) VALUES
(1, 'System Administrator', 'admin@zcoer.edu.in', 'Admin@123', 'admin', NULL, NULL, 'TY', NULL, '9876543210');

-- 3. HOD Account
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `student_roll_no`, `zprn`, `class`, `division`, `phone`) VALUES
(2, 'Dr. A. B. Deshmukh', 'hod.extc@zcoer.edu.in', 'hod123', 'hod', NULL, NULL, 'TY', 'Division C', '9876543211');

-- 4. Subject Faculty Accounts
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `student_roll_no`, `zprn`, `class`, `division`, `phone`) VALUES
(3, 'Prof. John Smith', 'faculty.smith@zcoer.edu.in', 'faculty123', 'faculty', NULL, NULL, 'TY', 'Division C', '9876543212'),
(4, 'Prof. S. R. Patil', 'faculty.patil@zcoer.edu.in', 'faculty123', 'faculty', NULL, NULL, 'TY', 'Division C', '9876543213');

-- 5. GFM Account
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `student_roll_no`, `zprn`, `class`, `division`, `phone`) VALUES
(5, 'Prof. P. V. Kulkarni', 'gfm.divc@zcoer.edu.in', 'gfm123', 'gfm', NULL, NULL, 'TY', 'Division C', '9876543214');

-- 6. 20 Division C Students (Roll Nos: EC1301 to EC1320, passwords: student123)
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `student_roll_no`, `zprn`, `class`, `division`, `phone`) VALUES
(101, 'Aarav Sharma', 'ec1301@zcoer.edu.in', 'student123', 'student', 'EC1301', 'ZPRN20261301', 'TY', 'Division C', '9900001301'),
(102, 'Ananya Verma', 'ec1302@zcoer.edu.in', 'student123', 'student', 'EC1302', 'ZPRN20261302', 'TY', 'Division C', '9900001302'),
(103, 'Aditya Joshi', 'ec1303@zcoer.edu.in', 'student123', 'student', 'EC1303', 'ZPRN20261303', 'TY', 'Division C', '9900001303'),
(104, 'Diya Patel', 'ec1304@zcoer.edu.in', 'student123', 'student', 'EC1304', 'ZPRN20261304', 'TY', 'Division C', '9900001304'),
(105, 'Ishan Mehta', 'ec1305@zcoer.edu.in', 'student123', 'student', 'EC1305', 'ZPRN20261305', 'TY', 'Division C', '9900001305'),
(106, 'Kavya Nair', 'ec1306@zcoer.edu.in', 'student123', 'student', 'EC1306', 'ZPRN20261306', 'TY', 'Division C', '9900001306'),
(107, 'Krishna Iyer', 'ec1307@zcoer.edu.in', 'student123', 'student', 'EC1307', 'ZPRN20261307', 'TY', 'Division C', '9900001307'),
(108, 'Neha Kulkarni', 'ec1308@zcoer.edu.in', 'student123', 'student', 'EC1308', 'ZPRN20261308', 'TY', 'Division C', '9900001308'),
(109, 'Pranav Shinde', 'ec1309@zcoer.edu.in', 'student123', 'student', 'EC1309', 'ZPRN20261309', 'TY', 'Division C', '9900001309'),
(110, 'Riya Deshmukh', 'ec1310@zcoer.edu.in', 'student123', 'student', 'EC1310', 'ZPRN20261310', 'TY', 'Division C', '9900001310'),
(111, 'Rohan More', 'ec1311@zcoer.edu.in', 'student123', 'student', 'EC1311', 'ZPRN20261311', 'TY', 'Division C', '9900001311'),
(112, 'Saanvi Pawar', 'ec1312@zcoer.edu.in', 'student123', 'student', 'EC1312', 'ZPRN20261312', 'TY', 'Division C', '9900001312'),
(113, 'Samarth Jadhav', 'ec1313@zcoer.edu.in', 'student123', 'student', 'EC1313', 'ZPRN20261313', 'TY', 'Division C', '9900001313'),
(114, 'Shreya Wagh', 'ec1314@zcoer.edu.in', 'student123', 'student', 'EC1314', 'ZPRN20261314', 'TY', 'Division C', '9900001314'),
(115, 'Siddharth Thorat', 'ec1315@zcoer.edu.in', 'student123', 'student', 'EC1315', 'ZPRN20261315', 'TY', 'Division C', '9900001315'),
(116, 'Tanvi Bhosale', 'ec1316@zcoer.edu.in', 'student123', 'student', 'EC1316', 'ZPRN20261316', 'TY', 'Division C', '9900001316'),
(117, 'Utkarsh Gaikwad', 'ec1317@zcoer.edu.in', 'student123', 'student', 'EC1317', 'ZPRN20261317', 'TY', 'Division C', '9900001317'),
(118, 'Vaishnavi Kale', 'ec1318@zcoer.edu.in', 'student123', 'student', 'EC1318', 'ZPRN20261318', 'TY', 'Division C', '9900001318'),
(119, 'Yash Mane', 'ec1319@zcoer.edu.in', 'student123', 'student', 'EC1319', 'ZPRN20261319', 'TY', 'Division C', '9900001319'),
(120, 'Zoya Shaikh', 'ec1320@zcoer.edu.in', 'student123', 'student', 'EC1320', 'ZPRN20261320', 'TY', 'Division C', '9900001320');

-- 7. Parent Accounts (Linked to students via student_roll_no)
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `student_roll_no`, `zprn`, `class`, `division`, `phone`) VALUES
(201, 'Rajesh Sharma (Parent)', 'parent.ec1301@zcoer.edu.in', 'parent123', 'parent', 'EC1301', 'ZPRN20261301', 'TY', 'Division C', '9811111301'),
(202, 'Sanjay Verma (Parent)', 'parent.ec1302@zcoer.edu.in', 'parent123', 'parent', 'EC1302', 'ZPRN20261302', 'TY', 'Division C', '9811111302'),
(203, 'Mahesh Joshi (Parent)', 'parent.ec1303@zcoer.edu.in', 'parent123', 'parent', 'EC1303', 'ZPRN20261303', 'TY', 'Division C', '9811111303'),
(204, 'Vikram Patel (Parent)', 'parent.ec1304@zcoer.edu.in', 'parent123', 'parent', 'EC1304', 'ZPRN20261304', 'TY', 'Division C', '9811111304'),
(205, 'Sunil Mehta (Parent)', 'parent.ec1305@zcoer.edu.in', 'parent123', 'parent', 'EC1305', 'ZPRN20261305', 'TY', 'Division C', '9811111305');

-- 8. Batches (Division C split into C1 & C2)
INSERT INTO `batches` (`id`, `batch_name`, `start_roll`, `end_roll`, `class`, `division`, `subject_assigned`, `academic_year`) VALUES
(1, 'C1', 'EC1301', 'EC1310', 'TY', 'Division C', 'Microprocessors & Microcontrollers', '2026-2027'),
(2, 'C2', 'EC1311', 'EC1320', 'TY', 'Division C', 'Digital Signal Processing', '2026-2027');

-- 9. Subject Faculty Allocations
INSERT INTO `faculty_allocations` (`id`, `faculty_id`, `subject_name`, `class`, `division`, `batch_id`, `academic_year`) VALUES
(1, 3, 'Microprocessors & Microcontrollers', 'TY', 'Division C', 1, '2026-2027'),
(2, 3, 'Microprocessors & Microcontrollers', 'TY', 'Division C', 2, '2026-2027'),
(3, 4, 'Digital Signal Processing', 'TY', 'Division C', 1, '2026-2027'),
(4, 4, 'Digital Signal Processing', 'TY', 'Division C', 2, '2026-2027');

-- 10. Practicals Setup (Plan Date = scheduled_date)
INSERT INTO `practicals` (`id`, `subject_name`, `exp_no`, `title`, `class`, `division`, `batch_id`, `faculty_id`, `scheduled_date`, `academic_year`) VALUES
(1, 'Microprocessors & Microcontrollers', 1, '8086 Assembly Language Programming - Addition & Subtraction', 'TY', 'Division C', 1, 3, '2026-07-10', '2026-2027'),
(2, 'Microprocessors & Microcontrollers', 2, '8086 String Manipulation & Block Transfer Instructions', 'TY', 'Division C', 1, 3, '2026-07-17', '2026-2027'),
(3, 'Microprocessors & Microcontrollers', 3, 'Interfacing 8255 Programmable Peripheral Interface with 8086', 'TY', 'Division C', 1, 3, '2026-07-24', '2026-2027'),
(4, 'Digital Signal Processing', 1, 'DFT and FFT Computation using MATLAB/Python', 'TY', 'Division C', 1, 4, '2026-07-12', '2026-2027'),
(5, 'Digital Signal Processing', 2, 'Design of IIR Butterworth Digital Filter', 'TY', 'Division C', 1, 4, '2026-07-19', '2026-2027');

-- 11. Sample Attendance Records for Practical Exp 1 (Batch C1)
INSERT INTO `attendance` (`practical_id`, `student_id`, `status`, `date_marked`) VALUES
(1, 101, 'Present', '2026-07-10 10:00:00'),
(1, 102, 'Present', '2026-07-10 10:00:00'),
(1, 103, 'Present', '2026-07-10 10:00:00'),
(1, 104, 'Present', '2026-07-10 10:00:00'),
(1, 105, 'Absent',  '2026-07-10 10:00:00'),
(1, 106, 'Present', '2026-07-10 10:00:00'),
(1, 107, 'Present', '2026-07-10 10:00:00'),
(1, 108, 'Present', '2026-07-10 10:00:00'),
(1, 109, 'Present', '2026-07-10 10:00:00'),
(1, 110, 'Present', '2026-07-10 10:00:00');

-- 12. Sample Assessment Records for Practical Exp 1 (Batch C1)
-- Evaluation Criteria: Regularity (5) + Conduction (10) + Output (5) + Viva (5) = Total (25)
INSERT INTO `assessment` (`practical_id`, `student_id`, `faculty_id`, `regularity_score`, `conduction_score`, `output_score`, `viva_score`, `total_score`, `evaluation_date`, `comments`) VALUES
(1, 101, 3, 5, 10, 5, 5, 25, '2026-07-10', 'Excellent conduction and clear understanding.'),
(1, 102, 3, 5, 10, 5, 4, 24, '2026-07-10', 'Good performance, minor viva glitch.'),
(1, 103, 3, 5, 7,  5, 4, 21, '2026-07-10', 'Conduction partially complete on time.'),
(1, 104, 3, 5, 10, 3, 3, 21, '2026-07-10', 'Output required correction.'),
(1, 105, 3, 0, 5,  2, 3, 10, '2026-07-18', 'Absent on scheduled date; performed later.'),
(1, 106, 3, 5, 10, 5, 5, 25, '2026-07-10', 'Flawless output and viva response.'),
(1, 107, 3, 5, 7,  3, 4, 19, '2026-07-10', 'Needs more practice on assembly syntax.'),
(1, 108, 3, 5, 10, 5, 5, 25, '2026-07-10', 'Very good conceptual clarity.'),
(1, 109, 3, 5, 10, 5, 4, 24, '2026-07-10', 'Satisfactory conduction and output.'),
(1, 110, 3, 5, 10, 5, 5, 25, '2026-07-10', 'Well organized code and verification.');

-- 13. Initial System Audit Logs
INSERT INTO `audit_logs` (`user_id`, `user_role`, `action_performed`, `target_module`, `IP_address`, `details`) VALUES
(1, 'admin', 'System Initialization & Database Seeding', 'system', '127.0.0.1', 'Initialized database schema and seeded users, batches, practicals, attendance, and assessment records.');
