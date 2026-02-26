<?php
// admin/manage_plan_subjects.php
// เวอร์ชัน: Premium Maroon Red Theme
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. รับค่า Parameter
$pla_id = $_GET['pla_id'] ?? $_GET['plan_id'] ?? null;
$year = $_GET['year'] ?? null;
$semester = $_GET['semester'] ?? null;

// ถ้าข้อมูลไม่ครบ
if (($pla_id === null || $pla_id === '') || !$year || !$semester) {
    if ($pla_id !== null && $pla_id !== '') {
        echo "<script>alert('กรุณาเลือกปีและเทอมจากหน้าโครงสร้างแผนครับ'); window.location='manage_plan_structure.php?id=$pla_id';</script>";
    }
    else {
        echo "<script>alert('ข้อมูลไม่ครบถ้วน'); window.location='manage_plans.php';</script>";
    }
    exit();

}

// Fetch Plan Data
$plan = $pdo->prepare("SELECT * FROM study_plans WHERE pla_id = ?");
$plan->execute([$pla_id]);
$plan_data = $plan->fetch();
if (!$plan_data)
    die("ไม่พบข้อมูลแผนการเรียน (ID: $pla_id)");

// Fetch Plan Classes (Rooms/Groups)
$stmt_classes = $pdo->prepare("SELECT c.cla_name, c.cla_id FROM study_plan_classes pc 
                               JOIN class_groups c ON pc.cla_id = c.cla_id 
                               WHERE pc.pla_id = ? ORDER BY c.cla_name ASC");
$stmt_classes->execute([$pla_id]);
$plan_classes = $stmt_classes->fetchAll();

// ดึงรายวิชาจากตะกร้าโดยตรง (ไม่ต้องใช้ filter)
$sql_cart = "SELECT s.*, sg.sug_name, 
                    COALESCE(NULLIF(s.sub_competency, ''), sg.sug_name, 'ไม่ระบุหมวด') as comp_group
             FROM plan_subject_cart pc 
             JOIN subjects s ON pc.sub_id = s.sub_id 
             LEFT JOIN subject_groups sg ON s.sug_id = sg.sug_id
             WHERE pc.pla_id = ?
             ORDER BY s.sug_id ASC, s.sub_competency ASC, s.sub_code ASC";
$stmt_cart = $pdo->prepare($sql_cart);
$stmt_cart->execute([$pla_id]);
$subjects = $stmt_cart->fetchAll();

// จัดกลุ่มวิชาในตะกร้าตามสมรรถนะ
$cart_grouped = [];
foreach ($subjects as $s) {
    $cg = $s['comp_group'];
    if (!isset($cart_grouped[$cg]))
        $cart_grouped[$cg] = [];
    $cart_grouped[$cg][] = $s;
}

// Fetch Teachers
$teachers = $pdo->query("SELECT tea_id, tea_fullname, sug_id FROM teachers ORDER BY tea_fullname ASC")->fetchAll();

// Existing Plan Subjects
$sql = "SELECT ps.*, s.sub_code, s.sub_name, s.sub_credit, s.sub_hours, s.sub_th_pr_ot, s.sub_competency, s.sug_id, sg.sug_name, t.tea_fullname 
        FROM plan_subjects ps 
        JOIN subjects s ON ps.sub_id = s.sub_id 
        LEFT JOIN subject_groups sg ON s.sug_id = sg.sug_id 
        LEFT JOIN teachers t ON ps.tea_id = t.tea_id 
        WHERE ps.pla_id = ? AND ps.pls_academic_year = ? AND ps.pls_semester = ? 
        ORDER BY s.sub_code ASC";

$stmt = $pdo->prepare($sql);

$stmt->execute([$pla_id, $year, $semester]);

$plan_subjects = $stmt->fetchAll();
$existing_sub_ids = array_column($plan_subjects, 'sub_id');

// Grouping Logic
$grand_total_subjects = count($plan_subjects);
$grand_total_credits = 0;
$grand_total_hours = 0;
$grouped_subjects = [];
foreach ($plan_subjects as $ps) {
    $current_sug_id = $ps['sug_id'];
    $current_sug_name = $ps['sug_name'];
    if ($ps['pls_note'] === 'free_elective') {
        $current_sug_id = 6;
        $current_sug_name = 'หมวดวิชาเลือกเสรี';
    }
    if (empty($current_sug_id)) {
        $current_sug_id = 999;
        $current_sug_name = 'ไม่ระบุหมวดวิชา';
    }

    if (!isset($grouped_subjects[$current_sug_id])) {
        $grouped_subjects[$current_sug_id] = ['name' => $current_sug_name, 'items' => [], 'credits' => 0, 'hours' => 0];
    }
    $grouped_subjects[$current_sug_id]['items'][] = $ps;
    $grouped_subjects[$current_sug_id]['credits'] += intval($ps['sub_credit']);
    $grouped_subjects[$current_sug_id]['hours'] += intval($ps['sub_hours']);
    $grand_total_credits += intval($ps['sub_credit']);
    $grand_total_hours += intval($ps['sub_hours']);
}
ksort($grouped_subjects);

// --- สีครบทุกหมวด (Refined for Premium Look) ---
$group_colors = [
    1 => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'text' => 'text-orange-800', 'icon_bg' => 'bg-orange-100', 'icon_text' => 'text-orange-600'],
    2 => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'icon_bg' => 'bg-blue-100', 'icon_text' => 'text-blue-600'],
    3 => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'text' => 'text-indigo-800', 'icon_bg' => 'bg-indigo-100', 'icon_text' => 'text-indigo-600'],
    4 => ['bg' => 'bg-cyan-50', 'border' => 'border-cyan-200', 'text' => 'text-cyan-800', 'icon_bg' => 'bg-cyan-100', 'icon_text' => 'text-cyan-600'],
    5 => ['bg' => 'bg-pink-50', 'border' => 'border-pink-200', 'text' => 'text-pink-800', 'icon_bg' => 'bg-pink-100', 'icon_text' => 'text-pink-600'],
    6 => ['bg' => 'bg-teal-50', 'border' => 'border-teal-200', 'text' => 'text-teal-800', 'icon_bg' => 'bg-teal-100', 'icon_text' => 'text-teal-600'],
    999 => ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'text' => 'text-slate-800', 'icon_bg' => 'bg-slate-100', 'icon_text' => 'text-slate-600']

];

