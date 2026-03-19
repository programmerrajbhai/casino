<?php
// যেকোনো ধরনের এরর আউটপুট ব্লক করা হলো যেন রেসপন্স JSON নষ্ট না হয়
error_reporting(0);
ini_set('display_errors', 0);

require_once 'config.php';
require_once 'db.php';

// 🔥 লগিং সিস্টেম: API কী পাঠাচ্ছে তা দেখার জন্য (callback_log.txt ফাইলে সেভ হবে)
function writeLog($title, $data) {
    $logStr = "\n[" . date('Y-m-d H:i:s') . "] === " . $title . " ===\n" . print_r($data, true) . "\n";
    file_put_contents(__DIR__ . '/callback_log.txt', $logStr, FILE_APPEND);
}

// রেসপন্স এনক্রিপ্ট করার ফাংশন (Huidu এর নিয়ম অনুযায়ী)
function encryptResponse($payload) {
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return base64_encode(openssl_encrypt($json, 'AES-256-ECB', API_AES_KEY, OPENSSL_RAW_DATA));
}

// প্রোভাইডারের কাছে সাকসেস/ফেইল মেসেজ পাঠানোর ফাংশন
function sendResponse($code, $msg, $payloadData = []) {
    $response = [
        'code' => $code,
        'msg' => $msg,
        'payload' => empty($payloadData) ? "" : encryptResponse($payloadData)
    ];
    $jsonResponse = json_encode($response);
    writeLog("Our Response", $jsonResponse);
    header('Content-Type: application/json');
    echo $jsonResponse;
    exit;
}

// ১. প্রোভাইডারের পাঠানো রিকোয়েস্ট রিসিভ করা
$rawPost = file_get_contents('php://input');
writeLog("Raw Request From Provider", $rawPost);

$data = json_decode($rawPost, true);

if (empty($data['payload'])) {
    sendResponse(9999, 'Missing payload');
}

// ২. পেলোড ডিক্রিপ্ট করা
$decryptedJson = openssl_decrypt(base64_decode($data['payload']), 'AES-256-ECB', API_AES_KEY, OPENSSL_RAW_DATA);
$req = json_decode($decryptedJson, true);
writeLog("Decrypted Request Data", $req);

if (!$req) {
    sendResponse(9999, 'Decryption failed');
}

$username = $req['member_account'] ?? '';
if (empty($username)) {
    sendResponse(1001, 'Invalid User');
}

// ইউজারের বর্তমান ব্যালেন্স চেক করা
$stmt = $pdo->prepare("SELECT balance FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    sendResponse(1001, 'User not found');
}

$currentBalance = (float)$user['balance'];
$tradeNo = $req['trade_no'] ?? $req['order_id'] ?? uniqid();
$action = $req['action'] ?? $req['trade_type'] ?? $req['type'] ?? '';

// ৩. ট্রানজেকশন প্রসেসিং (Bet / Settle / Refund)
try {
    
    // 🔴 BET (টাকা কাটা হবে)
    if (isset($req['credit_amount']) || $action === 'bet' || $action == 1 || isset($req['bet_amount'])) {
        $betAmount = (float)($req['credit_amount'] ?? $req['bet_amount'] ?? $req['amount'] ?? 0);
        
        if ($currentBalance < $betAmount) {
            sendResponse(1004, 'Insufficient Balance', [
                'member_account' => $username,
                'balance' => (string)$currentBalance,
                'currency' => API_CURRENCY
            ]);
        }
        
        // Race Condition ফিক্স করে ব্যালেন্স আপডেট
        $newBalance = $currentBalance - $betAmount;
        $pdo->prepare("UPDATE users SET balance = balance - ? WHERE username = ? AND balance >= ?")
            ->execute([$betAmount, $username, $betAmount]);

        sendResponse(0, 'success', [
            'member_account' => $username,
            'balance' => (string)$newBalance,
            'trade_no' => $tradeNo,
            'currency' => API_CURRENCY
        ]);
    }
    
    // 🟢 SETTLE / WIN (টাকা যোগ হবে)
    elseif (isset($req['win_amount']) || $action === 'settle' || $action == 2) {
        $winAmount = (float)($req['win_amount'] ?? $req['amount'] ?? 0);
        
        $newBalance = $currentBalance + $winAmount;
        $pdo->prepare("UPDATE users SET balance = balance + ? WHERE username = ?")
            ->execute([$winAmount, $username]);

        sendResponse(0, 'success', [
            'member_account' => $username,
            'balance' => (string)$newBalance,
            'trade_no' => $tradeNo,
            'currency' => API_CURRENCY
        ]);
    }
    
    // 🔵 REFUND / CANCEL (টাকা ফেরত দেওয়া হবে)
    elseif ($action === 'refund' || $action === 'cancel' || $action == 3) {
        $refundAmount = (float)($req['refund_amount'] ?? $req['amount'] ?? 0);
        
        $newBalance = $currentBalance + $refundAmount;
        $pdo->prepare("UPDATE users SET balance = balance + ? WHERE username = ?")
            ->execute([$refundAmount, $username]);

        sendResponse(0, 'success', [
            'member_account' => $username,
            'balance' => (string)$newBalance,
            'trade_no' => $tradeNo,
            'currency' => API_CURRENCY
        ]);
    }
    
    // 🟡 শুধু ব্যালেন্স চেক (Get Balance)
    else {
        sendResponse(0, 'success', [
            'member_account' => $username,
            'balance' => (string)$currentBalance,
            'currency' => API_CURRENCY
        ]);
    }

} catch (Exception $e) {
    writeLog("System Error", $e->getMessage());
    sendResponse(9999, 'System Error');
}
?>