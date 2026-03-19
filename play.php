<?php
ob_start(); 
require_once 'db.php';
require_once 'HuiduService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['game_uid'])) {
    $gameUid = trim($_POST['game_uid']);
    
    $stmt = $pdo->prepare("SELECT * FROM huidu_games WHERE game_uid = ?");
    $stmt->execute([$gameUid]);
    $game = $stmt->fetch();

    if (!$game) {
        die("<h2 style='color:red;text-align:center;'>❌ Game not found in local database!</h2>");
    }

    // 🔥 ১. ডেটাবেস থেকে কারেন্সি বের করা (একাধিক থাকলে কমা দিয়ে ভাগ করে প্রথমটি নেওয়া)
    $currencies = explode(',', $game['currency'] ?? 'BDT');
    $playCurrency = strtoupper(trim($currencies[0]));
    if (empty($playCurrency)) {
        $playCurrency = 'BDT'; // ডিফল্ট
    }

    // 🔥 ২. ডেটাবেস থেকে ভাষা (Language) বের করা
    $langs = explode(',', $game['lang'] ?? 'en');
    $playLang = strtolower(trim($langs[0]));
    if (empty($playLang)) {
        $playLang = 'en'; // ডিফল্ট
    }

    $username = 'playerraj' . strtolower($playCurrency); 
    
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    $balance = 5000.00; 

    if (!$user) {
        $pdo->prepare("INSERT INTO users (username, balance) VALUES (?, ?)")->execute([$username, $balance]);
    } else {
        $balance = (float)$user['balance'];
        if($balance < 10) {
            $balance = 5000.00;
            $pdo->prepare("UPDATE users SET balance = ? WHERE username = ?")->execute([$balance, $username]);
        }
    }

    $api = new HuiduService();
    
    // গেম লঞ্চ করার আগে প্লেয়ারকে API তে রেজিস্টার করে নেওয়া হচ্ছে
    $api->registerPlayer($username);

    // 🔥 আপডেট: গেম লঞ্চিং এ ডাইনামিক কারেন্সি এবং ভাষা পাঠানো হচ্ছে
    $result = $api->launchGame($username, $gameUid, $balance, $playCurrency, $playLang);

    if ($result['status'] && !empty($result['url'])) {
        $gameUrl = $result['url'];
        
        header("Location: " . $gameUrl);
        echo "<script>window.location.href = '{$gameUrl}';</script>";
        echo "<meta http-equiv='refresh' content='0;url={$gameUrl}'>";
        exit;
    } else {
        echo "<div style='background:#111; color:#fff; padding:40px; text-align:center; font-family:sans-serif; min-height:100vh;'>";
        echo "<h2 style='color:#ef4444;'>❌ গেম চালু করা যায়নি!</h2>";
        echo "<p style='font-size:18px;'><strong>গেমের নাম:</strong> {$game['game_name']}</p>";
        echo "<p style='font-size:18px; color:#eab308;'><strong>কারেন্সি:</strong> {$playCurrency} | <strong>ভাষা:</strong> {$playLang}</p>";
        
        echo "<div style='text-align:left; background:#222; padding:20px; border-radius:10px; max-width:600px; margin:20px auto; border:1px solid #444;'>";
        echo "<h4 style='margin-top:0; color:#888;'>API Error Details:</h4>";
        echo "<pre style='color:#ef4444; margin:0; white-space:pre-wrap;'>" . print_r($result['raw'] ?? 'Unknown', true) . "</pre>";
        echo "</div>";

        echo "<a href='index.php' style='display:inline-block; background:#eab308; color:#000; padding:12px 25px; text-decoration:none; font-weight:bold; border-radius:8px; font-size:16px;'>⬅ ফিরে যান</a>";
        echo "</div>";
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>