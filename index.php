<?php
require_once 'db.php';

// ফিল্টার রিকোয়েস্ট (প্রোভাইডার বা ক্যাটাগরি সিলেক্ট করলে)
$activeProvider = $_GET['provider'] ?? '';
$activeType = $_GET['type'] ?? '';

// ডেটাবেস থেকে সব প্রোভাইডারের নাম বের করা (মেনুর জন্য)
$provStmt = $pdo->query("SELECT DISTINCT provider_code, provider_name FROM huidu_games WHERE provider_name IS NOT NULL AND provider_name != '' ORDER BY provider_name ASC");
$providers = $provStmt->fetchAll();

// ডেটাবেস থেকে সব ক্যাটাগরি (Game Type) বের করা
$typeStmt = $pdo->query("SELECT DISTINCT game_type FROM huidu_games WHERE game_type IS NOT NULL AND game_type != '' ORDER BY game_type ASC");
$gameTypes = $typeStmt->fetchAll();

// গেম লোড করার মূল কুয়েরি
$sql = "SELECT * FROM huidu_games WHERE 1=1";
$params = [];

if ($activeProvider) {
    $sql .= " AND provider_code = ?";
    $params[] = $activeProvider;
}
if ($activeType) {
    $sql .= " AND game_type = ?";
    $params[] = $activeType;
}

