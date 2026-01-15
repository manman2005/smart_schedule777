<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM study_plans WHERE pla_id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: manage_plans.php");
?>