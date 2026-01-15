<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM time_slots WHERE tim_id = ?");
        $stmt->execute([$_GET['id']]);
    } catch (PDOException $e) {
        // กรณีลบไม่ได้ (เช่น ถูกใช้งานในตารางสอนแล้ว)
        echo "<script>
            alert('ไม่สามารถลบช่วงเวลานี้ได้ เนื่องจากมีการใช้งานในตารางสอนแล้ว'); 
            window.location='manage_time_slots.php';
        </script>";
        exit();
    }
}

header("Location: manage_time_slots.php");
exit();
?>