require_once '../includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .fade-out { opacity: 0; transform: translateX(20px); transition: all 0.3s ease-out; }
    .modal { transition: opacity 0.25s ease; }
    body.modal-active { overflow-x: hidden; overflow-y: hidden !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .glass-effect {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
    }
</style>

<div class="max-w-7xl mx-auto space-y-8 pb-24 pt-6">
    
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start gap-6">
        <div>
            <nav class="flex mb-3" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="manage_plans.php" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-red-700 transition">
                            <i class="fa-solid fa-table-list mr-2"></i> แผนการเรียน
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fa-solid fa-chevron-right text-slate-300 mx-2 text-xs"></i>
                            <a href="manage_plan_structure.php?id=<?php echo $pla_id; ?>" class="text-sm font-medium text-slate-500 hover:text-red-700 transition">โครงสร้างแผน</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fa-solid fa-chevron-right text-slate-300 mx-2 text-xs"></i>
                            <span class="text-sm font-bold text-red-800">จัดการรายวิชา</span>
                        </div>
                    </li>
                </ol>
            </nav>
            
            <h1 class="text-3xl font-serif font-bold text-slate-800 mb-3 border-l-4 border-red-700 pl-4">
                จัดการรายวิชาในแผน
            </h1>
            
            <div class="flex flex-col gap-2">
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <span class="bg-red-50 text-red-800 px-3 py-1 rounded-full font-bold border border-red-100 shadow-sm">
                        <i class="fa-solid fa-file-invoice mr-1.5"></i> <?php echo htmlspecialchars($plan_data['pla_name']); ?>
                    </span>
                    <span class="text-slate-300">|</span>
                    <span class="bg-white text-slate-700 px-3 py-1 rounded-full font-bold border border-slate-200 shadow-sm group hover:border-red-200 transition">
                        ปีการศึกษา <span class="text-red-600"><?php echo $year; ?></span>
                    </span>
                    <span class="bg-white text-slate-700 px-3 py-1 rounded-full font-bold border border-slate-200 shadow-sm group hover:border-red-200 transition">
                        ภาคเรียนที่ <span class="text-red-600"><?php echo $semester; ?></span>
                    </span>
                </div>
                
                <?php if (!empty($plan_classes)): ?>
                <div class="flex flex-wrap items-center gap-2 mt-1 ml-1">
                    <span class="text-xs text-slate-500 font-bold"><i class="fa-solid fa-users mr-1"></i> ใช้กับห้อง:</span>
                    <?php foreach ($plan_classes as $pc): ?>
                        <span class="bg-amber-50 text-amber-700 px-2.5 py-0.5 rounded-full text-xs font-bold border border-amber-200 shadow-sm">
                            <?php echo htmlspecialchars($pc['cla_name']); ?>
                        </span>
                    <?php
    endforeach; ?>
                </div>
                <?php
endif; ?>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="flex flex-wrap gap-4" id="stats-container">
            <div class="bg-white px-6 py-4 rounded-2xl shadow-sm border border-slate-200 text-center min-w-[120px] hover:shadow-lg hover:border-red-200 hover:-translate-y-1 transition-all duration-300 group cursor-default">
                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">จำนวนวิชา</div>
                <div class="flex items-center justify-center gap-2">
                    <i class="fa-solid fa-book text-emerald-200 group-hover:text-emerald-500 transition duration-300 text-lg"></i>
                    <div class="text-3xl font-black text-slate-700 group-hover:text-emerald-600 transition" id="total-subjects"><?php echo $grand_total_subjects; ?></div>
                </div>
            </div>
            
            <div class="bg-white px-6 py-4 rounded-2xl shadow-sm border border-slate-200 text-center min-w-[120px] hover:shadow-lg hover:border-red-200 hover:-translate-y-1 transition-all duration-300 group cursor-default">
                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">หน่วยกิตรวม</div>
                <div class="flex items-center justify-center gap-2">
                    <i class="fa-solid fa-star text-amber-200 group-hover:text-amber-500 transition duration-300 text-lg"></i>
                    <div class="text-3xl font-black text-slate-700 group-hover:text-amber-600 transition" id="total-credits"><?php echo $grand_total_credits; ?></div>
                </div>
            </div>

            <div class="bg-white px-6 py-4 rounded-2xl shadow-sm border border-slate-200 text-center min-w-[120px] hover:shadow-lg hover:border-red-200 hover:-translate-y-1 transition-all duration-300 group cursor-default">
                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1">ชั่วโมงรวม</div>
                <div class="flex items-center justify-center gap-2">
                    <i class="fa-regular fa-clock text-blue-200 group-hover:text-blue-500 transition duration-300 text-lg"></i>
                    <div class="text-3xl font-black text-slate-700 group-hover:text-blue-600 transition" id="total-hours"><?php echo $grand_total_hours; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Subjects Section -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
        <div class="bg-slate-50/80 px-8 py-5 border-b border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4 backdrop-blur-sm">
            <div>
                <h3 class="font-bold text-xl text-slate-800 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </span>
                    รายวิชาในตะกร้า
                </h3>
                <p class="text-slate-500 text-xs mt-1 ml-14">เลือกรายวิชาด้านล่างเพื่อเพิ่มเข้าสู่โครงสร้างแผนการเรียน</p>
            </div>
            
            <a href="manage_plan_cart.php?pla_id=<?php echo $pla_id; ?>" class="group flex items-center gap-2 text-sm bg-white text-slate-600 hover:text-red-600 px-4 py-2 rounded-xl border border-slate-200 hover:border-red-200 shadow-sm hover:shadow-md transition-all">
                <i class="fa-solid fa-pen-to-square text-slate-400 group-hover:text-red-500 transition"></i> 
                จัดการตะกร้าวิชา
            </a>
        </div>
        
        <div class="p-8">
            <?php if (empty($subjects)): ?>
                <div class="flex flex-col items-center justify-center p-12 bg-slate-50/50 rounded-2xl border-2 border-dashed border-slate-200 text-slate-400">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-sm mb-4">
                        <i class="fa-solid fa-basket-shopping text-3xl text-slate-300"></i>
                    </div>
                    <p class="font-bold text-lg text-slate-600">ยังไม่มีรายวิชาในตะกร้า</p>
                    <p class="text-sm mt-1 mb-6">กรุณาไปเพิ่มรายวิชาในตะกร้าก่อน ถึงจะนำมาจัดลงแผนได้</p>
                    <a href="manage_plan_cart.php?pla_id=<?php echo $pla_id; ?>" class="inline-flex items-center gap-2 bg-red-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-red-700 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                        <i class="fa-solid fa-plus"></i> ไปเลือกรายวิชา
                    </a>
                </div>
            <?php
else: ?>
                <form id="addSubjectsForm">
                    <input type="hidden" name="pla_id" value="<?php echo $pla_id; ?>">
                    <input type="hidden" name="year" value="<?php echo $year; ?>">
                    <input type="hidden" name="semester" value="<?php echo $semester; ?>">
                    <input type="hidden" name="filter_sug_context" value="">

                    <div class="mb-6 bg-slate-50/50 border border-slate-200 rounded-2xl p-6 max-h-[600px] overflow-y-auto custom-scrollbar shadow-inner">
                        <?php foreach ($cart_grouped as $comp_name => $comp_subs): ?>
                        <div class="mb-8 last:mb-0">
                            <div class="flex items-center gap-3 mb-4 sticky top-0 bg-slate-50/95 py-2 z-10 backdrop-blur-sm border-b border-slate-100">
                                <span class="w-1.5 h-6 rounded-full bg-red-500"></span>
                                <span class="text-sm font-bold text-slate-700 uppercase tracking-wide"><?php echo htmlspecialchars($comp_name); ?></span>
                                <span class="text-xs font-bold bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full"><?php echo count($comp_subs); ?></span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                <?php foreach ($comp_subs as $s):
            $already_added = in_array($s['sub_id'], $existing_sub_ids);
?>
                                    <div class="group relative bg-white border border-slate-200 rounded-xl hover:shadow-lg hover:border-red-200 transition-all duration-300 cursor-pointer overflow-hidden <?php echo $already_added ? 'opacity-60 grayscale' : ''; ?>" id="card_<?php echo $s['sub_id']; ?>">
                                        <?php if ($already_added): ?>
                                            <div class="absolute inset-0 flex items-center justify-center bg-white/60 backdrop-blur-[1px] z-20 cursor-not-allowed">
                                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200 shadow-sm flex items-center gap-1">
                                                    <i class="fa-solid fa-check-circle"></i> เพิ่มแล้ว
                                                </span>
                                            </div>
                                        <?php
            endif; ?>
                                        
                                        <label class="block p-4 cursor-pointer h-full">
                                            <input type="checkbox" name="sub_ids[]" value="<?php echo $s['sub_id']; ?>" class="peer sr-only sub-checkbox" <?php echo $already_added ? 'disabled' : ''; ?> onchange="toggleCardSelect(this, '<?php echo $s['sub_id']; ?>')">
                                            
                                            <!-- Check Indicator -->
                                            <div class="absolute top-3 right-3 w-6 h-6 rounded-full border-2 border-slate-200 flex items-center justify-center peer-checked:bg-red-500 peer-checked:border-red-500 transition-all z-10">
                                                <i class="fa-solid fa-check text-white text-[10px] opacity-0 peer-checked:opacity-100 transform scale-50 peer-checked:scale-100 transition-all duration-200"></i>
                                            </div>

                                            <div class="flex flex-col h-full">
                                                <div class="flex items-start justify-between mb-2 pr-6">
                                                    <span class="font-mono font-bold text-xs text-red-600 bg-red-50 px-2 py-1 rounded border border-red-100"><?php echo $s['sub_code']; ?></span>
                                                    <div class="flex gap-1">
                                                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200"><?php echo $s['sub_credit']; ?> นก.</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="text-sm font-bold text-slate-700 leading-snug mb-3 group-hover:text-red-700 transition-colors">
                                                    <?php echo htmlspecialchars($s['sub_name']); ?>
                                                </div>
                                                
                                                <div class="mt-auto pt-3 border-t border-slate-100 flex justify-between items-center text-xs text-slate-400 font-medium">
                                                    <span><?php echo $s['sub_th_pr_ot']; ?></span>
                                                    <span><?php echo $s['sub_hours']; ?> ชม.</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Selection Overlay -->
                                            <div class="absolute inset-0 border-2 border-red-500 rounded-xl opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity duration-200 shadow-[inset_0_0_0_2px_rgba(239,68,68,0.1)]"></div>
                                        </label>
                                    </div>
                                <?php
        endforeach; ?>
                            </div>
                        </div>
                        <?php
    endforeach; ?>
                    </div>

                    <div class="flex flex-col md:flex-row gap-4 items-center bg-slate-50 p-6 rounded-2xl border border-slate-200 shadow-sm">
                        <div class="flex-1 w-full">
                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700 mb-2">
                                <i class="fa-solid fa-chalkboard-user text-red-500"></i>
                                กำหนดครูผู้สอนสำหรับกลุ่มที่เลือก (Optional)
                            </label>
                            <div class="relative">
                                <select name="tea_id" id="tea_select" class="w-full text-sm text-slate-600 bg-white border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-red-200 focus:border-red-400 outline-none transition appearance-none cursor-pointer">
                                    <option value="">-- ปล่อยว่าง (รอเลือกทีหลัง) --</option>
                                    <?php foreach ($teachers as $t): ?>
                                        <option value="<?php echo $t['tea_id']; ?>"><?php echo $t['tea_fullname']; ?></option>
                                    <?php
    endforeach; ?>
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="submitAddSubjects()" class="w-full md:w-auto bg-slate-800 text-white px-8 py-3 rounded-xl font-bold hover:bg-slate-900 hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-plus-circle text-red-400"></i> 
                            เพิ่มรายวิชาที่เลือก
                        </button>
                    </div>
                </form>
            <?php
endif; ?>
        </div>
    </div>

    <!-- Grouped Tables Section -->
    <?php if (count($grouped_subjects) > 0): ?>
    <div class="space-y-8" id="plan-tables">
        <?php foreach ($grouped_subjects as $gid => $group):
        $theme = $group_colors[$gid % 7] ?? $group_colors[999];
?>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-all duration-500 group-section" data-group-id="<?php echo $gid; ?>">
                <!-- Group Header -->
                <div class="px-8 py-5 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4 bg-gradient-to-r from-slate-50 to-white">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl <?php echo $theme['icon_bg']; ?> <?php echo $theme['icon_text']; ?> flex items-center justify-center text-xl shadow-sm rotate-3">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-800"><?php echo htmlspecialchars($group['name']); ?></h3>
                            <div class="flex items-center gap-3 text-xs font-medium text-slate-500 mt-0.5">
                                <span class="bg-white px-2 py-0.5 rounded border border-slate-200 shadow-sm flex items-center gap-1">
                                    <i class="fa-solid fa-book text-slate-300"></i> <?php echo count($group['items']); ?> วิชา
                                </span>
                                <span class="bg-white px-2 py-0.5 rounded border border-slate-200 shadow-sm flex items-center gap-1">
                                    <i class="fa-solid fa-star text-slate-300"></i> <?php echo $group['credits']; ?> หน่วยกิต
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-xs text-slate-500 uppercase font-extrabold tracking-wider border-b border-slate-100">
                                <th class="px-6 py-4 w-32 whitespace-nowrap text-center">รหัสวิชา</th>
                                <th class="px-6 py-4">ชื่อวิชา</th>
                                <th class="px-6 py-4 text-center w-24">นก.</th>
                                <th class="px-6 py-4 text-center w-24">ชม.</th>
                                <th class="px-6 py-4 w-64">ครูผู้สอน</th>
                                <th class="px-6 py-4 text-center w-32">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php foreach ($group['items'] as $ps): ?>
                            <tr class="hover:bg-red-50/30 transition duration-200 group/row" id="row_<?php echo $ps['pls_id']; ?>" data-credit="<?php echo $ps['sub_credit']; ?>" data-hours="<?php echo $ps['sub_hours']; ?>">
                                <td class="px-6 py-4 align-middle text-center whitespace-nowrap">
                                    <span class="font-mono font-bold text-slate-700 bg-white border border-slate-200 px-2.5 py-1.5 rounded-lg shadow-sm text-sm group-hover/row:text-red-700 group-hover/row:border-red-200 transition">
                                        <?php echo $ps['sub_code']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <div class="font-bold text-slate-700 text-sm mb-0.5 group-hover/row:text-red-900 transition"><?php echo $ps['sub_name']; ?></div>
                                    <div class="text-[10px] text-slate-400 font-medium bg-slate-100 px-1.5 rounded w-fit"><?php echo $ps['sub_th_pr_ot']; ?></div>
                                </td>
                                <td class="px-6 py-4 align-middle text-center">
                                    <span class="font-bold text-slate-600"><?php echo $ps['sub_credit']; ?></span>
                                </td>
                                <td class="px-6 py-4 align-middle text-center">
                                    <span class="font-bold text-slate-400"><?php echo $ps['sub_hours']; ?></span>
                                </td>
                                
                                <td class="px-6 py-4 align-middle" id="teacher_cell_<?php echo $ps['pls_id']; ?>">
                                    <?php if ($ps['tea_fullname']): ?>
                                        <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-emerald-100 shadow-sm group-hover/row:bg-white group-hover/row:border-emerald-200 transition">
                                            <div class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center text-[10px]"><i class="fa-solid fa-user-check"></i></div>
                                            <?php echo $ps['tea_fullname']; ?>
                                        </div>
                                    <?php
            else: ?>
                                        <div class="inline-flex items-center gap-2 bg-orange-50 text-orange-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-orange-100 shadow-sm animate-pulse">
                                            <i class="fa-regular fa-clock"></i> รอการเลือกครู
                                        </div>
                                    <?php
            endif; ?>
                                </td>

                                <td class="px-6 py-4 align-middle text-center">
                                    <div class="flex justify-center items-center gap-2 opacity-60 group-hover/row:opacity-100 transition">
                                        <button onclick="openEditTeacher(<?php echo $ps['pls_id']; ?>, '<?php echo $ps['tea_id']; ?>', '<?php echo addslashes($ps['sub_name']); ?>', '<?php echo $ps['sug_id']; ?>')" class="w-8 h-8 rounded-lg border border-slate-200 text-slate-400 hover:bg-amber-500 hover:text-white hover:border-amber-500 flex items-center justify-center transition shadow-sm" title="เปลี่ยนครูผู้สอน">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>
                                        
                                        <button onclick="deleteSubject(<?php echo $ps['pls_id']; ?>, <?php echo $pla_id; ?>, this)" class="w-8 h-8 rounded-lg border border-slate-200 text-slate-400 hover:bg-red-500 hover:text-white hover:border-red-500 flex items-center justify-center transition shadow-sm" title="ลบออกจากแผน">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php
        endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php
    endforeach; ?>
    </div>
    <?php
endif; ?>
</div>

<!-- Modal: Teacher Selection -->
<div id="teacherModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeEditTeacher()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <!-- Modal Content -->
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border border-slate-200">
            <div class="bg-white p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-50 text-red-600">
                        <i class="fa-solid fa-user-pen text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg leading-6 font-bold text-slate-800" id="modal-title">เปลี่ยนครูผู้สอน</h3>
                        <p class="text-sm text-slate-500">เลือกครูผู้สอนสำหรับรายวิชานี้</p>
                    </div>
                </div>
                
                <div class="mt-4 bg-slate-50 p-4 rounded-xl border border-slate-100 mb-4">
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wide mb-1">รายวิชา</p>
                    <p class="text-sm font-bold text-slate-700" id="modalSubName">วิชา...</p>
                </div>
                
                <form id="modalForm">
                    <input type="hidden" id="modal_pls_id" name="pls_id">
                    <label class="block text-sm font-bold text-slate-700 mb-2">เลือกครูผู้สอน</label>
                    <div class="relative">
                        <select id="modal_tea_id" name="tea_id" class="w-full text-sm border border-slate-300 rounded-xl py-2.5 px-3 focus:ring-2 focus:ring-red-200 focus:border-red-500 outline-none appearance-none cursor-pointer">
                            <option value="">-- ปล่อยว่าง (รอเลือกครู) --</option>
                        </select>
                         <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="bg-slate-50 px-6 py-4 flex flex-col-reverse sm:flex-row gap-2 sm:justify-end border-t border-slate-100">
                <button type="button" onclick="closeEditTeacher()" class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-slate-200 shadow-sm px-4 py-2 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 focus:outline-none transition">
                    ยกเลิก
                </button>
                <button type="button" onclick="saveTeacherChange()" class="w-full sm:w-auto inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2 bg-red-600 text-sm font-bold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition">
                    <i class="fa-solid fa-save mr-2"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const allTeachers = <?php echo json_encode($teachers); ?>;

function toggleCardSelect(checkbox, subId) {
    const card = document.getElementById('card_' + subId);
    // Visual update handled by CSS mostly via peer-checked, but extra logic if needed
    // In this premium version, CSS peer-checked handles the border and overlay.
}

function openEditTeacher(pls_id, current_tea_id, sub_name, target_sug_id) {
    document.getElementById('modalSubName').innerText = sub_name;
    document.getElementById('modal_pls_id').value = pls_id;
    const select = document.getElementById('modal_tea_id');
    select.innerHTML = '<option value="">-- ปล่อยว่าง (รอเลือกครู) --</option>'; 
    allTeachers.forEach(tea => {
        if ((target_sug_id && tea.sug_id == target_sug_id) || !target_sug_id) {
            const option = document.createElement('option');
            option.value = tea.tea_id;
            option.text = tea.tea_fullname;
            select.appendChild(option);
        }
    });
    if (current_tea_id) {
        select.value = current_tea_id;
        if (select.value === "") { 
            const originalTea = allTeachers.find(t => t.tea_id == current_tea_id);
            if (originalTea) {
                const option = document.createElement('option');
                option.value = originalTea.tea_id;
                option.text = originalTea.tea_fullname + " (ต่างหมวด)";
                option.selected = true;
                select.appendChild(option);
            }
        }
    } else {
        select.value = "";
    }
    document.getElementById('teacherModal').classList.remove('hidden');
    document.body.classList.add('modal-active');
}

function closeEditTeacher() {
    document.getElementById('teacherModal').classList.add('hidden');
    document.body.classList.remove('modal-active');
}

async function saveTeacherChange() {
    const pls_id = document.getElementById('modal_pls_id').value;
    const tea_id = document.getElementById('modal_tea_id').value;
    const select = document.getElementById('modal_tea_id');
    const tea_name = select.selectedIndex >= 0 ? select.options[select.selectedIndex].text : '';
    const is_empty = (tea_id === "");
    const btn = document.querySelector('#teacherModal button[onclick="saveTeacherChange()"]');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> กำลังบันทึก';
    btn.disabled = true;

    try {
        const formData = new FormData();
        formData.append('pls_id', pls_id);
        formData.append('tea_id', tea_id);
        const response = await fetch('edit_plan_teacher.php', { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.status === 'success') {
            const cell = document.getElementById('teacher_cell_' + pls_id);
            if (is_empty) {
                cell.innerHTML = '<div class="inline-flex items-center gap-2 bg-orange-50 text-orange-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-orange-100 shadow-sm animate-pulse"><i class="fa-regular fa-clock"></i> รอการเลือกครู</div>';
            } else {
                const cleanName = tea_name.replace(' (ต่างหมวด)', '');
                cell.innerHTML = `<div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-emerald-100 shadow-sm group-hover/row:bg-white group-hover/row:border-emerald-200 transition"><div class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center text-[10px]"><i class="fa-solid fa-user-check"></i></div>${cleanName}</div>`;
            }
            closeEditTeacher();
            
            // SweetAlert2 Toast with Robot
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            Toast.fire({ 
                title: 'บันทึกข้อมูลเรียบร้อย',
                iconHtml: '<i class="fa-solid fa-robot text-emerald-500 text-xl"></i>',
                customClass: {
                    icon: 'border-0'
                }
            });
            
        } else {
            Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: data.message, confirmButtonColor: '#ef4444', customClass: { popup: 'rounded-3xl' } });
        }
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถเชื่อต่อกับเซิร์ฟเวอร์ได้', confirmButtonColor: '#ef4444', customClass: { popup: 'rounded-3xl' } });
    } finally {
        btn.innerHTML = originalContent;
        btn.disabled = false;
    }
}

