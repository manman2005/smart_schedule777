<?php
// admin/save_plan.php
// เวอร์ชัน: รองรับหลายกลุ่มเรียน (Multi-Class)
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pla_id = (isset($_POST['pla_id']) && $_POST['pla_id'] !== '') ? $_POST['pla_id'] : null;

    $pla_name = trim($_POST['pla_name']);

    // รับค่ากลุ่มเรียนเป็น array
    $cla_ids = $_POST['cla_ids'] ?? [];
    $first_cla_id = !empty($cla_ids) ? $cla_ids[0] : null;

    $pla_start_year = $_POST['pla_start_year'];
    $pla_semester = $_POST['pla_semester'];

    try {
        $pdo->beginTransaction();

        if ($pla_id !== null) {
            // Update (แก้ไขแผนเดิม) — ไม่แก้ pla_code
            $sql = "UPDATE study_plans SET pla_name=?, cla_id=?, pla_start_year=?, pla_semester=? WHERE pla_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$pla_name, $first_cla_id, $pla_start_year, $pla_semester, $pla_id]);

            // ลบกลุ่มเรียนเดิมออก แล้ว insert ใหม่
            $pdo->prepare("DELETE FROM study_plan_classes WHERE pla_id = ?")->execute([$pla_id]);

            $ins = $pdo->prepare("INSERT INTO study_plan_classes (pla_id, cla_id) VALUES (?, ?)");
            foreach ($cla_ids as $cid) {
                $ins->execute([$pla_id, $cid]);
            }

            $_SESSION['success'] = "แก้ไขแผนการเรียนเรียบร้อยแล้ว";
        }
        else {
            // สร้างรหัสแผนอัตโนมัติ: ปี พ.ศ. 2 หลัก + ลำดับ 3 หลัก
            $current_year = date('Y') + 543;
            $year_prefix = substr((string)$current_year, -2);
            $stmt_max = $pdo->prepare("SELECT MAX(CAST(SUBSTRING(pla_code, 3) AS UNSIGNED)) as max_seq FROM study_plans WHERE pla_code LIKE ?");
            $stmt_max->execute([$year_prefix . '%']);
            $max_seq = $stmt_max->fetch()['max_seq'] ?? 0;
            $pla_code = $year_prefix . str_pad($max_seq + 1, 3, '0', STR_PAD_LEFT);

            // Insert (สร้างแผนใหม่)
            $sql = "INSERT INTO study_plans (pla_code, pla_name, cla_id, pla_start_year, pla_semester) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$pla_code, $pla_name, $first_cla_id, $pla_start_year, $pla_semester]);
            $new_pla_id = $pdo->lastInsertId();

            // Insert กลุ่มเรียนทั้งหมดเข้า study_plan_classes
            $ins = $pdo->prepare("INSERT INTO study_plan_classes (pla_id, cla_id) VALUES (?, ?)");
            foreach ($cla_ids as $cid) {
                $ins->execute([$new_pla_id, $cid]);
            }

            $_SESSION['success'] = "เพิ่มแผนการเรียนใหม่เรียบร้อยแล้ว";
        }

        $pdo->commit();
        header("Location: manage_plans.php");
        exit();

    }
    catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        header("Location: manage_plans.php");
        exit();
    }
}
?>