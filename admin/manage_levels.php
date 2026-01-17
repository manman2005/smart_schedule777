<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

// ลบข้อมูล
if (isset($_GET['delete'])) {
    try {
        $pdo->prepare("DELETE FROM levels WHERE lev_id = ?")->execute([$_GET['delete']]);
        header("Location: manage_levels.php"); exit();
    } catch (Exception $e) { 
        echo "<script>alert('ลบไม่ได้: ข้อมูลถูกใช้งานอยู่ หรือเกิดข้อผิดพลาด'); window.location='manage_levels.php';</script>"; 
        exit(); 
    }
}

// ดึงข้อมูลเพื่อแก้ไข
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM levels WHERE lev_id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

// บันทึกข้อมูล (เพิ่ม/แก้ไข)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่า ID (ถ้าไม่มีค่า หรือเป็นค่าว่าง ให้เป็น null)
    $id = (isset($_POST['lev_id']) && $_POST['lev_id'] !== '') ? $_POST['lev_id'] : null;
    $code = trim($_POST['code']);
    $name = trim($_POST['name']);
    
    try {
        // 1. ตรวจสอบรหัสซ้ำ
        $chk = $pdo->prepare("SELECT COUNT(*) FROM levels WHERE lev_code = ? AND lev_id != ?");
        $chk->execute([$code, $id ?? 0]);
        if($chk->fetchColumn() > 0) {
             throw new Exception("รหัสระดับชั้น ($code) นี้มีอยู่ในระบบแล้ว");
        }

        if ($id) { 
            // --- กรณีแก้ไข (Update) ---
            $sql = "UPDATE levels SET lev_code=?, lev_name=? WHERE lev_id=?"; 
            $pdo->prepare($sql)->execute([$code, $name, $id]);
        } else { 
            // --- กรณีเพิ่มใหม่ (Insert) ---
            // หา ID ล่าสุด + 1 (ทำเผื่อไว้กรณี Database ไม่ได้เปิด Auto Increment)
            $maxStmt = $pdo->query("SELECT MAX(lev_id) FROM levels");
            $maxId = $maxStmt->fetchColumn();
            $newId = ($maxId) ? $maxId + 1 : 1;

            $sql = "INSERT INTO levels (lev_id, lev_code, lev_name) VALUES (?,?,?)"; 
            $pdo->prepare($sql)->execute([$newId, $code, $name]);
        }
        
        header("Location: manage_levels.php"); 
        exit();

    } catch (Exception $e) {
        // แสดง Error อย่างละเอียดแทนการขึ้นหน้าขาว 500
        $error_msg = $e->getMessage();
        echo "<script>alert('เกิดข้อผิดพลาด: $error_msg'); window.history.back();</script>";
        exit();
    }
}

$data = $pdo->query("SELECT * FROM levels ORDER BY lev_code ASC")->fetchAll();

require_once '../includes/header.php';
?>

<div class="max-w-6xl mx-auto pb-12">
    <div class="mb-8">
        <a href="index.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2"><i class="fa-solid fa-arrow-left mr-2"></i> Dashboard</a>
        <h1 class="text-3xl font-serif font-bold text-slate-800">ข้อมูลระดับชั้น <span class="text-slate-400 text-lg font-sans font-normal">(Levels)</span></h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">      
            <div class="card-premium p-6 sticky top-24 border-t-4 border-t-blue-500">
                <h2 class="font-bold text-lg text-slate-700 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fa-solid <?php echo $edit_data ? 'fa-pen' : 'fa-plus'; ?>"></i></div>
                    <?php echo $edit_data ? 'แก้ไขข้อมูล' : 'เพิ่มข้อมูลใหม่'; ?>
                </h2>
                <form method="POST" class="space-y-4">
                    <?php if($edit_data): ?><input type="hidden" name="lev_id" value="<?php echo $edit_data['lev_id']; ?>"><?php endif; ?>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">รหัสระดับชั้น <span class="text-red-500">*</span></label>
                        <input type="text" name="code" required value="<?php echo $edit_data['lev_code'] ?? ''; ?>" class="w-full font-mono font-bold text-slate-700" placeholder="เช่น 01, 02">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ชื่อระดับชั้น <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required value="<?php echo $edit_data['lev_name'] ?? ''; ?>" class="w-full" placeholder="เช่น ประกาศนียบัตรวิชาชีพ (ปวช.)">
                    </div>
                    <div class="pt-2 flex gap-2">
                        <button type="submit" class="btn-cvc flex-1 justify-center shadow-md"><i class="fa-solid fa-save mr-1"></i> บันทึก</button>
                        <?php if(isset($_GET['edit'])): ?>
                            <a href="manage_levels.php" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 text-sm font-bold transition">ยกเลิก</a>
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
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ชื่อระดับชั้น</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center w-24">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php foreach($data as $row): ?>
                            <tr class="hover:bg-blue-50/30 transition duration-150 <?php echo ($edit_data && $edit_data['lev_id'] == $row['lev_id']) ? 'bg-blue-50 ring-2 ring-inset ring-blue-100' : ''; ?>">
                                <td class="px-6 py-4 text-center">
                                    <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded border border-blue-100 shadow-sm"><?php echo $row['lev_code']; ?></span>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-700"><?php echo htmlspecialchars($row['lev_name']); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="?edit=<?php echo $row['lev_id']; ?>" class="w-8 h-8 rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50 flex items-center justify-center transition"><i class="fa-solid fa-pen-to-square text-xs"></i></a>
                                        <a href="?delete=<?php echo $row['lev_id']; ?>" onclick="return confirm('ยืนยันลบ?');" class="w-8 h-8 rounded-lg border border-slate-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition"><i class="fa-solid fa-trash-can text-xs"></i></a>
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