<?php
require_once __DIR__ . '/../includes/helpers.php';
$host = 'localhost';
$dbname = 'schedule';
$username = 'root'; 
$password = ''; 

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci" 
    ];

    $hosts = ['127.0.0.1', 'localhost'];
    $ports = [3306, 3307];
    $connected = false;
    $last_error = null;
    foreach ($hosts as $h) {
        foreach ($ports as $p) {
            try {
                $pdo = new PDO("mysql:host={$h};port={$p};dbname={$dbname};charset=utf8mb4", $username, $password, $options);
                $connected = true;
                $host = $h;
                $port = $p;
                break 2;
            } catch (PDOException $e) {
                $last_error = $e;
            }
        }
    }
    if (!$connected) {
        throw $last_error ?? new PDOException('Unable to connect to MySQL');
    }

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Bangkok');
?>
