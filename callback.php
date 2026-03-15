<?php
require_once 'db.php';
require_once 'config.php';

// গেম সার্ভারের রিকোয়েস্টের সময় কোনো HTML এরর যেন না যায় তাই সব এরর অফ করা হলো
error_reporting(0);
ini_set('display_errors', 0);

function writeLog($msg) {
    file_put_contents('callback_log.txt', "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n", FILE_APPEND);
}

writeLog("--- Callback Hit ---");

$input = file_get_contents("php://input");
$request = json_decode($input, true);

if (!isset($request['payload'])) {
    writeLog("Failed: No Payload");
    die(json_encode(['code' => 1, 'msg' => 'no payload']));
}

// 100% Official Decryption
$decrypted = openssl_decrypt(base64_decode($request['payload']), 'AES-256-ECB', API_AES_KEY, OPENSSL_RAW_DATA);
$data = json_decode($decrypted, true);

writeLog("Decrypted Data: " . print_r($data, true));

if (!$data || !isset($data['member_account'])) {
    die(json_encode(['code' => 1, 'msg' => 'invalid data']));
}

$username = str_replace(API_PLAYER_PREFIX, '', $data['member_account']);
$betAmount = isset($data['bet_amount']) ? (float)$data['bet_amount'] : 0.00;
$winAmount = isset($data['win_amount']) ? (float)$data['win_amount'] : 0.00;

$stmt = $pdo->prepare("SELECT balance FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    writeLog("Failed: User Not Found");
    die(json_encode(['code' => 1, 'msg' => 'user not found']));
}

$currentBalance = (float)$user['balance'];
$newBalance = $currentBalance - $betAmount + $winAmount;

if ($newBalance < 0) {
    $newBalance = 0;
}

// Update Database
$pdo->prepare("UPDATE users SET balance = ? WHERE username = ?")->execute([$newBalance, $username]);
writeLog("Success - Old Bal: {$currentBalance} | Bet: {$betAmount} | Win: {$winAmount} | New Bal: {$newBalance}");

// Prepare Response
$respPayload = [
    'credit_amount' => (string)number_format($newBalance, 2, '.', ''),
    'timestamp' => (string)(time() * 1000)
];

// 100% Official Encryption
$jsonResp = json_encode($respPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$encryptedResp = base64_encode(openssl_encrypt($jsonResp, 'AES-256-ECB', API_AES_KEY, OPENSSL_RAW_DATA));

$finalOut = json_encode([
    'code' => 0,
    'msg' => 'success',
    'payload' => $encryptedResp
]);

header('Content-Type: application/json');
echo $finalOut;
exit;
?>