<?php
// /api/notifications.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../utils.php';

$user = authenticate();
$pdo  = get_pdo();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // جلب الإشعارات
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['sub']]);
    send_json($stmt->fetchAll());
}

if ($method === 'PUT') {
    // تعليم كمقروء
    $body = json_decode(file_get_contents('php://input'), true);
    $nid = $body['notification_id'] ?? null;
    if (!$nid) {
        send_json(['error' => 'ID مفقود'], 400);
    }
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$nid, $user['sub']]);
    send_json(['message' => 'تم التمييز كمقروء']);
}

send_json(['error' => 'طريقة غير مسموحة'], 405);
