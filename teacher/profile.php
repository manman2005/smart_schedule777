<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkTeacher();

$tea_id = $_SESSION['user_id'];
$message = ""; $message_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tea_email = trim($_POST['tea_email']);
    $tea_phone = trim($_POST['tea_phone']);
    $new_password = $_POST['new_password'];
    $upload_sql = ""; $params = [];

    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        $ext = pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])) {
            $new_name = "profile_{$tea_id}_" . time() . "." . $ext;
            if (!file_exists("../uploads/teachers/")) mkdir("../uploads/teachers/", 0777, true);
            if (move_uploaded_file($_FILES['profile_img']['tmp_name'], "../uploads/teachers/" . $new_name)) {
                $upload_sql = ", tea_img = ?"; $params[] = $new_name;
            }
        }
    }

    try {
        $base_params = [$tea_email, $tea_phone];
        if (!empty($new_password)) {
            $sql = "UPDATE teachers SET tea_email=?, tea_phone=?, tea_password=? $upload_sql WHERE tea_id=?";
            $params = array_merge($base_params, [password_hash($new_password, PASSWORD_DEFAULT)], $params, [$tea_id]);
        } else {
            $sql = "UPDATE teachers SET tea_email=?, tea_phone=? $upload_sql WHERE tea_id=?";
            $params = array_merge($base_params, $params, [$tea_id]);
        }
        $stmt = $pdo->prepare($sql); $stmt->execute($params);
        $message = "บันทึกข้อมูลเรียบร้อยแล้ว"; $message_type = "success";
    } catch (PDOException $e) { $message = "Error: " . $e->getMessage(); $message_type = "error"; }
}

$stmt = $pdo->prepare("SELECT * FROM teachers WHERE tea_id = ?"); $stmt->execute([$tea_id]); $teacher = $stmt->fetch();
require_once '../includes/header.php';
?>

<div class="max-w-3xl mx-auto py-10">
    <div class="mb-6 flex justify-between items-center">
        <a href="index.php" class="text-slate-400 hover:text-cvc-blue text-sm font-bold transition flex items-center gap-2"><i class="fa-solid fa-arrow-left"></i> กลับหน้าหลัก</a>
        <h2 class="text-2xl font-serif font-bold text-slate-800">ข้อมูลส่วนตัว</h2>
    </div>

    <div class="card-premium p-10 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cvc-blue via-cvc-sky to-cvc-gold"></div>

        <?php if($message): ?>
            <div class="mb-6 p-4 rounded-xl text-center font-bold <?php echo $message_type=='success'?'bg-emerald-50 text-emerald-600 border border-emerald-100':'bg-red-50 text-red-600 border border-red-100'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-8">
            <div class="flex flex-col items-center">
                <div class="relative group cursor-pointer">
                    <div class="w-32 h-32 rounded-full p-1 bg-gradient-to-tr from-cvc-blue to-cvc-gold shadow-lg">
                        <div class="w-full h-full rounded-full bg-white overflow-hidden flex items-center justify-center">
                            <?php if (!empty($teacher['tea_img'])): ?>
                                <img src="../uploads/teachers/<?php echo $teacher['tea_img']; ?>" id="preview" class="w-full h-full object-cover">
                            <?php else: ?>
                                <img src="" id="preview" class="w-full h-full object-cover hidden">
                                <i id="default-icon" class="fa-solid fa-user-tie text-5xl text-slate-300"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <label for="fileInput" class="absolute bottom-1 right-1 w-8 h-8 bg-slate-800 text-white rounded-full flex items-center justify-center hover:bg-cvc-blue transition shadow-md"><i class="fa-solid fa-camera text-xs"></i></label>
                    <input type="file" name="profile_img" id="fileInput" class="hidden" accept="image/*" onchange="previewImage(event)">
                </div>
                <h3 class="mt-4 text-xl font-bold text-slate-800"><?php echo htmlspecialchars($teacher['tea_fullname']); ?></h3>
                <p class="text-slate-500 text-sm font-mono"><?php echo $teacher['tea_code']; ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">เบอร์โทรศัพท์</label><input type="text" name="tea_phone" value="<?php echo $teacher['tea_phone']; ?>" class="w-full bg-slate-50 focus:bg-white"></div>
                <div><label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">อีเมล</label><input type="email" name="tea_email" value="<?php echo $teacher['tea_email']; ?>" class="w-full bg-slate-50 focus:bg-white"></div>
            </div>

            <div class="border-t border-slate-100 pt-6">
                <h4 class="text-sm font-bold text-cvc-blue mb-4"><i class="fa-solid fa-lock mr-2"></i> เปลี่ยนรหัสผ่าน</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Username (แก้ไขไม่ได้)</label><input type="text" value="<?php echo $teacher['tea_username']; ?>" disabled class="w-full bg-slate-100 text-slate-400 cursor-not-allowed"></div>
                    <div><label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">รหัสผ่านใหม่ (ว่างไว้ถ้าไม่เปลี่ยน)</label><input type="password" name="new_password" placeholder="••••••" class="w-full bg-slate-50 focus:bg-white"></div>
                </div>
            </div>

            <div class="pt-4"><button type="submit" class="btn-cvc w-full justify-center py-3 text-base shadow-lg"><i class="fa-solid fa-save mr-2"></i> บันทึกการเปลี่ยนแปลง</button></div>
        </form>
    </div>
</div>
<script>
    function previewImage(event) { const reader = new FileReader(); reader.onload = function(){ const output = document.getElementById('preview'); document.getElementById('default-icon')?.classList.add('hidden'); output.src = reader.result; output.classList.remove('hidden'); }; reader.readAsDataURL(event.target.files[0]); }
</script>
<?php require_once '../includes/footer.php'; ?>