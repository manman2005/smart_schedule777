<?php
// api_clear_schedule.php - ลบตารางสอนทั้งหมดตามปี/เทอมที่ระบุ
define('IS_API', true);

error_reporting(0);
ini_set('display_errors', 0);

ob_start();
header('Content-Type: application/json');

try {
    require_once '../config/db.php';
    ob_clean();
    
    require_once '../includes/auth.php';
    ob_clean();

    // ตรวจสอบสิทธิ์
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Unauthorized: กรุณาเข้าสู่ระบบใหม่');
    }

    // รับข้อมูลจาก POST (JSON body)
    $input = json_decode(file_get_contents('php://input'), true);
    $year = $input['year'] ?? '';
    $semester = $input['semester'] ?? '';

    if (!$year || !$semester) {
        throw new Exception('Missing parameters: ระบุปีและเทอมไม่ครบถ้วน');
    }

    // ลบข้อมูลตารางสอนทั้งหมดของปี/เทอมที่ระบุ
    $sql = "DELETE FROM schedule WHERE sch_academic_year = ? AND sch_semester = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$year, $semester]);
    $deletedRows = $stmt->rowCount();

    ob_clean();
    echo json_encode([
        'status' => 'success',
        'message' => "ลบตารางสอนเรียบร้อยแล้ว ($deletedRows รายการ)",
        'deleted_count' => $deletedRows
    ]);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
exit;
?>