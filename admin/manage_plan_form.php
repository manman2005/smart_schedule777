<?php
// admin/manage_plan_form.php
// เวอร์ชัน: รองรับเลือกหลายกลุ่มเรียน (Multi-Class)
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

// ดึงกลุ่มเรียน
$classes = $pdo->query("SELECT * FROM class_groups ORDER BY cla_id DESC")->fetchAll();

$plan = null;

$title = "สร้างแผนการเรียนใหม่";
$selected_cla_ids = []; // array ของกลุ่มเรียนที่เลือก

// ปีปัจจุบัน (สำหรับคำนวณและตั้งค่าเริ่มต้น)
$current_year = date('Y') + 543;
$year_prefix = substr((string)$current_year, -2); // เช่น "69"

// หาลำดับถัดไปของรหัสแผนในปีนี้
$stmt_next = $pdo->prepare("SELECT MAX(CAST(SUBSTRING(pla_code, 3) AS UNSIGNED)) as max_seq FROM study_plans WHERE pla_code LIKE ?");
$stmt_next->execute([$year_prefix . '%']);
$max_seq = $stmt_next->fetch()['max_seq'] ?? 0;
$next_seq = $max_seq + 1;
$next_code = $year_prefix . str_pad($next_seq, 3, '0', STR_PAD_LEFT); // เช่น "69001"

// ถ้าเป็นการแก้ไข ให้ดึงข้อมูลเดิม
if (isset($_GET['id'])) {
    $title = "แก้ไขแผนการเรียน";
    $stmt = $pdo->prepare("SELECT * FROM study_plans WHERE pla_id = ?");
    $stmt->execute([$_GET['id']]);
    $plan = $stmt->fetch();

    // ดึงกลุ่มเรียนที่เลือกไว้จากตาราง study_plan_classes
    if ($plan) {
        $stmt_cls = $pdo->prepare("SELECT cla_id FROM study_plan_classes WHERE pla_id = ?");
        $stmt_cls->execute([$plan['pla_id']]);
        $selected_cla_ids = $stmt_cls->fetchAll(PDO::FETCH_COLUMN);
    }
}

// กำหนดค่าเริ่มต้นสำหรับ Hidden Fields
$val_year = $plan['pla_start_year'] ?? $current_year;
$val_sem = $plan['pla_semester'] ?? 1;

require_once '../includes/header.php';
?>

<div class="min-h-[80vh] flex flex-col justify-center items-center py-10">
    <div class="w-full max-w-2xl">
        <div class="mb-6 flex justify-between items-center">
            <a href="manage_plans.php" class="text-slate-400 hover:text-cvc-blue text-sm font-bold transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> ย้อนกลับ
            </a>
            <h2 class="text-xl font-serif font-bold text-slate-800"><?php echo $title; ?></h2>
        </div>

        <div class="card-premium p-8 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cvc-blue via-cvc-sky to-cvc-gold"></div>
            
            <form action="save_plan.php" method="POST" class="space-y-8">
                <?php if ($plan): ?><input type="hidden" name="pla_id" value="<?php echo $plan['pla_id']; ?>"><?php
endif; ?>

                <input type="hidden" name="pla_start_year" id="input_year" value="<?php echo $val_year; ?>">
                <input type="hidden" name="pla_semester" id="input_sem" value="<?php echo $val_sem; ?>">

                <!-- กลุ่มเรียน: เลือกได้หลายกลุ่ม -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2 ml-1">
                        สำหรับกลุ่มเรียน <span class="text-red-500">*</span>
                        <span class="text-slate-400 font-normal ml-2">(เลือกได้หลายกลุ่ม)</span>
                    </label>
                    
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 max-h-[280px] overflow-y-auto custom-scrollbar">
                        <?php if (empty($classes)): ?>
                            <p class="text-slate-400 text-center py-4">ไม่มีกลุ่มเรียนในระบบ</p>
                        <?php
