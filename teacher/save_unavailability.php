<?php
// htdocs/teacher/save_unavailability.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkTeacher();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tea_id = $_SESSION['user_id'];
    $busy_slots = $_POST['busy_slots'] ?? []; // รับค่า array ของช่องที่ไม่ว่าง

    try {
        $pdo->beginTransaction();

        // 1. ลบข้อมูลเก่าของอาจารย์คนนี้ออกก่อน (เพื่อบันทึกใหม่ทั้งหมดตามที่เลือก)
        $stmt_del = $pdo->prepare("DELETE FROM teacher_unavailability WHERE tea_id = ?");
        $stmt_del->execute([$tea_id]);

        // 2. วนลูปบันทึกข้อมูลใหม่
        if (!empty($busy_slots)) {
            $stmt_ins = $pdo->prepare("INSERT INTO teacher_unavailability (tea_id, day_id, tim_id) VALUES (?, ?, ?)");
            foreach ($busy_slots as $slot_str) {
                // ค่าที่ส่งมาจะอยู่ในรูปแบบ "day_id-tim_id" เช่น "1-5"
                list($day_id, $tim_id) = explode('-', $slot_str);
                $stmt_ins->execute([$tea_id, $day_id, $tim_id]);
            }
        }

        $pdo->commit();
        header("Location: unavailability.php?status=success");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>