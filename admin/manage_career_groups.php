<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

// ลบข้อมูล
if (isset($_GET['delete'])) {
    try {
        $pdo->prepare("DELETE FROM career_groups WHERE car_id = ?")->execute([$_GET['delete']]);
        header("Location: manage_career_groups.php"); exit();
    } catch (Exception $e) { 
        echo "<script>alert('ไม่สามารถลบข้อมูลได้ เนื่องจากมีการใช้งานอยู่'); window.location='manage_career_groups.php';</script>"; 
        exit(); 
    }
}

// ดึงข้อมูลเพื่อแก้ไข
$edit_data = null;
if (isset($_GET['edit'])) {
    // ป้องกันกรณี ?edit=0 (เพิ่มใหม่)
    if ($_GET['edit'] != 0) {
        $stmt = $pdo->prepare("SELECT * FROM career_groups WHERE car_id = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_data = $stmt->fetch();
    }
}

// บันทึกข้อมูล (เพิ่ม/แก้ไข)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (isset($_POST['car_id']) && $_POST['car_id'] !== '') ? $_POST['car_id'] : null;
    $typ_id = $_POST['typ_id'];
    $code = trim($_POST['code']);
    $name = trim($_POST['name']);

    try {
        // 1. ดึง cur_id จาก typ_id ที่เลือก (ใช้ Prepared Statement เพื่อความปลอดภัย)
        $stmtCur = $pdo->prepare("SELECT cur_id FROM subject_types WHERE typ_id = ?");
        $stmtCur->execute([$typ_id]);
        $cur_id = $stmtCur->fetchColumn();

        if (!$cur_id) {
            throw new Exception("ไม่พบข้อมูลหลักสูตรสำหรับประเภทวิชานี้");
        }

        // 2. ตรวจสอบรหัสซ้ำ
        $checkSql = "SELECT COUNT(*) FROM career_groups WHERE car_code = ?";
        $checkParams = [$code];
        if ($id !== null) {
            $checkSql .= " AND car_id != ?";
            $checkParams[] = $id;
        }
        $stmtCheck = $pdo->prepare($checkSql);
        $stmtCheck->execute($checkParams);
        if ($stmtCheck->fetchColumn() > 0) {
            throw new Exception("รหัสกลุ่มอาชีพ ($code) มีอยู่ในระบบแล้ว");
        }

        // 3. บันทึกข้อมูล
        if ($id !== null) { 
            // Update
            $sql = "UPDATE career_groups SET cur_id=?, typ_id=?, car_code=?, car_name=? WHERE car_id=?"; 
            $pdo->prepare($sql)->execute([$cur_id, $typ_id, $code, $name, $id]);
        } else { 
            // Insert (สร้าง ID เอง)
            $maxStmt = $pdo->query("SELECT MAX(car_id) FROM career_groups");
            $maxId = $maxStmt->fetchColumn();
            $newId = ($maxId) ? $maxId + 1 : 1;

            $sql = "INSERT INTO career_groups (car_id, cur_id, typ_id, car_code, car_name) VALUES (?,?,?,?,?)"; 
            $pdo->prepare($sql)->execute([$newId, $cur_id, $typ_id, $code, $name]);
        }
        
        header("Location: manage_career_groups.php"); 
        exit();

    } catch (Exception $e) {
        $msg = $e->getMessage();
        echo "<script>alert('เกิดข้อผิดพลาด: $msg'); window.history.back();</script>";
        exit();
    }
}

$data = $pdo->query("SELECT g.*, t.typ_name, c.cur_year, l.lev_name FROM career_groups g JOIN subject_types t ON g.typ_id=t.typ_id JOIN curriculums c ON g.cur_id=c.cur_id JOIN levels l ON c.lev_id=l.lev_id ORDER BY g.car_code ASC")->fetchAll();
$curriculums = $pdo->query("SELECT c.*, l.lev_name FROM curriculums c JOIN levels l ON c.lev_id=l.lev_id ORDER BY c.cur_year DESC")->fetchAll();
$types = $pdo->query("SELECT * FROM subject_types")->fetchAll();

require_once '../includes/header.php';
?>

