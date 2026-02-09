<?php
// admin/save_plan.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ตรวจสอบค่า ID ให้ละเอียดขึ้น (รองรับกรณี ID เป็น 0)
    $pla_id = (isset($_POST['pla_id']) && $_POST['pla_id'] !== '') ? $_POST['pla_id'] : null;
    
    $pla_code = trim($_POST['pla_code']);
    $pla_name = trim($_POST['pla_name']);
    $cla_id = empty($_POST['cla_id']) ? null : $_POST['cla_id'];
    
    // รับค่าปีการศึกษาและภาคเรียน
    $pla_start_year = $_POST['pla_start_year']; 
    $pla_semester = $_POST['pla_semester'];     

    try {
        if ($pla_id !== null) {
            // Update (แก้ไขแผนเดิม)
            $sql = "UPDATE study_plans SET pla_code=?, pla_name=?, cla_id=?, pla_start_year=?, pla_semester=? WHERE pla_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$pla_code, $pla_name, $cla_id, $pla_start_year, $pla_semester, $pla_id]);
            $_SESSION['success'] = "แก้ไขแผนการเรียนเรียบร้อยแล้ว";
        } else {
            // Insert (สร้างแผนใหม่)
            $sql = "INSERT INTO study_plans (pla_code, pla_name, cla_id, pla_start_year, pla_semester) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$pla_code, $pla_name, $cla_id, $pla_start_year, $pla_semester]);
            $_SESSION['success'] = "เพิ่มแผนการเรียนใหม่เรียบร้อยแล้ว";
        }
        
        header("Location: manage_plans.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        header("Location: manage_plans.php");
        exit();
    }
}
?>