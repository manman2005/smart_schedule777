<?php
$host = 'sql211.infinityfree.com';
$dbname = 'if0_40693934_schedule';
$username = 'if0_40693934';
$password = 'FABYtPKCRAGLi';

// เริ่ม Session ถ้ายังไม่ได้เริ่ม
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci"
    ];
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
} catch (PDOException $e) {
    // ถ้าเรียกจาก API ให้ส่ง JSON Error แทนข้อความธรรมดา
    if (defined('IS_API')) {
        http_response_code(500);
        echo json_encode(['error' => 'Database Connection Failed: ' . $e->getMessage()]);
        exit;
    } else {
        die("Connection failed: " . $e->getMessage());
    }
}
date_default_timezone_set('Asia/Bangkok');
// จบไฟล์แค่นี้