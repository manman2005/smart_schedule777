<?php
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        // 1. ตรวจสอบ ADMIN
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE adm_username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['adm_password'])) {
            $_SESSION['user_id'] = $user['adm_id'];
            $_SESSION['user_name'] = $user['adm_name'];
            $_SESSION['role'] = 'admin';
            header("Location: admin/index.php");
            exit();
        }

        // 2. ตรวจสอบ TEACHER
        $stmt = $pdo->prepare("SELECT * FROM teachers WHERE tea_username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['tea_password'])) {
            $_SESSION['user_id'] = $user['tea_id'];
            $_SESSION['user_name'] = $user['tea_fullname'];
            $_SESSION['role'] = 'teacher';
            header("Location: teacher/index.php");
            exit();
        }

        // 3. ตรวจสอบ STUDENT
        $stmt = $pdo->prepare("SELECT * FROM students WHERE stu_username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['stu_password'])) {
            $_SESSION['user_id'] = $user['stu_id'];
            $_SESSION['user_name'] = $user['stu_fullname'];
            $_SESSION['role'] = 'student';
            header("Location: student/index.php");
            exit();
        }

        // --- ถ้าไม่เจอเลย หรือรหัสผิด ---
        $_SESSION['error'] = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        header("Location: login.php"); // กลับไปหน้า Login
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "ระบบเกิดข้อผิดพลาด: " . $e->getMessage();
        header("Location: login.php"); // กลับไปหน้า Login
        exit();
    }
} else {
    header("Location: login.php"); // กลับไปหน้า Login
    exit();
}
?>