<?php
require __DIR__ . '/../db.php';

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS documents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_id INT UNSIGNED NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  doc_type ENUM('id_card','photo','proof_of_address','other') NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_documents_request (request_id),
  CONSTRAINT fk_documents_request
    FOREIGN KEY (request_id) REFERENCES requests(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

$pdo->exec($sql);
echo "Table `documents` created or exists.\n";
