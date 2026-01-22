<?php
// ประกาศว่าไฟล์นี้คือ API (ต้องอยู่บรรทัดบนสุด)
define('IS_API', true);

// ปิด Error หน้าเว็บเพื่อป้องกัน HTML แทรก
error_reporting(0);
ini_set('display_errors', 0);

// เริ่มเก็บ Buffer
ob_start();

header('Content-Type: application/json');

try {
    require_once '../config/db.php';
    // ล้างค่าขยะที่อาจติดมาจาก db.php
    ob_clean(); 
    
    require_once '../includes/auth.php';
    // ล้างค่าขยะที่อาจติดมาจาก auth.php
    ob_clean();

    // ตรวจสอบสิทธิ์
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Unauthorized: กรุณาเข้าสู่ระบบใหม่');
    }

    $year = $_GET['year'] ?? '';
    $semester = $_GET['semester'] ?? '';

    if (!$year || !$semester) {
        throw new Exception('Missing parameters: ระบุปีและเทอมไม่ครบถ้วน');
    }

    // --- Query Data ---
    $sql_tasks = "SELECT ps.pla_id, ps.sub_id, ps.tea_id, t.tea_fullname, 
                         s.sub_code, s.sub_name, s.sub_hours, s.sub_th_pr_ot, 
                         s.sub_room_theory, s.sub_room_practice, 
                         p.cla_id, c.cla_name, c.cla_year, c.cla_group_no 
                  FROM plan_subjects ps
                  JOIN study_plans p ON ps.pla_id = p.pla_id
                  JOIN subjects s ON ps.sub_id = s.sub_id
                  JOIN class_groups c ON p.cla_id = c.cla_id
                  LEFT JOIN teachers t ON ps.tea_id = t.tea_id 
                  WHERE ps.pls_academic_year = ? AND ps.pls_semester = ?";
    
    $stmt = $pdo->prepare($sql_tasks);
    $stmt->execute([$year, $semester]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $rooms = $pdo->query("SELECT roo_id, roo_name, roo_type, roo_capacity FROM rooms")->fetchAll(PDO::FETCH_ASSOC);
    $times = $pdo->query("SELECT tim_id, tim_start, tim_end, tim_range FROM time_slots ORDER BY tim_start ASC")->fetchAll(PDO::FETCH_ASSOC);
    $days = [1, 2, 3, 4, 5];
    $busy_slots = $pdo->query("SELECT tea_id, day_id, tim_id FROM teacher_unavailability")->fetchAll(PDO::FETCH_ASSOC);

    // ล้าง Buffer ครั้งสุดท้ายก่อนส่ง JSON จริง
    ob_clean();

    echo json_encode([
        'tasks' => $tasks,
        'rooms' => $rooms,
        'times' => $times,
        'days'  => $days,
        'busy_slots' => $busy_slots
    ]);

} catch (Exception $e) {
    ob_clean(); // ล้าง Buffer ก่อนส่ง Error
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
exit; // จบการทำงานทันที
?>