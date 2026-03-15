<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'addabaji_50taka');
define('DB_USER', 'root');
define('DB_PASS', '');

// API Credentials
define('API_AGENCY_UID', '24c0f3e3c178a0d986fdc89468304ed2');
define('API_AES_KEY', '6b4ea207483062b7404fe3840261ac50');
define('API_SERVER_URL', 'https://jsgame.live');
define('API_PLAYER_PREFIX', 'h902fc');
define('API_CURRENCY', 'BDT'); 
define('API_LANG', 'en');

// URL Detect for Ngrok & Live Server
$protocol = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$dir = dirname($_SERVER['PHP_SELF']);
define('BASE_URL', rtrim($protocol . "://" . $host . $dir, '/'));
?>