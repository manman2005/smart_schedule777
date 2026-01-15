<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM teachers WHERE tea_id = ?");
        $stmt->execute([$_GET['id']]);
        
        // ถ้าต้องการลบให้หมดจด อาจต้องไปลบในตาราง teaching_assignments ด้วย (แต่ใน DB เราตั้ง Cascade ไว้แล้วหรือไม่?)
        // จาก SQL ที่ให้มา: ON DELETE CASCADE ไว้แล้วที่ teaching_assignments สบายใจได้ครับ
        
        header("Location: manage_teachers.php");
    } catch (PDOException $e) {
        // กรณีลบไม่ได้เพราะติด Foreign Key ที่ไม่ได้ตั้ง Cascade
        echo "<script>alert('ไม่สามารถลบได้ เนื่องจากข้อมูลถูกใช้งานอยู่ในระบบ'); window.location='manage_teachers.php';</script>";
    }
} else {
    header("Location: manage_teachers.php");
}
?>