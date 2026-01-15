<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. รับค่า ID แผนการเรียน และ ครูผู้สอน
    $pla_id = $_POST['pla_id'];
    $tea_id = empty($_POST['tea_id']) ? null : $_POST['tea_id'];
    $sub_ids = isset($_POST['sub_ids']) ? $_POST['sub_ids'] : [];

    // *** รับค่า context ว่ามาจากหน้าจอเลือกเสรีหรือไม่ ***
    $filter_sug_context = $_POST['filter_sug_context'] ?? '';
    
    // ถ้า context เป็น 6 ให้บันทึก note ว่า 'free_elective'
    $pls_note = ($filter_sug_context == '6') ? 'free_elective' : null;

    // 3. ดึงข้อมูล ปีการศึกษา และ เทอม
    $stmtPlan = $pdo->prepare("SELECT pla_start_year, pla_semester FROM study_plans WHERE pla_id = ?");
    $stmtPlan->execute([$pla_id]);
    $planInfo = $stmtPlan->fetch();
    
    if (!$planInfo) {
        echo "<script>alert('ไม่พบข้อมูลแผนการเรียน'); window.history.back();</script>";
        exit();
    }

    $pls_semester = $planInfo['pla_semester'];
    $pls_academic_year = $planInfo['pla_start_year'];

    if (empty($sub_ids)) {
        echo "<script>alert('กรุณาติ๊กเลือกรายวิชาอย่างน้อย 1 วิชา'); window.history.back();</script>";
        exit();
    }

    try {
        $pdo->beginTransaction();
        
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM plan_subjects WHERE pla_id=? AND sub_id=? AND pls_semester=? AND pls_academic_year=?");
        
        // *** เพิ่ม pls_note ในคำสั่ง INSERT ***
        $sql = "INSERT INTO plan_subjects (pla_id, sub_id, pls_semester, pls_academic_year, tea_id, pls_note) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        $insertedCount = 0;

        foreach ($sub_ids as $sub_id) {
            $checkStmt->execute([$pla_id, $sub_id, $pls_semester, $pls_academic_year]);
            if ($checkStmt->fetchColumn() > 0) {
                continue; 
            }

            // บันทึกพร้อม pls_note
            $stmt->execute([$pla_id, $sub_id, $pls_semester, $pls_academic_year, $tea_id, $pls_note]);
            $insertedCount++;
        }

        $pdo->commit();
        
        if ($insertedCount > 0) {
            header("Location: manage_plan_subjects.php?pla_id=$pla_id");
        } else {
            echo "<script>alert('ไม่มีการบันทึกเพิ่ม (รายวิชาที่เลือกมีอยู่ในแผนนี้แล้ว)'); window.location='manage_plan_subjects.php?pla_id=$pla_id';</script>";
        }

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: manage_plans.php");
    exit();
}
?>