<?php
// admin/delete_class_group.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM class_groups WHERE cla_id = ?");
        $stmt->execute([$_GET['id']]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "ลบกลุ่มเรียนเรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "ไม่พบข้อมูลที่ต้องการลบ";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $_SESSION['error'] = "ไม่สามารถลบกลุ่มเรียนนี้ได้ เนื่องจากมีนักเรียนหรือข้อมูลอื่นเชื่อมโยงอยู่";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
header("Location: manage_class_groups.php");
exit();
?>