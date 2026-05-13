USE defaultdb;

ALTER TABLE users
  ADD COLUMN phone VARCHAR(40) NULL AFTER email;

CREATE TABLE IF NOT EXISTS sms_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  recipient_phone VARCHAR(40) NOT NULL,
  message TEXT NOT NULL,
  provider_sid VARCHAR(80) NULL,
  status ENUM('sent', 'failed') NOT NULL,
  error_message TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sms_logs_status (status)
);
