<?php
// ไฟล์: htdocs/test_key.php
header('Content-Type: text/html; charset=utf-8');

$apiKey = 'AIzaSyD0w5MPYifGapFlGCJkb_-ejR67xwBbSK8'; // Key เดิมของคุณ

echo "<h1>🔍 ทดสอบ API Key: $apiKey</h1>";

// ยิงไปถาม Google ว่ามี Model อะไรให้ใช้บ้าง?
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>ผลลัพธ์ (HTTP Code: $httpCode):</h3>";

if ($httpCode === 200) {
    echo "<div style='color:green; font-weight:bold;'>✅ API Key ใช้งานได้ปกติ!</div>";
    echo "<pre>" . print_r(json_decode($response, true), true) . "</pre>";
} else {
    echo "<div style='color:red; font-weight:bold;'>❌ API Key มีปัญหา!</div>";
    echo "<p>Google แจ้งว่า:</p>";
    echo "<pre style='background:#eee; padding:10px; border:1px solid #ccc;'>$response</pre>";
    echo "<hr>";
    echo "<b>วิธีแก้:</b> ต้องไปสร้าง API Key ใหม่ครับ (ดูขั้นตอนด้านล่าง)";
}
?>