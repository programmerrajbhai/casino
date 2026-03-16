<?php
require_once 'db.php';

// Provider Fetch (Directly from huidu_games)
$provStmt = $pdo->query("SELECT DISTINCT provider_code, provider_name FROM huidu_games WHERE status = 1 AND provider_name IS NOT NULL AND provider_name != '' ORDER BY provider_name ASC");
$providers = $provStmt->fetchAll();

// Filter Request
$activeProvider = $_GET['provider'] ?? '';
$activeType = $_GET['type'] ?? '';

// Game Fetch With Dynamic Filters
$sql = "SELECT * FROM huidu_games WHERE status = 1";
$params = [];

if ($activeProvider) {
    $sql .= " AND provider_code = ?";
    $params[] = $activeProvider;
}
if ($activeType) {
    $sql .= " AND game_type = ?";
    $params[] = $activeType;
}

$sql .= " ORDER BY id DESC LIMIT 150";
$gameStmt = $pdo->prepare($sql);
$gameStmt->execute($params);
$games = $gameStmt->fetchAll();

// Main Casino Categories
$categories = [
    ['name' => 'Slot', 'icon' => 'fa-solid fa-slot-machine', 'color' => 'text-yellow-400'],
    ['name' => 'Live Casino', 'icon' => 'fa-solid fa-headset', 'color' => 'text-green-400'],
    ['name' => 'Fishing', 'icon' => 'fa-solid fa-fish', 'color' => 'text-blue-400'],
    ['name' => 'Table Games', 'icon' => 'fa-solid fa-dice', 'color' => 'text-red-400']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Casino X - Premium Lobby</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #090c15; color: white; font-family: 'Segoe UI', Tahoma, sans-serif; overflow-x: hidden; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .game-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .game-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(234, 179, 8, 0.2); border-color: #eab308; }
        .play-overlay { transition: all 0.3s ease; opacity: 0; backdrop-filter: blur(2px); }
        .game-card:hover .play-overlay { opacity: 1; }
        .bottom-nav-item.active i { transform: translateY(-3px) scale(1.15); color: #eab308; transition: 0.3s; }
        .bottom-nav-item.active span { color: #eab308; font-weight: bold; }
        .hero-bg { 
            background: linear-gradient(to right, rgba(11, 15, 25, 0.95), rgba(11, 15, 25, 0.4)), 
                        url('https://images.unsplash.com/photo-1596838132731-3301c3fd4317?q=80&w=1200') center/cover; 
        }
    </style>
</head>
<body class="pb-24"> 

    <nav class="bg-gray-900 border-b border-gray-800 p-3 md:p-4 sticky top-0 z-40 shadow-xl">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl md:text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500 flex items-center gap-2">
                <i class="fa-solid fa-crown text-yellow-500"></i> CasinoX
            </h1>
            <div class="flex items-center gap-3">
                <div class="bg-gray-800 border border-gray-700 px-3 py-1.5 rounded-lg font-bold text-yellow-400 text-sm md:text-base flex items-center gap-2 shadow-inner">
                    <i class="fa-solid fa-wallet"></i> ৳ 5000
                </div>
                <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 p-0.5 cursor-pointer hidden sm:block shadow-lg">
                    <img src="https://ui-avatars.com/api/?name=Raj&background=1f2937&color=eab308" class="rounded-full w-full h-full object-cover">
                </div>
            </div>
        </div>
    </nav>

    <div class="bg-yellow-500/10 border-b border-yellow-500/20 text-yellow-400 py-2 flex items-center px-4 shadow-sm">
        <i class="fa-solid fa-bullhorn mr-3 animate-pulse"></i>
        <marquee behavior="scroll" direction="left" class="text-sm font-semibold tracking-wide" scrollamount="5">
            🎉 Welcome to Casino X! Play official games from JILI, PG Soft, Live22! | 🤑 Enjoy 100% First Deposit Bonus! | 🚀 Instant Cashouts available 24/7!
        </marquee>
    </div>

    <div class="max-w-7xl mx-auto px-4 mt-6">
        
        <div class="hero-bg rounded-2xl p-6 md:p-12 flex flex-col justify-center items-start border border-gray-800 shadow-2xl relative overflow-hidden mb-8">
            <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
            <h2 class="text-3xl md:text-5xl font-black mb-2 md:mb-4 text-white drop-shadow-lg">PLAY & WIN <span class="text-yellow-400">BIG TODAY</span></h2>
            <p class="text-gray-300 text-sm md:text-base mb-4 md:mb-6 max-w-lg drop-shadow-md">Experience the thrill of premium slots and live casino games. Instant deposits, fast withdrawals, and 24/7 support.</p>
            <button class="bg-gradient-to-r from-yellow-500 to-orange-500 text-black font-extrabold py-2.5 md:py-3 px-8 rounded-full shadow-[0_0_20px_rgba(234,179,8,0.4)] hover:scale-105 transition-transform flex items-center gap-2">
                <i class="fa-solid fa-gift"></i> Claim Bonus
            </button>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <h2 class="text-xl font-bold uppercase tracking-wider flex items-center gap-2 w-full md:w-auto text-gray-100">
                <i class="fa-solid fa-fire text-orange-500"></i> Game Lobby
            </h2>
            <div class="relative w-full md:w-80">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-search text-gray-500"></i>
                </div>
                <input type="text" id="gameSearch" placeholder="Search your favorite game..." class="w-full bg-gray-900 border border-gray-700 text-white rounded-full pl-11 pr-4 py-2.5 focus:outline-none focus:border-yellow-500 transition-colors shadow-inner text-sm">
            </div>
        </div>

        <h3 class="text-xs text-gray-500 font-bold mb-3 uppercase tracking-widest">Game Type</h3>
        <div class="flex gap-3 overflow-x-auto pb-4 mb-2 hide-scroll">
            <a href="index.php<?= $activeProvider ? '?provider='.$activeProvider : '' ?>" 
               class="flex-shrink-0 flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold transition-all border <?= $activeType === '' ? 'bg-gradient-to-r from-gray-800 to-gray-700 border-yellow-500 text-yellow-400 shadow-lg' : 'bg-gray-900 border-gray-800 text-gray-400 hover:bg-gray-800 hover:text-white' ?>">
                <i class="fa-solid fa-border-all"></i> All Types
            </a>
            
            <?php foreach($categories as $cat): ?>
                <?php 
                    $params = [];
                    if($activeProvider) $params['provider'] = $activeProvider;
                    $params['type'] = $cat['name'];
                    $queryStr = http_build_query($params);
                ?>
                <a href="index.php?<?= $queryStr ?>" 
                   class="flex-shrink-0 flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold transition-all border <?= $activeType === $cat['name'] ? 'bg-gradient-to-r from-gray-800 to-gray-700 border-yellow-500 text-yellow-400 shadow-lg' : 'bg-gray-900 border-gray-800 text-gray-400 hover:bg-gray-800 hover:text-white' ?>">
                    <i class="<?= $cat['icon'] ?> <?= $activeType === $cat['name'] ? 'text-yellow-400' : $cat['color'] ?>"></i> <?= $cat['name'] ?>
                </a>
            <?php endforeach; ?>
        </div>

        <h3 class="text-xs text-gray-500 font-bold mb-3 uppercase tracking-widest mt-4">Providers</h3>
        <div class="flex gap-2 overflow-x-auto pb-4 mb-6 hide-scroll border-b border-gray-800/50">
            <a href="index.php<?= $activeType ? '?type='.$activeType : '' ?>" class="flex-shrink-0 flex items-center px-6 py-2 text-sm rounded-full font-bold whitespace-nowrap transition-all <?= $activeProvider === '' ? 'bg-gradient-to-r from-yellow-500 to-orange-500 text-black shadow-lg shadow-yellow-500/20' : 'bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                All Providers
            </a>
            <?php foreach($providers as $p): ?>
                <?php 
                    $pParams = [];
                    $pParams['provider'] = $p['provider_code'];
                    if($activeType) $pParams['type'] = $activeType;
                    $pQueryStr = http_build_query($pParams);
                ?>
                <a href="index.php?<?= $pQueryStr ?>" class="flex-shrink-0 flex items-center gap-2 px-5 py-2 text-sm rounded-full font-bold whitespace-nowrap transition-all <?= $activeProvider === $p['provider_code'] ? 'bg-gradient-to-r from-yellow-500 to-orange-500 text-black shadow-lg shadow-yellow-500/20' : 'bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <?= htmlspecialchars($p['provider_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 md:gap-5" id="gamesGrid">
            <?php if(count($games) > 0): ?>
                <?php foreach($games as $g): 
                    $dbImage = !empty(trim($g['image'] ?? '')) ? trim($g['image']) : '';
                    if ($dbImage) {
                        $finalImage = 'image.php?url=' . urlencode($dbImage);
                    } else {
                        // ডাটাবেসে ছবি NULL থাকলে এই প্রিমিয়াম ক্যাসিনো ছবিটি দেখাবে
                        $finalImage = "https://images.unsplash.com/photo-1596838132731-3301c3fd4317?q=80&w=500&auto=format&fit=crop";
                    }
                ?>
                <div class="game-card bg-gray-800 border border-gray-700 rounded-xl overflow-hidden relative group transition-all duration-300 flex flex-col">
                    <div class="aspect-[4/3] bg-gray-900 relative overflow-hidden">
                        
                        <img src="<?= htmlspecialchars($finalImage) ?>" 
                             alt="<?= htmlspecialchars($g['game_name']) ?>" 
                             class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                        
                        <div class="absolute top-2 right-2 bg-black/80 backdrop-blur-sm border border-gray-600 text-yellow-400 text-[9px] md:text-[10px] font-bold px-2 py-0.5 rounded shadow-lg uppercase z-10">
                            <?= htmlspecialchars($g['provider_name']) ?>
                        </div>

                        <div class="play-overlay absolute inset-0 bg-black/60 flex items-center justify-center z-20">
                            <form action="play.php" method="POST">
                                <input type="hidden" name="game_uid" value="<?= htmlspecialchars($g['game_uid']) ?>">
                                <button type="submit" class="bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-300 hover:to-orange-400 text-black font-extrabold py-2 px-6 rounded-full transform hover:scale-110 transition-transform shadow-[0_0_20px_rgba(234,179,8,0.6)] flex items-center gap-2">
                                    <i class="fa-solid fa-play"></i> PLAY
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="p-2.5 md:p-3 bg-gradient-to-b from-gray-800 to-gray-900 flex-grow flex flex-col justify-between border-t border-gray-700/50">
                        <h3 class="game-title text-[12px] md:text-sm font-bold truncate text-gray-100 mb-1" title="<?= htmlspecialchars($g['game_name']) ?>">
                            <?= htmlspecialchars($g['game_name']) ?>
                        </h3>
                        <div class="flex justify-between items-center">
                            <p class="text-[9px] md:text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                                <?= htmlspecialchars($g['game_type'] ?? 'SLOT') ?>
                            </p>
                            <i class="fa-solid fa-star text-yellow-500 text-[9px] md:text-[10px]"></i>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center text-gray-500 border border-dashed border-gray-700 rounded-2xl bg-gray-800/30">
                    <i class="fa-solid fa-ghost text-5xl mb-4 text-gray-600"></i>
                    <h3 class="text-xl font-bold text-gray-400">No games found!</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="fixed bottom-0 left-0 w-full bg-gray-900 border-t border-gray-800 flex justify-around items-center py-1.5 px-1 z-50 md:hidden shadow-[0_-5px_20px_rgba(0,0,0,0.6)] backdrop-blur-md bg-opacity-95">
        <a href="index.php" class="bottom-nav-item active flex flex-col items-center justify-center w-1/4 p-1">
            <i class="fa-solid fa-house text-lg mb-1"></i>
            <span class="text-[10px]">Home</span>
        </a>
        <a href="#" class="bottom-nav-item flex flex-col items-center justify-center w-1/4 p-1">
            <i class="fa-solid fa-gift text-lg mb-1 text-gray-400"></i>
            <span class="text-[10px] text-gray-400">Promo</span>
        </a>
        <a href="#" class="bottom-nav-item flex flex-col items-center justify-center w-1/4 p-1 relative -top-4">
            <div class="bg-gradient-to-r from-yellow-400 to-orange-500 w-12 h-12 rounded-full flex items-center justify-center shadow-[0_0_15px_rgba(234,179,8,0.5)] border-4 border-gray-900 animate-bounce" style="animation-duration: 3s;">
                <i class="fa-solid fa-plus text-black text-xl font-black"></i>
            </div>
            <span class="text-[10px] text-yellow-500 font-bold mt-1">Deposit</span>
        </a>
        <a href="#" class="bottom-nav-item flex flex-col items-center justify-center w-1/4 p-1">
            <i class="fa-solid fa-user text-lg mb-1 text-gray-400"></i>
            <span class="text-[10px] text-gray-400">Profile</span>
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