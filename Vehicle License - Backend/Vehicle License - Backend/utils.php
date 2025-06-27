<?php
// utils.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// دالة لإرسال استجابة JSON
function send_json($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// دالة لإنشاء واسترجاع اتصال PDO
function get_pdo() {
    global $pdo;
    return $pdo;
}

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function generate_jwt($user) {
    $payload = [
        'iat' => time(),
        'exp' => time() + 3600 * 24, // صلاحية 24 ساعة
        'sub' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role']
    ];
    return JWT::encode($payload, JWT_SECRET, 'HS256');
}

function authenticate() {
    // Pull in all the request headers
    $raw = getallheaders();
    // Lowercase the keys for consistent lookup
    $headers = [];
    foreach ($raw as $key => $value) {
        $headers[strtolower($key)] = $value;
    }

    if (empty($headers['authorization'])) {
        send_json(['error' => 'Authorization header missing'], 401);
    }

    // Expect format "Bearer <token>"
    if (!preg_match('/Bearer\s+(\S+)/i', $headers['authorization'], $m)) {
        send_json(['error' => 'Invalid Authorization header format'], 401);
    }

    $token = $m[1];
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
        return (array)$decoded;
    } catch (Exception $e) {
        send_json(['error' => 'Invalid token: ' . $e->getMessage()], 401);
    }
}

// دالة لحفظ ملف مرفوع
function handle_upload($file_field) {
    if (!isset($_FILES[$file_field]) || $_FILES[$file_field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $tmp  = $_FILES[$file_field]['tmp_name'];
    $name = basename($_FILES[$file_field]['name']);
    $path = UPLOAD_DIR . time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $name);
    if (!move_uploaded_file($tmp, $path)) {
        return null;
    }
    return $path;
}
