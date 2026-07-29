USE practical_assessment_db;

CREATE TABLE IF NOT EXISTS `batch_students` (
  `batch_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  PRIMARY KEY (`batch_id`, `student_id`),
  CONSTRAINT `fk_batch_students_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_batch_students_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `batches` MODIFY `start_roll` VARCHAR(20) NULL;
ALTER TABLE `batches` MODIFY `end_roll` VARCHAR(20) NULL;

-- Migrate existing data (for batch 1 and 2)
INSERT IGNORE INTO batch_students (batch_id, student_id)
SELECT 1, id FROM users WHERE role = 'student' AND student_roll_no >= '1301' AND student_roll_no <= '1328';

INSERT IGNORE INTO batch_students (batch_id, student_id)
SELECT 2, id FROM users WHERE role = 'student' AND student_roll_no >= '1329' AND student_roll_no <= '1358';
