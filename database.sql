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

-- 6. FY Division C Students (56 Enrolled Students - Academic Year 2025-26)
DELETE FROM users WHERE division = 'Division C' AND role = 'student';

INSERT INTO `users` (`full_name`, `email`, `password`, `role`, `student_roll_no`, `zprn`, `class`, `division`, `phone`) VALUES
('AMBHORE PRAFUL RAJU', 'prafulambhore2580@gmail.com', 'student123', 'student', '1301', '125UEC1160', 'FY', 'Division C', '9371215742'),
('AMBURE KRISHNA GANESHRAO', 'krishambure07@gmail.com', 'student123', 'student', '1302', '125UEC1102', 'FY', 'Division C', '7843001885'),
('ATHARVA KISHOR CHINCHOLE', 'atharva110207@gmail.com', 'student123', 'student', '1303', '125UEC1042', 'FY', 'Division C', '8983677359'),
('BEDRE TUKARAM PRADIP', 'tukarambedre3@gmail.com', 'student123', 'student', '1304', '125UEC1108', 'FY', 'Division C', '8390710440'),
('BHAGAT SNEHAL PRAWESH', 'prajwalbhagat890@gmail.com', 'student123', 'student', '1305', '125UEC1147', 'FY', 'Division C', '7822944688'),
('CHAVHAN YASH AMARSING', 'ychavhan858@gmail.com', 'student123', 'student', '1306', '125UEC1105', 'FY', 'Division C', '9579961660'),
('CHEVALE OMKAR RAJKUMAR', 'omkarchewale@gmail.com', 'student123', 'student', '1307', '125UEC1063', 'FY', 'Division C', '8605783905'),
('CHOPADE PAVAN DNYANESHWAR', 'pawanchopade35@gmail.com', 'student123', 'student', '1308', '125UEC1072', 'FY', 'Division C', '9860181354'),
('DESHMUKH VEDANTI KISHOR', 'vedantideshmukh86@gmail.com', 'student123', 'student', '1309', '125UEC1038', 'FY', 'Division C', '9370885767'),
('GEETHA SANGVE', 'geethasangve@gmail.com', 'student123', 'student', '1310', '125UEC1166', 'FY', 'Division C', '8374015602'),
('GULHANE KRUSHNA PRASHANT', 'krushnagulhane2007@gmail.com', 'student123', 'student', '1311', '125UEC1034', 'FY', 'Division C', '8806413377'),
('INGOLE GOVIND RAJARAM', 'govindingole34547@gmail.com', 'student123', 'student', '1312', '125UEC1015', 'FY', 'Division C', '9588499961'),
('ISHWAR SHATRUGHNA UNHALE', 'ishwarunhale38@gmail.com', 'student123', 'student', '1313', '125UEC1027', 'FY', 'Division C', '9511217554'),
('JADHAV MAULI VYANKATRAO', 'shubhangijadhav66035@gmail.com', 'student123', 'student', '1314', '125UEC1131', 'FY', 'Division C', '8626090683'),
('KACHARE SEJAL SANTOSH', 'sejalkachare2007@gmail.com', 'student123', 'student', '1315', '125UEC1179', 'FY', 'Division C', '8767443283'),
('KACHE VEDANT RAJU', 'vedantkache@gmail.com', 'student123', 'student', '1316', '125UEC1086', 'FY', 'Division C', '8999952105'),
('KALSAI PRAJKTA ANIL', 'kalsaiprajkta@gmail.com', 'student123', 'student', '1317', '125UEC1040', 'FY', 'Division C', '8055574322'),
('KARANDE SAHIL SANJAY', 'sahilkarande1011@gmail.com', 'student123', 'student', '1318', '125UEC1090', 'FY', 'Division C', '8806890629'),
('KARIKANTE SANDESH CHANDRAKANT', 'sandeshkarikante838@gmail.com', 'student123', 'student', '1319', '125UEC1123', 'FY', 'Division C', '8263877670'),
('KARTIK RATNAKAR SHINDE', 'shindekartik269@gmail.com', 'student123', 'student', '1320', '125UEC1149', 'FY', 'Division C', '9922066212'),
('KASTE RUTUJA NAMDEV', 'namdevkaste123@gmail.com', 'student123', 'student', '1321', '125UEC1073', 'FY', 'Division C', '9922438704'),
('KHAMKAR DIPTI DATTA', 'khamkardipti6@gmail.com', 'student123', 'student', '1322', '125UEC1004', 'FY', 'Division C', '9420930725'),
('KHANDAGALE SHRAVANI SHARAD', 'shravanikhandagale2007@gmail.com', 'student123', 'student', '1323', '125UEC1161', 'FY', 'Division C', '7058979032'),
('KHUSHI RAJESH KET', 'khushiket18@gmail.com', 'student123', 'student', '1326', '125UEC1076', 'FY', 'Division C', '8080191278'),
('KOKARE SAKSHI DIPAK', 'dkokare241@gmail.com', 'student123', 'student', '1327', '125UEC1051', 'FY', 'Division C', '9730999239'),
('KOKATE VIJAY DADASAHEB', 'vijaykokate7776@gmail.com', 'student123', 'student', '1328', '125UEC1155', 'FY', 'Division C', '7776856263'),
('MANE BHAKTI BALWANT', 'manebhakti50@gmail.com', 'student123', 'student', '1329', '125UEC1024', 'FY', 'Division C', '9370476500'),
('MANMATH SHARAD PARANKAR', 'manmathparankar@gmail.com', 'student123', 'student', '1330', '125UEC1188', 'FY', 'Division C', '9860509449'),
('MATAL MAYURI RAVINDRA', 'mayurimatal1307@gmail.com', 'student123', 'student', '1331', '125UEC1171', 'FY', 'Division C', '7276962851'),
('MATE PALLAV PRASHANT', 'pallavmate@gmail.com', 'student123', 'student', '1332', '125UEC1100', 'FY', 'Division C', '8888835378'),
('MATHWALE PRATHMESH SHIVKUMAR', 'prathmeshmathwale@gmail.com', 'student123', 'student', '1333', '125UEC1046', 'FY', 'Division C', '9730910989'),
('MITKARI OM VASANT', 'mitkariom426@gmail.com', 'student123', 'student', '1334', '125UEC1128', 'FY', 'Division C', '7559177972'),
('NALAWADE ADITYA JALINDAR', 'nalawadeaditya206@gmail.com', 'student123', 'student', '1335', '125UEC1003', 'FY', 'Division C', '8468825215'),
('ONKAR VITTHAL DORKE', 'sarthakdorke@gmail.com', 'student123', 'student', '1336', '125UEC1075', 'FY', 'Division C', '7972540544'),
('PALHADE SHREYA SANTOSH', 'santoshpalhade19@gmail.com', 'student123', 'student', '1337', '125UEC1020', 'FY', 'Division C', '9325273198'),
('PANGHATE PANKAJ SANTOSH', 'pankajpanghate49@gmail.com', 'student123', 'student', '1338', '125UEC1061', 'FY', 'Division C', '9021040245'),
('PATARE ANUSHREE RAMESH', 'anushreepatare18@gmail.com', 'student123', 'student', '1339', '125UEC1099', 'FY', 'Division C', '9561822802'),
('PAWAR SARVESH SHANTARAM', 'sarveshpawar818@gmail.com', 'student123', 'student', '1340', '125UEC1142', 'FY', 'Division C', '8446290672'),
('PAWAR YOGESH ANKUSH', 'py8149972705@gmail.com', 'student123', 'student', '1341', '125UEC1036', 'FY', 'Division C', '8149972705'),
('PRAJAPATI RAVITA DIPAK PRASAD', 'info.dipak90@gmail.com', 'student123', 'student', '1342', '125UEC1006', 'FY', 'Division C', '7058349565'),
('PRATIKSHA BHAGWAN DOIPHODE', 'pratikshadoiphode010@gmail.com', 'student123', 'student', '1343', '125UEC1023', 'FY', 'Division C', '7498130477'),
('RENUSE SWARALI PRAKASH', 'mrunalrenuse25@gmail.com', 'student123', 'student', '1344', '125UEC1148', 'FY', 'Division C', '9923609272'),
('ROKADE MAYUR MANOJ', 'mayurrokade407@gmail.com', 'student123', 'student', '1345', '125UEC1030', 'FY', 'Division C', '9405212259'),
('SARVALE AJINKYA MARUTI', 'ajinkyasarvale6@gmail.com', 'student123', 'student', '1346', '125UEC1146', 'FY', 'Division C', '7030658009'),
('SARVARI VINAYAK MHETRE', 'vinayak9404@gmail.com', 'student123', 'student', '1347', '125UEC1116', 'FY', 'Division C', '8446802394'),
('SAWALE OMKAR MARUTI', 'savaleomkar7@gmail.com', 'student123', 'student', '1348', '125UEC1033', 'FY', 'Division C', '9209258881'),
('SHELKE PARNIKA ARVIND', 'parnikashelke@gmail.com', 'student123', 'student', '1349', '125UEC1167', 'FY', 'Division C', '8591240458'),
('SHINDE SANIKA PARASHARAM', 'parsharamshinde60@gmail.com', 'student123', 'student', '1350', '125UEC1022', 'FY', 'Division C', '8530687141'),
('SNEHA JHA', 'snehajha004@gmail.com', 'student123', 'student', '1351', '125UEC1170', 'FY', 'Division C', '7979838157'),
('SONAWNE RAJWARDHAN GOVARDHAN', 'aonawanemanisha@gmail.com', 'student123', 'student', '1352', '125UEC1101', 'FY', 'Division C', '9021115743'),
('SONTAKKE SANKET DHANRAJ', 'sanketsontakke2317@gmail.com', 'student123', 'student', '1353', '125UEC1066', 'FY', 'Division C', '9763271053'),
('SURYAWANSHI ANUSHKA SURESH', 'vimalsuryawanshi2480@gmail.com', 'student123', 'student', '1354', '125UEC1118', 'FY', 'Division C', '9850901255'),
('THORAT GAURI SATISH', 'gaurithorat7512@gmail.com', 'student123', 'student', '1355', '125UEC1174', 'FY', 'Division C', '7387937512'),
('WADKAR SIDDHI BALASAHEB', 'bswadkar@gmail.com', 'student123', 'student', '1356', '125UEC1130', 'FY', 'Division C', '9881371509'),
('WASU HARSHAD NARAYAN', 'wasuharshad@gmail.com', 'student123', 'student', '1357', '125UEC1083', 'FY', 'Division C', '9021510646'),
('WAVHALE HARSHDEEP DHARMARAJ', 'harshwavhale293@gmail.com', 'student123', 'student', '1358', '125UEC1062', 'FY', 'Division C', '9822424834');

