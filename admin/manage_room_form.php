<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

$room = null; $title = "เพิ่มห้องเรียนใหม่"; $is_edit = false;
if (isset($_GET['id'])) { $title = "แก้ไขข้อมูลห้องเรียน"; $is_edit = true; $stmt = $pdo->prepare("SELECT * FROM rooms WHERE roo_id = ?"); $stmt->execute([$_GET['id']]); $room = $stmt->fetch(); }
require_once '../includes/header.php';
?>

<div class="min-h-[80vh] flex flex-col justify-center items-center py-10">
    <div class="w-full max-w-2xl">
        <div class="mb-6 flex justify-between items-center">
            <a href="manage_rooms.php" class="text-slate-400 hover:text-cvc-blue text-sm font-bold transition flex items-center gap-2"><i class="fa-solid fa-arrow-left"></i> ย้อนกลับ</a>
            <h2 class="text-xl font-serif font-bold text-slate-800"><?php echo $title; ?></h2>
        </div>

        <div class="card-premium p-8 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-rose-400 via-pink-500 to-rose-600"></div>
            
            <form action="save_room.php" method="POST" class="space-y-6">
                <input type="hidden" name="mode" value="<?php echo $is_edit ? 'update' : 'insert'; ?>">
                <?php if ($is_edit): ?><input type="hidden" name="original_roo_id" value="<?php echo $room['roo_id']; ?>"><?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">รหัสห้อง <span class="text-red-500">*</span></label>
                        <input type="text" name="roo_id" required value="<?php echo $room['roo_id'] ?? ''; ?>" class="w-full font-mono font-bold text-rose-700 bg-rose-50 border-rose-200 focus:border-rose-500 focus:ring-rose-500/20 <?php echo $is_edit ? 'cursor-not-allowed opacity-70' : ''; ?>" <?php echo $is_edit ? 'readonly' : ''; ?>>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ชื่อห้องเรียน <span class="text-red-500">*</span></label>
                        <input type="text" name="roo_name" required value="<?php echo $room['roo_name'] ?? ''; ?>" class="w-full">
                    </div>
                    <div><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">อาคารเรียน</label><input type="text" name="roo_building" value="<?php echo $room['roo_building'] ?? ''; ?>" class="w-full"></div>
                    <div><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ชั้น</label><input type="text" name="roo_floor" value="<?php echo $room['roo_floor'] ?? ''; ?>" class="w-full"></div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ประเภทห้อง</label>
                        <select name="roo_type" class="w-full text-sm py-2">
                            <option value="ห้องเรียนสามัญ" <?php echo ($room && $room['roo_type'] == 'ห้องเรียนสามัญ') ? 'selected' : ''; ?>>ห้องเรียนสามัญ</option>
                            <option value="ห้องปฎิบัติการ" <?php echo ($room && $room['roo_type'] == 'ห้องปฎิบัติการ') ? 'selected' : ''; ?>>ห้องปฎิบัติการ</option>
                            <option value="ห้องประชุม" <?php echo ($room && $room['roo_type'] == 'ห้องประชุม') ? 'selected' : ''; ?>>ห้องประชุม</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ความจุ (ที่นั่ง)</label><input type="number" name="roo_capacity" value="<?php echo $room['roo_capacity'] ?? '40'; ?>" class="w-full"></div>
                </div>

                <div class="pt-4 flex gap-4 border-t border-slate-100">
                    <button type="submit" class="btn-cvc w-full justify-center text-base shadow-xl bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 border-none"><i class="fa-solid fa-save mr-2"></i> บันทึกข้อมูล</button>
                    <a href="manage_rooms.php" class="px-6 py-2.5 rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50 font-bold transition">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>