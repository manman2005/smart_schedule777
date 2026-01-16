<?php
ob_start(); // เริ่ม Buffer
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

// ล้างค่าขยะก่อนส่ง JSON เพื่อความชัวร์
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่า
    $pls_id = $_POST['pls_id'] ?? null;
    $tea_id = empty($_POST['tea_id']) ? null : $_POST['tea_id'];

    if (!$pls_id) {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบรหัสรายการ']);
        exit;
    }

    try {
        // อัปเดตข้อมูล
        $sql = "UPDATE plan_subjects SET tea_id = ? WHERE pls_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tea_id, $pls_id]);

        // ส่งผลลัพธ์กลับเป็น JSON
        echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อย']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
exit();
?>