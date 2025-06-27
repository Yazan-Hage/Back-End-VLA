<?php
require __DIR__ . '/../db.php';

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS licenses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('new','renewal','replacement') NOT NULL,
  number VARCHAR(50) NOT NULL UNIQUE,
  issue_date DATE NOT NULL,
  expiry_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_licenses_user (user_id),
  CONSTRAINT fk_licenses_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

$pdo->exec($sql);
echo "Table `licenses` created or exists.\n";
