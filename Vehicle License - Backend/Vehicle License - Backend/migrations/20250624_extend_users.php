<?php
// migrations/20250624_extend_users.php
require __DIR__ . '/../db.php';

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255)                NOT NULL,
  email VARCHAR(150)               NOT NULL UNIQUE,
  password_hash VARCHAR(255)       NOT NULL,
  phone VARCHAR(20)                NULL,
  role ENUM('user','admin')        NOT NULL DEFAULT 'user',
  national_number VARCHAR(50)      UNIQUE,
  date_of_birth DATE               NULL,
  blood_type ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NULL,
  card_front_path VARCHAR(255)     NULL,
  card_back_path VARCHAR(255)      NULL,
  created_at TIMESTAMP             DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP             DEFAULT CURRENT_TIMESTAMP 
    ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS national_number VARCHAR(50) UNIQUE,
  ADD COLUMN IF NOT EXISTS date_of_birth DATE,
  ADD COLUMN IF NOT EXISTS blood_type ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-'),
  ADD COLUMN IF NOT EXISTS card_front_path VARCHAR(255),
  ADD COLUMN IF NOT EXISTS card_back_path VARCHAR(255);
SQL;

$pdo->exec($sql);
echo "Table `users` created or updated with profile fields.\n";