async function deleteSubject(pls_id, pla_id, btn) {
    const result = await Swal.fire({
        title: 'ยืนยันการลบ?',
        html: "คุณต้องการลบวิชานี้ออกจากแผนหรือไม่<br><span class='text-sm text-slate-500'>การกระทำนี้ไม่สามารถย้อนกลับได้</span>",
        iconHtml: '<div class="flex items-center justify-center w-24 h-24 bg-red-100 rounded-full border-4 border-white shadow-lg p-2"><i class="fa-solid fa-robot text-4xl text-red-500 animate-bounce"></i></div>',
        customClass: {
            icon: 'border-0',
            popup: 'rounded-3xl font-sans',
            confirmButton: 'rounded-full px-6 py-2 shadow-lg',
            cancelButton: 'rounded-full px-6 py-2'
        },
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#e2e8f0',
        confirmButtonText: '<i class="fa-solid fa-trash-can mr-2"></i>ใช่, ลบเลย',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        try {
            const row = btn.closest('tr');
            // Call API
            const response = await fetch(`delete_plan_subject.php?id=${pls_id}&pla_id=${pla_id}&year=<?php echo $year; ?>&semester=<?php echo $semester; ?>&ajax=1`);
            const data = await response.json(); 
            
            if (data.status === 'success') {
                if(row) {
                    row.style.transition = 'all 0.5s ease';
                    row.style.transform = 'translateX(50px)';
                    row.style.opacity = '0';
                }
                setTimeout(() => window.location.reload(), 500);
            } else { 
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: data.message, confirmButtonColor: '#ef4444', customClass: { popup: 'rounded-3xl' } });
            }
        } catch (error) { 
            Swal.fire({ icon: 'error', title: 'Connection Error', text: 'เชื่อมต่อล้มเหลว', confirmButtonColor: '#ef4444', customClass: { popup: 'rounded-3xl' } });
        }
    }
}

