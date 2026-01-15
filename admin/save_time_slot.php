<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tim_id = $_POST['tim_id'] ?? null;
    $tim_start = $_POST['tim_start'];
    $tim_end = $_POST['tim_end'];
    $tim_range = trim($_POST['tim_range']);
    $tim_note = trim($_POST['tim_note']);

    // ถ้าไม่ได้กรอก range ให้สร้างเองอัตโนมัติ (ตัดวินาทีออก)
    if (empty($tim_range)) {
        $tim_range = substr($tim_start, 0, 5) . '-' . substr($tim_end, 0, 5);
    }

    try {
        if ($tim_id) {
            // Update
            $sql = "UPDATE time_slots SET tim_range=?, tim_start=?, tim_end=?, tim_note=? WHERE tim_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tim_range, $tim_start, $tim_end, $tim_note, $tim_id]);
        } else {
            // Insert
            $sql = "INSERT INTO time_slots (tim_range, tim_start, tim_end, tim_note) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tim_range, $tim_start, $tim_end, $tim_note]);
        }
        
        header("Location: manage_time_slots.php");

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>