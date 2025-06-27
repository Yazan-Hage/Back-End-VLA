<?php
// config.php

// بيانات الاتصال بقاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'vla_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// مفتاح سري لتشفير JWT 
define('JWT_SECRET', 'your_super_secret_key_here');

// مجلد رفع الملفات 
define('UPLOAD_DIR', __DIR__ . '/uploads/');
