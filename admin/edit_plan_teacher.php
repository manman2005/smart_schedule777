<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

// รับค่า id และ pla_id โดยใช้ isset เช็คเพื่อให้รองรับค่าที่เป็น 0 ได้
$pls_id = (isset($_GET['id']) && $_GET['id'] !== '') ? $_GET['id'] : null;
$pla_id = (isset($_GET['pla_id']) && $_GET['pla_id'] !== '') ? $_GET['pla_id'] : null;

// ถ้าไม่มีค่าส่งมาเลย ให้เด้งกลับ
if ($pls_id === null || $pla_id === null) { 
    header("Location: manage_plans.php"); 
    exit(); 
}

$sql = "SELECT ps.*, s.sub_code, s.sub_name, s.sug_id, sg.sug_name, t.tea_fullname FROM plan_subjects ps JOIN subjects s ON ps.sub_id = s.sub_id LEFT JOIN subject_groups sg ON s.sug_id = sg.sug_id LEFT JOIN teachers t ON ps.tea_id = t.tea_id WHERE ps.pls_id = ?";
$stmt = $pdo->prepare($sql); 
$stmt->execute([$pls_id]); 
$data = $stmt->fetch();

if (!$data) die("Not found");

$target_sug_id = $data['sug_id'];
$teachers = ($target_sug_id) ? $pdo->prepare("SELECT * FROM teachers WHERE sug_id = ? ORDER BY tea_fullname ASC") : $pdo->query("SELECT * FROM teachers ORDER BY tea_fullname ASC");
if($target_sug_id) $teachers->execute([$target_sug_id]);
$teachers_list = $teachers->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_tea_id = empty($_POST['tea_id']) ? null : $_POST['tea_id'];
    $pdo->prepare("UPDATE plan_subjects SET tea_id = ? WHERE pls_id = ?")->execute([$new_tea_id, $pls_id]);
    header("Location: manage_plan_subjects.php?pla_id=$pla_id"); exit();
}
require_once '../includes/header.php';
?>

<div class="flex items-center justify-center min-h-[60vh]">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden relative">
        <div class="absolute top-0 left-0 w-full h-1 bg-cvc-blue"></div>
        
        <div class="bg-slate-50 px-6 py-4 flex justify-between items-center border-b border-slate-200">
            <h2 class="text-slate-800 font-bold text-lg"><i class="fa-solid fa-user-pen mr-2 text-cvc-blue"></i> เปลี่ยนครูผู้สอน</h2>
            <a href="manage_plan_subjects.php?pla_id=<?php echo $pla_id; ?>" class="text-slate-400 hover:text-slate-600 transition"><i class="fa-solid fa-xmark text-xl"></i></a>
        </div>
        
        <div class="p-6">
            <div class="bg-blue-50/50 rounded-xl p-4 mb-6 border border-blue-100 text-center">
                <div class="text-xs text-blue-500 uppercase font-bold mb-1 tracking-wider">วิชา</div>
                <div class="font-mono font-bold text-slate-800 text-xl mb-1"><?php echo $data['sub_code']; ?></div>
                <div class="text-slate-600 font-medium"><?php echo $data['sub_name']; ?></div>
                <div class="mt-2 text-xs text-slate-400"><?php echo $data['sug_name'] ?: 'ไม่ระบุหมวด'; ?></div>
            </div>

            <form method="POST">
                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">เลือกครูผู้สอนท่านใหม่</label>
                    <div class="relative">
                        <i class="fa-solid fa-chalkboard-user absolute left-3 top-3 text-slate-400"></i>
                        <select name="tea_id" class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-cvc-blue outline-none transition cursor-pointer">
                            <option value="">-- ปล่อยว่าง (ยังไม่ระบุ) --</option>
                            <?php foreach ($teachers_list as $t): ?>
                                <option value="<?php echo $t['tea_id']; ?>" <?php echo ($data['tea_id'] == $t['tea_id']) ? 'selected' : ''; ?>><?php echo $t['tea_fullname']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="manage_plan_subjects.php?pla_id=<?php echo $pla_id; ?>" class="flex-1 py-2.5 text-center border border-slate-200 rounded-xl text-slate-500 font-bold hover:bg-slate-50 transition">ยกเลิก</a>
                    <button type="submit" class="flex-1 py-2.5 bg-cvc-blue text-white rounded-xl font-bold shadow-lg hover:bg-blue-800 transition">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>