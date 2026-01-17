<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

// ลบข้อมูล
if (isset($_GET['delete'])) {
    try {
        $pdo->prepare("DELETE FROM subject_types WHERE typ_id = ?")->execute([$_GET['delete']]);
        header("Location: manage_subject_types.php"); exit();
    } catch (Exception $e) { 
        echo "<script>alert('ไม่สามารถลบข้อมูลได้ เนื่องจากมีการใช้งานอยู่'); window.location='manage_subject_types.php';</script>"; 
        exit(); 
    }
}

// ดึงข้อมูลเพื่อแก้ไข
$edit_data = null;
if (isset($_GET['edit'])) {
    // ดึงข้อมูลตาม ID ที่ส่งมา
    $stmt = $pdo->prepare("SELECT * FROM subject_types WHERE typ_id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

// บันทึกข้อมูล (เพิ่ม/แก้ไข)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. รับค่า ID (ใช้ isset เช็คเพื่อให้รองรับค่า 0 ได้ถูกต้อง)
    $id = (isset($_POST['typ_id']) && $_POST['typ_id'] !== '') ? $_POST['typ_id'] : null;
    
    $typ_code = trim($_POST['code']);
    $typ_name = trim($_POST['name']);
    $cur_id = $_POST['cur_id'];
    
    try {
        // 2. ตรวจสอบรหัสซ้ำ
        $sqlCheck = "SELECT COUNT(*) FROM subject_types WHERE typ_code = ?";
        $paramsCheck = [$typ_code];
        
        // **จุดที่แก้ไข: ใช้เงื่อนไข $id !== null เพื่อให้ ID=0 ทำงานในบล็อกนี้ได้**
        if ($id !== null) { 
            $sqlCheck .= " AND typ_id != ?";
            $paramsCheck[] = $id;
        }
        
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute($paramsCheck);
        
        if ($stmtCheck->fetchColumn() > 0) {
            throw new Exception("รหัสประเภทวิชา ($typ_code) นี้มีอยู่ในระบบแล้ว");
        }

        // 3. บันทึกข้อมูล
        if ($id !== null) {
            // --- กรณีแก้ไข (Update) ---
            $sql = "UPDATE subject_types SET cur_id=?, typ_code=?, typ_name=? WHERE typ_id=?"; 
            $pdo->prepare($sql)->execute([$cur_id, $typ_code, $typ_name, $id]);
        } else {
            // --- กรณีเพิ่มใหม่ (Insert) ---
            // สร้าง ID เองอัตโนมัติ (แก้ปัญหา DB ไม่มี Auto Increment)
            $maxStmt = $pdo->query("SELECT MAX(typ_id) FROM subject_types");
            $maxId = $maxStmt->fetchColumn();
            // ถ้ามีข้อมูล ให้ +1, ถ้าไม่มี (เป็น null) ให้เริ่มที่ 1
            $newId = ($maxId !== false && $maxId !== null) ? $maxId + 1 : 1;

            $sql = "INSERT INTO subject_types (typ_id, cur_id, typ_code, typ_name) VALUES (?,?,?,?)"; 
            $pdo->prepare($sql)->execute([$newId, $cur_id, $typ_code, $typ_name]);
        }
        
        header("Location: manage_subject_types.php"); 
        exit();
        
    } catch (Exception $e) {
        // แสดง Error เป็น Alert
        $msg = $e->getMessage();
        echo "<script>alert('เกิดข้อผิดพลาด: $msg'); window.history.back();</script>";
        exit();
    }
}

$data = $pdo->query("SELECT t.*, c.cur_year, l.lev_name FROM subject_types t JOIN curriculums c ON t.cur_id=c.cur_id JOIN levels l ON c.lev_id=l.lev_id ORDER BY t.typ_code ASC")->fetchAll();
$curriculums = $pdo->query("SELECT c.*, l.lev_name FROM curriculums c JOIN levels l ON c.lev_id=l.lev_id ORDER BY c.cur_year DESC")->fetchAll();

require_once '../includes/header.php';
?>

<div class="max-w-6xl mx-auto pb-12">
    <div class="mb-8">
        <a href="index.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2"><i class="fa-solid fa-arrow-left mr-2"></i> Dashboard</a>
        <h1 class="text-3xl font-serif font-bold text-slate-800">ข้อมูลประเภทวิชา <span class="text-slate-400 text-lg font-sans font-normal">(Subject Types)</span></h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">      
            <div class="card-premium p-6 sticky top-24 border-t-4 border-t-sky-500">
                <h2 class="font-bold text-lg text-slate-700 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center"><i class="fa-solid <?php echo $edit_data ? 'fa-pen' : 'fa-plus'; ?>"></i></div>
                    <?php echo $edit_data ? 'แก้ไขข้อมูล' : 'เพิ่มข้อมูลใหม่'; ?>
                </h2>
                <form method="POST" class="space-y-4">
                    <?php if($edit_data): ?><input type="hidden" name="typ_id" value="<?php echo $edit_data['typ_id']; ?>"><?php endif; ?>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">สังกัดหลักสูตร <span class="text-red-500">*</span></label>
                        <select name="cur_id" required class="w-full bg-slate-50 focus:bg-white cursor-pointer border-slate-200">
                            <?php foreach($curriculums as $c): ?>
                                <option value="<?php echo $c['cur_id']; ?>" <?php echo ($edit_data && $edit_data['cur_id'] == $c['cur_id']) ? 'selected' : ''; ?>><?php echo $c['lev_name']." ".$c['cur_year']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">รหัสประเภท <span class="text-red-500">*</span></label>
                        <input type="text" name="code" required value="<?php echo $edit_data['typ_code'] ?? ''; ?>" class="w-full font-mono font-bold text-slate-700 border-slate-200">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ชื่อประเภทวิชา <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="<?php echo $edit_data['typ_name'] ?? ''; ?>" class="w-full border-slate-200">
                    </div>
                    <div class="pt-2 flex gap-2">
                        <button type="submit" class="btn-cvc flex-1 justify-center shadow-md"><i class="fa-solid fa-save mr-1"></i> บันทึก</button>
                        <?php if($edit_data): ?>
                            <a href="manage_subject_types.php" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 text-sm font-bold transition">ยกเลิก</a>
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
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ชื่อประเภทวิชา</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">หลักสูตร</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center w-24">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php foreach($data as $row): ?>
                            <tr class="hover:bg-blue-50/30 transition duration-150 <?php echo ($edit_data && $edit_data['typ_id'] == $row['typ_id']) ? 'bg-sky-50 ring-2 ring-inset ring-sky-100' : ''; ?>">
                                <td class="px-6 py-4 text-center"><span class="font-mono font-bold text-sky-600 bg-sky-50 px-2 py-1 rounded border border-sky-100 shadow-sm"><?php echo $row['typ_code']; ?></span></td>
                                <td class="px-6 py-4 font-bold text-slate-700"><?php echo htmlspecialchars($row['typ_name']); ?></td>
                                <td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-200 text-[10px] font-bold text-slate-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span><?php echo $row['lev_name'].' '.$row['cur_year']; ?></span></td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="?edit=<?php echo $row['typ_id']; ?>" class="w-8 h-8 rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50 flex items-center justify-center transition"><i class="fa-solid fa-pen-to-square text-xs"></i></a>
                                        <a href="?delete=<?php echo $row['typ_id']; ?>" onclick="return confirm('ยืนยันลบ?');" class="w-8 h-8 rounded-lg border border-slate-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition"><i class="fa-solid fa-trash-can text-xs"></i></a>
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
</div>
<?php require_once '../includes/footer.php'; ?>