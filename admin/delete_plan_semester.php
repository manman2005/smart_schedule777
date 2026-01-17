<?php
// admin/delete_plan_semester.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

$pla_id = $_GET['plan_id'] ?? null;
$year = $_GET['year'] ?? null;
$semester = $_GET['semester'] ?? null;

if (isset($pla_id) && $pla_id !== '' && $year && $semester) {
    try {
        // ลบทุกวิชาที่อยู่ใน Plan นี้ + ปีนี้ + เทอมนี้
        $sql = "DELETE FROM plan_subjects 
                WHERE pla_id = ? AND pls_academic_year = ? AND pls_semester = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pla_id, $year, $semester]);

        // กลับไปหน้าเดิม
        header("Location: manage_plan_structure.php?id=$pla_id");
    } catch (PDOException $e) {
        echo "Error deleting semester: " . $e->getMessage();
    }
} else {
    // ถ้าข้อมูลไม่ครบ กลับไปหน้าแผนรวม
    header("Location: manage_plans.php");
}
?>