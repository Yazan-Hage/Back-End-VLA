<?php
// jwt_test.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

$user = [
    'id' => 1,
    'email' => 'test@example.com',
    'role' => 'user'
];

// توليد JWT
$payload = [
    'iat' => time(),
    'exp' => time() + 3600,
    'sub' => $user['id'],
    'email' => $user['email'],
    'role' => $user['role']
];

$token = JWT::encode($payload, JWT_SECRET, 'HS256');

echo "Generated JWT:\n\n$token\n\n";


try {
    $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
    echo "\nDecoded JWT:\n";
    print_r($decoded);
} catch (Exception $e) {
    echo "Token invalid: " . $e->getMessage();
}
