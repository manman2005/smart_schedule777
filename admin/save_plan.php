<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // แก้ไขจุดที่ 1: ตรวจสอบค่า ID ให้ละเอียดขึ้น (รองรับกรณี ID เป็น 0)
    // ถ้า $_POST['pla_id'] ถูกส่งมาและไม่ใช่ค่าว่าง ('') ให้ถือว่ามีค่า
    $pla_id = (isset($_POST['pla_id']) && $_POST['pla_id'] !== '') ? $_POST['pla_id'] : null;
    
    $pla_code = trim($_POST['pla_code']);
    $pla_name = trim($_POST['pla_name']);
    $cla_id = empty($_POST['cla_id']) ? null : $_POST['cla_id'];
    
    // รับค่าปีการศึกษาและภาคเรียน
    $pla_start_year = $_POST['pla_start_year']; 
    $pla_semester = $_POST['pla_semester'];     

    try {
        // แก้ไขจุดที่ 2: เปลี่ยนเงื่อนไข if จากเดิม if ($pla_id) เป็น if ($pla_id !== null)
        if ($pla_id !== null) {
            // Update (แก้ไขแผนเดิม)
            $sql = "UPDATE study_plans SET pla_code=?, pla_name=?, cla_id=?, pla_start_year=?, pla_semester=? WHERE pla_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$pla_code, $pla_name, $cla_id, $pla_start_year, $pla_semester, $pla_id]);
        } else {
            // Insert (สร้างแผนใหม่)
            $sql = "INSERT INTO study_plans (pla_code, pla_name, cla_id, pla_start_year, pla_semester) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$pla_code, $pla_name, $cla_id, $pla_start_year, $pla_semester]);
        }
        
        // บันทึกเสร็จแล้วกลับไปหน้ารายการ
        header("Location: manage_plans.php");
        exit();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>