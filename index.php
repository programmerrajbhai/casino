<?php
require_once 'db.php';

$provStmt = $pdo->query("SELECT * FROM huidu_providers WHERE status = 1 ORDER BY provider_name ASC");
$providers = $provStmt->fetchAll();

$activeProvider = $_GET['provider'] ?? '';

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
    <title>Casino X</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { background: #0f172a; color: white; }</style>
</head>
<body class="pb-20">

    <nav class="bg-gray-900 border-b border-gray-800 p-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-black text-yellow-500">🎰 Casino X</h1>
            <div class="bg-gray-800 px-4 py-1.5 rounded-full font-bold text-yellow-400">Balance: ৳ 5000</div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 mt-8">
        <div class="flex gap-3 overflow-x-auto pb-4 mb-6" style="scrollbar-width: none;">
            <a href="index.php" class="px-6 py-2 rounded-lg font-bold <?= $activeProvider === '' ? 'bg-yellow-500 text-black' : 'bg-gray-800' ?>">All</a>
            <?php foreach($providers as $p): ?>
                <a href="index.php?provider=<?= $p['provider_code'] ?>" class="px-6 py-2 rounded-lg font-bold <?= $activeProvider === $p['provider_code'] ? 'bg-yellow-500 text-black' : 'bg-gray-800' ?>">
                    <?= htmlspecialchars($p['provider_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach($games as $g): 
                $imageUrl = !empty($g['image']) ? $g['image'] : "https://ui-avatars.com/api/?name=".urlencode($g['game_name'])."&background=1f2937&color=eab308&size=256";
            ?>
            <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden relative group">
                <div class="aspect-square bg-gray-800 relative">
                    <img src="<?= $imageUrl ?>" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-all">
                        <form action="play.php" method="POST">
                            <input type="hidden" name="game_uid" value="<?= htmlspecialchars($g['game_uid']) ?>">
                            <button type="submit" class="bg-yellow-500 text-black font-bold py-2 px-4 rounded-full">▶ Play</button>
                        </form>
                    </div>
                </div>
                <div class="p-3"><h3 class="text-sm font-bold truncate"><?= htmlspecialchars($g['game_name']) ?></h3></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>