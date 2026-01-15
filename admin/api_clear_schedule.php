<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/auth.php';

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// รับข้อมูล JSON
$input = json_decode(file_get_contents('php://input'), true);
$year = $input['year'] ?? '';
$semester = $input['semester'] ?? '';

if (!$year || !$semester) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลปีการศึกษาหรือภาคเรียนไม่ครบถ้วน']);
    exit;
}

try {
    // ลบข้อมูลตามปีและเทอมที่ระบุ
    $sql = "DELETE FROM schedule WHERE sch_academic_year = ? AND sch_semester = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$year, $semester]);
    
    $count = $stmt->rowCount();

    if ($count > 0) {
        echo json_encode(['status' => 'success', 'message' => "ลบข้อมูลเรียบร้อยแล้วจำนวน $count รายการ"]);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'ไม่พบข้อมูลตารางเรียนในช่วงเวลานี้ (ข้อมูลว่างเปล่าอยู่แล้ว)']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>