<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. แก้ไขการรับค่า sub_id ให้รองรับเลข 0
    $sub_id = (isset($_POST['sub_id']) && $_POST['sub_id'] !== '') ? $_POST['sub_id'] : null;
    
    $sub_code = trim($_POST['sub_code']);
    $sub_name = trim($_POST['sub_name']);
    $sub_th_pr_ot = trim($_POST['sub_th_pr_ot']);
    $sub_hours = $_POST['sub_hours'];
    $sub_credit = $_POST['sub_credit'];
    $sug_id = empty($_POST['sug_id']) ? null : $_POST['sug_id'];
    $sub_competency = trim($_POST['sub_competency'] ?? '');
    $cur_id = empty($_POST['cur_id']) ? null : $_POST['cur_id'];
    
    // รับค่าสาขาวิชา (ถ้าส่งค่ามาเป็นค่าว่าง หรือ 'ALL' ให้เป็น NULL)
    $maj_id = !empty($_POST['maj_id']) && $_POST['maj_id'] !== 'ALL' ? $_POST['maj_id'] : null;

    // รับค่าห้องเรียน 2 ประเภท (ถ้าไม่ได้เลือก ให้บันทึกเป็น NULL)
    $sub_room_theory = !empty($_POST['sub_room_theory']) ? $_POST['sub_room_theory'] : null;
    $sub_room_practice = !empty($_POST['sub_room_practice']) ? $_POST['sub_room_practice'] : null;

    try {
        // 2. เปลี่ยนเงื่อนไข if จากเดิม if ($sub_id) เป็น if ($sub_id !== null)
        if ($sub_id !== null) {
            // Update: อัปเดตข้อมูลเดิม
            $sql = "UPDATE subjects SET 
                    sub_code=?, sub_name=?, sub_th_pr_ot=?, sub_hours=?, sub_credit=?, 
                    sug_id=?, maj_id=?, sub_competency=?, cur_id=?, 
                    sub_room_theory=?, sub_room_practice=? 
                    WHERE sub_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $sub_code, $sub_name, $sub_th_pr_ot, $sub_hours, $sub_credit, 
                $sug_id, $maj_id, $sub_competency, $cur_id, 
                $sub_room_theory, $sub_room_practice, // ค่าห้องใหม่
                $sub_id
            ]);
        } else {
            // Insert: เพิ่มข้อมูลใหม่
            $check = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE sub_code = ?");
            $check->execute([$sub_code]);
            if ($check->fetchColumn() > 0) {
                echo "<script>alert('รหัสวิชานี้มีอยู่แล้ว'); window.history.back();</script>";
                exit();
            }

            $sql = "INSERT INTO subjects 
                    (sub_code, sub_name, sub_th_pr_ot, sub_hours, sub_credit, sug_id, maj_id, sub_competency, cur_id, sub_room_theory, sub_room_practice) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $sub_code, $sub_name, $sub_th_pr_ot, $sub_hours, $sub_credit, 
                $sug_id, $maj_id, $sub_competency, $cur_id, 
                $sub_room_theory, $sub_room_practice // ค่าห้องใหม่
            ]);
        }
        header("Location: manage_subjects.php");

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>