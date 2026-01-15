    <?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM class_groups WHERE cla_id = ?");
        $stmt->execute([$_GET['id']]);
        header("Location: manage_class_groups.php");
    } catch (PDOException $e) {
        // กรณีลบไม่ได้เพราะมีนักเรียนสังกัดอยู่
        echo "<script>alert('ไม่สามารถลบกลุ่มเรียนนี้ได้ เนื่องจากมีนักเรียนหรือข้อมูลอื่นเชื่อมโยงอยู่'); window.location='manage_class_groups.php';</script>";
    }
} else {
    header("Location: manage_class_groups.php");
}
?> 