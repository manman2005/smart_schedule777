<?php
// htdocs/admin/api_scheduler_data.php

// 1. กำหนดค่าเริ่มต้น และ Header
define('IS_API', true);
ob_start(); // เริ่มเก็บ Buffer ทันที
error_reporting(0); // ปิด Error หน้าเว็บ (แต่ log ยังอยู่)
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

try {
    // 2. เชื่อมต่อฐานข้อมูล
    require_once '../config/db.php';

    // (Optional) ตรวจสอบสิทธิ์ Admin 
    // require_once '../includes/auth.php'; 
    // if (!isset($_SESSION['user_id'])) { throw new Exception('Unauthorized'); }

    // *** ล้าง Buffer ทิ้ง! (สำคัญมาก) ***
    // เพื่อกำจัดช่องว่าง หรือ Warning ที่อาจติดมาจากไฟล์ db.php
    if (ob_get_length())
        ob_clean();

    // 3. รับค่าตัวแปร (ปีการศึกษา/เทอม)
    $year = $_GET['year'] ?? 2569;
    $semester = $_GET['semester'] ?? 1;

    // ถ้าไม่มีการส่งค่ามา ให้ลองดึงจากตาราง Setting (ถ้ามี)
    if (!isset($_GET['year'])) {
        try {
            $stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'current_academic_year'");
            if ($row = $stmt->fetch())
                $year = $row['setting_value'];

            $stmt = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'current_semester'");
            if ($row = $stmt->fetch())
                $semester = $row['setting_value'];
        }
        catch (Exception $ex) {
        // ถ้าไม่เจอ table setting ก็ใช้ค่า default ต่อไป ไม่ต้อง error
        }
    }

    // --- ส่วนดึงข้อมูล (Query) ---

    // 4. ดึงรายวิชา (Tasks)
    // join ตารางต่างๆ เพื่อให้ได้ชื่อครู ชื่อวิชา และกลุ่มเรียน
    $sql = "SELECT ps.*, 
                   t.tea_fullname, 
                   s.sub_code, s.sub_name, s.sub_hours, s.sub_th_pr_ot, 
                   s.sub_room_theory, s.sub_room_practice, s.sub_preferred_room_type,
                   c.cla_id, c.cla_name, c.cla_year, c.cla_group_no
            FROM plan_subjects ps
            JOIN subjects s ON ps.sub_id = s.sub_id
            JOIN study_plans p ON ps.pla_id = p.pla_id
            JOIN study_plan_classes spc ON p.pla_id = spc.pla_id
            JOIN class_groups c ON spc.cla_id = c.cla_id
            LEFT JOIN teachers t ON ps.tea_id = t.tea_id 
            WHERE ps.pls_academic_year = ? AND ps.pls_semester = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$year, $semester]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. ดึงห้องเรียน (Rooms)
    // ดึงมาทั้งหมด (*) เพื่อให้มั่นใจว่าได้ฟิลด์ roo_type แน่นอน
    $rooms = $pdo->query("SELECT * FROM rooms")->fetchAll(PDO::FETCH_ASSOC);

    // 6. ดึงเวลาเรียน (Time Slots)
    $times = $pdo->query("SELECT * FROM time_slots ORDER BY tim_start ASC")->fetchAll(PDO::FETCH_ASSOC);

    // วนลูปสร้าง tim_range เอง (เช่น "08:00-09:00") เพื่อป้องกันปัญหาถ้าใน DB ไม่มีคอลัมน์นี้
    foreach ($times as &$t) {
        $start = substr($t['tim_start'], 0, 5); // ตัดเอาแค่ 08:00
        $end = substr($t['tim_end'], 0, 5);
        $t['tim_range'] = "$start-$end";
    }

    // 7. ข้อมูลประกอบอื่นๆ (วัน, เวลาครูไม่ว่าง)
    $days = [1, 2, 3, 4, 5]; // จันทร์-ศุกร์

    // ตรวจสอบว่ามีตาราง teacher_unavailability หรือไม่
    try {
        $busy = $pdo->query("SELECT tea_id, day_id, tim_id FROM teacher_unavailability")->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (Exception $e) {
        $busy = []; // ถ้าไม่มีตารางนี้ ให้ส่ง array ว่างไป
    }

    // 8. ส่ง JSON กลับ
    echo json_encode([
        'tasks' => $tasks,
        'rooms' => $rooms,
        'times' => $times,
        'days' => $days,
        'busy_slots' => $busy
    ]);

}
catch (Exception $e) {
    // ถ้าพัง ให้ล้าง buffer อีกรอบ แล้วส่ง Error เป็น JSON
    if (ob_get_length())
        ob_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

// จบการทำงานทันที
exit;
?>