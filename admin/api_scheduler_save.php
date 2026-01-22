<?php
// htdocs/admin/api_scheduler_save.php

// 1. ตั้งค่าเริ่มต้น
define('IS_API', true);
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

try {
    // 2. เชื่อมต่อฐานข้อมูล
    require_once '../config/db.php';
    
    // ล้าง Buffer ทิ้งก่อนเริ่มงาน
    if (ob_get_length()) ob_clean();

    // 3. รับข้อมูล JSON ที่ส่งมาจาก JavaScript
    $jsonContent = file_get_contents('php://input');
    $input = json_decode($jsonContent, true);
    
    // ตรวจสอบว่ามีข้อมูลมาไหม
    if (!$input) {
        throw new Exception('ไม่พบข้อมูลที่ส่งมา (No Input Data)');
    }

    $year = $input['year'];
    $semester = $input['semester'];
    $schedules = $input['schedules']; // อาร์เรย์ตารางที่จัดเสร็จแล้ว

    if (empty($schedules)) {
        throw new Exception('ไม่มีข้อมูลตารางเรียนที่จะบันทึก');
    }

    // 4. เริ่ม Transaction (เพื่อให้แน่ใจว่าลบและเพิ่มสำเร็จพร้อมกัน)
    $pdo->beginTransaction();

    // --- Step A: ลบข้อมูลเก่าทิ้งก่อน ---
    // (เฉพาะของปีและเทอมนี้ เพื่อป้องกันข้อมูลซ้ำซ้อน)
    $sql_del = "DELETE FROM schedule WHERE sch_academic_year = ? AND sch_semester = ?";
    $stmt_del = $pdo->prepare($sql_del);
    $stmt_del->execute([$year, $semester]);

    // --- Step B: วนลูปบันทึกข้อมูลใหม่ ---
    $sql_insert = "INSERT INTO schedule 
                   (cla_id, sub_id, tea_id, roo_id, day_id, tim_id, sch_academic_year, sch_semester, sch_hours) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $pdo->prepare($sql_insert);

    $count = 0;
    foreach ($schedules as $sch) {
        // ตรวจสอบค่าว่าง (ถ้าไม่มีให้ใส่ 0 หรือค่า Default)
        $cla_id = $sch['cla_id'];
        $sub_id = $sch['sub_id'];
        $tea_id = !empty($sch['tea_id']) ? $sch['tea_id'] : 0;
        $roo_id = !empty($sch['roo_id']) ? $sch['roo_id'] : '';
        $day_id = $sch['day_id'];
        $tim_id = $sch['tim_id'];
        $hours  = !empty($sch['sch_hours']) ? $sch['sch_hours'] : 1;

        $stmt_insert->execute([
            $cla_id, $sub_id, $tea_id, $roo_id, $day_id, $tim_id, 
            $year, $semester, $hours
        ]);
        $count++;
    }

    // 5. ยืนยันการบันทึก (Commit)
    $pdo->commit();

    // ส่งผลลัพธ์กลับ
    echo json_encode([
        'status' => 'success', 
        'message' => "บันทึกข้อมูลเรียบร้อยแล้ว จำนวน $count รายการ"
    ]);

} catch (Exception $e) {
    // ถ้ามี Error ให้ยกเลิกการบันทึก (Rollback)
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    if (ob_get_length()) ob_clean();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

exit;
?>