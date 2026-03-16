<?php
// error_reporting অফ রাখা হয়েছে যেন কোনো ওয়ার্নিং ইমেজের ডেটা নষ্ট না করে
error_reporting(0);

if (isset($_GET['url']) && !empty($_GET['url'])) {
    $url = urldecode($_GET['url']);
    
    // URL যদি শুধু // দিয়ে শুরু হয়, তাহলে শুরুতে https যুক্ত করা
    if (strpos($url, '//') === 0) {
        $url = 'https:' . $url;
    }
    
    $url = str_replace(' ', '%20', $url);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    // Anti-Hotlink Bypass Headers (প্রোভাইডারকে ধোঁকা দেওয়ার জন্য)
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_REFERER, 'https://google.com/'); 
    
    $image = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    // ছবি সফলভাবে পেলে আউটপুট দিবে
    if ($httpCode == 200 && $image) {
        if (empty($contentType)) {
            $contentType = 'image/jpeg';
        }
        header("Content-Type: $contentType");
        header("Cache-Control: max-age=2592000, public");
        echo $image;
        exit;
    }
}

// কোনো কারণে ছবি না পেলে এই ডিফল্ট ছবিটি দেখাবে
header("Location: https://images.unsplash.com/photo-1596838132731-3301c3fd4317?q=80&w=500&auto=format&fit=crop");
exit;
?>