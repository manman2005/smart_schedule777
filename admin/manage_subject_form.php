<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

// ดึงข้อมูล Master Data สำหรับ Dropdown ต่างๆ
$groups = $pdo->query("SELECT * FROM subject_groups ORDER BY sug_id ASC")->fetchAll();
$curriculums = $pdo->query("SELECT c.*, l.lev_name FROM curriculums c JOIN levels l ON c.lev_id = l.lev_id ORDER BY c.cur_year DESC")->fetchAll();
$majors = $pdo->query("SELECT m.*, c.cur_year FROM majors m LEFT JOIN curriculums c ON m.cur_id = c.cur_id ORDER BY m.maj_code ASC")->fetchAll();

// เตรียมข้อมูลห้องเรียนสำหรับ Filter ใน JavaScript
$room_types_list = $pdo->query("SELECT DISTINCT roo_type FROM rooms WHERE roo_type IS NOT NULL AND roo_type != '' ORDER BY roo_type ASC")->fetchAll(PDO::FETCH_COLUMN);
$all_rooms = $pdo->query("SELECT * FROM rooms ORDER BY roo_type, roo_id ASC")->fetchAll();
$rooms_by_type = [];
foreach ($all_rooms as $room) $rooms_by_type[$room['roo_type']][] = $room;

$subject = null; 
$title = "เพิ่มรายวิชาใหม่"; 
$t = 1; $p = 2; $n = 2; // ค่า Default ท-ป-น

if (isset($_GET['id'])) {
    $title = "แก้ไขรายวิชา";
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE sub_id = ?");
    $stmt->execute([$_GET['id']]);
    $subject = $stmt->fetch();
    if (!empty($subject['sub_th_pr_ot'])) { 
        $parts = explode('-', $subject['sub_th_pr_ot']); 
        if (count($parts) == 3) { $t = $parts[0]; $p = $parts[1]; $n = $parts[2]; } 
    }
}
require_once '../includes/header.php';
?>

