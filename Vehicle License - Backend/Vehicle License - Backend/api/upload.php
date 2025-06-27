<?php
// /api/upload.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['error' => 'طريقة غير مسموحة'], 405);
}

$paths = [];
foreach ($_FILES as $field => $info) {
    $path = handle_upload($field);
    if ($path) {
        $paths[$field] = basename($path);
    }
}

if (empty($paths)) {
    send_json(['error' => 'لم يتم رفع أي ملف'], 400);
}

send_json(['files' => $paths]);
