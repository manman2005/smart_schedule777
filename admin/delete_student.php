<?php
// admin/delete_student.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE stu_id = ?");
        $stmt->execute([$_GET['id']]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "ลบข้อมูลนักเรียนเรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "ไม่พบข้อมูลที่ต้องการลบ";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $_SESSION['error'] = "ไม่สามารถลบได้ เนื่องจากนักเรียนมีข้อมูลที่เกี่ยวข้องในระบบ";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
header("Location: manage_students.php");
exit();
?>