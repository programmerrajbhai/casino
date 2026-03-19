<?php
// সেশন আগে থেকে স্টার্ট করা থাকলে যেন এরর না দেয়, তার বাইপাস
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'addabaji_50taka');
define('DB_USER', 'root');
define('DB_PASS', '');

// API Credentials
define('API_AGENCY_UID', '24c0f3e3c178a0d986fdc89468304ed2');
define('API_AES_KEY', '6b4ea207483062b7404fe3840261ac50');
// লিংকের শেষে ভুল করে স্ল্যাশ (/) পড়ে গেলে যেন API ব্লক না করে তার বাইপাস rtrim
define('API_SERVER_URL', rtrim('https://jsgame.live', '/')); 
define('API_PLAYER_PREFIX', 'h902fc');
define('API_CURRENCY', 'BDT'); 
define('API_LANG', 'en');

// 🚀 Ultimate URL Detect & Bypass (Ngrok + Cloudflare + XAMPP + Live Server)
$protocol = 'http';
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $protocol = 'https';
} elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $protocol = 'https';
}

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Windows (XAMPP/WAMP) ব্যাকস্ল্যাশ (\) ফিক্স করে ফরওয়ার্ড স্ল্যাশ (/) করার বাইপাস
$dir = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
$dir = ($dir === '/' || $dir === '\\') ? '' : $dir;

define('BASE_URL', rtrim($protocol . "://" . $host . $dir, '/'));
?>