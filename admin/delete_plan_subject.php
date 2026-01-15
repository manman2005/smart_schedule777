<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id']) && isset($_GET['pla_id'])) {
    $pls_id = $_GET['id'];
    $pla_id = $_GET['pla_id'];

    // ลบเฉพาะแถวที่มี pls_id ตรงกัน (จะทำงานถูกต้องเมื่อแก้ DB แล้ว)
    $sql = "DELETE FROM plan_subjects WHERE pls_id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$pls_id])) {
        header("Location: manage_plan_subjects.php?pla_id=" . $pla_id);
    } else {
        echo "<script>alert('ลบไม่สำเร็จ'); window.history.back();</script>";
    }
} else {
    header("Location: manage_plans.php");
}
?>