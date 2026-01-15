<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

$classes = $pdo->query("SELECT * FROM class_groups ORDER BY cla_id DESC")->fetchAll();
$plan = null; $title = "สร้างแผนการเรียนใหม่";

// เพิ่มตัวแปรปีปัจจุบันสำหรับคำนวณชั้นปี
$current_year = date('Y') + 543;

if (isset($_GET['id'])) {
    $title = "แก้ไขแผนการเรียน"; $stmt = $pdo->prepare("SELECT * FROM study_plans WHERE pla_id = ?"); $stmt->execute([$_GET['id']]); $plan = $stmt->fetch();
}
require_once '../includes/header.php';
?>

<div class="min-h-[80vh] flex flex-col justify-center items-center py-10">
    <div class="w-full max-w-2xl">
        <div class="mb-6 flex justify-between items-center">
            <a href="manage_plans.php" class="text-slate-400 hover:text-cvc-blue text-sm font-bold transition flex items-center gap-2"><i class="fa-solid fa-arrow-left"></i> ย้อนกลับ</a>
            <h2 class="text-xl font-serif font-bold text-slate-800"><?php echo $title; ?></h2>
        </div>

        <div class="card-premium p-8 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cvc-blue via-cvc-sky to-cvc-gold"></div>
            
            <form action="save_plan.php" method="POST" class="space-y-8">
                <?php if ($plan): ?><input type="hidden" name="pla_id" value="<?php echo $plan['pla_id']; ?>"><?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ปีการศึกษา <span class="text-red-500">*</span></label>
                        <input type="number" name="pla_start_year" id="input_year" required value="<?php echo $plan['pla_start_year'] ?? (date('Y')+543); ?>" class="w-full font-bold text-center text-lg text-cvc-blue bg-white border border-slate-200 rounded-xl py-3 focus:ring-2 focus:ring-cvc-blue" oninput="genPlanCode()">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ภาคเรียน <span class="text-red-500">*</span></label>
                        <select name="pla_semester" id="input_sem" class="w-full font-bold text-center text-lg text-slate-700 bg-white border border-slate-200 rounded-xl py-3 focus:ring-2 focus:ring-cvc-blue" onchange="genPlanCode()">
                            <option value="1" <?php echo ($plan && $plan['pla_semester'] == '1') ? 'selected' : ''; ?>>เทอม 1</option>
                            <option value="2" <?php echo ($plan && $plan['pla_semester'] == '2') ? 'selected' : ''; ?>>เทอม 2</option>
                            <option value="3" <?php echo ($plan && $plan['pla_semester'] == '3') ? 'selected' : ''; ?>>ฤดูร้อน</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">สำหรับกลุ่มเรียน <span class="text-red-500">*</span></label>
                    <select name="cla_id" id="input_class" required class="w-full font-bold text-slate-700 bg-white border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-cvc-blue" onchange="genPlanCode()">
                        <option value="">-- เลือกกลุ่มเรียน --</option>
                        <?php foreach ($classes as $c): 
                            // คำนวณชั้นปีและรูปแบบการแสดงผลใหม่
                            $stu_year = $current_year - $c['cla_year'] + 1;
                            // รูปแบบ: ชื่อกลุ่ม.ปี/ห้อง (เช่น สสส.1/2)
                            $display_name = $c['cla_name'] . "." . $stu_year . '/' . intval($c['cla_group_no']);
                        ?>
                            <option value="<?php echo $c['cla_id']; ?>" <?php echo ($plan && $plan['cla_id'] == $c['cla_id']) ? 'selected' : ''; ?>>
                                <?php echo $display_name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="bg-slate-800 p-6 rounded-xl flex flex-col items-center justify-center shadow-inner relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full blur-2xl"></div>
                    <label class="text-[10px] font-bold text-cvc-sky uppercase mb-2 tracking-[0.2em]">PLAN CODE (AUTO)</label>
                    <input type="text" name="pla_code" id="input_code" readonly required value="<?php echo $plan['pla_code'] ?? ''; ?>" class="w-full bg-transparent border-none text-white font-mono font-black text-3xl text-center tracking-[0.15em] p-0 focus:ring-0">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ชื่อแผนการเรียน</label>
                    <input type="text" name="pla_name" required value="<?php echo $plan['pla_name'] ?? ''; ?>" placeholder="เช่น แผนการเรียน ปวส.1 เทคโนโลยีสารสนเทศ" class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4">
                </div>

                <div class="pt-4 flex gap-4 border-t border-slate-100">
                    <button type="submit" class="btn-cvc w-full justify-center text-base shadow-xl"><i class="fa-solid fa-save mr-2"></i> บันทึกข้อมูล</button>
                    <a href="manage_plans.php" class="px-6 py-2.5 rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50 font-bold transition">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function genPlanCode() {
        const year = document.getElementById('input_year').value; const sem = document.getElementById('input_sem').value; const cla = document.getElementById('input_class').value;
        let yearCode = (year.length === 4) ? year.substring(2, 4) : "00"; let semCode = sem.toString().padStart(2, '0'); let classCode = (cla && cla.length >= 4) ? cla.substring(cla.length - 4) : "0000";
        document.getElementById('input_code').value = `${yearCode}${semCode}${classCode}`;
    }
    window.onload = function() { if(!document.getElementById('input_code').value) genPlanCode(); }
</script>
<?php require_once '../includes/footer.php'; ?>