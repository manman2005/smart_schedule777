<?php
// admin/delete_subject.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE sub_id = ?");
        $stmt->execute([$_GET['id']]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "ลบข้อมูลรายวิชาเรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "ไม่พบข้อมูลที่ต้องการลบ";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $_SESSION['error'] = "ไม่สามารถลบได้ วิชานี้ถูกใช้งานในตารางสอนหรือแผนการเรียนแล้ว";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
header("Location: manage_subjects.php");
exit();
?>