else: ?>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                <?php foreach ($classes as $c):
        $stu_year = $current_year - $c['cla_year'] + 1;
        $display_name = $c['cla_name'] . "." . $stu_year . '/' . intval($c['cla_group_no']);
        $is_checked = in_array($c['cla_id'], $selected_cla_ids);
?>
                                    <label class="class-checkbox-card cursor-pointer block relative">
                                        <input type="checkbox" name="cla_ids[]" value="<?php echo $c['cla_id']; ?>" 
                                               class="peer sr-only class-cb" onchange="onClassChange()"
                                               <?php echo $is_checked ? 'checked' : ''; ?>>
                                        <div class="bg-white border-2 border-slate-200 rounded-lg px-3 py-2.5 text-center
                                                    transition-all duration-200 hover:border-cvc-blue/50 hover:shadow-sm
                                                    peer-checked:border-cvc-blue peer-checked:bg-blue-50 peer-checked:shadow-md">
                                            <div class="font-bold text-sm text-slate-700 peer-checked:text-cvc-blue">
                                                <?php echo $display_name; ?>
                                            </div>
                                            <div class="text-[10px] text-slate-400 mt-0.5"><?php echo $c['cla_id']; ?></div>
                                        </div>
                                        <div class="absolute top-1.5 right-1.5 text-cvc-blue opacity-0 peer-checked:opacity-100 transition text-xs">
                                            <i class="fa-solid fa-check-circle"></i>
                                        </div>
                                    </label>
                                <?php
    endforeach; ?>
                            </div>
                        <?php
endif; ?>
                    </div>
                    
                    <!-- แสดงจำนวนที่เลือก -->
                    <div class="mt-2 flex items-center gap-2">
                        <span class="text-xs text-slate-400">เลือกแล้ว:</span>
                        <span id="selectedCount" class="text-xs font-bold text-cvc-blue bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">
                            <?php echo count($selected_cla_ids); ?> กลุ่ม
                        </span>
                    </div>
                </div>

                <div class="bg-slate-800 p-6 rounded-xl flex flex-col items-center justify-center shadow-inner relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full blur-2xl group-hover:bg-white/10 transition"></div>
                    <label class="text-[10px] font-bold text-cvc-sky uppercase mb-2 tracking-[0.2em]">PLAN CODE (AUTO)</label>
                    <input type="text" name="pla_code" id="input_code" readonly required value="<?php echo $plan['pla_code'] ?? ''; ?>" class="w-full bg-transparent border-none text-white font-mono font-black text-3xl text-center tracking-[0.15em] p-0 focus:ring-0 cursor-default">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ชื่อแผนการเรียน</label>
                    <input type="text" name="pla_name" required value="<?php echo $plan['pla_name'] ?? ''; ?>" placeholder="เช่น แผนการเรียน ปวส.1 เทคโนโลยีสารสนเทศ" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-cvc-blue outline-none transition">
                </div>

                <div class="pt-4 flex gap-4 border-t border-slate-100">
                    <button type="submit" class="btn-cvc w-full justify-center text-base shadow-xl hover:-translate-y-0.5 transition"><i class="fa-solid fa-save mr-2"></i> บันทึกข้อมูล</button>
                    <a href="manage_plans.php" class="px-6 py-2.5 rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50 font-bold transition">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script>
    // อัปเดตจำนวนกลุ่มที่เลือก
    function onClassChange() {
        const checked = document.querySelectorAll('.class-cb:checked');
        document.getElementById('selectedCount').textContent = checked.length + ' กลุ่ม';
    }

    // Validate ก่อน submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.class-cb:checked');
        if (checked.length === 0) {
            e.preventDefault();
            alert('กรุณาเลือกกลุ่มเรียนอย่างน้อย 1 กลุ่ม');
        }
    });

    // ตั้งค่ารหัสแผนอัตโนมัติเมื่อสร้างใหม่
    window.onload = function() {
        const codeInput = document.getElementById('input_code');
        if (!codeInput.value) {
            codeInput.value = '<?php echo $next_code; ?>';
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>