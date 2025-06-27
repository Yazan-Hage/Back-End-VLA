<?php
// /api/auth.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../utils.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    send_json(['error' => 'طريقة غير مسموحة'], 405);
}

$body   = $_POST;             // for form‐data uploads
$action = $body['action'] ?? null;

// ——————————————————————————————————————————
// 1) Register new user (multipart-form-data)
// ——————————————————————————————————————————
if ($action === 'register') {
    // 1.a) Required fields
    $req = [
        'name',
        'email',
        'password',
        'phone',
        'national_number',
        'date_of_birth',
        'blood_type'
    ];
    foreach ($req as $f) {
        if (empty($body[$f])) {
            send_json(['error'=> "Missing field: $f"], 400);
        }
    }

    // 1.b) Validate blood type
    $valid_blood = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
    if (!in_array($body['blood_type'], $valid_blood)) {
        send_json(['error'=>'Invalid blood_type'], 400);
    }

    // 1.c) Validate & hash password
    if (strlen($body['password']) < 6) {
        send_json(['error'=>'Password must be ≥ 6 chars'], 400);
    }
    $pw_hash = password_hash($body['password'], PASSWORD_DEFAULT);

    // 1.d) Handle uploads (front/back of ID)
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
        send_json(['error'=>'Could not create upload dir'], 500);
    }
    function saveFile($field) {
        global $upload_dir;
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            send_json(['error'=>"Upload error: $field"], 400);
        }
        $ext  = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
        $dest = $upload_dir . uniqid($field.'_') . ".$ext";
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
            send_json(['error'=>"Failed to move $field"], 500);
        }
        return "uploads/" . basename($dest);
    }
    $front = saveFile('card_front');
    $back  = saveFile('card_back');

    // 1.e) Insert into DB
    $pdo = get_pdo();
    $sql = "INSERT INTO users
      (name, email, password_hash, phone, national_number, date_of_birth, blood_type, card_front_path, card_back_path)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            $body['name'],
            $body['email'],
            $pw_hash,
            $body['phone'],
            $body['national_number'],
            $body['date_of_birth'],
            $body['blood_type'],
            $front,
            $back,
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            send_json(['error'=>'Email, National Number or Phone already in use'], 409);
        }
        send_json(['error'=>'Database error'], 500);
    }

    // 1.f) Fetch the newly created user
    $newId = $pdo->lastInsertId();
    $stmt2 = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt2->execute([$newId]);
    $user = $stmt2->fetch();

    // 1.g) Generate JWT
    $token = generate_jwt($user);

    // 1.h) Build profile payload
    $profile = [
        'id'               => (int)$user['id'],
        'name'             => $user['name'],
        'email'            => $user['email'],
        'phone'            => $user['phone'],
        'role'             => $user['role'],
        'national_number'  => $user['national_number'],
        'date_of_birth'    => $user['date_of_birth'],
        'blood_type'       => $user['blood_type'],
        'card_front_path'  => $user['card_front_path'],
        'card_back_path'   => $user['card_back_path'],
    ];

    // 1.i) Return token + user exactly like login
    send_json([
        'token' => $token,
        'user'  => $profile
    ], 201);
    exit;
}

// ——————————————————————————————————————————
// 2) Login existing user
// ——————————————————————————————————————————
if ($action === 'login') {
    $email = trim($body['email']   ?? '');
    $pass  =          $body['password'] ?? '';
    if (!$email || !$pass) {
        send_json(['error'=>'جميع الحقول مطلوبة'], 400);
    }

    $pdo  = get_pdo();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($pass, $user['password_hash'])) {
        send_json(['error'=>'بيانات غير صحيحة'], 401);
    }

    $token = generate_jwt($user);

    $profile = [
        'id'               => $user['id'],
        'name'             => $user['name'],
        'email'            => $user['email'],
        'phone'            => $user['phone'],
        'role'             => $user['role'],
        'national_number'  => $user['national_number'],
        'date_of_birth'    => $user['date_of_birth'],
        'blood_type'       => $user['blood_type'],
        'card_front_path'  => $user['card_front_path'],
        'card_back_path'   => $user['card_back_path'],
    ];

    send_json([
        'token' => $token,
        'user'  => $profile
    ]);
    exit;
}

send_json(['error' => 'الإجراء غير مدعوم'], 400);
