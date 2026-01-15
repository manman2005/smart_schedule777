<?php
ob_start(); // เริ่ม Buffer เพื่อป้องกัน Error เรื่อง Header/Session หลุด
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';
require_once '../includes/auth.php';

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Error: Session Expired or Access Denied. (กรุณาล็อกอินใหม่)");
}

// ตรวจสอบว่าเป็น POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_teachers.php");
    exit();
}

try {
    // 1. แก้ไขการรับค่า ID ให้รองรับเลข 0
    $tea_id = (isset($_POST['tea_id']) && $_POST['tea_id'] !== '') ? $_POST['tea_id'] : null;
    
    $tea_code = trim($_POST['tea_code']);
    $tea_fullname = trim($_POST['tea_fullname']);
    $tea_username = trim($_POST['tea_username']);
    $tea_password = $_POST['tea_password'];
    $tea_phone = trim($_POST['tea_phone']);
    $tea_email = trim($_POST['tea_email']);

    // รับค่า Dropdown
    $sug_id = !empty($_POST['sug_id']) ? $_POST['sug_id'] : null;
    $typ_id = !empty($_POST['typ_id']) ? $_POST['typ_id'] : null;
    $car_id = !empty($_POST['car_id']) ? $_POST['car_id'] : null;
    $maj_id = !empty($_POST['maj_id']) ? $_POST['maj_id'] : null;

    // --- 1. เช็ค Username ซ้ำ ---
    $sqlCheck = "SELECT COUNT(*) FROM teachers WHERE tea_username = ?";
    $paramsCheck = [$tea_username];
    
    // แก้ไขเงื่อนไขตรงนี้ด้วย
    if ($tea_id !== null) {
        $sqlCheck .= " AND tea_id != ?";
        $paramsCheck[] = $tea_id;
    }
    
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute($paramsCheck);

    if ($stmtCheck->fetchColumn() > 0) {
        $_SESSION['error'] = "Username '$tea_username' นี้มีผู้ใช้งานแล้ว";
        // กลับไปหน้าฟอร์ม
        $redirect = "manage_teacher_form.php" . ($tea_id !== null ? "?id=$tea_id" : "");
        header("Location: $redirect");
        exit();
    }

    // --- 2. บันทึกข้อมูล ---
    // แก้ไขเงื่อนไขตรงนี้ เพื่อให้เลข 0 เข้าทำงานในบล็อกนี้
    if ($tea_id !== null) {
        // ============================
        // UPDATE (แก้ไข)
        // ============================
        $sql = "UPDATE teachers SET 
                tea_code = ?, tea_fullname = ?, sug_id = ?, typ_id = ?, car_id = ?, maj_id = ?,
                tea_username = ?, tea_phone = ?, tea_email = ? 
                WHERE tea_id = ?";
        $params = [$tea_code, $tea_fullname, $sug_id, $typ_id, $car_id, $maj_id, $tea_username, $tea_phone, $tea_email, $tea_id];

        // ถ้าเปลี่ยนรหัสผ่าน
        if (!empty($tea_password)) {
            $hash = password_hash($tea_password, PASSWORD_DEFAULT);
            $sql = "UPDATE teachers SET 
                    tea_code = ?, tea_fullname = ?, sug_id = ?, typ_id = ?, car_id = ?, maj_id = ?,
                    tea_username = ?, tea_phone = ?, tea_email = ?, tea_password = ? 
                    WHERE tea_id = ?";
            $params = [$tea_code, $tea_fullname, $sug_id, $typ_id, $car_id, $maj_id, $tea_username, $tea_phone, $tea_email, $hash, $tea_id];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['success'] = "อัปเดตข้อมูลครูเรียบร้อยแล้ว";

    } else {
        // ============================
        // INSERT (เพิ่มใหม่)
        // ============================
        
        // เช็ครหัสครูซ้ำ (เฉพาะตอนเพิ่มใหม่)
        $chkCode = $pdo->prepare("SELECT COUNT(*) FROM teachers WHERE tea_code = ?");
        $chkCode->execute([$tea_code]);
        if($chkCode->fetchColumn() > 0){
             $_SESSION['error'] = "รหัสครู '$tea_code' มีอยู่ในระบบแล้ว";
             header("Location: manage_teacher_form.php");
             exit();
        }

        $hash = password_hash($tea_password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO teachers (tea_code, tea_fullname, sug_id, typ_id, car_id, maj_id, tea_username, tea_password, tea_phone, tea_email) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tea_code, $tea_fullname, $sug_id, $typ_id, $car_id, $maj_id, $tea_username, $hash, $tea_phone, $tea_email]);
        $_SESSION['success'] = "เพิ่มครูใหม่เรียบร้อยแล้ว";
    }

    header("Location: manage_teachers.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Database Error: " . $e->getMessage();
    header("Location: manage_teachers.php");
    exit();
}
?>