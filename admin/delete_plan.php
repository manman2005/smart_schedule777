<?php
// admin/delete_plan.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // ลบรายวิชาในแผนก่อน (plan_subjects)
        $stmt = $pdo->prepare("DELETE FROM plan_subjects WHERE pla_id = ?");
        $stmt->execute([$id]);

        // ลบกลุ่มเรียนที่ผูกกับแผน (study_plan_classes)
        $stmt = $pdo->prepare("DELETE FROM study_plan_classes WHERE pla_id = ?");
        $stmt->execute([$id]);

        // ลบแผนการเรียน
        $stmt = $pdo->prepare("DELETE FROM study_plans WHERE pla_id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "ลบแผนการเรียนเรียบร้อยแล้ว";
        }
        else {
            $_SESSION['error'] = "ไม่พบข้อมูลที่ต้องการลบ";
        }

    }
    catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $_SESSION['error'] = "ไม่สามารถลบได้ เนื่องจากแผนการเรียนนี้ถูกใช้งานอยู่ในระบบ";
        }
        else {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
header("Location: manage_plans.php");
exit();
?>