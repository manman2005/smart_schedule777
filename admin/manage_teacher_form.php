<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

$subject_groups = $pdo->query("SELECT * FROM subject_groups ORDER BY sug_id ASC")->fetchAll();
$subject_types = $pdo->query("SELECT * FROM subject_types ORDER BY typ_code ASC")->fetchAll();
$career_groups = $pdo->query("SELECT * FROM career_groups ORDER BY car_code ASC")->fetchAll();
$majors = $pdo->query("SELECT * FROM majors ORDER BY maj_code ASC")->fetchAll();

$teacher = null; $title = "เพิ่มข้อมูลครู";
if (isset($_GET['id'])) {
    $title = "แก้ไขข้อมูลครู";
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE tea_id = ?");
    $stmt->execute([$_GET['id']]);
    $teacher = $stmt->fetch();
}
require_once '../includes/header.php';
?>

<div class="min-h-[80vh] flex flex-col justify-center items-center py-10">
    <div class="w-full max-w-3xl">
        <div class="mb-6 flex justify-between items-center">
            <a href="manage_teachers.php" class="text-slate-400 hover:text-cvc-blue text-sm font-bold transition flex items-center gap-2"><i class="fa-solid fa-arrow-left"></i> ย้อนกลับ</a>
            <h2 class="text-xl font-serif font-bold text-slate-800"><?php echo $title; ?></h2>
        </div>

        <div class="card-premium p-8 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cvc-blue via-cvc-sky to-cvc-gold"></div>
            
            <form action="save_teacher.php" method="POST" onsubmit="enableInputs()" class="space-y-8">
                <?php if ($teacher): ?><input type="hidden" name="tea_id" value="<?php echo $teacher['tea_id']; ?>"><?php endif; ?>

                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-cvc-gold uppercase tracking-widest border-b border-slate-100 pb-2 mb-4">ข้อมูลบุคลากร</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">รหัสประจำตัว <span class="text-red-500">*</span></label>
                            <input type="text" name="tea_code" required value="<?php echo $teacher['tea_code'] ?? ''; ?>" class="w-full font-mono font-bold text-slate-700 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
                            <input type="text" name="tea_fullname" required value="<?php echo $teacher['tea_fullname'] ?? ''; ?>" class="w-full bg-slate-50">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">กลุ่มวิชา (หมวด)</label>
                            <select name="sug_id" class="w-full text-sm py-2">
                                <option value="">-- ไม่ระบุ --</option>
                                <?php foreach ($subject_groups as $sg): ?>
                                    <option value="<?php echo $sg['sug_id']; ?>" <?php echo ($teacher && $teacher['sug_id'] == $sg['sug_id']) ? 'selected' : ''; ?>><?php echo $sg['sug_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100">
                    <h3 class="text-sm font-bold text-indigo-800 uppercase tracking-wider mb-4 flex items-center"><i class="fa-solid fa-filter mr-2"></i> สังกัดสาขาวิชา</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-xs font-bold text-slate-500 mb-1">1. ประเภทวิชา</label><select name="typ_id" id="typ_id" required onchange="filterCareerGroups()" class="w-full text-sm py-2"><option value="">-- เลือก --</option><?php foreach ($subject_types as $st): ?><option value="<?php echo $st['typ_id']; ?>" <?php echo ($teacher && $teacher['typ_id'] == $st['typ_id']) ? 'selected' : ''; ?>><?php echo $st['typ_name']; ?></option><?php endforeach; ?></select></div>
                        <div><label class="block text-xs font-bold text-slate-500 mb-1">2. กลุ่มอาชีพ</label><select name="car_id" id="car_id" disabled onchange="filterMajors()" class="w-full text-sm py-2 disabled:bg-slate-200"><option value="">-- รอเลือกประเภท --</option></select></div>
                        <div class="md:col-span-2"><label class="block text-xs font-bold text-slate-500 mb-1">3. สาขาวิชา</label><select name="maj_id" id="maj_id" disabled class="w-full text-sm py-2 disabled:bg-slate-200"><option value="">-- รอเลือกกลุ่มอาชีพ --</option></select></div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 mb-4">บัญชีผู้ใช้</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">Username <span class="text-red-500">*</span></label><input type="text" name="tea_username" required value="<?php echo $teacher['tea_username'] ?? ''; ?>" class="w-full font-mono text-sm bg-slate-50"></div>
                        <div><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">Password</label><input type="password" name="tea_password" <?php echo $teacher ? '' : 'required'; ?> placeholder="<?php echo $teacher ? 'เว้นว่างถ้าไม่เปลี่ยน' : ''; ?>" class="w-full font-mono text-sm bg-slate-50"></div>
                        <div><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">เบอร์โทรศัพท์</label><input type="text" name="tea_phone" value="<?php echo $teacher['tea_phone'] ?? ''; ?>" class="w-full text-sm bg-slate-50"></div>
                        <div><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">อีเมล</label><input type="email" name="tea_email" value="<?php echo $teacher['tea_email'] ?? ''; ?>" class="w-full text-sm bg-slate-50"></div>
                    </div>
                </div>

                <div class="pt-4 flex gap-4 border-t border-slate-100">
                    <button type="submit" class="btn-cvc w-full justify-center text-base shadow-xl"><i class="fa-solid fa-save mr-2"></i> บันทึกข้อมูล</button>
                    <a href="manage_teachers.php" class="px-6 py-2.5 rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50 font-bold transition">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const careerGroupsData = <?php echo json_encode($career_groups); ?>; const majorsData = <?php echo json_encode($majors); ?>; const oldCarId = "<?php echo $teacher['car_id'] ?? ''; ?>"; const oldMajId = "<?php echo $teacher['maj_id'] ?? ''; ?>";
    function enableInputs() { document.getElementById('car_id').disabled = false; document.getElementById('maj_id').disabled = false; }
    function filterCareerGroups() { const typeId = document.getElementById('typ_id').value; const careerSelect = document.getElementById('car_id'); const majorSelect = document.getElementById('maj_id'); careerSelect.innerHTML = '<option value="">-- เลือกกลุ่มอาชีพ --</option>'; majorSelect.innerHTML = '<option value="">-- รอเลือกกลุ่มอาชีพ --</option>'; majorSelect.disabled = true; if (typeId) { careerSelect.disabled = false; careerGroupsData.filter(c => c.typ_id == typeId).forEach(c => careerSelect.add(new Option(c.car_name, c.car_id))); } else { careerSelect.disabled = true; } }
    function filterMajors() { const carId = document.getElementById('car_id').value; const majorSelect = document.getElementById('maj_id'); majorSelect.innerHTML = '<option value="">-- เลือกสาขาวิชา --</option>'; if (carId) { majorSelect.disabled = false; majorsData.filter(m => m.car_id == carId).forEach(m => majorSelect.add(new Option(m.maj_name, m.maj_id))); } else { majorSelect.disabled = true; } }
    window.onload = function() { if(document.getElementById('typ_id').value) { filterCareerGroups(); if(oldCarId) { document.getElementById('car_id').value = oldCarId; filterMajors(); if(oldMajId) document.getElementById('maj_id').value = oldMajId; } } }
</script>
<?php require_once '../includes/footer.php'; ?>