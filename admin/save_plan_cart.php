<?php
// admin/save_plan_cart.php
// API: เพิ่ม/ลบรายวิชาในตะกร้าของแผน
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
    exit;
}

$action = $_POST['action'] ?? '';
$pla_id = $_POST['pla_id'] ?? null;
$sub_id = $_POST['sub_id'] ?? null;
$sub_ids = $_POST['sub_ids'] ?? [];

if (!$pla_id) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่ได้ระบุแผน']);
    exit;
}

try {
    if ($action === 'add' && $sub_id) {
        // เพิ่มวิชาเดียว
        $stmt = $pdo->prepare("INSERT IGNORE INTO plan_subject_cart (pla_id, sub_id) VALUES (?, ?)");
        $stmt->execute([$pla_id, $sub_id]);
        echo json_encode(['status' => 'success', 'action' => 'added']);

    }
    elseif ($action === 'add_multiple' && !empty($sub_ids)) {
        // เพิ่มหลายวิชา
        $count = 0;
        $stmt = $pdo->prepare("INSERT IGNORE INTO plan_subject_cart (pla_id, sub_id) VALUES (?, ?)");
        foreach ($sub_ids as $sid) {
            $stmt->execute([$pla_id, $sid]);
            if ($stmt->rowCount() > 0)
                $count++;
        }
        echo json_encode(['status' => 'success', 'action' => 'added', 'count' => $count]);

    }
    elseif ($action === 'remove' && $sub_id) {
        // ลบวิชาเดียว
        $stmt = $pdo->prepare("DELETE FROM plan_subject_cart WHERE pla_id = ? AND sub_id = ?");
        $stmt->execute([$pla_id, $sub_id]);
        echo json_encode(['status' => 'success', 'action' => 'removed']);

    }
    elseif ($action === 'clear') {
        // ล้างตะกร้าทั้งหมด
        $stmt = $pdo->prepare("DELETE FROM plan_subject_cart WHERE pla_id = ?");
        $stmt->execute([$pla_id]);
        echo json_encode(['status' => 'success', 'action' => 'cleared']);

    }
    else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
}
catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
