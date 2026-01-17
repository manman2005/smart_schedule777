<?php
// admin/save_plan_subject.php
require_once '../config/db.php';
header('Content-Type: application/json');

$is_ajax = isset($_GET['ajax']) && $_GET['ajax'] == 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pla_id = $_POST['pla_id'] ?? null;
    $year = $_POST['year'] ?? null;
    $semester = $_POST['semester'] ?? null;
    $sub_ids = $_POST['sub_ids'] ?? []; 
    $tea_id = $_POST['tea_id'] ?? null;
    $filter_sug = $_POST['filter_sug_context'] ?? null;

    // แก้ไขจุดบั๊ก: รองรับ ID 0
    if (($pla_id === null || $pla_id === '') || !$year || !$semester) {
        echo json_encode(['status'=>'error', 'message'=>'ข้อมูลไม่ครบ (Year/Sem/Plan)']);
        exit;
    }

    if (!empty($sub_ids)) {
        try {
            $pdo->beginTransaction();
            $count = 0;
            
            $check = $pdo->prepare("SELECT * FROM plan_subjects WHERE pla_id=? AND pls_academic_year=? AND pls_semester=? AND sub_id=?");
            $insert = $pdo->prepare("INSERT INTO plan_subjects (pla_id, pls_academic_year, pls_semester, sub_id, tea_id, pls_note) VALUES (?, ?, ?, ?, ?, ?)");

            foreach ($sub_ids as $sid) {
                $check->execute([$pla_id, $year, $semester, $sid]);
                if ($check->rowCount() == 0) {
                    $note = ($filter_sug == 6) ? 'free_elective' : null;
                    $tea_to_save = empty($tea_id) ? null : $tea_id;
                    $insert->execute([$pla_id, $year, $semester, $sid, $tea_to_save, $note]);
                    $count++;
                }
            }
            $pdo->commit();
            echo json_encode(['status'=>'success', 'added'=>$count]);
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['status'=>'error', 'message'=>$e->getMessage()]);
        }
    } else {
        echo json_encode(['status'=>'error', 'message'=>'ไม่ได้เลือกวิชา']);
    }
}
?>