<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE sub_id = ?");
        $stmt->execute([$_GET['id']]);
        header("Location: manage_subjects.php");
    } catch (PDOException $e) {
        echo "<script>alert('ลบไม่ได้: วิชานี้ถูกใช้งานในตารางสอนหรือแผนการเรียนแล้ว'); window.location='manage_subjects.php';</script>";
    }
} else {
    header("Location: manage_subjects.php");
}
?>