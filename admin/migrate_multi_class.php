<?php
// admin/migrate_multi_class.php
// รันครั้งเดียวเพื่อสร้างตาราง study_plan_classes และ migrate ข้อมูลเดิม
require_once '../config/db.php';

echo "<h2>Migration: Multi-Class for Study Plans</h2>";

try {
    // 1. สร้างตาราง study_plan_classes
    $pdo->exec("CREATE TABLE IF NOT EXISTS study_plan_classes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pla_id INT NOT NULL,
        cla_id VARCHAR(20) NOT NULL,
        UNIQUE KEY unique_plan_class (pla_id, cla_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p>✅ สร้างตาราง study_plan_classes เรียบร้อย</p>";

    // 2. Migrate ข้อมูลเดิมจาก study_plans.cla_id
    $stmt = $pdo->query("SELECT pla_id, cla_id FROM study_plans WHERE cla_id IS NOT NULL AND cla_id != ''");
    $rows = $stmt->fetchAll();
    $count = 0;
    foreach ($rows as $row) {
        try {
            $ins = $pdo->prepare("INSERT IGNORE INTO study_plan_classes (pla_id, cla_id) VALUES (?, ?)");
            $ins->execute([$row['pla_id'], $row['cla_id']]);
            if ($ins->rowCount() > 0)
                $count++;
        }
        catch (Exception $e) {
        // skip duplicates
        }
    }
    echo "<p>✅ Migrate ข้อมูลเดิม {$count} รายการเรียบร้อย</p>";

    // 3. แสดงข้อมูลใหม่
    $result = $pdo->query("SELECT * FROM study_plan_classes ORDER BY pla_id")->fetchAll();
    echo "<h3>ข้อมูลใน study_plan_classes:</h3><table border='1' cellpadding='5'><tr><th>ID</th><th>pla_id</th><th>cla_id</th></tr>";
    foreach ($result as $r) {
        echo "<tr><td>{$r['id']}</td><td>{$r['pla_id']}</td><td>{$r['cla_id']}</td></tr>";
    }
    echo "</table>";

    echo "<br><p style='color:green; font-weight:bold;'>🎉 Migration เสร็จสมบูรณ์! สามารถลบไฟล์นี้ได้แล้ว</p>";
}
catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