-- 7. Parent Accounts (Linked to students via student_roll_no)
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `student_roll_no`, `zprn`, `class`, `division`, `phone`) VALUES
(201, 'Rajesh Sharma (Parent)', 'parent.1301@zcoer.edu.in', 'parent123', 'parent', '1301', '125UEC1160', 'FY', 'Division C', '9811111301'),
(202, 'Sanjay Verma (Parent)', 'parent.1302@zcoer.edu.in', 'parent123', 'parent', '1302', '125UEC1102', 'FY', 'Division C', '9811111302'),
(203, 'Kishor Chinchole (Parent)', 'parent.1303@zcoer.edu.in', 'parent123', 'parent', '1303', '125UEC1042', 'FY', 'Division C', '9811111303'),
(204, 'Pradip Bedre (Parent)', 'parent.1304@zcoer.edu.in', 'parent123', 'parent', '1304', '125UEC1108', 'FY', 'Division C', '9811111304'),
(205, 'Prawesh Bhagat (Parent)', 'parent.1305@zcoer.edu.in', 'parent123', 'parent', '1305', '125UEC1147', 'FY', 'Division C', '9811111305');

-- 8. Batches (Division C split into C1 & C2)
INSERT INTO `batches` (`id`, `batch_name`, `start_roll`, `end_roll`, `class`, `division`, `subject_assigned`, `academic_year`) VALUES
(1, 'C1', '1301', '1328', 'FY', 'Division C', 'Microprocessors & Microcontrollers', '2025-2026'),
(2, 'C2', '1329', '1358', 'FY', 'Division C', 'Digital Signal Processing', '2025-2026');

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
