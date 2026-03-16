<?php
// PHP Error Show & Timeout Fix
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0); // টাইমআউট বন্ধ করে দেওয়া হলো
ob_implicit_flush(1); // রিয়েল-টাইম আউটপুট দেখানোর জন্য

require_once 'db.php';
require_once 'HuiduService.php';

$api = new HuiduService();

echo "<div style='font-family:sans-serif; background:#111; color:#0f0; padding:20px; min-height:100vh;'>";
echo "<h2>🚀 Starting API Sync... Please wait!</h2><hr>";

// 1. Sync Providers
echo "<b>[1] Fetching Providers...</b><br>";
$provRes = $api->fetchProviders();

if (!isset($provRes['code']) || $provRes['code'] !== 0) {
    die("<b style='color:red;'>❌ Failed to fetch providers! API Response: " . json_encode($provRes) . "</b></div>");
}

$providers = $provRes['data'] ?? [];
$providerMap = [];

foreach ($providers as $p) {
    $code = $p['code'];
    $name = $p['name'] ?? $code;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO huidu_providers (provider_code, provider_name) VALUES (?, ?) ON DUPLICATE KEY UPDATE provider_name = ?");
        $stmt->execute([$code, $name, $name]);
    } catch (PDOException $e) {
        echo "<span style='color:red;'>Provider DB Error ({$code}): {$e->getMessage()}</span><br>";
    }
}
echo "<p style='color:#0ea5e9;'>✅ Providers synced perfectly. Total: " . count($providers) . "</p><hr>";

// 2. Sync Games
echo "<b>[2] Fetching Games (This may take a few minutes)...</b><br><br>";
$totalGames = 0;

foreach ($providers as $p) {
    $code = $p['code'];
    echo "🔄 Syncing games for provider: <b>{$p['name']}</b> ({$code})... ";
    
    $gamesRes = $api->fetchGames($code);
    
    if (isset($gamesRes['code']) && $gamesRes['code'] === 0) {
        $games = $gamesRes['data'] ?? [];
        $providerGameCount = 0;

        foreach ($games as $g) {
            $uid = $g['game_uid'] ?? $g['gameId'] ?? null;
            if (!$uid) continue;

            $gName = $g['game_name'] ?? $g['gameName'] ?? 'Unknown';
            $gType = $g['game_type'] ?? $g['gameType'] ?? 'Slot';
            $curr = $g['currency'] ?? 'BDT';
            $lang = $g['lang'] ?? 'en';
            
            // API থেকে ইমেজ লিংক ধরার কোড
            $image = $g['image'] ?? $g['logo'] ?? $g['icon'] ?? $g['picture'] ?? NULL;

            try {
                // image কলামসহ ডাটাবেসে সেভ করার আপডেট করা লজিক
                $stmt = $pdo->prepare("
                    INSERT INTO huidu_games (game_uid, game_name, provider_code, provider_name, game_type, currency, lang, image) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE game_name = ?, game_type = ?, image = ?
                ");
                $stmt->execute([$uid, $gName, $code, $p['name'], $gType, $curr, $lang, $image, $gName, $gType, $image]);
                $providerGameCount++;
                $totalGames++;
            } catch (PDOException $e) {
                echo "<br><span style='color:red;'>❌ DB Error on game {$gName}: {$e->getMessage()}</span>";
                break; 
            }
        }
        echo "<span style='color:yellow;'> Added/Updated {$providerGameCount} games.</span><br>";
        
        usleep(100000); 
    } else {
        echo "<span style='color:red;'> Failed! (Msg: " . ($gamesRes['msg'] ?? 'Unknown') . ")</span><br>";
    }
    
    flush(); 
}

echo "<hr><h3 style='color:#22c55e;'>🎉 Sync Completed Successfully! Total {$totalGames} games added/updated.</h3>";
echo "<a href='index.php' style='color:#111; background:#eab308; padding:10px 20px; text-decoration:none; border-radius:5px; font-weight:bold; display:inline-block; margin-top:10px;'>Go to Home</a>";
echo "</div>";
?>