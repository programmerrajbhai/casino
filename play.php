<?php
require_once 'db.php';
require_once 'HuiduService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['game_uid'])) {
    $gameUid = trim($_POST['game_uid']);
    $username = 'player_raj_01'; 
    
    // Check user & add 5000 tk automatically
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        $pdo->prepare("INSERT INTO users (username, balance) VALUES (?, ?)")->execute([$username, 5000.00]);
        $balance = 5000.00;
    } else {
        $balance = (float)$user['balance'];
    }

    $api = new HuiduService();
    $result = $api->launchGame($username, $gameUid, $balance);

    if ($result['status']) {
        header("Location: " . $result['url']);
        exit;
    } else {
        echo "<h2 style='color:red;text-align:center;'>❌ API Error: {$result['message']}</h2>";
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>