<?php
// admin/delete_room.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE roo_id = ?");
        $stmt->execute([$_GET['id']]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "ลบข้อมูลห้องเรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "ไม่พบข้อมูลที่ต้องการลบ";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            $_SESSION['error'] = "ไม่สามารถลบห้องนี้ได้ เนื่องจากมีการใช้งานในตารางสอนแล้ว";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
header("Location: manage_rooms.php");
exit();
?>