<?php
require_once 'db.php';
require_once 'config.php';

// ১. API সার্ভার থেকে রিকোয়েস্ট রিসিভ করা
$input = file_get_contents("php://input");
$request = json_decode($input, true);

if (!isset($request['payload'])) {
    echo json_encode(['code' => 1, 'msg' => 'Missing payload']);
    exit;
}

// ২. রিকোয়েস্ট ডিক্রিপ্ট করা
$decryptedJson = openssl_decrypt($request['payload'], 'AES-256-ECB', API_AES_KEY, 0);
$data = json_decode(trim($decryptedJson), true);

if (!$data || !isset($data['member_account'])) {
    echo json_encode(['code' => 1, 'msg' => 'Invalid data format']);
    exit;
}

// ৩. ইউজারনেম এবং বেট/উইন অ্যামাউন্ট বের করা
$username = str_replace(API_PLAYER_PREFIX, '', $data['member_account']);
$betAmount = isset($data['bet_amount']) ? (float)$data['bet_amount'] : 0.00;
$winAmount = isset($data['win_amount']) ? (float)$data['win_amount'] : 0.00;

// ৪. ডাটাবেস থেকে ইউজারের বর্তমান ব্যালেন্স চেক করা
$stmt = $pdo->prepare("SELECT balance FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['code' => 1, 'msg' => 'User not found']);
    exit;
}

$currentBalance = (float)$user['balance'];

// ৫. ব্যালেন্স হিসাব করা (বর্তমান ব্যালেন্স - বেট অ্যামাউন্ট + জেতা অ্যামাউন্ট)
$newBalance = $currentBalance - $betAmount + $winAmount;

// ৬. নতুন ব্যালেন্স ডাটাবেসে আপডেট করা
$updateStmt = $pdo->prepare("UPDATE users SET balance = ? WHERE username = ?");
$updateStmt->execute([$newBalance, $username]);

// ৭. API সার্ভারকে নতুন ব্যালেন্স জানিয়ে দেওয়া
$responsePayload = [
    'credit_amount' => (string)$newBalance,
    'timestamp' => (string)(time() * 1000)
];

$jsonResponse = json_encode($responsePayload);
$encryptedResponse = openssl_encrypt($jsonResponse, 'AES-256-ECB', API_AES_KEY, 0);

header('Content-Type: application/json');
echo json_encode([
    'code' => 0,
    'msg' => 'success',
    'payload' => $encryptedResponse
]);
exit;
?>