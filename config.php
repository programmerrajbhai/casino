<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', 'localhost');
define('DB_NAME', 'addabaji_50taka');
define('DB_USER', 'root');
define('DB_PASS', '');

// API Credentials
define('API_AGENCY_UID', '24c0f3e3c178a0d986fdc89468304ed2');
define('API_AES_KEY', '6b4ea207483062b7404fe3840261ac50');
define('API_SERVER_URL', 'https://jsgame.live');
define('API_PLAYER_PREFIX', 'h902fc');
define('API_CURRENCY', 'BDT'); // যদি BDT তে গেম না আসে, তবে এখানে USD দিয়ে ট্রাই করবেন
define('API_LANG', 'en');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
define('BASE_URL', rtrim($baseUrl, '/'));
?>