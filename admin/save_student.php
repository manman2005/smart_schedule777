<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stu_id = $_POST['stu_id'] ?? null;
    $stu_fullname = trim($_POST['stu_fullname']);
    $cla_id = $_POST['cla_id'];
    $stu_gender = $_POST['stu_gender'];
    $stu_username = trim($_POST['stu_username']);
    $stu_password = $_POST['stu_password'];

    try {
        if ($stu_id) {
            // --- กรณีแก้ไข (Update) : ไม่ต้องแก้ Logic เดิม ---
            $sql = "UPDATE students SET stu_fullname=?, cla_id=?, stu_gender=?, stu_username=? WHERE stu_id=?";
            $params = [$stu_fullname, $cla_id, $stu_gender, $stu_username, $stu_id];
            
            if (!empty($stu_password)) {
                $hash = password_hash($stu_password, PASSWORD_DEFAULT);
                $sql = "UPDATE students SET stu_fullname=?, cla_id=?, stu_gender=?, stu_username=?, stu_password=? WHERE stu_id=?";
                $params = [$stu_fullname, $cla_id, $stu_gender, $stu_username, $hash, $stu_id];
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $_SESSION['success'] = "อัปเดตข้อมูลนักเรียนเรียบร้อยแล้ว";

        } else {
            // --- กรณีเพิ่มใหม่ (Insert) : ต้องสร้าง ID เองเพราะไม่มี Trigger ---
            
            // 1. ตรวจสอบ username ซ้ำ
            $chk = $pdo->prepare("SELECT COUNT(*) FROM students WHERE stu_username = ?");
            $chk->execute([$stu_username]);
            if($chk->fetchColumn() > 0){
                $_SESSION['error'] = "Username นี้ถูกใช้งานแล้ว";
                header("Location: manage_student_form.php");
                exit();
            }

            // 2. [เพิ่มใหม่] คำนวณรหัสนักเรียน (Running Number)
            // ค้นหารหัสล่าสุดในกลุ่มเรียนนี้ (เช่น 6802...001, 6802...002)
            $sql_max = "SELECT stu_id FROM students WHERE stu_id LIKE ? ORDER BY stu_id DESC LIMIT 1";
            $stmt_max = $pdo->prepare($sql_max);
            $stmt_max->execute([$cla_id . '%']); // ค้นหาที่ขึ้นต้นด้วยรหัสกลุ่ม
            $last_id = $stmt_max->fetchColumn();

            if ($last_id) {
                // ถ้ามีแล้ว ให้ตัด 3 ตัวท้ายมาบวก 1
                $running_no = intval(substr($last_id, -3)) + 1;
            } else {
                // ถ้ายังไม่มี ให้เริ่มที่ 1
                $running_no = 1;
            }
            
            // สร้างรหัสใหม่ (รหัสกลุ่ม + เลขลำดับ 3 หลัก)
            $new_stu_id = $cla_id . str_pad($running_no, 3, '0', STR_PAD_LEFT);


            // 3. บันทึกข้อมูล (เพิ่ม stu_id เข้าไปใน Insert)
            $hash = password_hash($stu_password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO students (stu_id, stu_fullname, cla_id, stu_gender, stu_username, stu_password) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$new_stu_id, $stu_fullname, $cla_id, $stu_gender, $stu_username, $hash]);
            
            $_SESSION['success'] = "เพิ่มข้อมูลนักเรียนใหม่เรียบร้อยแล้ว (รหัส: $new_stu_id)";
        }

        header("Location: manage_students.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "ระบบเกิดข้อผิดพลาด: " . $e->getMessage();
        header("Location: manage_students.php");
        exit();
    }
} else {
    header("Location: manage_students.php");
    exit();
}
?>