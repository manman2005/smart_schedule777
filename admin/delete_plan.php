<?php
// admin/delete_student.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // ลองลบข้อมูล
        $stmt = $pdo->prepare("DELETE FROM students WHERE stu_id = ?");
        $stmt->execute([$id]);

        // เช็คว่ามีแถวถูกลบจริงหรือไม่
        if ($stmt->rowCount() > 0) {
            header("Location: manage_students.php?status=deleted");
        } else {
            // ถ้า SQL ผ่านแต่ไม่มีอะไรถูกลบ (เช่น ID ผิด)
            header("Location: manage_students.php?status=error&msg=" . urlencode("ไม่พบข้อมูลที่ต้องการลบ"));
        }
        exit();

    } catch (PDOException $e) {
        // ดักจับ Error กรณีลบไม่ได้ (ติด Foreign Key)
        if ($e->getCode() == '23000') {
            $msg = "ไม่สามารถลบได้ เนื่องจากนักเรียนคนนี้มีข้อมูลการลงทะเบียนหรือข้อมูลอื่นในระบบ";
        } else {
            $msg = "Database Error: " . $e->getMessage();
        }
        header("Location: manage_students.php?status=error&msg=" . urlencode($msg));
        exit();
    }
} else {
    header("Location: manage_students.php");
    exit();
}
?>