<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mode = $_POST['mode'];
    $cla_name = trim($_POST['cla_name']);
    $cla_year = $_POST['cla_year'];
    $cla_note = $_POST['cla_note'];
    // เพิ่ม: รับค่าครูที่ปรึกษา (ถ้าไม่เลือกให้เป็น NULL)
    $tea_id = !empty($_POST['tea_id']) ? $_POST['tea_id'] : null;

    $cla_year_code = $_POST['cla_year_code'];
    $cla_level_code = $_POST['cla_level_code'];
    $cla_type_code = $_POST['cla_type_code'];
    $cla_major_code = $_POST['cla_major_code'];
    $cla_group_no = $_POST['cla_group_no'];

    // [แก้ไข] สร้าง ID ใน PHP แทน Trigger โดยใช้ str_pad เติม 0 ข้างหน้า
    $new_cla_id = $cla_year_code . $cla_level_code . $cla_type_code . $cla_major_code . str_pad($cla_group_no, 2, '0', STR_PAD_LEFT);

    try {
        if ($mode == 'update') {
            $original_cla_id = $_POST['original_cla_id'];

            if ($new_cla_id != $original_cla_id) {
                $check = $pdo->prepare("SELECT COUNT(*) FROM class_groups WHERE cla_id = ?");
                $check->execute([$new_cla_id]);
                if ($check->fetchColumn() > 0) {
                    echo "<script>alert('รหัสกลุ่มเรียนใหม่ ($new_cla_id) มีอยู่ในระบบแล้ว กรุณาตรวจสอบอีกครั้ง'); window.history.back();</script>";
                    exit();
                }

                $sql = "UPDATE class_groups SET 
                        cla_id = ?, cla_name = ?, cla_year = ?, cla_year_code = ?, cla_level_code = ?, 
                        cla_type_code = ?, cla_major_code = ?, cla_group_no = ?, cla_note = ?, tea_id = ? 
                        WHERE cla_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $new_cla_id, $cla_name, $cla_year, $cla_year_code, $cla_level_code, 
                    $cla_type_code, $cla_major_code, $cla_group_no, $cla_note, $tea_id, $original_cla_id
                ]);

            } else {
                $sql = "UPDATE class_groups SET 
                        cla_name = ?, cla_year = ?, cla_year_code = ?, cla_level_code = ?, 
                        cla_type_code = ?, cla_major_code = ?, cla_group_no = ?, cla_note = ?, tea_id = ? 
                        WHERE cla_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $cla_name, $cla_year, $cla_year_code, $cla_level_code, 
                    $cla_type_code, $cla_major_code, $cla_group_no, $cla_note, $tea_id, $original_cla_id
                ]);
            }

        } else {
            $check = $pdo->prepare("SELECT COUNT(*) FROM class_groups WHERE cla_id = ?");
            $check->execute([$new_cla_id]);
            if ($check->fetchColumn() > 0) {
                echo "<script>alert('รหัสกลุ่มเรียนนี้ ($new_cla_id) มีอยู่แล้ว'); window.history.back();</script>";
                exit();
            }

            $sql = "INSERT INTO class_groups 
                    (cla_id, cla_name, cla_year, cla_year_code, cla_level_code, 
                    cla_type_code, cla_major_code, cla_group_no, cla_note, tea_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $new_cla_id, $cla_name, $cla_year, $cla_year_code, $cla_level_code, 
                $cla_type_code, $cla_major_code, $cla_group_no, $cla_note, $tea_id
            ]);
        }

        header("Location: manage_class_groups.php");
        exit();

    } catch (PDOException $e) {
        echo "<script>alert('เกิดข้อผิดพลาด: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }
}
?>