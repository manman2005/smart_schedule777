<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }
}

function checkAdmin() {
    checkLogin();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        die("Access Denied: สำหรับผู้ดูแลระบบเท่านั้น");
    }
}

// เพิ่มฟังก์ชันนี้สำหรับครู (ถ้ายังไม่มี)
function checkTeacher() {
    checkLogin();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
        die("Access Denied: สำหรับครูอาจารย์เท่านั้น");
    }
}

// *** เพิ่มฟังก์ชันนี้สำหรับนักเรียน ***
function checkStudent() {
    checkLogin();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
        die("Access Denied: สำหรับนักเรียนเท่านั้น");
    }
}
?>