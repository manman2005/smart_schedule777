<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

$class = null; $is_edit = false;
$levels = $pdo->query("SELECT * FROM levels ORDER BY lev_code ASC")->fetchAll();
$curriculums = $pdo->query("SELECT * FROM curriculums ORDER BY cur_year DESC")->fetchAll();
$types = $pdo->query("SELECT * FROM subject_types ORDER BY typ_code ASC")->fetchAll();
$career_groups = $pdo->query("SELECT * FROM career_groups ORDER BY car_code ASC")->fetchAll();
$majors = $pdo->query("SELECT * FROM majors ORDER BY maj_code ASC")->fetchAll();
$teachers = $pdo->query("SELECT * FROM teachers ORDER BY tea_fullname ASC")->fetchAll();

$edit_codes = ['year' => '', 'level' => '', 'type' => '', 'major' => '', 'group' => ''];
$current_year = date('Y') + 543;

if (isset($_GET['id'])) {
    $is_edit = true;
    $stmt = $pdo->prepare("SELECT * FROM class_groups WHERE cla_id = ?"); $stmt->execute([$_GET['id']]); $class = $stmt->fetch();
    if ($class) { 
        $id = $class['cla_id']; 
        $edit_codes['year'] = substr($id, 0, 2); 
        $edit_codes['level'] = substr($id, 2, 2); 
        $edit_codes['type'] = substr($id, 4, 2); 
        $edit_codes['major'] = substr($id, 6, 2); 
        $edit_codes['group'] = substr($id, 8, 2); 
        $current_student_year = $current_year - $class['cla_year'] + 1;
        if($current_student_year < 1) $current_student_year = 1; 
        if($current_student_year > 4) $current_student_year = 4;
    }
}
require_once '../includes/header.php';
?>

