<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        // ถ้าเป็น API ให้ส่ง JSON แจ้งเตือน แทนการเด้งไปหน้า Login (ซึ่งเป็น HTML)
        if (defined('IS_API')) {
            http_response_code(401); // 401 Unauthorized
            echo json_encode(['error' => 'Session Expired: กรุณาเข้าสู่ระบบใหม่']);
            exit;
        }
        
        header("Location: ../index.php");
        exit();
    }
}

function checkAdmin() {
    checkLogin();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        if (defined('IS_API')) {
            http_response_code(403);
            echo json_encode(['error' => 'Access Denied: สำหรับผู้ดูแลระบบเท่านั้น']);
            exit;
        }
        die("Access Denied: สำหรับผู้ดูแลระบบเท่านั้น");
    }
}

function checkTeacher() {
    checkLogin();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
        if (defined('IS_API')) {
            http_response_code(403);
            echo json_encode(['error' => 'Access Denied: สำหรับครูอาจารย์เท่านั้น']);
            exit;
        }
        die("Access Denied: สำหรับครูอาจารย์เท่านั้น");
    }
}

function checkStudent() {
    checkLogin();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
        if (defined('IS_API')) {
            http_response_code(403);
            echo json_encode(['error' => 'Access Denied: สำหรับนักเรียนเท่านั้น']);
            exit;
        }
        die("Access Denied: สำหรับนักเรียนเท่านั้น");
    }
}
// จบไฟล์แค่นี้ 