// একসাথে অনেক গেম লোড হলে সাইট স্লো হতে পারে, তাই লিমিট ২০০ রাখা হলো
$sql .= " ORDER BY id DESC LIMIT 200"; 
$gameStmt = $pdo->prepare($sql);
$gameStmt->execute($params);
$games = $gameStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Casino X - Premium Game Lobby</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #0b0f19; color: white; font-family: 'Segoe UI', Tahoma, sans-serif; overflow-x: hidden; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .game-card { transition: all 0.3s ease; }
        .game-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(234, 179, 8, 0.2); border-color: #eab308; }
        .play-overlay { transition: all 0.3s ease; opacity: 0; backdrop-filter: blur(3px); }
        .game-card:hover .play-overlay { opacity: 1; }
        .glass-panel { background: rgba(17, 24, 39, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="pb-24"> 

    <nav class="glass-panel p-4 sticky top-0 z-40 shadow-xl border-b border-gray-800">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl md:text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500 flex items-center gap-2">
                <i class="fa-solid fa-crown text-yellow-500"></i> CasinoX
            </h1>
            <div class="flex items-center gap-4">
                <div class="bg-gray-900 border border-gray-700 px-4 py-2 rounded-xl font-bold text-yellow-400 flex items-center gap-2 shadow-inner">
                    <i class="fa-solid fa-wallet"></i> ৳ 5000
                </div>
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 p-0.5 cursor-pointer shadow-lg hidden sm:block">
                    <img src="https://ui-avatars.com/api/?name=Raj&background=1f2937&color=eab308" class="rounded-full w-full h-full object-cover">
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 mt-6">
        <div class="rounded-3xl p-8 md:p-12 flex flex-col justify-center items-start border border-yellow-500/20 shadow-2xl relative overflow-hidden mb-8 bg-gradient-to-r from-gray-900 to-gray-800">
            <div class="absolute top-0 right-0 w-64 h-64 bg-yellow-500 rounded-full mix-blend-multiply filter blur-[80px] opacity-20 animate-pulse"></div>
            <h2 class="text-3xl md:text-5xl font-black mb-3 text-white">খেলুন এবং জিতুন <span class="text-yellow-400">বড় অংকের টাকা</span></h2>
            <p class="text-gray-400 text-sm md:text-base mb-6 max-w-lg">আপনার পছন্দের স্লট, লাইভ ক্যাসিনো এবং টেবিল গেমগুলো এখন হাতের মুঠোয়। সরাসরি ডেটাবেস থেকে দ্রুততম লোডিং স্পিড!</p>
            <button class="bg-gradient-to-r from-yellow-500 to-orange-500 text-black font-extrabold py-3 px-8 rounded-full shadow-[0_0_20px_rgba(234,179,8,0.4)] hover:scale-105 transition-transform flex items-center gap-2">
                <i class="fa-solid fa-bolt"></i> এখনই খেলুন
            </button>
        </div>

        <div class="relative w-full mb-8">
            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                <i class="fa-solid fa-search text-gray-500 text-lg"></i>
            </div>
            <input type="text" id="gameSearch" placeholder="গেমের নাম দিয়ে খুঁজুন..." class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-2xl pl-12 pr-4 py-4 focus:outline-none focus:border-yellow-500 transition-colors shadow-inner text-lg placeholder-gray-600">
        </div>

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-white flex items-center gap-2"><i class="fa-solid fa-gamepad text-yellow-500"></i> ক্যাটাগরি</h2>
        </div>
        <div class="flex gap-3 overflow-x-auto pb-4 mb-6 hide-scroll">
            <a href="index.php<?= $activeProvider ? '?provider='.$activeProvider : '' ?>" 
               class="flex-shrink-0 px-6 py-2.5 rounded-xl font-bold transition-all border <?= $activeType === '' ? 'bg-gradient-to-r from-yellow-500 to-orange-500 text-black border-transparent shadow-lg' : 'bg-gray-900 border-gray-700 text-gray-400 hover:bg-gray-800 hover:text-white' ?>">
                সব গেম
            </a>
            <?php foreach($gameTypes as $cat): ?>
                <?php 
                    $params = [];
                    if($activeProvider) $params['provider'] = $activeProvider;
                    $params['type'] = $cat['game_type'];
                    $queryStr = http_build_query($params);
                ?>
                <a href="index.php?<?= $queryStr ?>" 
                   class="flex-shrink-0 px-6 py-2.5 rounded-xl font-bold transition-all border <?= $activeType === $cat['game_type'] ? 'bg-gradient-to-r from-yellow-500 to-orange-500 text-black border-transparent shadow-lg' : 'bg-gray-900 border-gray-700 text-gray-400 hover:bg-gray-800 hover:text-white' ?>">
                    <?= htmlspecialchars($cat['game_type']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-white flex items-center gap-2"><i class="fa-solid fa-building text-blue-400"></i> প্রোভাইডার</h2>
        </div>
        <div class="flex gap-2 overflow-x-auto pb-4 mb-8 hide-scroll">
            <a href="index.php<?= $activeType ? '?type='.$activeType : '' ?>" class="flex-shrink-0 px-5 py-2 text-sm rounded-full font-bold transition-all <?= $activeProvider === '' ? 'bg-white text-black shadow-lg' : 'bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                All Providers
            </a>
            <?php foreach($providers as $p): ?>
                <?php 
                    $pParams = [];
                    $pParams['provider'] = $p['provider_code'];
                    if($activeType) $pParams['type'] = $activeType;
                    $pQueryStr = http_build_query($pParams);
                ?>
                <a href="index.php?<?= $pQueryStr ?>" class="flex-shrink-0 px-5 py-2 text-sm rounded-full font-bold transition-all <?= $activeProvider === $p['provider_code'] ? 'bg-white text-black shadow-lg' : 'bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <?= htmlspecialchars($p['provider_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 md:gap-6" id="gamesGrid">
            <?php if(count($games) > 0): ?>
                <?php foreach($games as $g): 


                   $dbImage = !empty(trim($g['image'] ?? '')) ? trim($g['image']) : '';
                    if ($dbImage) {
                        // image.php এর মাধ্যমে কল করা হলো
                        $finalImage = 'image.php?url=' . urlencode($dbImage);
                    } else {
                        $finalImage = "https://images.unsplash.com/photo-1596838132731-3301c3fd4317?q=80&w=500&auto=format&fit=crop";
                    }


                ?>
                <div class="game-card bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden relative group flex flex-col shadow-lg">
                    <div class="aspect-square bg-gray-800 relative overflow-hidden">
                        
                        <img src="<?= htmlspecialchars($finalImage) ?>" 
                             alt="<?= htmlspecialchars($g['game_name']) ?>" 
                             class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500" loading="lazy">
                        
                        <div class="absolute top-2 left-2 bg-black/80 backdrop-blur-md border border-gray-700 text-white text-[10px] font-bold px-2 py-1 rounded-md uppercase z-10 shadow-lg">
                            <?= htmlspecialchars($g['provider_name']) ?>
                        </div>

                        <div class="play-overlay absolute inset-0 bg-black/70 flex items-center justify-center z-20">
                            <form action="play.php" method="POST">
                                <input type="hidden" name="game_uid" value="<?= htmlspecialchars($g['game_uid']) ?>">
                                <button type="submit" class="bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-300 hover:to-orange-400 text-black font-extrabold py-2 px-6 rounded-full transform hover:scale-110 transition-transform shadow-[0_0_20px_rgba(234,179,8,0.6)] flex items-center gap-2">
                                    <i class="fa-solid fa-play"></i> PLAY
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="p-3 bg-gray-900 flex-grow flex flex-col justify-between">
                        <h3 class="game-title text-sm font-bold truncate text-white mb-1" title="<?= htmlspecialchars($g['game_name']) ?>">
                            <?= htmlspecialchars($g['game_name']) ?>
                        </h3>
                        <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wide">
                            <?= htmlspecialchars($g['game_type'] ?? 'CASINO') ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-24 text-center text-gray-500 border-2 border-dashed border-gray-800 rounded-3xl bg-gray-900/50">
                    <i class="fa-solid fa-box-open text-6xl mb-4 text-gray-700"></i>
                    <h3 class="text-2xl font-bold text-gray-400">কোনো গেম পাওয়া যায়নি!</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="fixed bottom-0 left-0 w-full glass-panel border-t border-gray-800 flex justify-around items-center py-2 px-2 z-50 md:hidden pb-safe">
        <a href="index.php" class="flex flex-col items-center justify-center w-1/4 p-1 text-yellow-500">
            <i class="fa-solid fa-house text-xl mb-1"></i>
            <span class="text-[10px] font-bold">হোম</span>
        </a>
        <a href="#" class="flex flex-col items-center justify-center w-1/4 p-1 text-gray-500 hover:text-gray-300">
            <i class="fa-solid fa-gift text-xl mb-1"></i>
            <span class="text-[10px]">অফার</span>
        </a>
        <a href="#" class="flex flex-col items-center justify-center w-1/4 p-1 relative -top-6">
            <div class="bg-gradient-to-r from-yellow-400 to-orange-500 w-14 h-14 rounded-full flex items-center justify-center shadow-[0_0_20px_rgba(234,179,8,0.4)] border-4 border-[#0b0f19]">
                <i class="fa-solid fa-plus text-black text-2xl font-black"></i>
            </div>
        </a>
        <a href="#" class="flex flex-col items-center justify-center w-1/4 p-1 text-gray-500 hover:text-gray-300">
            <i class="fa-solid fa-user text-xl mb-1"></i>
            <span class="text-[10px]">প্রোফাইল</span>
        </a>
    </div>

    <script>
        document.getElementById('gameSearch').addEventListener('input', function(e) {
            let filter = e.target.value.toLowerCase();
            let cards = document.querySelectorAll('.game-card');
            
            cards.forEach(card => {
                let title = card.querySelector('.game-title').innerText.toLowerCase();
                if (title.includes(filter)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>