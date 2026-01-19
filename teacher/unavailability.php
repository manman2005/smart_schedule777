<?php
// htdocs/teacher/unavailability.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkTeacher();

$tea_id = $_SESSION['user_id'];

// --- [NEW] 1. ตรวจสอบสถานะระบบ ---
$stmt_sys = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'teacher_unavailability'");
$stmt_sys->execute();
$system_status = $stmt_sys->fetchColumn(); 
// --------------------------------

// ดึงข้อมูลวันและคาบเรียน
$days = $pdo->query("SELECT * FROM days WHERE day_id BETWEEN 1 AND 5")->fetchAll();
$time_slots = $pdo->query("SELECT * FROM time_slots ORDER BY tim_start ASC")->fetchAll();

// ดึงข้อมูลที่เคยบันทึกไว้
$stmt_busy = $pdo->prepare("SELECT CONCAT(day_id, '-', tim_id) as slot_key FROM teacher_unavailability WHERE tea_id = ?");
$stmt_busy->execute([$tea_id]);
$busy_data = $stmt_busy->fetchAll(PDO::FETCH_COLUMN);

require_once '../includes/header.php';
?>

<div class="max-w-6xl mx-auto mb-20">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-serif font-bold text-slate-800">
            <i class="fa-solid fa-user-clock text-red-500 mr-2"></i> กำหนดเวลาที่ไม่สะดวกสอน
        </h1>
        <a href="index.php" class="text-slate-500 hover:text-slate-700 font-bold"><i class="fa-solid fa-arrow-left"></i> กลับหน้าหลัก</a>
    </div>

    <?php if ($system_status == '0'): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-r-xl shadow-sm mb-6 flex items-start gap-4">
            <div class="bg-red-100 p-3 rounded-full text-red-500">
                <i class="fa-solid fa-lock text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-red-700">ปิดการแก้ไขข้อมูล</h3>
                <p class="text-red-600 text-sm mt-1">ขณะนี้ระบบปิดไม่ให้แก้ไขข้อมูลวัน/เวลาที่ไม่สะดวกแล้ว หากต้องการเปลี่ยนแปลงโปรดติดต่อเจ้าหน้าที่</p>
            </div>
        </div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center shadow-sm">
            <i class="fa-solid fa-circle-check mr-2 text-xl"></i>
            <span>บันทึกข้อมูลเรียบร้อยแล้ว</span>
        </div>
    <?php endif; ?>

    <div class="card-premium p-6 border-t-4 border-t-red-500 <?php echo ($system_status == '0') ? 'opacity-80' : ''; ?>">
        <?php if ($system_status == '1'): ?>
        <div class="mb-6 bg-red-50 p-4 rounded-xl border border-red-100 flex gap-3 items-start">
            <i class="fa-solid fa-circle-info text-red-500 mt-1"></i>
            <div>
                <p class="font-bold text-red-700 text-sm">คำแนะนำ</p>
                <p class="text-xs text-red-600">คลิกที่ช่องตารางเพื่อระบุช่วงเวลาที่คุณ <span class="font-bold underline">ไม่สะดวกสอน</span> (ช่องที่เป็นสีแดง = ไม่ว่าง)</p>
            </div>
        </div>
        <?php endif; ?>

        <form action="save_unavailability.php" method="POST">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="p-3 border bg-slate-100 text-slate-600 w-24">วัน / เวลา</th>
                            <?php foreach($time_slots as $slot): ?>
                                <th class="p-2 border bg-slate-50 text-slate-500 text-xs min-w-[60px]">
                                    <div class="font-bold"><?php echo substr($slot['tim_range'], 0, 5); ?></div>
                                    <div class="font-light scale-90"><?php echo substr($slot['tim_range'], 6, 5); ?></div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($days as $day): ?>
                        <tr>
                            <td class="p-3 border font-bold text-slate-700 text-center bg-slate-50">
                                <?php echo $day['day_name']; ?>
                            </td>
                            <?php foreach($time_slots as $slot): 
                                $slot_key = $day['day_id'] . '-' . $slot['tim_id'];
                                $is_busy = in_array($slot_key, $busy_data);
                                $is_lunch = (strpos($slot['tim_range'], '12:00') === 0);
                                // [NEW] ถ้าปิดระบบ ให้คลิกไม่ได้ (pointer-events-none)
                                $disabled_class = ($system_status == '0' || $is_lunch) ? 'pointer-events-none' : 'cursor-pointer hover:bg-slate-100';
                            ?>
                                <td class="border text-center p-0 relative h-12 transition select-none cell-selector <?php echo $disabled_class; ?> <?php echo $is_lunch ? 'bg-slate-200' : ''; ?> <?php echo $is_busy ? 'bg-red-500 text-white hover:bg-red-600' : ''; ?>" 
                                    onclick="toggleSlot(this)"
                                    data-key="<?php echo $slot_key; ?>">
                                    
                                    <?php if($is_lunch): ?>
                                        <span class="text-[10px] text-slate-400 rotate-45 block">พัก</span>
                                    <?php else: ?>
                                        <input type="checkbox" name="busy_slots[]" value="<?php echo $slot_key; ?>" 
                                            class="absolute opacity-0 pointer-events-none" 
                                            <?php echo $is_busy ? 'checked' : ''; ?>
                                            <?php echo ($system_status == '0') ? 'disabled' : ''; ?>>
                                            
                                        <div class="status-icon <?php echo $is_busy ? '' : 'hidden'; ?>">
                                            <?php if ($system_status == '0'): ?>
                                                <i class="fa-solid fa-lock text-white/50"></i>
                                            <?php else: ?>
                                                <i class="fa-solid fa-xmark"></i>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-8 flex justify-center gap-4">
                <?php if ($system_status == '1'): ?>
                    <button type="reset" class="px-6 py-2 rounded-full border border-slate-300 text-slate-500 hover:bg-slate-100 font-bold transition">
                        ล้างค่า
                    </button>
                    <button type="submit" class="px-8 py-2 rounded-full bg-red-600 text-white font-bold shadow-lg shadow-red-500/30 hover:bg-red-700 hover:-translate-y-1 transition">
                        <i class="fa-solid fa-save mr-2"></i> บันทึกข้อมูล
                    </button>
                <?php else: ?>
                    <button type="button" disabled class="px-8 py-2 rounded-full bg-slate-300 text-white font-bold shadow-none cursor-not-allowed">
                        <i class="fa-solid fa-lock mr-2"></i> บันทึกไม่ได้ (ระบบปิด)
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleSlot(cell) {
        // ห้ามคลิกช่องพักเที่ยง หรือถ้าระบบปิด
        if(cell.classList.contains('pointer-events-none')) return;

        const checkbox = cell.querySelector('input[type="checkbox"]');
        const icon = cell.querySelector('.status-icon');

        if (checkbox.checked) {
            checkbox.checked = false;
            cell.classList.remove('bg-red-500', 'text-white', 'hover:bg-red-600');
            icon.classList.add('hidden');
        } else {
            checkbox.checked = true;
            cell.classList.add('bg-red-500', 'text-white', 'hover:bg-red-600');
            icon.classList.remove('hidden');
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>