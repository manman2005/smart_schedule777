<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

// ลบข้อมูล
if (isset($_GET['delete'])) {
    try {
        $pdo->prepare("DELETE FROM majors WHERE maj_id = ?")->execute([$_GET['delete']]);
        header("Location: manage_majors.php"); exit();
    } catch (Exception $e) { 
        echo "<script>alert('ไม่สามารถลบข้อมูลได้ เนื่องจากมีการใช้งานอยู่'); window.location='manage_majors.php';</script>"; 
        exit(); 
    }
}

// ดึงข้อมูลเพื่อแก้ไข
$edit_data = null;
if (isset($_GET['edit'])) {
    // ป้องกันกรณี ?edit=0 (เพิ่มใหม่)
    if ($_GET['edit'] != 0) {
        $stmt = $pdo->prepare("SELECT * FROM majors WHERE maj_id = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_data = $stmt->fetch();
    }
}

// บันทึกข้อมูล (เพิ่ม/แก้ไข)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่า ID (ถ้าไม่มี หรือเป็นค่าว่าง ให้เป็น null)
    $id = (isset($_POST['maj_id']) && $_POST['maj_id'] !== '') ? $_POST['maj_id'] : null;
    $car_id = $_POST['car_id'];
    $code = trim($_POST['code']);
    $name = trim($_POST['name']);

    try {
        // 1. ดึง cur_id และ typ_id จากกลุ่มอาชีพที่เลือก (เพื่อความถูกต้องของข้อมูล)
        $grpStmt = $pdo->prepare("SELECT cur_id, typ_id FROM career_groups WHERE car_id = ?");
        $grpStmt->execute([$car_id]);
        $g = $grpStmt->fetch();

        if (!$g) {
            throw new Exception("ไม่พบข้อมูลกลุ่มอาชีพที่เลือก");
        }

        // 2. ตรวจสอบรหัสซ้ำ
        $checkSql = "SELECT COUNT(*) FROM majors WHERE maj_code = ?";
        $checkParams = [$code];
        if ($id !== null) {
            $checkSql .= " AND maj_id != ?";
            $checkParams[] = $id;
        }
        $stmtCheck = $pdo->prepare($checkSql);
        $stmtCheck->execute($checkParams);
        if ($stmtCheck->fetchColumn() > 0) {
            throw new Exception("รหัสสาขาวิชา ($code) มีอยู่ในระบบแล้ว");
        }

        // 3. บันทึกข้อมูล
        if ($id !== null) {
            // Update
            $sql = "UPDATE majors SET cur_id=?, typ_id=?, car_id=?, maj_code=?, maj_name=? WHERE maj_id=?";
            $pdo->prepare($sql)->execute([$g['cur_id'], $g['typ_id'], $car_id, $code, $name, $id]);
        } else {
            // Insert (สร้าง ID เอง)
            $maxStmt = $pdo->query("SELECT MAX(maj_id) FROM majors");
            $maxId = $maxStmt->fetchColumn();
            $newId = ($maxId) ? $maxId + 1 : 1;

            $sql = "INSERT INTO majors (maj_id, cur_id, typ_id, car_id, maj_code, maj_name) VALUES (?,?,?,?,?,?)";
            $pdo->prepare($sql)->execute([$newId, $g['cur_id'], $g['typ_id'], $car_id, $code, $name]);
        }
        
        header("Location: manage_majors.php"); 
        exit();

    } catch (Exception $e) {
        $msg = $e->getMessage();
        echo "<script>alert('เกิดข้อผิดพลาด: $msg'); window.history.back();</script>";
        exit();
    }
}

$sql = "SELECT m.*, g.car_name, t.typ_name, c.cur_year, l.lev_name FROM majors m JOIN career_groups g ON m.car_id = g.car_id JOIN subject_types t ON m.typ_id = t.typ_id JOIN curriculums c ON m.cur_id = c.cur_id JOIN levels l ON c.lev_id = l.lev_id ORDER BY m.maj_code ASC";
$data = $pdo->query($sql)->fetchAll();
$curriculums = $pdo->query("SELECT c.*, l.lev_name, l.lev_code FROM curriculums c JOIN levels l ON c.lev_id = l.lev_id ORDER BY l.lev_code ASC, c.cur_year DESC")->fetchAll();
$types = $pdo->query("SELECT * FROM subject_types")->fetchAll();
$groups = $pdo->query("SELECT * FROM career_groups")->fetchAll();

require_once '../includes/header.php';
?>

