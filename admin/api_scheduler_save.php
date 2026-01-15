<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// รับข้อมูล JSON ที่ส่งมาจาก JavaScript
$input = json_decode(file_get_contents('php://input'), true);

$year = $input['year'] ?? '';
$semester = $input['semester'] ?? '';
$schedules = $input['schedules'] ?? [];

if (!$year || !$semester) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลปีการศึกษาหรือภาคเรียนไม่ครบถ้วน']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. ล้างข้อมูลตารางสอนเดิม ของปีและเทอมนี้ทิ้งก่อน (Clear Old Data)
    // ระวัง! ลบเฉพาะ record ที่ตรงกับเงื่อนไข
    $sql_delete = "DELETE FROM schedule WHERE sch_academic_year = ? AND sch_semester = ?";
    $stmt_del = $pdo->prepare($sql_delete);
    $stmt_del->execute([$year, $semester]);

    // 2. วนลูปบันทึกข้อมูลใหม่ (Insert New Data)
    $sql_insert = "INSERT INTO schedule (cla_id, sub_id, tea_id, roo_id, day_id, tim_id, sch_hours, sch_academic_year, sch_semester) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_ins = $pdo->prepare($sql_insert);

    foreach ($schedules as $sch) {
        $stmt_ins->execute([
            $sch['cla_id'],
            $sch['sub_id'],
            $sch['tea_id'] ?: null, // ถ้าไม่มีครู ให้ใส่ NULL
            $sch['roo_id'],
            $sch['day_id'],
            $sch['tim_id'],
            $sch['sch_hours'],
            $year,
            $semester
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>