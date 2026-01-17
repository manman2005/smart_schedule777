<?php
// admin/delete_plan_subject.php
require_once '../config/db.php';

$id = $_GET['id'] ?? null;
$pla_id = $_GET['pla_id'] ?? $_GET['plan_id'] ?? null;
$year = $_GET['year'] ?? null;
$semester = $_GET['semester'] ?? null;
$is_ajax = isset($_GET['ajax']) && $_GET['ajax'] == 1;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM plan_subjects WHERE pls_id = ?");
        $stmt->execute([$id]);
        
        if ($is_ajax) {
            echo json_encode(['status'=>'success']);
            exit;
        }
    } catch (PDOException $e) {
        if ($is_ajax) {
            echo json_encode(['status'=>'error', 'message'=>$e->getMessage()]);
            exit;
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Redirect กลับหน้าเดิม (ถ้าไม่ใช่ Ajax)
if (!$is_ajax) {
    if ($pla_id && $year && $semester) {
        header("Location: manage_plan_subjects.php?pla_id=$pla_id&year=$year&semester=$semester");
    } else {
        header("Location: manage_plans.php");
    }
}
?>