<?php
// htdocs/admin/system_settings.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

// บันทึกข้อมูลเมื่อมีการ POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // รับค่าจาก Form (ถ้าติ๊กถูกจะได้ 1, ถ้าไม่ติ๊กจะเป็น 0)
        $booking = isset($_POST['teacher_booking']) ? '1' : '0';
        $unavail = isset($_POST['teacher_unavailability']) ? '1' : '0';
        
        // ใช้ UPSERT เพื่อบันทึกข้อมูล
        $sql = "INSERT INTO system_settings (setting_key, setting_value, setting_label) VALUES (:key, :val, :label) 
                ON DUPLICATE KEY UPDATE setting_value = :val";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':key' => 'teacher_booking', ':val' => $booking, ':label' => 'ระบบจองรายวิชาสอน']);
        $stmt->execute([':key' => 'teacher_unavailability', ':val' => $unavail, ':label' => 'ระบบระบุเวลาที่ไม่สะดวก']);
        
        $pdo->commit();
        $success_msg = "บันทึกการตั้งค่าเรียบร้อยแล้ว";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

// ดึงค่าปัจจุบันมาแสดง
try {
    // [แก้ไข] เลือกเฉพาะ 2 คอลัมน์ที่จำเป็น (setting_key, setting_value) เพื่อไม่ให้ fetchAll(PDO::FETCH_KEY_PAIR) พัง
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    $settings_query = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $settings = [
        'teacher_booking' => $settings_query['teacher_booking'] ?? '1', 
        'teacher_unavailability' => $settings_query['teacher_unavailability'] ?? '1'
    ];
} catch (Exception $e) {
    // ถ้า error จริงๆ ให้แสดงข้อความ Original ออกมาดู
    $error_msg = "Database Error: " . $e->getMessage();
    $settings = ['teacher_booking' => '0', 'teacher_unavailability' => '0'];
}

require_once '../includes/header.php';
?>

<div class="max-w-4xl mx-auto pb-12">
    <div class="mb-8">
        <a href="index.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2">
            <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
        </a>
        <h1 class="text-3xl font-serif font-bold text-slate-800">ตั้งค่าระบบ (System Settings)</h1>
    </div>

    <?php if(isset($success_msg)): ?>
        <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
            <i class="fa-solid fa-circle-check mr-2 text-xl"></i> <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <?php if(isset($error_msg)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
            <i class="fa-solid fa-triangle-exclamation mr-2 text-xl"></i> <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <div class="card-premium p-8 border-t-4 border-t-slate-600">
        <form method="POST">
            <h2 class="text-xl font-bold text-slate-700 mb-6 border-b pb-2">
                <i class="fa-solid fa-sliders mr-2"></i> ควบคุมการเข้าถึงของอาจารย์
            </h2>

            <div class="space-y-6">
                <div class="flex items-center justify-between py-4 border-b border-slate-100">
                    <div>
                        <div class="font-bold text-lg text-slate-800">เปิดให้จองรายวิชาสอน</div>
                        <div class="text-slate-500 text-sm">อนุญาตให้อาจารย์เลือกรายวิชาที่จะสอนได้ด้วยตนเอง</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="teacher_booking" value="1" class="sr-only peer" 
                            <?php echo ($settings['teacher_booking'] == '1') ? 'checked' : ''; ?>>
                        <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between py-4">
                    <div>
                        <div class="font-bold text-lg text-slate-800">เปิดให้ระบุเวลาที่ไม่สะดวก</div>
                        <div class="text-slate-500 text-sm">อนุญาตให้อาจารย์กำหนดวัน/เวลาที่ไม่สามารถทำการสอนได้</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="teacher_unavailability" value="1" class="sr-only peer" 
                            <?php echo ($settings['teacher_unavailability'] == '1') ? 'checked' : ''; ?>>
                        <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
                    </label>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="btn-cvc px-8 py-3 shadow-lg flex items-center gap-2">
                    <i class="fa-solid fa-save"></i> บันทึกการตั้งค่า
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>