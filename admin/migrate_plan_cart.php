<?php
// admin/migrate_plan_cart.php
// รันครั้งเดียวเพื่อสร้างตาราง plan_subject_cart
require_once '../config/db.php';

echo "<h2>Migration: Plan Subject Cart</h2>";

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS plan_subject_cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pla_id INT NOT NULL,
        sub_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_plan_sub (pla_id, sub_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color:green; font-weight:bold;'>✅ สร้างตาราง plan_subject_cart เรียบร้อย!</p>";
    echo "<p>🎉 Migration เสร็จสมบูรณ์! สามารถลบไฟล์นี้ได้แล้ว</p>";
}
catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
