<?php
// htdocs/admin/api_scheduler_data.php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/auth.php';

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$year = $_GET['year'] ?? '';
$semester = $_GET['semester'] ?? '';

if (!$year || !$semester) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

try {
    // 1. ดึงรายวิชาที่ต้องเรียนในเทอมนี้
    // [แก้ไข] เพิ่ม t.tea_fullname, c.cla_year, c.cla_group_no
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

    // 2. ดึงข้อมูลห้องเรียนทั้งหมด
    $rooms = $pdo->query("SELECT roo_id, roo_name, roo_type, roo_capacity FROM rooms")->fetchAll(PDO::FETCH_ASSOC);

    // 3. ดึงข้อมูลคาบเรียน
    $times = $pdo->query("SELECT tim_id, tim_start, tim_end, tim_range FROM time_slots ORDER BY tim_start ASC")->fetchAll(PDO::FETCH_ASSOC);

    // 4. ดึงวัน (จันทร์-ศุกร์)
    $days = [1, 2, 3, 4, 5];

    // 5. [NEW] ดึงข้อมูลเวลาที่ไม่สะดวกของครู
    $busy_slots = $pdo->query("SELECT tea_id, day_id, tim_id FROM teacher_unavailability")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'tasks' => $tasks,
        'rooms' => $rooms,
        'times' => $times,
        'days'  => $days,
        'busy_slots' => $busy_slots
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>