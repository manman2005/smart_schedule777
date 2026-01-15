<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE stu_id = ?");
        $stmt->execute([$_GET['id']]);
        header("Location: manage_students.php");
    } catch (PDOException $e) {
        echo "<script>alert('ลบไม่ได้: เกิดข้อผิดพลาด'); window.location='manage_students.php';</script>";
    }
} else {
    header("Location: manage_students.php");
}
?>