<?php
require_once 'db.php';
require_once 'HuiduService.php';

$input = file_get_contents("php://input");
$request = json_decode($input, true);

if (!isset($request['payload'])) {
    echo json_encode(['code' => 1, 'msg' => 'Missing payload']);
    exit;
}

// Decrypt request from Game Server
$decryptedJson = openssl_decrypt($request['payload'], 'AES-256-ECB', API_AES_KEY, 0);
$data = json_decode($decryptedJson, true);

if (!$data || !isset($data['member_account'])) {
    echo json_encode(['code' => 1, 'msg' => 'Invalid data format']);
    exit;
}

$username = str_replace(API_PLAYER_PREFIX, '', $data['member_account']);

// Get User Balance
$stmt = $pdo->prepare("SELECT balance FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

$currentBalance = $user ? (float)$user['balance'] : 0.00;

// API কে ব্যালেন্স জানিয়ে দেওয়া যাতে গেম চলতে থাকে
$responsePayload = [
    'credit_amount' => (string)$currentBalance,
    'timestamp' => (string)(time() * 1000)
];

$encryptedResponse = openssl_encrypt(json_encode($responsePayload), 'AES-256-ECB', API_AES_KEY, 0);

header('Content-Type: application/json');
echo json_encode([
    'code' => 0,
    'msg' => 'success',
    'payload' => $encryptedResponse
]);
exit;
?>