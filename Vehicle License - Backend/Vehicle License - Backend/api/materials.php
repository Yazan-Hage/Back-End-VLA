<?php
// /api/materials.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../utils.php';

$pdo = get_pdo();
$stmt = $pdo->query("SELECT * FROM learning_materials ORDER BY created_at DESC");
$data = $stmt->fetchAll();
send_json($data);