<div class="min-h-[80vh] flex flex-col justify-center items-center py-10">
    <div class="w-full max-w-4xl">
        <div class="mb-6 flex justify-between items-center">
            <a href="manage_class_groups.php" class="text-slate-400 hover:text-cvc-blue text-sm font-bold transition flex items-center gap-2"><i class="fa-solid fa-arrow-left"></i> ย้อนกลับ</a>
            <h2 class="text-xl font-serif font-bold text-slate-800"><?php echo $is_edit ? "แก้ไขกลุ่มเรียน" : "สร้างกลุ่มเรียนใหม่"; ?></h2>
        </div>

        <div class="card-premium p-8 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cvc-blue via-cvc-sky to-cvc-gold"></div>
            
            <form action="save_class_group.php" method="POST" class="space-y-8">
                <input type="hidden" name="mode" value="<?php echo $is_edit ? 'update' : 'insert'; ?>">
                <?php if ($is_edit): ?><input type="hidden" name="original_cla_id" value="<?php echo $class['cla_id']; ?>"><?php endif; ?>
                
                <div class="bg-indigo-50/50 p-6 rounded-2xl border border-indigo-100 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>
                    <h3 class="text-indigo-800 font-bold mb-6 flex items-center text-xs uppercase tracking-widest"><i class="fa-solid fa-wand-magic-sparkles mr-2"></i> ID Generator & ข้อมูลชั้นปี</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                        <div><label class="block text-xs text-slate-500 font-bold mb-1">1. ระดับการศึกษา</label><select id="sel_level" class="w-full text-sm py-2" onchange="filterCurriculum()"><option value="">-- เลือกระดับ --</option><?php foreach($levels as $l): ?><option value="<?php echo $l['lev_id']; ?>" data-code="<?php echo $l['lev_code']; ?>"><?php echo $l['lev_name']; ?></option><?php endforeach; ?></select></div>
                        <div><label class="block text-xs text-slate-500 font-bold mb-1">2. หลักสูตร</label><select id="sel_curriculum" class="w-full text-sm py-2 disabled:bg-slate-100" disabled onchange="filterType()"><option value="">-- รอเลือกระดับ --</option></select></div>
                        <div><label class="block text-xs text-slate-500 font-bold mb-1">3. ประเภทวิชา</label><select id="sel_type" class="w-full text-sm py-2 disabled:bg-slate-100" disabled onchange="filterCareer()"><option value="">-- รอเลือกหลักสูตร --</option></select></div>
                        <div><label class="block text-xs text-slate-500 font-bold mb-1">4. กลุ่มอาชีพ</label><select id="sel_career" class="w-full text-sm py-2 disabled:bg-slate-100" disabled onchange="filterMajor()"><option value="">-- รอเลือกประเภทวิชา --</option></select></div>
                        <div class="md:col-span-2"><label class="block text-xs text-slate-500 font-bold mb-1">5. สาขาวิชา</label><select id="sel_major" class="w-full text-sm py-2 disabled:bg-slate-100" disabled onchange="updateCodes()"><option value="">-- รอเลือกกลุ่มอาชีพ --</option></select></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 pt-6 border-t border-indigo-200">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2">ปีที่เข้าเรียน (พ.ศ.) <span class="text-red-500">*</span></label>
                            <input type="number" name="cla_year" id="input_year" required 
                                   value="<?php echo $class['cla_year'] ?? $current_year; ?>" 
                                   class="w-full text-lg py-2.5 font-bold text-center text-indigo-700 bg-white border border-indigo-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none" 
                                   oninput="updateYearCode()">
                            <p class="text-[10px] text-slate-400 mt-1 ml-1">ใช้สร้าง 2 หลักแรกของรหัส</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2">ระดับชั้นปี (ปัจจุบัน)</label>
                            <select id="sel_student_year" class="w-full text-lg py-2.5 font-bold text-indigo-700 bg-white border border-indigo-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none" onchange="calculateAdmissionYear()">
                                <option value="1">ปี 1</option>
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1 ml-1">ใช้คำนวณปีเข้าเรียนอัตโนมัติ</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2">ลำดับกลุ่ม (ห้องที่) <span class="text-red-500">*</span></label>
                            <input type="number" name="cla_group_no" id="input_group_no" value="<?php echo $is_edit ? intval($edit_codes['group']) : ''; ?>" placeholder="1" required min="1" max="99" class="w-full font-bold text-center text-lg text-indigo-700 bg-white border border-indigo-200 rounded-xl" oninput="updateGroupCode()">
                        </div>
                    </div>

                    <div class="bg-slate-800 p-6 rounded-xl flex flex-col items-center justify-center shadow-inner">
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-3 tracking-[0.2em]">GENERATED ID</label>
                        <div class="flex gap-2 mb-4 flex-wrap justify-center">
                            <input type="hidden" name="cla_year_code" id="code_year"><input type="hidden" name="cla_level_code" id="code_level"><input type="hidden" name="cla_type_code" id="code_type"><input type="hidden" name="cla_major_code" id="code_major"><input type="hidden" name="cla_group_no" id="code_group">
                            
                            <div class="w-10 h-12 bg-gradient-to-b from-blue-600 to-slate-900 border border-blue-500/30 text-white flex items-center justify-center font-mono font-bold text-lg rounded shadow-lg" id="view_year">XX</div>
                            
                            <div class="w-10 h-12 bg-gradient-to-b from-blue-600 to-slate-900 border border-blue-500/30 text-white flex items-center justify-center font-mono font-bold text-lg rounded shadow-lg" id="view_level">XX</div>
                            <div class="w-10 h-12 bg-gradient-to-b from-blue-600 to-slate-900 border border-blue-500/30 text-white flex items-center justify-center font-mono font-bold text-lg rounded shadow-lg" id="view_type">XX</div>
                            <div class="w-10 h-12 bg-gradient-to-b from-blue-600 to-slate-900 border border-blue-500/30 text-white flex items-center justify-center font-mono font-bold text-lg rounded shadow-lg" id="view_major">XX</div>
                            
                            <div class="w-10 h-12 bg-gradient-to-b from-blue-600 to-slate-900 border border-blue-500/30 text-white flex items-center justify-center font-mono font-bold text-lg rounded shadow-lg" id="view_group">XX</div>
                        </div>
                        <div class="text-2xl font-black text-white tracking-widest font-mono" id="preview_id">----------</div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">ชื่อย่อกลุ่มเรียน <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" name="cla_name" required value="<?php echo $class['cla_name'] ?? ''; ?>" placeholder="เช่น สสส, บช, ชอ" class="w-full pl-4 pr-10">
                                <i class="fa-solid fa-tag absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 ml-1">* ใส่เฉพาะชื่อย่อ เช่น "สสส"</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">ครูที่ปรึกษา</label>
                            <select name="tea_id" class="w-full text-sm py-2">
                                <option value="">-- เลือกครูที่ปรึกษา --</option>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?php echo $t['tea_id']; ?>" <?php echo ($class && $class['tea_id'] == $t['tea_id']) ? 'selected' : ''; ?>>
                                        <?php echo $t['tea_fullname']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <input type="hidden" name="cla_note" value="<?php echo $class['cla_note'] ?? ''; ?>">
                </div>

                <div class="pt-4 flex gap-4 border-t border-slate-100">
                    <button type="submit" class="btn-cvc w-full justify-center text-base shadow-xl"><i class="fa-solid fa-save mr-2"></i> บันทึกข้อมูล</button>
                    <a href="manage_class_groups.php" class="px-6 py-2.5 rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50 font-bold transition">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const currentYear = <?php echo $current_year; ?>;
    const isEdit = <?php echo $is_edit ? 'true' : 'false'; ?>;
    const editStudentYear = <?php echo $current_student_year ?? 1; ?>;
    
    // JS Logic
    const curriculums = <?php echo json_encode($curriculums); ?>; const types = <?php echo json_encode($types); ?>; const career_groups = <?php echo json_encode($career_groups); ?>; const majors = <?php echo json_encode($majors); ?>; const editCodes = <?php echo json_encode($edit_codes); ?>;

    function calculateAdmissionYear() {
        const studentYear = parseInt(document.getElementById('sel_student_year').value);
        const admissionYear = currentYear - studentYear + 1;
        document.getElementById('input_year').value = admissionYear;
        updateYearCode();
    }

    function updateYearCode() { 
        const val = document.getElementById('input_year').value; 
        const code = (val && val.length === 4) ? val.substring(2, 4) : ''; 
        document.getElementById('code_year').value = code; 
        document.getElementById('view_year').innerText = code || 'XX'; 
        updatePreview(); 
    }
    
    function updateGroupCode() { 
        const val = document.getElementById('input_group_no').value; 
        const code = val ? val.toString().padStart(2, '0') : ''; 
        document.getElementById('code_group').value = code; 
        document.getElementById('view_group').innerText = code || 'XX'; 
        updatePreview(); 
    }

    function filterCurriculum() { 
        const levId = document.getElementById('sel_level').value; 
        const selLevel = document.getElementById('sel_level');
        const code = selLevel.selectedIndex > 0 ? selLevel.options[selLevel.selectedIndex].getAttribute('data-code') : ''; 
        
        document.getElementById('code_level').value = code; 
        document.getElementById('view_level').innerText = code || 'XX'; 
        
        const selYear = document.getElementById('sel_student_year');
        selYear.innerHTML = '';
        if (code === '01') { 
            selYear.add(new Option("ปี 1", 1));
            selYear.add(new Option("ปี 2", 2));
            selYear.add(new Option("ปี 3", 3));
        } else { 
            selYear.add(new Option("ปี 1", 1));
            selYear.add(new Option("ปี 2", 2));
        }
        
        if(isEdit) selYear.value = editStudentYear;
        calculateAdmissionYear(); 

        const selCur = document.getElementById('sel_curriculum'); 
        selCur.innerHTML = '<option value="">-- เลือกหลักสูตร --</option>'; 
        selCur.disabled = !levId; 
        document.getElementById('sel_type').disabled = true; 
        document.getElementById('sel_career').disabled = true; 
        document.getElementById('sel_major').disabled = true; 
        
        if(levId) { curriculums.filter(c => c.lev_id == levId).forEach(c => { selCur.add(new Option("หลักสูตรปี " + c.cur_year, c.cur_id)); }); } 
        updatePreview(); 
    }

    // ฟังก์ชันอื่นๆ คงเดิม
    function filterType() { const curId = document.getElementById('sel_curriculum').value; const selType = document.getElementById('sel_type'); selType.innerHTML = '<option value="">-- เลือกประเภทวิชา --</option>'; selType.disabled = !curId; document.getElementById('sel_career').disabled = true; document.getElementById('sel_major').disabled = true; if(curId) { types.filter(t => t.cur_id == curId).forEach(t => { let opt = new Option(t.typ_name, t.typ_id); opt.setAttribute('data-code', t.typ_code); selType.add(opt); }); } }
    function filterCareer() { const typId = document.getElementById('sel_type').value; const selCareer = document.getElementById('sel_career'); const selType = document.getElementById('sel_type'); const code = selType.selectedIndex > 0 ? selType.options[selType.selectedIndex].getAttribute('data-code') : ''; document.getElementById('code_type').value = code; document.getElementById('view_type').innerText = code || 'XX'; selCareer.innerHTML = '<option value="">-- เลือกกลุ่มอาชีพ --</option>'; selCareer.disabled = !typId; document.getElementById('sel_major').disabled = true; if(typId) { career_groups.filter(c => c.typ_id == typId).forEach(c => { selCareer.add(new Option(c.car_name, c.car_id)); }); } updatePreview(); }
    function filterMajor() { const carId = document.getElementById('sel_career').value; const selMajor = document.getElementById('sel_major'); selMajor.innerHTML = '<option value="">-- เลือกสาขาวิชา --</option>'; selMajor.disabled = !carId; if(carId) { majors.filter(m => m.car_id == carId).forEach(m => { let opt = new Option(m.maj_name, m.maj_id); opt.setAttribute('data-code', m.maj_code); selMajor.add(opt); }); } updateCodes(); }
    function updateCodes() { const selMajor = document.getElementById('sel_major'); const code = selMajor.selectedIndex > 0 ? selMajor.options[selMajor.selectedIndex].getAttribute('data-code') : ''; document.getElementById('code_major').value = code; document.getElementById('view_major').innerText = code || 'XX'; updatePreview(); }
    function updatePreview() { const parts = ['view_year', 'view_level', 'view_type', 'view_major', 'view_group'].map(id => document.getElementById(id).innerText); const full = parts.join(''); document.getElementById('preview_id').innerText = full.includes('XX') ? '----------' : full; }
    
    function autoFillEditMode() { 
        if(!isEdit) return; 
        const selLevel = document.getElementById('sel_level'); for(let i=0; i<selLevel.options.length; i++) { if(selLevel.options[i].getAttribute('data-code') === editCodes.level) { selLevel.selectedIndex = i; filterCurriculum(); break; } } 
        const selCur = document.getElementById('sel_curriculum'); const targetYear = "25" + editCodes.year; let curIdFound = null; for(let i=0; i<selCur.options.length; i++) { if(selCur.options[i].text.includes(targetYear)) { selCur.selectedIndex = i; curIdFound = selCur.value; filterType(); break; } } if(!curIdFound && selCur.options.length > 1) { selCur.selectedIndex = 1; filterType(); } 
        const selType = document.getElementById('sel_type'); for(let i=0; i<selType.options.length; i++) { if(selType.options[i].getAttribute('data-code') === editCodes.type) { selType.selectedIndex = i; filterCareer(); break; } } 
        const selCareer = document.getElementById('sel_career'); const selMajor = document.getElementById('sel_major'); const targetMajor = majors.find(m => m.maj_code === editCodes.major); if (targetMajor) { const targetCarId = targetMajor.car_id; for(let i=0; i<selCareer.options.length; i++) { if(selCareer.options[i].value == targetCarId) { selCareer.selectedIndex = i; filterMajor(); break; } } for(let i=0; i<selMajor.options.length; i++) { if(selMajor.options[i].getAttribute('data-code') === editCodes.major) { selMajor.selectedIndex = i; updateCodes(); break; } } } 
    }
    
    window.onload = function() { 
        updateYearCode(); 
        updateGroupCode(); 
        if(isEdit) setTimeout(autoFillEditMode, 100);
        else calculateAdmissionYear(); // Default run for insert mode
    }
</script>
<?php require_once '../includes/footer.php'; ?>