<div class="max-w-6xl mx-auto pb-12">
    <div class="mb-8">
        <a href="dashboard.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2"><i class="fa-solid fa-arrow-left mr-2"></i> Dashboard</a>
        <h1 class="text-3xl font-serif font-bold text-slate-800">ข้อมูลกลุ่มอาชีพ <span class="text-slate-400 text-lg font-sans font-normal">(Career Groups)</span></h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">       
            <div class="card-premium p-6 sticky top-24 border-t-4 border-t-indigo-500">
                <h2 class="font-bold text-lg text-slate-700 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center"><i class="fa-solid <?php echo $edit_data ? 'fa-pen' : 'fa-plus'; ?>"></i></div>
                    <?php echo $edit_data ? 'แก้ไขข้อมูล' : 'เพิ่มข้อมูลใหม่'; ?>
                </h2>
                <form method="POST" class="space-y-4">
                    <?php if($edit_data): ?><input type="hidden" name="car_id" value="<?php echo $edit_data['car_id']; ?>"><?php endif; ?>
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
                            <select name="typ_id" id="typ_id" required class="w-full text-sm py-2 disabled:bg-slate-200" disabled>
                                <option value="">-- รอเลือกหลักสูตร --</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">รหัสกลุ่ม <span class="text-red-500">*</span></label>
                        <input type="text" name="code" required value="<?php echo $edit_data['car_code'] ?? ''; ?>" class="w-full font-mono font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ชื่อกลุ่มอาชีพ <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="<?php echo $edit_data['car_name'] ?? ''; ?>" class="w-full">
                    </div>
                    <div class="pt-2 flex gap-2">
                        <button type="submit" class="btn-cvc flex-1 justify-center shadow-md"><i class="fa-solid fa-save mr-1"></i> บันทึก</button>
                        <?php if(isset($_GET['edit'])): ?>
                            <a href="manage_career_groups.php" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 text-sm font-bold transition">ยกเลิก</a>
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
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ชื่อกลุ่มอาชีพ</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">สังกัดประเภท/หลักสูตร</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center w-24">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php foreach($data as $row): ?>
                            <tr class="hover:bg-blue-50/30 transition duration-150 <?php echo ($edit_data && $edit_data['car_id'] == $row['car_id']) ? 'bg-indigo-50 ring-2 ring-inset ring-indigo-100' : ''; ?>">
                                <td class="px-6 py-4 text-center"><span class="font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded border border-indigo-100 shadow-sm"><?php echo $row['car_code']; ?></span></td>
                                <td class="px-6 py-4 font-bold text-slate-700"><?php echo htmlspecialchars($row['car_name']); ?></td>
                                <td class="px-6 py-4">
                                    <div class="text-xs font-bold text-slate-600"><?php echo $row['typ_name']; ?></div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono"><i class="fa-solid fa-turn-up mr-1 transform rotate-90"></i><?php echo $row['lev_name'].' '.$row['cur_year']; ?></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="?edit=<?php echo $row['car_id']; ?>" class="w-8 h-8 rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50 flex items-center justify-center transition"><i class="fa-solid fa-pen-to-square text-xs"></i></a>
                                        <a href="?delete=<?php echo $row['car_id']; ?>" onclick="return confirm('ยืนยันลบ?');" class="w-8 h-8 rounded-lg border border-slate-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition"><i class="fa-solid fa-trash-can text-xs"></i></a>
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
        const types = <?php echo json_encode($types); ?>; 
        const currentTypId = "<?php echo $edit_data['typ_id'] ?? ''; ?>";
        
        function filterTypes() {
            const curId = document.getElementById('cur_id').value; 
            const typeSelect = document.getElementById('typ_id');
            
            typeSelect.innerHTML = '<option value="">-- เลือกประเภท --</option>'; 
            typeSelect.disabled = !curId;
            
            if(curId) { 
                types.filter(t => t.cur_id == curId).forEach(t => { 
                    const opt = new Option(t.typ_name, t.typ_id); 
                    if(t.typ_id == currentTypId) opt.selected = true; 
                    typeSelect.add(opt); 
                }); 
            }
        }
        
        // เรียกทำงานตอนโหลดหน้า (ถ้ามีค่าอยู่แล้ว)
        if(document.getElementById('cur_id').value) filterTypes();
    </script>
</div>
<?php require_once '../includes/footer.php'; ?>