<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_GET['pla_id'])) { header("Location: manage_plans.php"); exit(); }
$pla_id = $_GET['pla_id'];

// Reset Filters
if (isset($_GET['action']) && $_GET['action'] == 'reset') { unset($_SESSION['plan_filters']); header("Location: manage_plan_subjects.php?pla_id=$pla_id"); exit(); }

// Filter Logic
$saved_filters = $_SESSION['plan_filters'] ?? [];
$filter_cur = $saved_filters['cur'] ?? ''; $filter_sug = $saved_filters['sug'] ?? ''; $filter_com = $saved_filters['com'] ?? '';
if (isset($_GET['filter_cur'])) { 
    $filter_cur = $_GET['filter_cur']; $filter_sug = $_GET['filter_sug'] ?? '';
    if ($filter_sug == 6 || $filter_sug == 5) { $filter_com = ''; } else { $filter_com = $_GET['filter_com'] ?? ''; }
    $_SESSION['plan_filters'] = ['cur' => $filter_cur, 'sug' => $filter_sug, 'com' => $filter_com];
}

// Data Fetching
$plan = $pdo->prepare("SELECT * FROM study_plans WHERE pla_id = ?"); $plan->execute([$pla_id]); $plan_data = $plan->fetch();

// --- [เพิ่มใหม่] หาว่าแผนการเรียนนี้ เป็นของสาขาวิชาอะไร (maj_id) ---
$stmt_maj = $pdo->prepare("
    SELECT m.maj_id 
    FROM study_plans p 
    JOIN class_groups c ON p.cla_id = c.cla_id 
    JOIN majors m ON c.cla_major_code = m.maj_code 
    WHERE p.pla_id = ?
");
$stmt_maj->execute([$pla_id]);
$target_maj_id = $stmt_maj->fetchColumn(); 
// ----------------------------------------------------------------

$curriculums = $pdo->query("SELECT c.*, l.lev_name FROM curriculums c JOIN levels l ON c.lev_id = l.lev_id ORDER BY l.lev_id ASC, c.cur_year DESC")->fetchAll();
$groups = $pdo->query("SELECT * FROM subject_groups ORDER BY FIELD(sug_name, 'หมวดวิชาสมรรถนะแกนกลาง', 'หมวดวิชาสมรรถนะวิชาชีพ', 'หมวดวิชาเลือกเสรี', 'กิจกรรมเสริมหลักสูตร') ASC")->fetchAll();

$competencies_list = [];
if (!empty($filter_cur) && !empty($filter_sug) && $filter_sug != 6 && $filter_sug != 5) {
    $sql_com = "SELECT DISTINCT sub_competency FROM subjects WHERE cur_id = ? AND sug_id = ? AND sub_competency != '' ORDER BY sub_competency ASC";
    $stmt_com = $pdo->prepare($sql_com); $stmt_com->execute([$filter_cur, $filter_sug]); $competencies_list = $stmt_com->fetchAll(PDO::FETCH_COLUMN);
}

$subjects = [];
if (!empty($filter_cur) && !empty($filter_sug)) {
    // เตรียม Query พื้นฐาน
    if ($filter_sug == 6) { 
        $sql_sub = "SELECT * FROM subjects WHERE cur_id = ?"; 
        $params_sub = [$filter_cur]; 
    } else { 
        $sql_sub = "SELECT * FROM subjects WHERE cur_id = ? AND sug_id = ?"; 
        $params_sub = [$filter_cur, $filter_sug]; 
    }

    // กรองสมรรถนะ (ถ้ามี)
    if ($filter_sug != 6 && $filter_sug != 5 && !empty($filter_com)) { 
        $sql_sub .= " AND sub_competency = ?"; 
        $params_sub[] = $filter_com; 
    }

    // --- [เพิ่มใหม่] กรองเฉพาะวิชาของสาขานั้น หรือ วิชาที่ไม่สังกัดสาขา ---
    // เงื่อนไข: (maj_id เป็นค่าว่าง/NULL) หรือ (maj_id ตรงกับสาขาของแผนนี้)
    if ($target_maj_id) {
        $sql_sub .= " AND (maj_id IS NULL OR maj_id = '' OR maj_id = ?)";
        $params_sub[] = $target_maj_id;
    } else {
        // กรณีหา maj_id ของแผนไม่เจอ (เช่น ข้อมูลไม่ครบ) ให้แสดงเฉพาะวิชาส่วนกลาง
        $sql_sub .= " AND (maj_id IS NULL OR maj_id = '')";
    }
    // ----------------------------------------------------------------

    $sql_sub .= " ORDER BY sub_code ASC";
    $stmt_sub = $pdo->prepare($sql_sub); 
    $stmt_sub->execute($params_sub); 
    $subjects = $stmt_sub->fetchAll();
}

$teachers = $pdo->query("SELECT * FROM teachers ORDER BY tea_fullname ASC")->fetchAll();

// Existing Subjects in Plan (เหมือนเดิม)
$sql = "SELECT ps.*, s.sub_code, s.sub_name, s.sub_credit, s.sub_hours, s.sub_th_pr_ot, s.sub_competency, s.sug_id, sg.sug_name, t.tea_fullname 
        FROM plan_subjects ps 
        JOIN subjects s ON ps.sub_id = s.sub_id 
        LEFT JOIN subject_groups sg ON s.sug_id = sg.sug_id 
        LEFT JOIN teachers t ON ps.tea_id = t.tea_id 
        WHERE ps.pla_id = ? 
        ORDER BY s.sub_code ASC"; 
$stmt = $pdo->prepare($sql); $stmt->execute([$pla_id]); $plan_subjects = $stmt->fetchAll();
$existing_sub_ids = array_column($plan_subjects, 'sub_id');

// คำนวณยอดรวม (เหมือนเดิม)
$grand_total_subjects = count($plan_subjects); 
$grand_total_credits = 0; 
$grand_total_hours = 0;
$grouped_subjects = [];

foreach ($plan_subjects as $ps) {
    $current_sug_id = $ps['sug_id']; $current_sug_name = $ps['sug_name'];
    if ($ps['pls_note'] === 'free_elective') { $current_sug_id = 6; $current_sug_name = 'หมวดวิชาเลือกเสรี'; }
    if (empty($current_sug_id)) { $current_sug_id = 999; $current_sug_name = 'ไม่ระบุหมวดวิชา'; }
    
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

$group_colors = [ 1=>['bg'=>'bg-orange-50','border'=>'border-orange-400'], 2=>['bg'=>'bg-blue-50','border'=>'border-blue-400'], 5=>['bg'=>'bg-pink-50','border'=>'border-pink-400'], 6=>['bg'=>'bg-teal-50','border'=>'border-teal-400'], 999=>['bg'=>'bg-slate-50','border'=>'border-slate-400'] ];

require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto space-y-6 pb-12">
    
    <div class="flex flex-col md:flex-row justify-between items-start gap-4">
        <div>
            <a href="manage_plans.php" class="text-slate-400 hover:text-cvc-blue text-sm font-bold flex items-center gap-2 mb-2"><i class="fa-solid fa-arrow-left"></i> กลับหน้ารายชื่อแผน</a>
            <h1 class="text-3xl font-serif font-bold text-slate-800">จัดการรายวิชาในแผน</h1>
            <p class="text-slate-500 font-medium"><?php echo htmlspecialchars($plan_data['pla_name']); ?></p>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <div class="bg-white border border-slate-200 px-5 py-3 rounded-xl shadow-sm text-center min-w-[100px] flex-1 md:flex-none">
                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">จำนวนวิชา</div>
                <div class="text-3xl font-black text-emerald-500"><?php echo $grand_total_subjects; ?></div>
            </div>
            <div class="bg-white border border-slate-200 px-5 py-3 rounded-xl shadow-sm text-center min-w-[100px] flex-1 md:flex-none">
                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">หน่วยกิตรวม</div>
                <div class="text-3xl font-black text-cvc-blue"><?php echo $grand_total_credits; ?></div>
            </div>
            <div class="bg-white border border-slate-200 px-5 py-3 rounded-xl shadow-sm text-center min-w-[100px] flex-1 md:flex-none">
                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">ชั่วโมงรวม</div>
                <div class="text-3xl font-black text-amber-500"><?php echo $grand_total_hours; ?></div>
            </div>
        </div>
    </div>

    <div class="card-premium p-0 overflow-hidden border border-slate-200">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="font-bold text-slate-700 flex items-center gap-2"><i class="fa-solid fa-circle-plus text-green-500"></i> เพิ่มรายวิชา (Multi-Select)</h3>
            <?php if($filter_cur || $filter_sug): ?>
                <a href="?pla_id=<?php echo $pla_id; ?>&action=reset" class="text-xs text-red-500 hover:underline"><i class="fa-solid fa-rotate-left"></i> รีเซ็ตตัวกรอง</a>
            <?php endif; ?>
        </div>
        <div class="p-6">
            <form action="" method="GET" id="filterForm" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <input type="hidden" name="pla_id" value="<?php echo $pla_id; ?>">
                <div><label class="block text-xs font-bold text-slate-500 mb-1">1. หลักสูตร</label><select name="filter_cur" class="w-full text-sm" onchange="document.getElementById('filterForm').submit()"><option value="">-- เลือกหลักสูตร --</option><?php foreach ($curriculums as $c): ?><option value="<?php echo $c['cur_id']; ?>" <?php echo $filter_cur == $c['cur_id'] ? 'selected' : ''; ?>><?php echo $c['lev_name'] . ' ' . $c['cur_year']; ?></option><?php endforeach; ?></select></div>
                <div><label class="block text-xs font-bold text-slate-500 mb-1">2. หมวดวิชา</label><select name="filter_sug" class="w-full text-sm disabled:bg-slate-100" onchange="document.getElementById('filterForm').submit()" <?php echo empty($filter_cur) ? 'disabled' : ''; ?>><option value="">-- เลือกหมวด --</option><?php foreach ($groups as $g): ?><option value="<?php echo $g['sug_id']; ?>" <?php echo $filter_sug == $g['sug_id'] ? 'selected' : ''; ?>><?php echo $g['sug_name']; ?></option><?php endforeach; ?></select></div>
                <div><label class="block text-xs font-bold text-slate-500 mb-1">3. สมรรถนะ (ถ้ามี)</label><select name="filter_com" class="w-full text-sm disabled:bg-slate-100" onchange="document.getElementById('filterForm').submit()" <?php echo (empty($filter_cur) || empty($filter_sug) || $filter_sug == 6 || $filter_sug == 5) ? 'disabled' : ''; ?>><option value="">-- ทั้งหมด --</option><?php foreach ($competencies_list as $com): ?><option value="<?php echo $com; ?>" <?php echo $filter_com == $com ? 'selected' : ''; ?>><?php echo $com; ?></option><?php endforeach; ?></select></div>
            </form>

            <?php if (!empty($filter_cur) && !empty($filter_sug)): ?>
            <form action="save_plan_subject.php" method="POST">
                <input type="hidden" name="pla_id" value="<?php echo $pla_id; ?>">
                <input type="hidden" name="filter_sug_context" value="<?php echo $filter_sug; ?>">

                <div class="mb-4 bg-slate-50 border border-slate-200 rounded-xl p-4 h-[400px] overflow-y-auto custom-scrollbar relative">
                    <?php if (empty($subjects)): ?>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                            <i class="fa-regular fa-folder-open text-4xl mb-2"></i>
                            <p>ไม่พบรายวิชาตามเงื่อนไขที่เลือก (หรือกรองตามสาขาแล้วไม่พบ)</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <?php $vis = 0; foreach ($subjects as $s): if (in_array($s['sub_id'], $existing_sub_ids)) continue; $vis++; ?>
                                <label class="cursor-pointer group block relative">
                                    <input type="checkbox" name="sub_ids[]" value="<?php echo $s['sub_id']; ?>" class="peer sr-only sub-checkbox" onchange="filterTeachers()">
                                    <div class="p-3 bg-white border border-slate-200 rounded-lg peer-checked:border-cvc-blue peer-checked:bg-blue-50 peer-checked:ring-1 peer-checked:ring-cvc-blue hover:shadow-md transition">
                                        <div class="flex justify-between items-start mb-1">
                                            <span class="font-mono font-bold text-xs text-cvc-blue bg-blue-100 px-1.5 rounded"><?php echo $s['sub_code']; ?></span>
                                            <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 rounded"><?php echo $s['sub_th_pr_ot']; ?></span>
                                        </div>
                                        <div class="text-sm font-bold text-slate-700 leading-tight mb-1 group-hover:text-blue-700"><?php echo $s['sub_name']; ?></div>
                                        <div class="absolute top-2 right-2 text-cvc-blue opacity-0 peer-checked:opacity-100 transition"><i class="fa-solid fa-check-circle"></i></div>
                                    </div>
                                </label>
                            <?php endforeach; if ($vis == 0): ?><div class="absolute inset-0 flex items-center justify-center text-green-600 font-bold"><i class="fa-solid fa-check mr-2"></i> เลือกครบทุกวิชาแล้ว</div><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex gap-4 items-center bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">ครูผู้สอน (Optional)</label>
                        <select name="tea_id" id="tea_select" class="w-full text-sm"><option value="">-- ปล่อยว่าง (ยังไม่ระบุ) --</option><?php foreach ($teachers as $t): ?><option value="<?php echo $t['tea_id']; ?>" data-sug-id="<?php echo $t['sug_id']; ?>"><?php echo $t['tea_fullname']; ?></option><?php endforeach; ?></select>
                    </div>
                    <button type="submit" class="btn-cvc self-end h-10 shadow-lg"><i class="fa-solid fa-plus-circle mr-2"></i> บันทึกรายการ</button>
                </div>
            </form>
            <?php else: ?>
                <div class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-300 text-slate-400">
                    <i class="fa-solid fa-arrow-up text-3xl mb-2 animate-bounce"></i>
                    <p class="font-bold">กรุณาเลือก "หลักสูตร" และ "หมวดวิชา" ด้านบน</p>
                    <p class="text-xs">เพื่อแสดงรายชื่อวิชาที่สามารถเพิ่มลงในแผนได้</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (count($grouped_subjects) > 0): ?>
    <div class="space-y-4">
        <?php foreach ($grouped_subjects as $gid => $group): 
            $theme = $group_colors[$gid % 7] ?? $group_colors[999];
        ?>
            <div class="card-premium overflow-hidden border-0 shadow-sm">
                <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center <?php echo $theme['bg']; ?> border-l-4 <?php echo $theme['border']; ?>">
                    <h3 class="font-bold text-slate-700"><?php echo htmlspecialchars($group['name']); ?></h3>
                    
                    <div class="flex gap-2">
                        <span class="text-xs font-bold bg-white px-3 py-1 rounded-full border shadow-sm text-emerald-600">
                            <?php echo count($group['items']); ?> วิชา
                        </span>
                        <span class="text-xs font-bold bg-white px-3 py-1 rounded-full border shadow-sm text-slate-600">
                            <?php echo $group['credits']; ?> นก.
                        </span>
                        <span class="text-xs font-bold bg-white px-3 py-1 rounded-full border shadow-sm text-amber-600">
                            <?php echo $group['hours']; ?> ชม.
                        </span>
                    </div>
                </div>
                
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100 text-xs text-slate-500 uppercase font-extrabold tracking-wide">
                        <tr>
                            <th class="px-6 py-3 w-32 whitespace-nowrap bg-slate-50 text-center">รหัสวิชา</th>
                            <th class="px-6 py-3 font-bold bg-slate-50">ชื่อวิชา</th>
                            <th class="px-6 py-3 text-center w-20 whitespace-nowrap font-bold bg-slate-50">นก.</th>
                            <th class="px-6 py-3 text-center w-24 whitespace-nowrap font-bold bg-slate-50">ชม.</th>
                            <th class="px-6 py-3 font-bold w-64 bg-slate-50">ครูผู้สอน</th>
                            <th class="px-6 py-3 text-center w-32 font-bold bg-slate-50">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php foreach ($group['items'] as $ps): ?>
                        <tr class="hover:bg-blue-50/20 transition group">
                            
                            <td class="px-6 py-3 align-middle text-center whitespace-nowrap">
                                <span class="font-mono font-bold text-cvc-blue bg-blue-50 px-2 py-1 rounded border border-blue-100 shadow-sm text-sm">
                                    <?php echo $ps['sub_code']; ?>
                                </span>
                            </td>

                            <td class="px-6 py-3 align-middle">
                                <div class="font-medium text-slate-700 text-sm">
                                    <?php echo $ps['sub_name']; ?>
                                </div>
                                <?php if($ps['sub_competency']): ?>
                                    <div class="text-[10px] text-slate-400 mt-0.5"><?php echo $ps['sub_competency']; ?></div>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-3 align-middle text-center">
                                <span class="font-bold text-slate-600 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-200 text-xs">
                                    <?php echo $ps['sub_credit']; ?>
                                </span>
                            </td>
                            
                            <td class="px-6 py-3 align-middle text-center">
                                <div class="flex flex-col items-center">
                                    <span class="font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200 text-xs shadow-sm">
                                        <?php echo $ps['sub_hours']; ?>
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-mono mt-1">
                                        (<?php echo $ps['sub_th_pr_ot']; ?>)
                                    </span>
                                </div>
                            </td>
                            
                            <td class="px-6 py-3 align-middle">
                                <?php if($ps['tea_fullname']): ?>
                                    <div class="flex items-center">
                                        <span class="bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-emerald-100 flex items-center gap-2 shadow-sm">
                                            <i class="fa-solid fa-user-check"></i> <?php echo $ps['tea_fullname']; ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="flex items-center">
                                        <span class="bg-orange-50 text-orange-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-orange-100 flex items-center gap-2 shadow-sm animate-pulse">
                                            <i class="fa-regular fa-clock"></i> รอการเลือกครูผู้สอน
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-3 align-middle text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <a href="edit_plan_teacher.php?id=<?php echo $ps['pls_id']; ?>&pla_id=<?php echo $pla_id; ?>" 
                                       class="w-9 h-9 rounded-lg border border-amber-200 text-amber-500 hover:bg-amber-500 hover:text-white flex items-center justify-center transition shadow-sm group/btn" 
                                       title="แก้ไขครู">
                                        <i class="fa-solid fa-pen-to-square text-xs group-hover/btn:scale-110 transition-transform"></i>
                                    </a>
                                    <a href="delete_plan_subject.php?id=<?php echo $ps['pls_id']; ?>&pla_id=<?php echo $pla_id; ?>" 
                                       class="w-9 h-9 rounded-lg border border-red-200 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition shadow-sm group/btn" 
                                       onclick="return confirm('ยืนยันลบรายวิชานี้ออกจากแผน?');" 
                                       title="ลบวิชา">
                                        <i class="fa-solid fa-trash-can text-xs group-hover/btn:scale-110 transition-transform"></i>
                                    </a>
                                </div>
                            </td>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function filterTeachers() {
    const checkedBoxes = document.querySelectorAll('.sub-checkbox:checked');
    const teacherSelect = document.getElementById('tea_select');
    const options = teacherSelect.getElementsByTagName('option');
    const allowedSugIds = new Set();
    checkedBoxes.forEach(box => { const sugId = box.getAttribute('data-sug-id'); if(sugId) allowedSugIds.add(sugId); });
    for (let i = 1; i < options.length; i++) { 
        const opt = options[i]; const teacherSugId = opt.getAttribute('data-sug-id');
        if (allowedSugIds.size === 0 || allowedSugIds.has(teacherSugId)) { opt.hidden = false; opt.disabled = false; } else { opt.hidden = true; opt.disabled = true; }
    }
    if (teacherSelect.options[teacherSelect.selectedIndex].hidden) teacherSelect.value = "";
}
document.addEventListener("DOMContentLoaded", function() {
    const key = 'plan_scroll_<?php echo $pla_id; ?>';
    const pos = sessionStorage.getItem(key);
    if (pos) window.scrollTo(0, pos);
});
window.addEventListener("beforeunload", function() { sessionStorage.setItem('plan_scroll_<?php echo $pla_id; ?>', window.scrollY); });
</script>
<?php require_once '../includes/footer.php'; ?>