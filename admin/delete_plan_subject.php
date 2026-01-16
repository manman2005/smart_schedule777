<?php
// เริ่ม Buffer เพื่อดักจับข้อความขยะที่อาจหลุดออกมา
ob_start();

require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

// ล้างค่า Buffer ก่อนส่ง JSON (สำคัญมาก! ช่วยแก้ปัญหาหน้าจอกระตุก/ไม่เปลี่ยน)
ob_clean();
header('Content-Type: application/json');

if (isset($_GET['id']) && isset($_GET['pla_id'])) {
    $pls_id = $_GET['id'];
    $pla_id = $_GET['pla_id'];

    try {
        $sql = "DELETE FROM plan_subjects WHERE pls_id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$pls_id]);
        
        if ($result) {
            if (isset($_GET['ajax'])) {
                echo json_encode(['status' => 'success', 'message' => 'ลบรายวิชาเรียบร้อยแล้ว']);
                exit();
            }
            header("Location: manage_plan_subjects.php?pla_id=" . $pla_id);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ลบไม่สำเร็จ']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
}

// จบการทำงานทันทีเพื่อป้องกัน HTML อื่นหลุดติดไป
exit(); 
?>