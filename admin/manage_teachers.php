<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

$search = $_GET['search'] ?? '';

// SQL Query
$sql = "SELECT t.*, sg.sug_name, m.maj_name 
        FROM teachers t 
        LEFT JOIN subject_groups sg ON t.sug_id = sg.sug_id 
        LEFT JOIN majors m ON t.maj_id = m.maj_id
        WHERE t.tea_fullname LIKE ? OR t.tea_username LIKE ? 
        ORDER BY t.tea_code ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%", "%$search%"]);
$teachers = $stmt->fetchAll();

// กำหนดสี Badge
$badge_colors = [
    0 => 'bg-blue-50 text-blue-700 border-blue-100',
    1 => 'bg-emerald-50 text-emerald-700 border-emerald-100',
    2 => 'bg-purple-50 text-purple-700 border-purple-100',
    3 => 'bg-amber-50 text-amber-700 border-amber-100',
];

require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto pb-12">
    
    <div class="mb-8">
        <a href="index.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2">
            <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
        </a>
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <h1 class="text-3xl font-serif font-bold text-slate-800">ข้อมูลครูผู้สอน</h1>
                <p class="text-slate-500 mt-1">Instructor Management Database</p>
            </div>
            
            <div class="flex gap-3 w-full md:w-auto">
                <form class="relative flex-1 md:w-64 group">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="ค้นหาชื่อ/Username..." 
                           class="w-full pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-full focus:ring-2 focus:ring-blue-100 focus:border-cvc-blue outline-none transition shadow-sm text-sm">
                    <i class="fa-solid fa-search absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-cvc-blue transition pointer-events-none"></i>
                </form>
                
                <a href="manage_teacher_form.php" class="btn-cvc text-sm shadow-md hover:shadow-lg">
                    <i class="fa-solid fa-plus"></i> <span class="hidden sm:inline">เพิ่มข้อมูล</span>
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center shadow-sm">
            <i class="fa-solid fa-circle-check text-xl mr-3"></i>
            <span class="font-bold"><?php echo $_SESSION['success']; ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-center shadow-sm">
            <i class="fa-solid fa-circle-exclamation text-xl mr-3"></i>
            <span class="font-bold"><?php echo $_SESSION['error']; ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <div class="card-premium overflow-hidden border-0 shadow-xl shadow-slate-200/50">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-16 text-center">#</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">รหัส</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ชื่อ-นามสกุล</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">หมวดวิชา</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">สาขาวิชา</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if(count($teachers) > 0): ?>
                        <?php 
                        $count = 1; 
                        foreach ($teachers as $tea): 
                            $color_index = intval($tea['sug_id']) % 4; 
                            $badge_class = $tea['sug_id'] ? ($badge_colors[$color_index] ?? 'bg-slate-50 text-slate-600') : 'bg-slate-50 text-slate-400';
                        ?>
                        <tr class="hover:bg-blue-50/30 transition duration-150 group">
                            <td class="px-6 py-4 text-slate-400 text-xs text-center"><?php echo $count++; ?></td>
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-cvc-blue bg-blue-50 px-2 py-1 rounded text-xs border border-blue-100 shadow-sm">
                                    <?php echo $tea['tea_code']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200 shadow-sm">
                                        <?php if (!empty($tea['tea_img']) && file_exists("../uploads/teachers/" . $tea['tea_img'])): ?>
                                            <img src="../uploads/teachers/<?php echo $tea['tea_img']; ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-cvc-blue"><?php echo mb_substr($tea['tea_fullname'], 0, 1); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-700 text-sm group-hover:text-cvc-blue transition"><?php echo htmlspecialchars($tea['tea_fullname']); ?></div>
                                        <div class="text-[10px] text-slate-400 font-mono"><?php echo $tea['tea_username']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?php echo $badge_class; ?>">
                                    <?php echo $tea['sug_name'] ? htmlspecialchars($tea['sug_name']) : '-'; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-600 font-medium">
                                    <?php echo $tea['maj_name'] ? htmlspecialchars($tea['maj_name']) : '<span class="text-slate-300 italic">ไม่ระบุ</span>'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 opacity-60 group-hover:opacity-100 transition">
                                    <a href="manage_teacher_form.php?id=<?php echo $tea['tea_id']; ?>" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-amber-500 hover:border-amber-500 hover:bg-amber-50 flex items-center justify-center transition shadow-sm">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <a href="delete_teacher.php?id=<?php echo $tea['tea_id']; ?>" onclick="return confirm('ยืนยันลบข้อมูลครู?');" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-red-500 hover:border-red-500 hover:bg-red-50 flex items-center justify-center transition shadow-sm">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 font-light">ไม่พบข้อมูลครู</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>