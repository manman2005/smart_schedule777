<?php
ob_start(); // เริ่ม Buffer

require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

$is_ajax = isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

if ($is_ajax) { 
    ob_clean(); // ล้างขยะก่อนส่ง JSON
    header('Content-Type: application/json'); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pla_id = $_POST['pla_id'];
    
    // รับค่าครูแบบ Global (ถ้าไม่ได้เลือกคือ NULL = รอเลือกครู)
    $tea_id_global = empty($_POST['tea_id']) ? null : $_POST['tea_id'];
    
    // ไม่รับ tea_ids_individual แล้ว (ตัดออก)

    $sub_ids = isset($_POST['sub_ids']) ? $_POST['sub_ids'] : [];
    $filter_sug_context = $_POST['filter_sug_context'] ?? '';
    $pls_note = ($filter_sug_context == '6') ? 'free_elective' : null;

    if (empty($sub_ids)) {
        if ($is_ajax) { echo json_encode(['status' => 'error', 'message' => 'กรุณาเลือกอย่างน้อย 1 วิชา']); exit; }
        echo "<script>alert('กรุณาเลือกรายวิชา'); window.history.back();</script>"; exit();
    }

    $stmtPlan = $pdo->prepare("SELECT pla_start_year, pla_semester FROM study_plans WHERE pla_id = ?");
    $stmtPlan->execute([$pla_id]);
    $planInfo = $stmtPlan->fetch();
    
    if (!$planInfo) {
        if ($is_ajax) { echo json_encode(['status' => 'error', 'message' => 'ไม่พบแผนการเรียน']); exit; }
        exit();
    }
    
    $pls_semester = $planInfo['pla_semester'];
    $pls_academic_year = $planInfo['pla_start_year'];

    try {
        $pdo->beginTransaction();
        
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM plan_subjects WHERE pla_id=? AND sub_id=? AND pls_semester=? AND pls_academic_year=?");
        $sql = "INSERT INTO plan_subjects (pla_id, sub_id, pls_semester, pls_academic_year, tea_id, pls_note) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        $insertedCount = 0;

        foreach ($sub_ids as $sub_id) {
            $checkStmt->execute([$pla_id, $sub_id, $pls_semester, $pls_academic_year]);
            if ($checkStmt->fetchColumn() > 0) continue; 

            // ใช้ค่า $tea_id_global อย่างเดียว (ถ้าไม่เลือกก็เป็น NULL ตามต้องการ)
            $stmt->execute([$pla_id, $sub_id, $pls_semester, $pls_academic_year, $tea_id_global, $pls_note]);
            $insertedCount++;
        }

        $pdo->commit();
        
        if ($is_ajax) {
            echo json_encode(['status' => 'success', 'count' => $insertedCount]);
            exit;
        }
        header("Location: manage_plan_subjects.php?pla_id=$pla_id");

    } catch (PDOException $e) {
        $pdo->rollBack();
        if ($is_ajax) { echo json_encode(['status' => 'error', 'message' => $e->getMessage()]); exit; }
        echo "Error: " . $e->getMessage();
    }
}
?>