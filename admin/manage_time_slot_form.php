<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

$slot = null; $title = "เพิ่มคาบเรียนใหม่";
if (isset($_GET['id'])) { $title = "แก้ไขคาบเรียน"; $stmt = $pdo->prepare("SELECT * FROM time_slots WHERE tim_id = ?"); $stmt->execute([$_GET['id']]); $slot = $stmt->fetch(); }
require_once '../includes/header.php';
?>

<div class="min-h-[80vh] flex flex-col justify-center items-center py-10">
    <div class="w-full max-w-xl">
        <div class="mb-6 flex justify-between items-center">
            <a href="manage_time_slots.php" class="text-slate-400 hover:text-cvc-blue text-sm font-bold transition flex items-center gap-2"><i class="fa-solid fa-arrow-left"></i> ย้อนกลับ</a>
            <h2 class="text-xl font-serif font-bold text-slate-800"><?php echo $title; ?></h2>
        </div>

        <div class="card-premium p-8 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cvc-blue via-cvc-sky to-cvc-gold"></div>
            
            <form action="save_time_slot.php" method="POST" class="space-y-6">
                <?php if ($slot): ?><input type="hidden" name="tim_id" value="<?php echo $slot['tim_id']; ?>"><?php endif; ?>

                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 grid grid-cols-2 gap-6 relative shadow-inner">
                    <div class="absolute inset-0 bg-blue-500/5 pointer-events-none"></div>
                    <div><label class="block text-xs font-bold text-slate-500 mb-2 ml-1">เวลาเริ่ม <span class="text-red-500">*</span></label><input type="time" name="tim_start" required value="<?php echo $slot['tim_start'] ?? ''; ?>" class="w-full text-center font-mono font-bold text-2xl text-slate-800 bg-white border-slate-200 focus:ring-0 cursor-pointer h-12"></div>
                    <div><label class="block text-xs font-bold text-slate-500 mb-2 ml-1">เวลาสิ้นสุด <span class="text-red-500">*</span></label><input type="time" name="tim_end" required value="<?php echo $slot['tim_end'] ?? ''; ?>" class="w-full text-center font-mono font-bold text-2xl text-slate-800 bg-white border-slate-200 focus:ring-0 cursor-pointer h-12"></div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">ข้อความแสดงผล (Display Text)</label>
                    <div class="relative">
                        <i class="fa-solid fa-tag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="tim_range" value="<?php echo $slot['tim_range'] ?? ''; ?>" placeholder="เช่น 08:30-09:30" class="w-full pl-10 pr-4">
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2 ml-1">* หากเว้นว่าง ระบบจะสร้างให้อัตโนมัติตามเวลาที่เลือก</p>
                </div>

                <div><label class="block text-sm font-bold text-slate-700 mb-2 ml-1">หมายเหตุ</label><textarea name="tim_note" rows="2" placeholder="เช่น พักกลางวัน, กิจกรรมหน้าเสาธง" class="w-full"><?php echo $slot['tim_note'] ?? ''; ?></textarea></div>

                <div class="pt-4 flex gap-4 border-t border-slate-100">
                    <button type="submit" class="btn-cvc w-full justify-center text-base shadow-xl"><i class="fa-solid fa-save mr-2"></i> บันทึกข้อมูล</button>
                    <a href="manage_time_slots.php" class="px-6 py-2.5 rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50 font-bold transition">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>