<div class="min-h-[80vh] flex flex-col justify-center items-center py-10">
    <div class="w-full max-w-3xl">
        <div class="mb-6 flex justify-between items-center">
            <a href="manage_subjects.php" class="text-slate-400 hover:text-cvc-blue text-sm font-bold transition flex items-center gap-2"><i class="fa-solid fa-arrow-left"></i> ย้อนกลับ</a>
            <h2 class="text-xl font-serif font-bold text-slate-800"><?php echo $title; ?></h2>
        </div>

        <div class="card-premium p-8 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cvc-blue via-cvc-sky to-cvc-gold"></div>
            
            <form action="save_subject.php" method="POST" class="space-y-8">
                <?php if ($subject): ?><input type="hidden" name="sub_id" value="<?php echo $subject['sub_id']; ?>"><?php endif; ?>
                <input type="hidden" name="sub_th_pr_ot" id="full_tpn" value="<?php echo "$t-$p-$n"; ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">รหัสวิชา <span class="text-red-500">*</span></label>
                        <input type="text" name="sub_code" required value="<?php echo $subject['sub_code'] ?? ''; ?>" placeholder="เช่น 20000-1101" class="w-full font-mono font-bold text-slate-700 bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ชื่อวิชา <span class="text-red-500">*</span></label>
                        <input type="text" name="sub_name" required value="<?php echo $subject['sub_name'] ?? ''; ?>" class="w-full bg-slate-50">
                    </div>
                </div>

                <div class="bg-amber-50 p-6 rounded-2xl border border-amber-100 relative overflow-hidden">
                    <label class="block text-sm font-bold text-amber-700 mb-4 flex items-center"><i class="fa-solid fa-calculator mr-2"></i> คำนวณหน่วยกิต (ท-ป-น)</label>
                    <div class="flex items-end gap-3 md:gap-6">
                        <div class="flex-1"><label class="text-[10px] text-amber-600 mb-1 block text-center uppercase">ทฤษฎี</label><input type="number" id="theory" min="0" value="<?php echo $t; ?>" class="w-full text-center font-bold text-xl bg-white border-amber-200 text-slate-700" oninput="calculateAll()"></div>
                        <div class="pb-4 text-amber-300 font-bold text-xl">-</div>
                        <div class="flex-1"><label class="text-[10px] text-amber-600 mb-1 block text-center uppercase">ปฏิบัติ</label><input type="number" id="practice" min="0" value="<?php echo $p; ?>" class="w-full text-center font-bold text-xl bg-white border-amber-200 text-slate-700" oninput="calculateAll()"></div>
                        <div class="pb-4 text-amber-300 font-bold text-xl">-</div>
                        <div class="flex-1"><label class="text-[10px] text-amber-600 mb-1 block text-center uppercase font-bold">หน่วยกิต</label><input type="number" name="sub_credit" id="credit" min="0" value="<?php echo $n; ?>" class="w-full text-center font-black text-2xl bg-white border-amber-300 text-amber-600 shadow-sm" oninput="calculateAll()"></div>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-xs bg-white/50 p-3 rounded-lg border border-amber-100">
                        <span class="text-amber-700 font-bold">รวมชั่วโมงสอนต่อสัปดาห์:</span>
                        <div class="relative w-20"><input type="number" name="sub_hours" id="hoursInput" readonly value="<?php echo $subject['sub_hours'] ?? ($t+$p); ?>" class="w-full bg-transparent text-right font-bold text-amber-800 pr-6 outline-none text-lg"><span class="absolute right-0 top-1 text-amber-600">ชม.</span></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">หมวดวิชา <span class="text-red-500">*</span></label><select name="sug_id" id="sug_id" required onchange="updateFormFields()" class="w-full text-sm py-2"><option value="" data-name="">-- เลือกหมวดวิชา --</option><?php foreach ($groups as $g): ?><option value="<?php echo $g['sug_id']; ?>" data-name="<?php echo htmlspecialchars($g['sug_name']); ?>" <?php echo ($subject && $subject['sug_id'] == $g['sug_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($g['sug_name']); ?></option><?php endforeach; ?></select></div>
                    <div><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">หลักสูตร <span class="text-red-500">*</span></label><select name="cur_id" required class="w-full text-sm py-2"><option value="">-- เลือกหลักสูตร --</option><?php foreach ($curriculums as $c): ?><option value="<?php echo $c['cur_id']; ?>" <?php echo ($subject && $subject['cur_id'] == $c['cur_id']) ? 'selected' : ''; ?>><?php echo $c['lev_name'] . " " . $c['cur_year']; ?></option><?php endforeach; ?></select></div>
                    <div id="major_field_container" class="md:col-span-2 hidden"><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">สาขาวิชาที่สังกัด</label><select name="maj_id" id="maj_id" class="w-full text-sm py-2"><option value="">-- ระบุสาขาวิชา --</option><option value="ALL">ทุกสาขาวิชา (วิชาพื้นฐานวิชาชีพ)</option><?php foreach ($majors as $m): ?><option value="<?php echo $m['maj_id']; ?>" <?php echo ($subject && $subject['maj_id'] == $m['maj_id']) ? 'selected' : ''; ?>><?php echo $m['maj_code'] . " - " . $m['maj_name']; ?></option><?php endforeach; ?></select></div>
                    <div class="md:col-span-2" id="competency_container"><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">กลุ่มสมรรถนะ</label><input type="text" name="sub_competency" id="sub_competency" value="<?php echo $subject['sub_competency'] ?? ''; ?>" class="w-full bg-slate-50"></div>
                </div>

                <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100">
                    <label class="block text-sm font-bold text-blue-700 mb-4 flex items-center"><i class="fa-solid fa-door-open mr-2"></i> การใช้ห้องเรียน</label>
                    
                    <div class="mb-4 pb-4 border-b border-blue-100/50">
                        <label class="block text-xs font-bold text-slate-500 mb-2">1. สำหรับการเรียนทฤษฎี</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <select id="filter_theory" class="w-full text-sm py-2 bg-white" onchange="updateRoomOptions('theory')">
                                    <option value="">-- เลือกประเภทห้อง (กรอง) --</option>
                                    <?php foreach ($room_types_list as $rt): ?>
                                        <option value="<?php echo $rt; ?>"><?php echo $rt; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <select name="sub_room_theory" id="target_theory" class="w-full text-sm py-2 text-blue-700 font-bold bg-white">
                                    <option value="">-- ไม่ระบุ (ไม่ใช้ห้อง) --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-2">2. สำหรับการเรียนปฏิบัติ</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <select id="filter_practice" class="w-full text-sm py-2 bg-white" onchange="updateRoomOptions('practice')">
                                    <option value="">-- เลือกประเภทห้อง (กรอง) --</option>
                                    <?php foreach ($room_types_list as $rt): ?>
                                        <option value="<?php echo $rt; ?>"><?php echo $rt; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <select name="sub_room_practice" id="target_practice" class="w-full text-sm py-2 text-blue-700 font-bold bg-white">
                                    <option value="">-- ไม่ระบุ (ไม่ใช้ห้อง) --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex gap-4 border-t border-slate-100">
                    <button type="submit" class="btn-cvc w-full justify-center text-base shadow-xl"><i class="fa-solid fa-save mr-2"></i> บันทึกข้อมูล</button>
                    <a href="manage_subjects.php" class="px-6 py-2.5 rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50 font-bold transition">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // คำนวณหน่วยกิต
    function calculateAll() { 
        let t = parseInt(document.getElementById('theory').value) || 0; 
        let p = parseInt(document.getElementById('practice').value) || 0; 
        let n = parseInt(document.getElementById('credit').value) || 0; 
        document.getElementById('hoursInput').value = t + p; 
        document.getElementById('full_tpn').value = `${t}-${p}-${n}`; 
    }

    // ข้อมูลห้องเรียนจาก PHP (สำหรับ JS)
    const roomsByType = <?php echo json_encode($rooms_by_type); ?>; 
    const roomTypes = <?php echo json_encode($room_types_list); ?>;
    
    // ค่าเดิมที่บันทึกไว้ในฐานข้อมูล (กรณีแก้ไข)
    // หมายเหตุ: ใช้ @ นำหน้าเพื่อกัน Error กรณีคอลัมน์ยังไม่ถูกสร้างใน DB (แต่ควรสร้างแล้ว)
    const savedTheory = "<?php echo $subject['sub_room_theory'] ?? ''; ?>";
    const savedPractice = "<?php echo $subject['sub_room_practice'] ?? ''; ?>";

    // ฟังก์ชันจัดการ Dropdown ห้องเรียน (ใช้ได้ทั้ง Theory และ Practice)
    function updateRoomOptions(type, initialValue = null) {
        const filterId = `filter_${type}`;
        const targetId = `target_${type}`;
        
        const filterType = document.getElementById(filterId).value;
        const targetSelect = document.getElementById(targetId);
        
        // เก็บค่าที่เลือกปัจจุบันไว้ เพื่อคงค่าไว้หลัง filter เปลี่ยน
        const currentValue = initialValue || targetSelect.value; 

        targetSelect.innerHTML = '<option value="">-- ไม่ระบุ (ไม่ใช้ห้อง) --</option>'; // Default Option

        if (filterType) {
            // เพิ่มตัวเลือก "ใช้ประเภทนี้ (ไม่ระบุห้อง)"
            let anyOption = new Option(`★ ใช้${filterType} (ไม่ระบุห้องเจาะจง)`, filterType);
            targetSelect.add(anyOption);

            // เพิ่มรายชื่อห้องในประเภทนั้น
            if (roomsByType[filterType]) {
                roomsByType[filterType].forEach(room => {
                    let opt = new Option(`${room.roo_id} - ${room.roo_name}`, room.roo_id);
                    targetSelect.add(opt);
                });
            }
        } 

        // คืนค่าที่เลือกไว้เดิม (ถ้ามี)
        if (currentValue) {
             let exists = false;
             for(let i=0; i<targetSelect.options.length; i++){
                 if(targetSelect.options[i].value == currentValue) exists = true;
             }
             
             if(!exists && initialValue) {
                 // ถ้าเป็นค่าเริ่มต้นจาก DB แล้วหาไม่เจอใน filter (อาจจะคนละประเภทกับที่เลือก)
                 // ให้เพิ่มเข้าไปชั่วคราวเพื่อให้แสดงผลได้ถูกต้อง
                 let opt = new Option(`${currentValue} (ค่าเดิม)`, currentValue);
                 opt.selected = true;
                 targetSelect.add(opt);
             } else if(exists) {
                 targetSelect.value = currentValue;
             }
        }
    }

    // ฟังก์ชันช่วยหาว่าห้อง ID นี้อยู่ประเภทไหน (เพื่อ Auto Select Filter ตอนโหลด)
    function findTypeForRoom(roomId) {
        // กรณี roomId ตรงกับชื่อประเภทห้อง (เช่นบันทึกว่า "ห้องปฏิบัติการ" ตรงๆ)
        if(roomTypes.includes(roomId)) return roomId; 
        
        // กรณี roomId เป็นรหัสห้อง (เช่น 111) ให้หาว่าอยู่ Type ไหน
        for (const [type, rooms] of Object.entries(roomsByType)) {
            if (rooms.find(r => r.roo_id == roomId)) return type;
        }
        return "";
    }

    // ฟังก์ชันเปิด/ปิด Input สาขาวิชา/สมรรถนะ ตามหมวดวิชา
    function updateFormFields() { 
        const sugSelect = document.getElementById('sug_id'); 
        const selectedOption = sugSelect.options[sugSelect.selectedIndex]; 
        let sugName = selectedOption.getAttribute('data-name') || selectedOption.text || ""; 
        const isCoreOrActivity = sugName.includes('แกนกลาง') || sugName.includes('กิจกรรม'); 
        document.getElementById('major_field_container').style.display = isCoreOrActivity ? 'none' : 'block'; 
        document.getElementById('maj_id').required = !isCoreOrActivity; 
        const isActivity = sugName.includes('กิจกรรม'); 
        document.getElementById('competency_container').style.display = isActivity ? 'none' : 'block'; 
        if(isActivity) document.getElementById('sub_competency').value = ''; 
    }

    // เริ่มทำงานเมื่อโหลดหน้า
    window.addEventListener('load', function() { 
        calculateAll(); 
        updateFormFields();

        // ตั้งค่าห้องทฤษฎี (ถ้ามีค่าเดิม)
        if(savedTheory) {
            let type = findTypeForRoom(savedTheory);
            if(type) document.getElementById('filter_theory').value = type;
            updateRoomOptions('theory', savedTheory);
        } else {
            updateRoomOptions('theory');
        }

        // ตั้งค่าห้องปฏิบัติ (ถ้ามีค่าเดิม)
        if(savedPractice) {
            let type = findTypeForRoom(savedPractice);
            if(type) document.getElementById('filter_practice').value = type;
            updateRoomOptions('practice', savedPractice);
        } else {
            updateRoomOptions('practice');
        }
    });
</script>
<?php require_once '../includes/footer.php'; ?>