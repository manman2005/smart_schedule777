<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

// ลบข้อมูล
if (isset($_GET['delete'])) {
    try {
        $pdo->prepare("DELETE FROM curriculums WHERE cur_id = ?")->execute([$_GET['delete']]);
        header("Location: manage_curriculums.php"); exit();
    } catch (Exception $e) { 
        echo "<script>alert('ลบไม่ได้: ข้อมูลถูกใช้งานอยู่ หรือเกิดข้อผิดพลาด'); window.location='manage_curriculums.php';</script>"; 
        exit(); 
    }
}

// ดึงข้อมูลเพื่อแก้ไข
$edit_data = null;
if (isset($_GET['edit'])) {
    // ดึงข้อมูลเฉพาะเมื่อ ID ไม่ใช่ 0 (กรณี ?edit=0 คือกดเพิ่มใหม่)
    if ($_GET['edit'] != 0) {
        $stmt = $pdo->prepare("SELECT * FROM curriculums WHERE cur_id = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_data = $stmt->fetch();
    }
}

// บันทึกข้อมูล (เพิ่ม/แก้ไข)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่า ID (ถ้าไม่มี หรือเป็นค่าว่าง ให้เป็น null เพื่อเข้าเงื่อนไข Insert)
    $id = (isset($_POST['cur_id']) && $_POST['cur_id'] !== '') ? $_POST['cur_id'] : null;
    
    $lev_id = $_POST['lev_id'];
    $cur_year = $_POST['cur_year'];
    $cur_start = $_POST['cur_start'];
    $cur_end = $_POST['cur_end'];

    try {
        if ($id !== null) {
            // --- กรณีแก้ไข (Update) ---
            $sql = "UPDATE curriculums SET lev_id=?, cur_year=?, cur_start_year=?, cur_end_year=? WHERE cur_id=?";
            $pdo->prepare($sql)->execute([$lev_id, $cur_year, $cur_start, $cur_end, $id]);
        } else {
            // --- กรณีเพิ่มใหม่ (Insert) ---
            // สร้าง ID เองอัตโนมัติ (แก้ปัญหา DB ไม่มี Auto Increment)
            $maxStmt = $pdo->query("SELECT MAX(cur_id) FROM curriculums");
            $maxId = $maxStmt->fetchColumn();
            $newId = ($maxId) ? $maxId + 1 : 1;

            $sql = "INSERT INTO curriculums (cur_id, lev_id, cur_year, cur_start_year, cur_end_year) VALUES (?,?,?,?,?)";
            $pdo->prepare($sql)->execute([$newId, $lev_id, $cur_year, $cur_start, $cur_end]);
        }
        
        header("Location: manage_curriculums.php"); 
        exit();

    } catch (Exception $e) {
        $msg = $e->getMessage();
        echo "<script>alert('เกิดข้อผิดพลาด: $msg'); window.history.back();</script>";
        exit();
    }
}

$data = $pdo->query("SELECT c.*, l.lev_name FROM curriculums c JOIN levels l ON c.lev_id = l.lev_id ORDER BY c.cur_year DESC")->fetchAll();
$levels = $pdo->query("SELECT * FROM levels ORDER BY lev_code ASC")->fetchAll();

require_once '../includes/header.php';
?>

<div class="max-w-6xl mx-auto pb-12">
    <div class="mb-8">
        <a href="dashboard.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2"><i class="fa-solid fa-arrow-left mr-2"></i> Dashboard</a>
        <h1 class="text-3xl font-serif font-bold text-slate-800">ข้อมูลหลักสูตร <span class="text-slate-400 text-lg font-sans font-normal">(Curriculums)</span></h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1">    
            <div class="card-premium p-6 sticky top-24 border-t-4 border-t-cvc-blue">
                <h2 class="font-bold text-lg text-slate-700 mb-4 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-cvc-blue flex items-center justify-center"><i class="fa-solid <?php echo $edit_data ? 'fa-pen' : 'fa-plus'; ?>"></i></div>
                    <?php echo $edit_data ? 'แก้ไขข้อมูล' : 'เพิ่มหลักสูตรใหม่'; ?>
                </h2>
                
                <form method="POST" class="space-y-4">
                    <?php if($edit_data): ?><input type="hidden" name="cur_id" value="<?php echo $edit_data['cur_id']; ?>"><?php endif; ?>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ระดับชั้น <span class="text-red-500">*</span></label>
                        <select name="lev_id" required class="w-full bg-slate-50 focus:bg-white cursor-pointer border-slate-200">
                            <?php foreach($levels as $l): ?>
                                <option value="<?php echo $l['lev_id']; ?>" <?php echo ($edit_data && $edit_data['lev_id'] == $l['lev_id']) ? 'selected' : ''; ?>><?php echo $l['lev_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ปีหลักสูตร (พ.ศ.) <span class="text-red-500">*</span></label>
                        <input type="number" name="cur_year" required value="<?php echo $edit_data['cur_year'] ?? date('Y')+543; ?>" class="w-full font-bold text-center text-lg text-cvc-blue border-slate-200">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">เริ่มใช้ปี</label><input type="number" name="cur_start" value="<?php echo $edit_data['cur_start_year'] ?? ''; ?>" class="w-full text-center border-slate-200"></div>
                        <div><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">สิ้นสุดปี</label><input type="number" name="cur_end" value="<?php echo $edit_data['cur_end_year'] ?? ''; ?>" class="w-full text-center border-slate-200"></div>
                    </div>
                    
                    <div class="pt-2 flex gap-2">
                        <button type="submit" class="btn-cvc flex-1 justify-center shadow-md"><i class="fa-solid fa-save mr-1"></i> บันทึก</button>
                        <?php if(isset($_GET['edit'])): ?>
                            <a href="manage_curriculums.php" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 text-sm font-bold transition">ยกเลิก</a>
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
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ระดับ</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ชื่อหลักสูตร</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ระยะเวลา</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-center w-24">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php foreach($data as $row): ?>
                            <tr class="hover:bg-blue-50/30 transition duration-150 <?php echo ($edit_data && $edit_data['cur_id'] == $row['cur_id']) ? 'bg-blue-50 ring-2 ring-inset ring-blue-100' : ''; ?>">
                                <td class="px-6 py-4"><span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded border border-slate-200"><?php echo $row['lev_name']; ?></span></td>
                                <td class="px-6 py-4"><span class="font-bold text-cvc-blue">หลักสูตรปี <?php echo $row['cur_year']; ?></span></td>
                                <td class="px-6 py-4"><div class="flex items-center text-xs text-slate-500 font-mono"><i class="fa-regular fa-calendar mr-2 text-cvc-gold"></i><?php echo $row['cur_start_year'].' - '.$row['cur_end_year']; ?></div></td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="?edit=<?php echo $row['cur_id']; ?>" class="w-8 h-8 rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50 flex items-center justify-center transition"><i class="fa-solid fa-pen-to-square text-xs"></i></a>
                                        <a href="?delete=<?php echo $row['cur_id']; ?>" onclick="return confirm('ยืนยันลบ?');" class="w-8 h-8 rounded-lg border border-slate-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition"><i class="fa-solid fa-trash-can text-xs"></i></a>
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