<div class="max-w-6xl mx-auto pb-12">
    <div class="mb-8">
        <a href="index.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2"><i class="fa-solid fa-arrow-left mr-2"></i> Dashboard</a>
        <h1 class="text-3xl font-serif font-bold text-slate-800">ข้อมูลสาขาวิชา <span class="text-slate-400 text-lg font-sans font-normal">(Majors)</span></h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">      
            <div class="card-premium p-6 sticky top-24 border-t-4 border-t-purple-500">
                <h2 class="font-bold text-lg text-slate-700 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center"><i class="fa-solid <?php echo $edit_data ? 'fa-pen' : 'fa-plus'; ?>"></i></div>
                    <?php echo $edit_data ? 'แก้ไขข้อมูล' : 'เพิ่มข้อมูลใหม่'; ?>
                </h2>
                <form method="POST" class="space-y-4">
                    <?php if($edit_data): ?><input type="hidden" name="maj_id" value="<?php echo $edit_data['maj_id']; ?>"><?php endif; ?>
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">1. เลือกหลักสูตร</label>
                            <select id="cur_id" class="w-full text-sm py-2" onchange="filterTypes()">
                                <option value="">-- กรุณาเลือก --</option>
                                <?php foreach($curriculums as $c): ?>
                                    <option value="<?php echo $c['cur_id']; ?>" <?php echo ($edit_data && $edit_data['cur_id'] == $c['cur_id']) ? 'selected' : ''; ?>>
                                        <?php echo $c['lev_name']." ".$c['cur_year']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">2. เลือกประเภทวิชา</label>
                            <select id="typ_id" class="w-full text-sm py-2 disabled:bg-slate-200" disabled onchange="filterGroups()">
                                <option value="">-- รอเลือกหลักสูตร --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">3. เลือกกลุ่มอาชีพ</label>
                            <select name="car_id" id="car_id" required class="w-full text-sm py-2 disabled:bg-slate-200" disabled>
                                <option value="">-- รอเลือกประเภท --</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">รหัสสาขา <span class="text-red-500">*</span></label>
                        <input type="text" name="code" required value="<?php echo $edit_data['maj_code'] ?? ''; ?>" class="w-full font-mono font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ชื่อสาขาวิชา <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="<?php echo $edit_data['maj_name'] ?? ''; ?>" class="w-full">
                    </div>
                    <div class="pt-2 flex gap-2">
                        <button type="submit" class="btn-cvc flex-1 justify-center shadow-md"><i class="fa-solid fa-save mr-1"></i> บันทึก</button>
                        <?php if(isset($_GET['edit'])): ?>
                            <a href="manage_majors.php" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 text-sm font-bold transition">ยกเลิก</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="card-premium overflow-hidden border-0 shadow-lg">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase w-24 text-center">รหัส</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ชื่อสาขาวิชา</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">สังกัด (กลุ่ม/ประเภท)</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center w-24">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php foreach($data as $r): ?>
                            <tr class="hover:bg-blue-50/30 transition duration-150 <?php echo ($edit_data && $edit_data['maj_id'] == $r['maj_id']) ? 'bg-purple-50 ring-2 ring-inset ring-purple-100' : ''; ?>">
                                <td class="px-6 py-4 text-center"><span class="font-mono font-bold text-purple-600 bg-purple-50 px-2 py-1 rounded border border-purple-100 shadow-sm"><?php echo $r['maj_code']; ?></span></td>
                                <td class="px-6 py-4 font-bold text-slate-700"><?php echo htmlspecialchars($r['maj_name']); ?></td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-bold text-slate-600"><i class="fa-solid fa-briefcase text-purple-400 mr-1"></i><?php echo $r['car_name']; ?></div>
                                    <div class="text-[10px] text-slate-400 mt-0.5"><span class="bg-slate-50 px-1 rounded border border-slate-100"><?php echo $r['typ_name']; ?></span> <span class="bg-slate-50 px-1 rounded border border-slate-100"><?php echo $r['lev_name']; ?></span></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="?edit=<?php echo $r['maj_id']; ?>" class="w-8 h-8 rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50 flex items-center justify-center transition"><i class="fa-solid fa-pen-to-square text-xs"></i></a>
                                        <a href="?delete=<?php echo $r['maj_id']; ?>" onclick="return confirm('ยืนยันลบ?');" class="w-8 h-8 rounded-lg border border-slate-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition"><i class="fa-solid fa-trash-can text-xs"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        const typesData = <?php echo json_encode($types); ?>; 
        const groupsData = <?php echo json_encode($groups); ?>; 
        const currentTypId = "<?php echo $edit_data['typ_id'] ?? ''; ?>"; 
        const currentCarId = "<?php echo $edit_data['car_id'] ?? ''; ?>";
        
        function filterTypes() { 
            const curId = document.getElementById('cur_id').value; 
            const typeSelect = document.getElementById('typ_id'); 
            const groupSelect = document.getElementById('car_id'); 
            
            typeSelect.innerHTML = '<option value="">-- กรุณาเลือก --</option>'; 
            groupSelect.innerHTML = '<option value="">-- กรุณาเลือกประเภทวิชาก่อน --</option>'; 
            groupSelect.disabled = true; 
            
            if (curId) { 
                typeSelect.disabled = false; 
                typesData.filter(t => t.cur_id == curId).forEach(t => { 
                    const opt = new Option(t.typ_name, t.typ_id); 
                    if(t.typ_id == currentTypId) opt.selected = true; 
                    typeSelect.add(opt); 
                }); 
                if(currentTypId) filterGroups(); 
            } else { 
                typeSelect.disabled = true; 
            } 
        }
        
        function filterGroups() { 
            const typeId = document.getElementById('typ_id').value; 
            const groupSelect = document.getElementById('car_id'); 
            
            groupSelect.innerHTML = '<option value="">-- กรุณาเลือก --</option>'; 
            
            if (typeId) { 
                groupSelect.disabled = false; 
                groupsData.filter(g => g.typ_id == typeId).forEach(g => { 
                    const opt = new Option(g.car_name, g.car_id); 
                    if(g.car_id == currentCarId) opt.selected = true; 
                    groupSelect.add(opt); 
                }); 
            } else { 
                groupSelect.disabled = true; 
            } 
        }
        
        // Auto-Run if in edit mode
        if(document.getElementById('cur_id').value) { filterTypes(); }
    </script>
</div>
<?php require_once '../includes/footer.php'; ?>