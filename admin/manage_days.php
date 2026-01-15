<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $day_id = $_POST['day_id']; $day_name = trim($_POST['day_name']);
    try { $stmt = $pdo->prepare("UPDATE days SET day_name = ? WHERE day_id = ?"); $stmt->execute([$day_name, $day_id]); echo "<script>alert('บันทึกชื่อวันเรียบร้อยแล้ว');</script>"; } 
    catch (PDOException $e) { echo "<script>alert('Error: " . $e->getMessage() . "');</script>"; }
}

$days = $pdo->query("SELECT * FROM days ORDER BY day_id ASC")->fetchAll();
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto pb-12">
    <div class="mb-8">
        <a href="manage_time_slots.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2"><i class="fa-solid fa-arrow-left mr-2"></i> กลับหน้าจัดการเวลา</a>
        <h1 class="text-3xl font-serif font-bold text-slate-800">จัดการข้อมูลวัน <span class="text-slate-400 text-lg font-sans font-normal">(Weekdays)</span></h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php 
        $colors = [
            1 => ['bg'=>'bg-yellow-50', 'icon'=>'text-yellow-600 bg-yellow-100', 'border'=>'border-yellow-200'],
            2 => ['bg'=>'bg-pink-50', 'icon'=>'text-pink-600 bg-pink-100', 'border'=>'border-pink-200'],
            3 => ['bg'=>'bg-emerald-50', 'icon'=>'text-emerald-600 bg-emerald-100', 'border'=>'border-emerald-200'],
            4 => ['bg'=>'bg-orange-50', 'icon'=>'text-orange-600 bg-orange-100', 'border'=>'border-orange-200'],
            5 => ['bg'=>'bg-blue-50', 'icon'=>'text-blue-600 bg-blue-100', 'border'=>'border-blue-200'],
            6 => ['bg'=>'bg-purple-50', 'icon'=>'text-purple-600 bg-purple-100', 'border'=>'border-purple-200'],
            7 => ['bg'=>'bg-red-50', 'icon'=>'text-red-600 bg-red-100', 'border'=>'border-red-200']
        ];
        foreach ($days as $day): $theme = $colors[$day['day_id']] ?? ['bg'=>'bg-slate-50', 'icon'=>'text-slate-600 bg-slate-200', 'border'=>'border-slate-200'];
        ?>
        <div class="card-premium p-6 relative group overflow-hidden border <?php echo $theme['border']; ?> <?php echo $theme['bg']; ?>">
            <form action="" method="POST" class="relative z-10">
                <input type="hidden" name="day_id" value="<?php echo $day['day_id']; ?>">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 flex items-center justify-center rounded-xl font-black text-xl shadow-sm <?php echo $theme['icon']; ?>"><?php echo $day['day_id']; ?></div>
                    <div class="flex-1"><label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Day Name</label><input type="text" name="day_name" value="<?php echo $day['day_name']; ?>" required class="w-full bg-white/50 border-0 border-b-2 border-slate-200 focus:ring-0 focus:border-slate-400 px-0 py-1 font-bold text-lg text-slate-800"></div>
                </div>
                <button type="submit" class="w-full py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-500 hover:text-cvc-blue hover:border-cvc-blue transition shadow-sm opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 duration-300">บันทึกการแก้ไข</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>