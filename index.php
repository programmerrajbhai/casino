<?php
require_once 'db.php';

// প্রোভাইডার ফেচ করা (ক্যাটাগরি হিসেবে দেখানোর জন্য)
$provStmt = $pdo->query("SELECT * FROM huidu_providers WHERE status = 1 ORDER BY provider_name ASC");
$providers = $provStmt->fetchAll();

$activeProvider = $_GET['provider'] ?? '';

// গেম ফেচ করা
$sql = "SELECT * FROM huidu_games WHERE status = 1";
$params = [];
if ($activeProvider) {
    $sql .= " AND provider_code = ?";
    $params[] = $activeProvider;
}
$sql .= " ORDER BY id DESC LIMIT 100";

$gameStmt = $pdo->prepare($sql);
$gameStmt->execute($params);
$games = $gameStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casino X - Premium Lobby</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #0b0f19; color: white; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .game-card:hover .play-overlay { opacity: 1; }
        .play-overlay { transition: all 0.3s ease-in-out; }
    </style>
</head>
<body class="pb-20">

    <nav class="bg-gray-900 border-b border-gray-800 p-4 sticky top-0 z-50 shadow-lg">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500">
                <i class="fa-solid fa-crown mr-2"></i>Casino X
            </h1>
            <div class="bg-gray-800 border border-gray-700 px-4 py-1.5 rounded-full font-bold text-yellow-400 shadow-inner">
                <i class="fa-solid fa-wallet mr-2"></i>৳ 5000
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 mt-8">
        
        <div class="flex items-center gap-2 mb-4">
            <i class="fa-solid fa-gamepad text-yellow-500"></i>
            <h2 class="text-lg font-bold text-gray-200 uppercase tracking-wider">Game Categories</h2>
        </div>
        <div class="flex gap-3 overflow-x-auto pb-4 mb-6 hide-scroll">
            <a href="index.php" class="px-6 py-2.5 rounded-lg font-bold whitespace-nowrap transition-all <?= $activeProvider === '' ? 'bg-gradient-to-r from-yellow-500 to-orange-500 text-black shadow-lg shadow-yellow-500/30' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' ?>">
                <i class="fa-solid fa-border-all mr-1"></i> All Games
            </a>
            <?php foreach($providers as $p): ?>
                <a href="index.php?provider=<?= $p['provider_code'] ?>" class="px-6 py-2.5 rounded-lg font-bold whitespace-nowrap transition-all <?= $activeProvider === $p['provider_code'] ? 'bg-gradient-to-r from-yellow-500 to-orange-500 text-black shadow-lg shadow-yellow-500/30' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' ?>">
                    <?= htmlspecialchars($p['provider_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-6">
            <?php foreach($games as $g): 
                // রিয়েল ছবি না থাকলে প্রিমিয়াম ডিফল্ট ক্যাসিনো ব্যাকগ্রাউন্ড দেখাবে
                $defaultCasinoImage = "https://images.unsplash.com/photo-1596838132731-3301c3fd4317?q=80&w=500&auto=format&fit=crop";
                $imageUrl = !empty($g['image']) ? $g['image'] : $defaultCasinoImage;
            ?>
            <div class="game-card bg-gray-800 border border-gray-700 hover:border-yellow-500 rounded-xl overflow-hidden relative group shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                
                <div class="aspect-square bg-gray-900 relative">
                    <img src="<?= $imageUrl ?>" alt="<?= htmlspecialchars($g['game_name']) ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                    
                    <div class="absolute top-2 right-2 bg-black/80 backdrop-blur-sm border border-gray-600 text-yellow-400 text-[10px] font-bold px-2 py-1 rounded shadow-md uppercase">
                        <?= htmlspecialchars($g['provider_name']) ?>
                    </div>

                    <div class="play-overlay absolute inset-0 bg-black/70 opacity-0 flex items-center justify-center backdrop-blur-[2px]">
                        <form action="play.php" method="POST">
                            <input type="hidden" name="game_uid" value="<?= htmlspecialchars($g['game_uid']) ?>">
                            <button type="submit" class="bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-300 hover:to-yellow-500 text-black font-extrabold py-2.5 px-6 rounded-full shadow-[0_0_15px_rgba(234,179,8,0.5)] transform hover:scale-105 transition-all flex items-center gap-2">
                                <i class="fa-solid fa-play"></i> Play
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="p-3 bg-gradient-to-b from-gray-800 to-gray-900">
                    <h3 class="text-sm font-bold truncate text-white" title="<?= htmlspecialchars($g['game_name']) ?>">
                        <?= htmlspecialchars($g['game_name']) ?>
                    </h3>
                    <div class="flex justify-between items-center mt-1.5">
                        <p class="text-[11px] text-gray-400 uppercase font-semibold tracking-wider">
                            <?= htmlspecialchars($g['game_type'] ?? 'SLOT') ?>
                        </p>
                        <i class="fa-solid fa-star text-yellow-500 text-[10px]"></i>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
    </div>
</body>
</html>