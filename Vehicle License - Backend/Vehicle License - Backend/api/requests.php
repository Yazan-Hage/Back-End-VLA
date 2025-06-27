<?php
// /api/requests.php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../utils.php';

$user   = authenticate();    // Must return ['sub'=>user_id, 'role'=>...] from your JWT
$pdo    = get_pdo();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // إنشاء طلب جديد
    $body = json_decode(file_get_contents('php://input'), true);
    $type = $body['license_type'] ?? '';
    if (!in_array($type, ['new','renewal','replacement'])) {
        send_json(['error' => 'نوع الطلب غير صالح'], 400);
    }
    $stmt = $pdo->prepare("
        INSERT INTO requests (user_id, license_type)
        VALUES (?, ?)
    ");
    $stmt->execute([$user['sub'], $type]);

    send_json([
        'message'    => 'تم إنشاء الطلب.',
        'request_id' => (int)$pdo->lastInsertId()
    ], 201);
    exit;
}

if ($method === 'GET') {
    // جلب الطلبات
    // If admin, fetch all; else only this user
    $isAdmin = isset($user['role']) && $user['role'] === 'admin';

    if ($isAdmin) {
        $sql = <<<SQL
SELECT
  r.id, r.license_type, r.status, r.created_at, r.updated_at,
  u.id   AS user_id,
  u.name, u.email, u.phone, u.national_number, u.date_of_birth,
  u.blood_type, u.card_front_path, u.card_back_path
FROM requests r
JOIN users    u ON u.id = r.user_id
ORDER BY r.created_at DESC
SQL;
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $sql = <<<SQL
SELECT
  r.id, r.license_type, r.status, r.created_at, r.updated_at,
  u.id   AS user_id,
  u.name, u.email, u.phone, u.national_number, u.date_of_birth,
  u.blood_type, u.card_front_path, u.card_back_path
FROM requests r
JOIN users    u ON u.id = r.user_id
WHERE r.user_id = ?
ORDER BY r.created_at DESC
SQL;
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['sub']]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    send_json($data);
    exit;
}

send_json(['error' => 'طريقة غير مسموحة'], 405);
