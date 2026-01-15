<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

// ลบข้อมูล
if (isset($_GET['delete'])) { 
    try { 
        $pdo->prepare("DELETE FROM subject_groups WHERE sug_id = ?")->execute([$_GET['delete']]); 
        header("Location: manage_subject_groups.php"); exit(); 
    } catch (Exception $e) { 
        echo "<script>alert('ลบไม่ได้: ข้อมูลถูกใช้อยู่'); window.location='manage_subject_groups.php';</script>"; 
        exit(); 
    } 
}

// ดึงข้อมูลเพื่อแก้ไข
$edit_data = null; 
if (isset($_GET['edit'])) { 
    $stmt = $pdo->prepare("SELECT * FROM subject_groups WHERE sug_id = ?"); 
    $stmt->execute([$_GET['edit']]); 
    $edit_data = $stmt->fetch(); 
}

// บันทึกข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
    // 1. รับค่า ID (รองรับเลข 0)
    $id = (isset($_POST['sug_id']) && $_POST['sug_id'] !== '') ? $_POST['sug_id'] : null;
    
    $code = trim($_POST['sug_code']);
    $name = trim($_POST['sug_name']);
    $note = trim($_POST['sug_note']);

    try {
        // 2. ตรวจสอบรหัสซ้ำ
        $sqlCheck = "SELECT COUNT(*) FROM subject_groups WHERE sug_code = ?";
        $paramsCheck = [$code];
        if ($id !== null) {
            $sqlCheck .= " AND sug_id != ?";
            $paramsCheck[] = $id;
        }
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute($paramsCheck);
        if ($stmtCheck->fetchColumn() > 0) {
            throw new Exception("รหัสหมวดวิชา ($code) มีอยู่ในระบบแล้ว");
        }

        // 3. บันทึกข้อมูล
        if ($id !== null) { 
            // --- กรณีแก้ไข (Update) ---
            $sql = "UPDATE subject_groups SET sug_code=?, sug_name=?, sug_note=? WHERE sug_id=?"; 
            $pdo->prepare($sql)->execute([$code, $name, $note, $id]); 
        } else { 
            // --- กรณีเพิ่มใหม่ (Insert) ---
            // สร้าง ID เอง
            $maxStmt = $pdo->query("SELECT MAX(sug_id) FROM subject_groups");
            $maxId = $maxStmt->fetchColumn();
            $newId = ($maxId !== false && $maxId !== null) ? $maxId + 1 : 1;

            $sql = "INSERT INTO subject_groups (sug_id, sug_code, sug_name, sug_note) VALUES (?, ?, ?, ?)"; 
            $pdo->prepare($sql)->execute([$newId, $code, $name, $note]); 
        }
        
        header("Location: manage_subject_groups.php"); 
        exit();

    } catch (Exception $e) {
        $msg = $e->getMessage();
        echo "<script>alert('เกิดข้อผิดพลาด: $msg'); window.history.back();</script>";
        exit();
    }
}

$groups = $pdo->query("SELECT * FROM subject_groups ORDER BY sug_code ASC")->fetchAll();

require_once '../includes/header.php';
?>

<div class="max-w-6xl mx-auto pb-12">
    <div class="mb-8">
        <a href="dashboard.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2"><i class="fa-solid fa-arrow-left mr-2"></i> Dashboard</a>
        <h1 class="text-3xl font-serif font-bold text-slate-800">หมวดวิชา <span class="text-slate-400 text-lg font-sans font-normal">(Subject Groups)</span></h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
            <div class="card-premium p-6 sticky top-24 border-t-4 border-t-emerald-500">
                <h2 class="font-bold text-lg text-slate-700 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa-solid <?php echo $edit_data ? 'fa-pen' : 'fa-plus'; ?>"></i></div>
                    <?php echo $edit_data ? 'แก้ไขหมวดวิชา' : 'เพิ่มหมวดวิชา'; ?>
                </h2>
                <form method="POST" class="space-y-4">
                    <?php if($edit_data): ?><input type="hidden" name="sug_id" value="<?php echo $edit_data['sug_id']; ?>"><?php endif; ?>
                    <div><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">รหัสหมวด <span class="text-red-500">*</span></label><input type="text" name="sug_code" required value="<?php echo $edit_data['sug_code'] ?? ''; ?>" class="w-full font-mono font-bold text-slate-700"></div>
                    <div><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ชื่อหมวดวิชา <span class="text-red-500">*</span></label><input type="text" name="sug_name" required value="<?php echo $edit_data['sug_name'] ?? ''; ?>" class="w-full"></div>
                    <div><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">หมายเหตุ</label><textarea name="sug_note" rows="3" class="w-full"><?php echo $edit_data['sug_note'] ?? ''; ?></textarea></div>
                    <div class="pt-2 flex gap-2">
                        <button type="submit" class="btn-cvc flex-1 justify-center shadow-md"><i class="fa-solid fa-save mr-1"></i> บันทึก</button>
                        <?php if($edit_data): ?>
                            <a href="manage_subject_groups.php" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 text-sm font-bold transition">ยกเลิก</a>
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
                                <th class="px-6 py-4 w-24 text-center text-xs font-bold text-slate-500 uppercase">รหัส</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ชื่อหมวดวิชา</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">หมายเหตุ</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center w-28">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php foreach($groups as $row): ?>
                            <tr class="hover:bg-blue-50/30 transition duration-150 <?php echo ($edit_data && $edit_data['sug_id'] == $row['sug_id']) ? 'bg-emerald-50 ring-2 ring-inset ring-emerald-100' : ''; ?>">
                                <td class="px-6 py-4 text-center"><span class="font-mono font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded border border-emerald-100 shadow-sm"><?php echo $row['sug_code']; ?></span></td>
                                <td class="px-6 py-4 font-bold text-slate-700"><?php echo htmlspecialchars($row['sug_name']); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-500"><?php echo htmlspecialchars($row['sug_note']); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="?edit=<?php echo $row['sug_id']; ?>" class="w-8 h-8 rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50 flex items-center justify-center transition"><i class="fa-solid fa-pen-to-square text-xs"></i></a>
                                        <a href="?delete=<?php echo $row['sug_id']; ?>" onclick="return confirm('ยืนยันลบ?');" class="w-8 h-8 rounded-lg border border-slate-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition"><i class="fa-solid fa-trash-can text-xs"></i></a>
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