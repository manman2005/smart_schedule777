<?php
$host = 'sql211.infinityfree.com';
$dbname = 'if0_40693934_schedule';
$username = 'if0_40693934'; 
$password = 'FABYtPKCRAGLi'; 

try {
    // ใช้ general_ci เพื่อให้ตรงกับฐานข้อมูลที่เราเพิ่งสร้างใหม่
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci" 
    ];

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Bangkok');
?>