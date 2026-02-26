<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

$search = $_GET['search'] ?? '';

// SQL: ดึงข้อมูลแผนการเรียน + กลุ่มเรียนทั้งหมด (ผ่าน study_plan_classes)
$sql = "SELECT p.*, 
               GROUP_CONCAT(c.cla_id ORDER BY c.cla_id SEPARATOR '||') as all_cla_ids,
               GROUP_CONCAT(CONCAT(c.cla_name, '.', (? - c.cla_year + 1), '/', CAST(c.cla_group_no AS UNSIGNED)) ORDER BY c.cla_id SEPARATOR '||') as all_class_names,
               GROUP_CONCAT(m.maj_name ORDER BY c.cla_id SEPARATOR '||') as all_maj_names
        FROM study_plans p 
        LEFT JOIN study_plan_classes spc ON p.pla_id = spc.pla_id
        LEFT JOIN class_groups c ON spc.cla_id = c.cla_id
        LEFT JOIN majors m ON c.cla_major_code = m.maj_code
        WHERE p.pla_name LIKE ? OR p.pla_code LIKE ? 
        GROUP BY p.pla_id
        ORDER BY p.pla_id DESC";

$current_year = date('Y') + 543;

$stmt = $pdo->prepare($sql);
$stmt->execute([$current_year, "%$search%", "%$search%"]);
$plans = $stmt->fetchAll();

// ดึงรายวิชาทั้งหมดของทุกแผน (รวมทุกเทอม) แยกตามสมรรถนะ
$plan_ids = array_column($plans, 'pla_id');
$plan_subjects_map = [];

if (!empty($plan_ids)) {
    $placeholders = implode(',', array_fill(0, count($plan_ids), '?'));
    $sql_sub = "SELECT pc.pla_id, s.sub_id, s.sub_code, s.sub_name, s.sub_credit, s.sub_hours,
                       COALESCE(NULLIF(s.sub_competency, ''), sg.sug_name, 'ไม่ระบุหมวด') as competency_group,
                       sg.sug_name, s.sug_id
                FROM plan_subject_cart pc
                JOIN subjects s ON pc.sub_id = s.sub_id
                LEFT JOIN subject_groups sg ON s.sug_id = sg.sug_id
                WHERE pc.pla_id IN ($placeholders)
                ORDER BY s.sug_id ASC, s.sub_competency ASC, s.sub_code ASC";
    $stmt_sub = $pdo->prepare($sql_sub);
    $stmt_sub->execute($plan_ids);
    $all_subs = $stmt_sub->fetchAll();

    foreach ($all_subs as $sub) {
        $pid = $sub['pla_id'];
        $comp = $sub['competency_group'];
        if (!isset($plan_subjects_map[$pid])) {
            $plan_subjects_map[$pid] = ['groups' => [], 'total_subjects' => 0, 'total_credits' => 0];
        }
        if (!isset($plan_subjects_map[$pid]['groups'][$comp])) {
            $plan_subjects_map[$pid]['groups'][$comp] = [];
        }
        $plan_subjects_map[$pid]['groups'][$comp][] = $sub;
        $plan_subjects_map[$pid]['total_subjects']++;
        $plan_subjects_map[$pid]['total_credits'] += intval($sub['sub_credit']);
    }
}

require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto pb-12">
    <div class="mb-8">
        <a href="index.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2"><i class="fa-solid fa-arrow-left mr-2"></i> Dashboard</a>
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div><h1 class="text-3xl font-serif font-bold text-slate-800">จัดการแผนการเรียน</h1><p class="text-slate-500 mt-1">Study Plans Management</p></div>
            <div class="flex gap-3 w-full md:w-auto">
                <form class="relative flex-1 md:w-64 group">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="ค้นหาแผน..." class="w-full pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-full focus:ring-2 focus:ring-blue-100 focus:border-cvc-blue outline-none transition shadow-sm text-sm">
                    <i class="fa-solid fa-search absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                </form>
                <a href="manage_plan_form.php" class="btn-cvc text-sm shadow-md hover:shadow-lg"><i class="fa-solid fa-plus"></i> สร้างแผนใหม่</a>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <?php if (count($plans) > 0):
    foreach ($plans as $row):
        $pid = $row['pla_id'];
        $sub_data = $plan_subjects_map[$pid] ?? ['groups' => [], 'total_subjects' => 0, 'total_credits' => 0];
?>
            <div class="card-premium overflow-hidden border-0 shadow-xl shadow-slate-200/50">
                <!-- Header Row -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 px-6 py-5 bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
                    <div class="flex items-center gap-4">
                        <span class="font-mono font-bold text-cvc-blue bg-blue-50 px-3 py-1.5 rounded text-sm border border-blue-100 shadow-sm"><?php echo $row['pla_code']; ?></span>
                        <div>
                            <h3 class="font-bold text-slate-800 text-lg"><?php echo htmlspecialchars($row['pla_name']); ?></h3>
                            <div class="flex flex-wrap gap-1.5 mt-1">
                                <?php
        if ($row['all_class_names']):
            $class_names = explode('||', $row['all_class_names']);
            foreach ($class_names as $cls_name):
?>
                                    <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-xs border font-bold"><?php echo htmlspecialchars($cls_name); ?></span>
                                <?php
            endforeach;
        else: ?>
                                    <span class="text-slate-300 italic text-xs">ไม่ระบุกลุ่ม</span>
                                <?php
        endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <!-- สถิติ -->
                        <div class="flex gap-2 mr-2">
                            <span class="bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-emerald-200 shadow-sm">
                                <i class="fa-solid fa-book mr-1"></i> <?php echo $sub_data['total_subjects']; ?> วิชา
                            </span>
                            <span class="bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-blue-200 shadow-sm">
                                <i class="fa-solid fa-graduation-cap mr-1"></i> <?php echo $sub_data['total_credits']; ?> นก.
                            </span>
                        </div>
                        <!-- ปุ่มจัดการ -->
                        <a href="manage_plan_cart.php?pla_id=<?php echo $row['pla_id']; ?>" class="inline-flex items-center justify-center text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-200 px-4 py-2 rounded-lg transition font-bold shadow-sm hover:shadow-md">
                            <i class="fa-solid fa-cart-shopping mr-2"></i> เลือกรายวิชา
                        </a>
                        <a href="manage_plan_structure.php?id=<?php echo $row['pla_id']; ?>" class="inline-flex items-center justify-center text-xs bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white border border-indigo-200 px-4 py-2 rounded-lg transition font-bold shadow-sm hover:shadow-md">
                            <i class="fa-solid fa-sitemap mr-2"></i> จัดการโครงสร้าง
                        </a>
                        <a href="manage_plan_form.php?id=<?php echo $row['pla_id']; ?>" class="w-8 h-8 rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50 flex items-center justify-center transition"><i class="fa-solid fa-pen-to-square text-xs"></i></a>
                        <a href="javascript:void(0)" onclick="confirmDelete('delete_plan.php?id=<?php echo $row['pla_id']; ?>', '<?php echo addslashes($row['pla_name']); ?>')" class="w-8 h-8 rounded-lg border border-slate-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition"><i class="fa-solid fa-trash-can text-xs"></i></a>
                    </div>
                </div>
            </div>
        <?php
    endforeach;
else: ?>
            <div class="card-premium p-12 text-center text-slate-400">ไม่พบข้อมูลแผนการเรียน</div>
        <?php
endif; ?>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>