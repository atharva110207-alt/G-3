USE practical_assessment_db;
ALTER TABLE users ADD COLUMN reset_otp VARCHAR(6) NULL, ADD COLUMN otp_expires_at DATETIME NULL;
