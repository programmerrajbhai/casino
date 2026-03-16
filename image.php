<?php
// image.php - Ultimate Hotlink, SSL & Multi-Domain Bypass
error_reporting(0);

if (isset($_GET['url']) && !empty($_GET['url'])) {
    $url = urldecode($_GET['url']);
    
    // URL ফিক্স
    $url = str_replace(' ', '%20', $url);
    if(strpos($url, 'http://') === 0) {
        $url = str_replace('http://', 'https://', $url);
    }
    
    // ডাইনামিক রেফারার (যে সাইট থেকে ছবি আসছে, তাকে ধোকা দেওয়ার জন্য সেই সাইটেরই নাম ব্যবহার করবে)
    $parsedUrl = parse_url($url);
    $host = $parsedUrl['host'] ?? 'addabaji.com';
    $dynamicReferer = $parsedUrl['scheme'] . '://' . $host . '/';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_ENCODING, ''); // GZIP সাপোর্ট
    
    // Powerful Browser Spoofing
    curl_setopt($ch, CURLOPT_REFERER, $dynamicReferer);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8",
        "Accept-Language: en-US,en;q=0.9",
        "Connection: keep-alive"
    ]);
    
    $image = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    // সফলভাবে ছবি পেলে দেখাবে
    if ($httpCode == 200 && $image) {
        header("Content-Type: $contentType");
        header("Cache-Control: max-age=2592000, public"); // ৩০ দিন ক্যাশে থাকবে
        echo $image;
        exit;
    }
}

// ডাটাবেসে ছবি NULL থাকলে বা প্রক্সি ফেইল করলে প্রিমিয়াম ক্যাসিনো ইমেজ দেখাবে
header("Location: https://images.unsplash.com/photo-1596838132731-3301c3fd4317?q=80&w=500&auto=format&fit=crop");
exit;
?>