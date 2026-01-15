<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mode = $_POST['mode'];
    $roo_id = trim($_POST['roo_id']);
    $roo_name = trim($_POST['roo_name']);
    $roo_building = trim($_POST['roo_building']);
    $roo_floor = trim($_POST['roo_floor']);
    $roo_type = $_POST['roo_type'];
    $roo_capacity = $_POST['roo_capacity'];

    try {
        if ($mode == 'insert') {
            // เช็คว่ารหัสห้องซ้ำหรือไม่
            $check = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE roo_id = ?");
            $check->execute([$roo_id]);
            if ($check->fetchColumn() > 0) {
                echo "<script>alert('รหัสห้องนี้มีอยู่แล้ว'); window.history.back();</script>";
                exit();
            }

            $sql = "INSERT INTO rooms (roo_id, roo_name, roo_building, roo_floor, roo_type, roo_capacity) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$roo_id, $roo_name, $roo_building, $roo_floor, $roo_type, $roo_capacity]);
        
        } elseif ($mode == 'update') {
            // Update ตาม Primary Key เดิม (เพราะเราไม่ให้แก้ ID)
            $original_roo_id = $_POST['original_roo_id'];
            
            $sql = "UPDATE rooms SET roo_name=?, roo_building=?, roo_floor=?, roo_type=?, roo_capacity=? 
                    WHERE roo_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$roo_name, $roo_building, $roo_floor, $roo_type, $roo_capacity, $original_roo_id]);
        }
        
        header("Location: manage_rooms.php");

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>