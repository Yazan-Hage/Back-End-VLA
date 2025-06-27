<?php
// /api/payments.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../utils.php';

$user = authenticate();
$pdo  = get_pdo();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // بدء الدفع
    $body = json_decode(file_get_contents('php://input'), true);
    $reqId = $body['request_id'] ?? null;
    $amount = $body['amount'] ?? null;
    if (!$reqId || !$amount) {
        send_json(['error' => 'المعطيات غير كاملة'], 400);
    }
    $stmt = $pdo->prepare("INSERT INTO payments (request_id, amount) VALUES (?, ?)");
    $stmt->execute([$reqId, $amount]);
    // هنا يمكنك دمج بوابة دفع حقيقية
    send_json(['message' => 'تم إنشاء عملية الدفع', 'payment_id' => $pdo->lastInsertId()]);
}

if ($method === 'GET' && isset($_GET['payment_id'])) {
    // استعلام حالة الدفع
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ?");
    $stmt->execute([$_GET['payment_id']]);
    $payment = $stmt->fetch();
    send_json($payment ?: ['error' => 'غير موجود'], $payment ? 200 : 404);
}

send_json(['error' => 'طريقة غير مسموحة'], 405);
