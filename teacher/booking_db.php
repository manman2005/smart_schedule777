<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkTeacher();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tea_id = $_SESSION['user_id'];
    $pls_id = $_POST['pls_id'];

    try {
        // 1. ตรวจสอบก่อนว่าวิชานี้ยังว่างอยู่ไหม (Lock Check)
        $check = $pdo->prepare("SELECT tea_id FROM plan_subjects WHERE pls_id = ?");
        $check->execute([$pls_id]);
        $current = $check->fetch();

        if ($current) {
            if ($current['tea_id'] == null) {
                // 2. ถ้ายังว่าง ให้ทำการจอง (Update tea_id เป็นของครูคนนี้)
                $update = $pdo->prepare("UPDATE plan_subjects SET tea_id = ? WHERE pls_id = ?");
                $update->execute([$tea_id, $pls_id]);
                
                // *** เปลี่ยนการ Redirect กลับไปหน้า booking.php ***
                echo "<script>
                    alert('บันทึกข้อมูลสำเร็จ! คุณได้รับผิดชอบรายวิชานี้แล้ว');
                    window.location = 'booking.php'; // กลับไปหน้าเลือกวิชาต่อ
                </script>";
            } else {
                // 3. ถ้าไม่ว่างแล้ว (โดนคนอื่นจองตัดหน้า)
                echo "<script>
                    alert('ขออภัย รายวิชานี้ถูกเลือกโดยอาจารย์ท่านอื่นไปแล้ว');
                    window.location = 'booking.php';
                </script>";
            }
        } else {
            echo "<script>alert('ไม่พบข้อมูลรายวิชา'); window.location = 'booking.php';</script>";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    // ถ้าเข้าไฟล์นี้โดยตรง
    header("Location: booking.php");
    exit();
}
?>