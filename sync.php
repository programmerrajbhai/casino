<?php
// error_reporting(E_ALL); // টেস্ট করার সময় অন রাখতে পারেন
ini_set('display_errors', 1);
set_time_limit(0);
ob_implicit_flush(1);

require_once 'db.php';
require_once 'HuiduService.php';

$api = new HuiduService();

echo "<div style='font-family:sans-serif; background:#000; color:#0f0; padding:20px; min-height:100vh;'>";
echo "<h2>🚀 Deep Syncing Started...</h2><hr>";

// ১. প্রোভাইডার সিঙ্ক
$provRes = $api->fetchProviders();
if (isset($provRes['code']) && $provRes['code'] === 0) {
    foreach ($provRes['data'] as $p) {
        $stmt = $pdo->prepare("INSERT INTO huidu_providers (provider_code, provider_name) VALUES (?, ?) ON DUPLICATE KEY UPDATE provider_name = ?");
        $stmt->execute([$p['code'], $p['name'], $p['name']]);
    }
    echo "✅ Providers updated.<br>";
}

// ২. গেম সিঙ্ক (ডিপ স্ক্যানার)
echo "<b>[2] Syncing Games & Preserving Images...</b><br><br>";
$totalSynced = 0;

$providers = $pdo->query("SELECT provider_code, provider_name FROM huidu_providers")->fetchAll();

foreach ($providers as $p) {
    $code = $p['provider_code'];
    echo "🔄 Provider: {$p['provider_name']}... ";
    
    $res = $api->fetchGames($code);
    if (isset($res['code']) && $res['code'] === 0) {
        $games = $res['data'] ?? [];
        foreach ($games as $g) {
            $uid = $g['game_uid'] ?? $g['gameId'] ?? null;
            if (!$uid) continue;

            $name = $g['game_name'] ?? $g['gameName'] ?? 'Unknown';
            $type = $g['game_type'] ?? $g['gameType'] ?? 'Slot';
            $curr = $g['currency'] ?? 'BDT';
            $lang = $g['lang'] ?? 'en';
            
            // এপিআই থেকে ছবি খোঁজা
            $newImg = $g['image'] ?? $g['imageUrl'] ?? $g['icon'] ?? $g['logo'] ?? NULL;

            // ডাটাবেসে ইনসার্ট বা আপডেট
            // ছবি যদি এপিআইতে না থাকে (NULL), তবে আগের ছবি যেন ডিলিট না হয় (COALESCE ব্যবহার করা হয়েছে)
            $sql = "INSERT INTO huidu_games (game_uid, game_name, provider_code, provider_name, game_type, currency, lang, image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    game_name = VALUES(game_name), 
                    game_type = VALUES(game_type), 
                    image = IFNULL(VALUES(image), image)";
            
            $pdo->prepare($sql)->execute([$uid, $name, $code, $p['provider_name'], $type, $curr, $lang, $newImg]);
            $totalSynced++;
        }
        echo "Done.<br>";
    } else {
        echo "<span style='color:red;'>Failed!</span><br>";
    }
    flush();
}

echo "<hr><h3>🎉 Success! Total {$totalSynced} games processed.</h3>";
echo "<a href='index.php' style='color:#000; background:#eab308; padding:10px 20px; text-decoration:none; font-weight:bold; border-radius:5px;'>Back to Lobby</a>";
echo "</div>";
?>