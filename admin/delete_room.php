<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE roo_id = ?");
        $stmt->execute([$_GET['id']]);
        header("Location: manage_rooms.php");
    } catch (PDOException $e) {
        echo "<script>alert('ไม่สามารถลบห้องนี้ได้ เนื่องจากมีการใช้งานในตารางสอนแล้ว'); window.location='manage_rooms.php';</script>";
    }
} else {
    header("Location: manage_rooms.php");
}
?>