async function submitAddSubjects() {
    const form = document.getElementById('addSubjectsForm');
    const formData = new FormData(form);
    let hasSubject = false;
    for (var pair of formData.entries()) { if (pair[0] === 'sub_ids[]') hasSubject = true; }
    
    if (!hasSubject) { 
        Swal.fire({ 
            title: 'ยังไม่ได้เลือกวิชา', 
            html: 'กรุณาติ๊กเลือกวิชาที่ต้องการเพิ่มก่อนครับ',
            iconHtml: '<div class="flex items-center justify-center w-20 h-20 bg-amber-100 rounded-full border-4 border-white shadow-lg"><i class="fa-solid fa-robot text-3xl text-amber-500 animate-pulse"></i></div>',
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'เข้าใจแล้ว',
            customClass: { 
                icon: 'border-0',
                popup: 'rounded-3xl font-sans',
                confirmButton: 'rounded-full px-6 py-2'
            }
        }); 
        return; 
    }

    Swal.fire({ 
        title: 'กำลังบันทึก...', 
        html: 'กรุณารอสักครู่ ระบบกำลังเพิ่มวิชาเข้าสู่แผน',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading() },
        customClass: { popup: 'rounded-3xl' }
    });
    
    try {
        const response = await fetch('save_plan_subject.php?ajax=1', { method: 'POST', body: formData });
        const data = await response.json(); 
        
        if (data.status === 'success') {
            await Swal.fire({ 
                title: 'บันทึกสำเร็จ!', 
                html: 'เพิ่มรายวิชาเข้าสู่แผนเรียบร้อยแล้ว',
                iconHtml: '<div class="flex items-center justify-center w-24 h-24 bg-emerald-100 rounded-full border-4 border-white shadow-lg"><i class="fa-solid fa-robot text-4xl text-emerald-500 animate-bounce"></i></div>',
                timer: 1500, 
                showConfirmButton: false,
                customClass: { 
                    icon: 'border-0',
                    popup: 'rounded-3xl font-sans'
                }
            });
            window.location.reload();
        } else { 
            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error', confirmButtonColor: '#ef4444', customClass: { popup: 'rounded-3xl' } }); 
        }
    } catch (error) { 
        console.error(error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Connection Error', confirmButtonColor: '#ef4444', customClass: { popup: 'rounded-3xl' } }); 
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const key = 'plan_scroll_<?php echo $pla_id; ?>';
    const pos = sessionStorage.getItem(key);
    if (pos) window.scrollTo(0, pos);
});
window.addEventListener("beforeunload", function() { sessionStorage.setItem('plan_scroll_<?php echo $pla_id; ?>', window.scrollY); });
</script>

<?php require_once '../includes/footer.php'; ?>