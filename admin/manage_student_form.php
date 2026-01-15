<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

// ดึงข้อมูล Master Data สำหรับทำ Dropdown
$types = $pdo->query("SELECT * FROM subject_types ORDER BY typ_code ASC")->fetchAll();
$career_groups = $pdo->query("SELECT * FROM career_groups ORDER BY car_code ASC")->fetchAll();
$majors = $pdo->query("SELECT * FROM majors ORDER BY maj_code ASC")->fetchAll();
$class_groups = $pdo->query("SELECT * FROM class_groups ORDER BY cla_id DESC")->fetchAll();

$student = null;
$title = "เพิ่มนักเรียนใหม่";

// ตัวแปรสำหรับเก็บค่าเดิม
$current_typ_id = '';
$current_car_id = '';
$current_maj_id = '';
$current_cla_id = '';

if (isset($_GET['id'])) {
    $title = "แก้ไขข้อมูลนักเรียน";
    $stmt = $pdo->prepare("SELECT * FROM students WHERE stu_id = ?");
    $stmt->execute([$_GET['id']]);
    $student = $stmt->fetch();
    
    if ($student) {
        $current_cla_id = $student['cla_id'];
        // Logic หาค่า Parent
        $stmt_cla = $pdo->prepare("SELECT cla_major_code FROM class_groups WHERE cla_id = ?");
        $stmt_cla->execute([$current_cla_id]);
        $cla_data = $stmt_cla->fetch();
        if ($cla_data) {
            $maj_code = $cla_data['cla_major_code'];
            $stmt_maj = $pdo->prepare("SELECT maj_id, car_id FROM majors WHERE maj_code = ? LIMIT 1");
            $stmt_maj->execute([$maj_code]);
            $maj_data = $stmt_maj->fetch();
            if ($maj_data) {
                $current_maj_id = $maj_data['maj_id'];
                $current_car_id = $maj_data['car_id'];
                $stmt_car = $pdo->prepare("SELECT typ_id FROM career_groups WHERE car_id = ?");
                $stmt_car->execute([$current_car_id]);
                $car_data = $stmt_car->fetch();
                if ($car_data) $current_typ_id = $car_data['typ_id'];
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="min-h-[80vh] flex flex-col justify-center items-center py-10">
    <div class="w-full max-w-2xl">
        
        <div class="mb-6 flex justify-between items-center">
            <a href="manage_students.php" class="text-slate-400 hover:text-cvc-blue text-sm font-bold transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> ย้อนกลับ
            </a>
            <h2 class="text-xl font-serif font-bold text-slate-800"><?php echo $title; ?></h2>
        </div>

        <div class="card-premium p-8 md:p-10 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cvc-blue via-cvc-sky to-cvc-gold"></div>

            <form action="save_student.php" method="POST" class="space-y-6">
                <?php if ($student): ?><input type="hidden" name="stu_id" value="<?php echo $student['stu_id']; ?>"><?php endif; ?>

                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-cvc-gold uppercase tracking-widest border-b border-slate-100 pb-2 mb-4">ข้อมูลส่วนตัว</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-1 ml-1">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
                            <input type="text" name="stu_fullname" required value="<?php echo $student['stu_fullname'] ?? ''; ?>" 
                                   class="w-full bg-slate-50 border-slate-200 focus:bg-white transition" placeholder="ระบุคำนำหน้าและชื่อสกุล">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1 ml-1">เพศสภาพ</label>
                            <select name="stu_gender" class="w-full bg-slate-50 border-slate-200 focus:bg-white transition cursor-pointer">
                                <option value="M" <?php echo ($student && $student['stu_gender'] == 'M') ? 'selected' : ''; ?>>ชาย</option>
                                <option value="F" <?php echo ($student && $student['stu_gender'] == 'F') ? 'selected' : ''; ?>>หญิง</option>
                                <option value="O" <?php echo ($student && $student['stu_gender'] == 'O') ? 'selected' : ''; ?>>อื่นๆ</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 pt-4">
                    <h3 class="text-xs font-bold text-cvc-blue uppercase tracking-widest border-b border-slate-100 pb-2 mb-4">ข้อมูลการศึกษา</h3>
                    
                    <div class="p-5 rounded-xl bg-slate-50 border border-slate-100 space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="text-xs font-bold text-slate-500 mb-1 block">1. ประเภทวิชา</label>
                                <select id="sel_type" class="w-full text-sm py-2" onchange="filterCareer()">
                                    <option value="">-- เลือกประเภท --</option>
                                    <?php foreach($types as $t): ?>
                                        <option value="<?php echo $t['typ_id']; ?>" <?php echo ($current_typ_id == $t['typ_id']) ? 'selected' : ''; ?>>
                                            <?php echo $t['typ_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 mb-1 block">2. กลุ่มสมรรถนะ/อาชีพ</label>
                                <select id="sel_career" class="w-full text-sm py-2 disabled:bg-slate-100 disabled:text-slate-400" disabled onchange="filterMajor()">
                                    <option value="">-- เลือกประเภทวิชาก่อน --</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-500 mb-1 block">3. สาขาวิชา</label>
                                <select id="sel_major" class="w-full text-sm py-2 disabled:bg-slate-100 disabled:text-slate-400" disabled onchange="filterClassGroup()">
                                    <option value="">-- เลือกกลุ่มสมรรถนะก่อน --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1 ml-1">กลุ่มเรียน (Class Group) <span class="text-red-500">*</span></label>
                        <select name="cla_id" id="sel_class" required class="w-full bg-white border-cvc-blue/30 text-cvc-blue font-bold focus:ring-cvc-blue/20">
                            <option value="">-- เลือกสาขาวิชาเพื่อกรองกลุ่มเรียน --</option>
                            <?php foreach ($class_groups as $cg): ?>
                                <option value="<?php echo $cg['cla_id']; ?>" data-maj-code="<?php echo $cg['cla_major_code']; ?>" <?php echo ($current_cla_id == $cg['cla_id']) ? 'selected' : ''; ?>>
                                    <?php echo $cg['cla_id'] . " - " . $cg['cla_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="space-y-4 pt-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 mb-4">บัญชีผู้ใช้</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1 ml-1">Username</label>
                            <input type="text" name="stu_username" required value="<?php echo $student['stu_username'] ?? ''; ?>" 
                                   class="w-full bg-slate-50 border-slate-200 font-mono text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1 ml-1">Password</label>
                            <input type="password" name="stu_password" <?php echo $student ? '' : 'required'; ?> 
                                   class="w-full bg-slate-50 border-slate-200 font-mono text-sm" placeholder="••••••">
                        </div>
                    </div>
                </div>

                <div class="pt-8 flex gap-4 border-t border-slate-100 mt-4">
                    <button type="submit" class="btn-cvc w-full justify-center text-base shadow-xl">
                        <i class="fa-solid fa-save mr-2"></i> บันทึกข้อมูล
                    </button>
                    <a href="manage_students.php" class="px-6 py-2.5 rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50 font-bold transition">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const careerGroupsData = <?php echo json_encode($career_groups); ?>;
    const majorsData = <?php echo json_encode($majors); ?>;
    const oldTypId = "<?php echo $current_typ_id; ?>";
    const oldCarId = "<?php echo $current_car_id; ?>";
    const oldMajId = "<?php echo $current_maj_id; ?>";

    function filterCareer() {
        const typeId = document.getElementById('sel_type').value;
        const careerSelect = document.getElementById('sel_career');
        const majorSelect = document.getElementById('sel_major');
        careerSelect.innerHTML = '<option value="">-- เลือกกลุ่มสมรรถนะ/อาชีพ --</option>';
        majorSelect.innerHTML = '<option value="">-- เลือกประเภทวิชาก่อน --</option>';
        majorSelect.disabled = true;
        if (typeId) {
            careerSelect.disabled = false;
            careerSelect.classList.remove('bg-slate-100');
            careerGroupsData.filter(c => c.typ_id == typeId).forEach(c => careerSelect.add(new Option(c.car_name, c.car_id)));
        } else {
            careerSelect.disabled = true;
            careerSelect.classList.add('bg-slate-100');
        }
        if(oldCarId && careerSelect.querySelector(`option[value="${oldCarId}"]`)) {
             careerSelect.value = oldCarId;
             filterMajor();
        }
    }

    function filterMajor() {
        const carId = document.getElementById('sel_career').value;
        const majorSelect = document.getElementById('sel_major');
        majorSelect.innerHTML = '<option value="">-- เลือกสาขาวิชา --</option>';
        if (carId) {
            majorSelect.disabled = false;
            majorSelect.classList.remove('bg-slate-100');
            majorsData.filter(m => m.car_id == carId).forEach(m => {
                let opt = new Option(m.maj_name, m.maj_id);
                opt.setAttribute('data-code', m.maj_code); 
                majorSelect.add(opt);
            });
        } else {
            majorSelect.disabled = true;
            majorSelect.classList.add('bg-slate-100');
        }
        if(oldMajId && majorSelect.querySelector(`option[value="${oldMajId}"]`)) {
             majorSelect.value = oldMajId;
             filterClassGroup();
        }
    }

    function filterClassGroup() {
        const majorSelect = document.getElementById('sel_major');
        const classSelect = document.getElementById('sel_class');
        let selectedMajCode = "";
        if (majorSelect.selectedIndex > 0) {
            selectedMajCode = majorSelect.options[majorSelect.selectedIndex].getAttribute('data-code');
        }
        let count = 0;
        for (let i = 0; i < classSelect.options.length; i++) {
            let opt = classSelect.options[i];
            let optMajCode = opt.getAttribute('data-maj-code');
            if (opt.value === "") continue;
            if (selectedMajCode && optMajCode === selectedMajCode) {
                opt.style.display = ""; opt.disabled = false; count++;
            } else if (selectedMajCode) {
                opt.style.display = "none"; opt.disabled = true;
            } else {
                opt.style.display = ""; opt.disabled = false;
            }
        }
        if (selectedMajCode) {
            if(count > 0) classSelect.options[0].text = `-- พบ ${count} กลุ่มเรียน --`;
            else { classSelect.options[0].text = "-- ไม่พบกลุ่มเรียน --"; classSelect.value = ""; }
        } else { classSelect.options[0].text = "-- เลือกสาขาวิชาเพื่อกรองกลุ่มเรียน --"; }
    }

    window.onload = function() {
        if(oldTypId) {
            document.getElementById('sel_type').value = oldTypId;
            filterCareer();
        }
    }
</script>
<?php require_once '../includes/footer.php'; ?>