<?php
// admin/manage_plan_subjects.php
// เวอร์ชัน: Full Option + No Wrap Sub Code
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. รับค่า Parameter
$pla_id = $_GET['pla_id'] ?? $_GET['plan_id'] ?? null;
$year = $_GET['year'] ?? null;
$semester = $_GET['semester'] ?? null;

// ถ้าข้อมูลไม่ครบ
if (($pla_id === null || $pla_id === '') || !$year || !$semester) { 
    if ($pla_id !== null && $pla_id !== '') {
        echo "<script>alert('กรุณาเลือกปีและเทอมจากหน้าโครงสร้างแผนครับ'); window.location='manage_plan_structure.php?id=$pla_id';</script>";
    } else {
        echo "<script>alert('ข้อมูลไม่ครบถ้วน'); window.location='manage_plans.php';</script>";
    }
    exit(); 
}

// Reset Filters
if (isset($_GET['action']) && $_GET['action'] == 'reset') { 
    unset($_SESSION['plan_filters']); 
    header("Location: manage_plan_subjects.php?pla_id=$pla_id&year=$year&semester=$semester"); 
    exit(); 
}

// Filter Logic
$saved_filters = $_SESSION['plan_filters'] ?? [];
$filter_cur = $saved_filters['cur'] ?? ''; 
$filter_sug = $saved_filters['sug'] ?? ''; 
$filter_com = $saved_filters['com'] ?? '';
$show_all_majors = $saved_filters['all_majors'] ?? 0;

if (isset($_GET['filter_cur'])) { 
    $filter_cur = $_GET['filter_cur']; 
    $filter_sug = $_GET['filter_sug'] ?? '';
    $show_all_majors = isset($_GET['show_all_majors']) ? 1 : 0;
    
    if ($filter_sug == 6 || $filter_sug == 5) { $filter_com = ''; } else { $filter_com = $_GET['filter_com'] ?? ''; }
    $_SESSION['plan_filters'] = ['cur' => $filter_cur, 'sug' => $filter_sug, 'com' => $filter_com, 'all_majors' => $show_all_majors];
}

// Fetch Plan Data
$plan = $pdo->prepare("SELECT * FROM study_plans WHERE pla_id = ?"); 
$plan->execute([$pla_id]); 
$plan_data = $plan->fetch();

if (!$plan_data) die("ไม่พบข้อมูลแผนการเรียน (ID: $pla_id)");

// Maj_id Logic
$stmt_maj = $pdo->prepare("SELECT m.maj_id FROM study_plans p JOIN class_groups c ON p.cla_id = c.cla_id JOIN majors m ON c.cla_major_code = m.maj_code WHERE p.pla_id = ?");
$stmt_maj->execute([$pla_id]);
$target_maj_id = $stmt_maj->fetchColumn(); 

// Data for Filters
$curriculums = $pdo->query("SELECT c.*, l.lev_name FROM curriculums c JOIN levels l ON c.lev_id = l.lev_id ORDER BY l.lev_id ASC, c.cur_year DESC")->fetchAll();
$groups = $pdo->query("SELECT * FROM subject_groups ORDER BY FIELD(sug_name, 'หมวดวิชาสมรรถนะแกนกลาง', 'หมวดวิชาสมรรถนะวิชาชีพ', 'หมวดวิชาเลือกเสรี', 'กิจกรรมเสริมหลักสูตร') ASC")->fetchAll();

$competencies_list = [];
if (!empty($filter_cur) && !empty($filter_sug) && $filter_sug != 6 && $filter_sug != 5) {
    $sql_com = "SELECT DISTINCT sub_competency FROM subjects WHERE cur_id = ? AND sug_id = ? AND sub_competency != '' ORDER BY sub_competency ASC";
    $stmt_com = $pdo->prepare($sql_com); $stmt_com->execute([$filter_cur, $filter_sug]); $competencies_list = $stmt_com->fetchAll(PDO::FETCH_COLUMN);
}

// Fetch Subjects for Add List
$subjects = [];
if (!empty($filter_cur) && !empty($filter_sug)) {
    $params_sub = [];
    $sql_sub = "SELECT * FROM subjects WHERE cur_id = ?";
    $params_sub[] = $filter_cur;

    if ($filter_sug != 6) { $sql_sub .= " AND sug_id = ?"; $params_sub[] = $filter_sug; }
    if ($filter_sug != 6 && $filter_sug != 5 && !empty($filter_com)) { $sql_sub .= " AND sub_competency = ?"; $params_sub[] = $filter_com; }
    
    // --- เงื่อนไข: ถ้าไม่ติ๊ก "แสดงทุกสาขา" ให้กรองเฉพาะสาขาของแผน ---
    if (!$show_all_majors && $target_maj_id) { 
        $sql_sub .= " AND (maj_id IS NULL OR maj_id = '' OR maj_id = ?)"; 
        $params_sub[] = $target_maj_id; 
    } 
    // --------------------------------------------------------

    $sql_sub .= " ORDER BY sub_code ASC";
    $stmt_sub = $pdo->prepare($sql_sub); 
    $stmt_sub->execute($params_sub); 
    $subjects = $stmt_sub->fetchAll();
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
$grand_total_subjects = count($plan_subjects); $grand_total_credits = 0; $grand_total_hours = 0; $grouped_subjects = [];
foreach ($plan_subjects as $ps) {
    $current_sug_id = $ps['sug_id']; 
    $current_sug_name = $ps['sug_name'];
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

// --- สีครบทุกหมวด ---
$group_colors = [ 
    1 => ['bg'=>'bg-orange-50','border'=>'border-orange-400'], 
    2 => ['bg'=>'bg-blue-50','border'=>'border-blue-400'], 
    3 => ['bg'=>'bg-indigo-50','border'=>'border-indigo-400'], 
    4 => ['bg'=>'bg-cyan-50','border'=>'border-cyan-400'],     
    5 => ['bg'=>'bg-pink-50','border'=>'border-pink-400'], 
    6 => ['bg'=>'bg-teal-50','border'=>'border-teal-400'], 
    999 => ['bg'=>'bg-slate-50','border'=>'border-slate-400'] 
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
</style>

<div class="max-w-7xl mx-auto space-y-6 pb-12 relative">
    
    <div class="flex flex-col md:flex-row justify-between items-start gap-4">
        <div>
            <a href="manage_plan_structure.php?id=<?php echo $pla_id; ?>" class="text-slate-400 hover:text-cvc-blue text-sm font-bold flex items-center gap-2 mb-2">
                <i class="fa-solid fa-arrow-left"></i> กลับหน้าโครงสร้างแผน
            </a>
            <h1 class="text-3xl font-serif font-bold text-slate-800">จัดการรายวิชาในแผน</h1>
            <div class="text-slate-500 font-medium text-sm mt-1">
                แผน: <span class="text-indigo-700 font-bold"><?php echo htmlspecialchars($plan_data['pla_name']); ?></span>
                <span class="mx-2">|</span>
                ปีการศึกษา: <span class="text-slate-800 font-bold bg-slate-100 px-2 py-0.5 rounded"><?php echo $year; ?></span>
                ภาคเรียน: <span class="text-slate-800 font-bold bg-slate-100 px-2 py-0.5 rounded"><?php echo $semester; ?></span>
            </div>
        </div>
        <div class="flex flex-wrap gap-3" id="stats-container">
            <div class="bg-white border border-slate-200 px-5 py-3 rounded-xl shadow-sm text-center min-w-[100px] flex-1 md:flex-none">
                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">จำนวนวิชา</div>
                <div class="text-3xl font-black text-emerald-500" id="total-subjects"><?php echo $grand_total_subjects; ?></div>
            </div>
            <div class="bg-white border border-slate-200 px-5 py-3 rounded-xl shadow-sm text-center min-w-[100px] flex-1 md:flex-none">
                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">หน่วยกิตรวม</div>
                <div class="text-3xl font-black text-cvc-blue" id="total-credits"><?php echo $grand_total_credits; ?></div>
            </div>
            <div class="bg-white border border-slate-200 px-5 py-3 rounded-xl shadow-sm text-center min-w-[100px] flex-1 md:flex-none">
                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">ชั่วโมงรวม</div>
                <div class="text-3xl font-black text-amber-500" id="total-hours"><?php echo $grand_total_hours; ?></div>
            </div>
        </div>
    </div>

    <div class="card-premium p-0 overflow-hidden border border-slate-200">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="font-bold text-slate-700 flex items-center gap-2"><i class="fa-solid fa-circle-plus text-green-500"></i> เพิ่มรายวิชา</h3>
            <?php if($filter_cur || $filter_sug): ?>
                <a href="?pla_id=<?php echo $pla_id; ?>&year=<?php echo $year; ?>&semester=<?php echo $semester; ?>&action=reset" class="text-xs text-red-500 hover:underline"><i class="fa-solid fa-rotate-left"></i> รีเซ็ตตัวกรอง</a>
            <?php endif; ?>
        </div>
        <div class="p-6">
            <form action="" method="GET" id="filterForm">
                <input type="hidden" name="pla_id" value="<?php echo $pla_id; ?>">
                <input type="hidden" name="year" value="<?php echo $year; ?>">
                <input type="hidden" name="semester" value="<?php echo $semester; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 items-end">
                    <div><label class="block text-xs font-bold text-slate-500 mb-1">1. หลักสูตร</label><select name="filter_cur" class="w-full text-sm" onchange="document.getElementById('filterForm').submit()"><option value="">-- เลือกหลักสูตร --</option><?php foreach ($curriculums as $c): ?><option value="<?php echo $c['cur_id']; ?>" <?php echo $filter_cur == $c['cur_id'] ? 'selected' : ''; ?>><?php echo $c['lev_name'] . ' ' . $c['cur_year']; ?></option><?php endforeach; ?></select></div>
                    <div><label class="block text-xs font-bold text-slate-500 mb-1">2. หมวดวิชา</label><select name="filter_sug" class="w-full text-sm disabled:bg-slate-100" onchange="document.getElementById('filterForm').submit()" <?php echo empty($filter_cur) ? 'disabled' : ''; ?>><option value="">-- เลือกหมวด --</option><?php foreach ($groups as $g): ?><option value="<?php echo $g['sug_id']; ?>" <?php echo $filter_sug == $g['sug_id'] ? 'selected' : ''; ?>><?php echo $g['sug_name']; ?></option><?php endforeach; ?></select></div>
                    <div><label class="block text-xs font-bold text-slate-500 mb-1">3. สมรรถนะ (ถ้ามี)</label><select name="filter_com" class="w-full text-sm disabled:bg-slate-100" onchange="document.getElementById('filterForm').submit()" <?php echo (empty($filter_cur) || empty($filter_sug) || $filter_sug == 6 || $filter_sug == 5) ? 'disabled' : ''; ?>><option value="">-- ทั้งหมด --</option><?php foreach ($competencies_list as $com): ?><option value="<?php echo $com; ?>" <?php echo $filter_com == $com ? 'selected' : ''; ?>><?php echo $com; ?></option><?php endforeach; ?></select></div>
                    
                    <div class="pb-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="show_all_majors" value="1" onchange="document.getElementById('filterForm').submit()" <?php echo $show_all_majors ? 'checked' : ''; ?> class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 border-gray-300">
                            <span class="ml-2 text-sm text-slate-600 font-bold">แสดงวิชาทุกสาขา</span>
                        </label>
                    </div>
                </div>
            </form>

            <?php if (!empty($filter_cur) && !empty($filter_sug)): ?>
            <form id="addSubjectsForm">
                <input type="hidden" name="pla_id" value="<?php echo $pla_id; ?>">
                <input type="hidden" name="year" value="<?php echo $year; ?>">
                <input type="hidden" name="semester" value="<?php echo $semester; ?>">
                <input type="hidden" name="filter_sug_context" value="<?php echo $filter_sug; ?>">

                <div class="mb-4 bg-slate-50 border border-slate-200 rounded-xl p-4 h-[400px] overflow-y-auto custom-scrollbar relative">
                    <?php if (empty($subjects)): ?>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                            <i class="fa-regular fa-folder-open text-4xl mb-2"></i>
                            <p>ไม่พบรายวิชาตามเงื่อนไข</p>
                            <?php if(!$show_all_majors): ?><p class="text-xs text-red-400 mt-2">*ลองติ๊ก "แสดงวิชาทุกสาขา" ดูนะครับ</p><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <?php $vis = 0; foreach ($subjects as $s): if (in_array($s['sub_id'], $existing_sub_ids)) continue; $vis++; ?>
                                <div class="bg-white border border-slate-200 rounded-lg hover:shadow-md transition relative group-card" id="card_<?php echo $s['sub_id']; ?>">
                                    <label class="cursor-pointer block p-3 pb-2">
                                        <input type="checkbox" name="sub_ids[]" value="<?php echo $s['sub_id']; ?>" class="peer sr-only sub-checkbox" onchange="toggleTeacherSelect(this, '<?php echo $s['sub_id']; ?>')">
                                        <div class="flex justify-between items-start mb-1">
                                            <span class="font-mono font-bold text-xs text-cvc-blue bg-blue-100 px-1.5 rounded"><?php echo $s['sub_code']; ?></span>
                                            <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 rounded"><?php echo $s['sub_th_pr_ot']; ?></span>
                                        </div>
                                        <div class="text-sm font-bold text-slate-700 leading-tight group-hover:text-blue-700"><?php echo $s['sub_name']; ?></div>
                                        <div class="absolute top-2 right-2 text-cvc-blue opacity-0 peer-checked:opacity-100 transition"><i class="fa-solid fa-check-circle"></i></div>
                                    </label>
                                </div>
                            <?php endforeach; if ($vis == 0): ?><div class="absolute inset-0 flex items-center justify-center text-green-600 font-bold"><i class="fa-solid fa-check mr-2"></i> เลือกครบทุกวิชาแล้ว</div><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex gap-4 items-center bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">ตั้งค่าครูแบบกลุ่ม (Optional)</label>
                        <select name="tea_id" id="tea_select" class="w-full text-sm text-slate-500">
                            <option value="">-- ปล่อยว่าง (รอเลือกทีหลัง) --</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?php echo $t['tea_id']; ?>"><?php echo $t['tea_fullname']; ?> (ใช้กับทุกวิชาที่เลือกด้านบน)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="button" onclick="submitAddSubjects()" class="btn-cvc self-end h-10 shadow-lg"><i class="fa-solid fa-plus-circle mr-2"></i> บันทึกรายการ</button>
                </div>
            </form>
            <?php else: ?>
                <div class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-300 text-slate-400">
                    <i class="fa-solid fa-arrow-up text-3xl mb-2 animate-bounce"></i>
                    <p class="font-bold">กรุณาเลือก "หลักสูตร" และ "หมวดวิชา" ด้านบน</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (count($grouped_subjects) > 0): ?>
    <div class="space-y-4" id="plan-tables">
        <?php foreach ($grouped_subjects as $gid => $group): $theme = $group_colors[$gid % 7] ?? $group_colors[999]; ?>
            <div class="card-premium overflow-hidden border-0 shadow-sm group-section" data-group-id="<?php echo $gid; ?>">
                <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center <?php echo $theme['bg']; ?> border-l-4 <?php echo $theme['border']; ?>">
                    <h3 class="font-bold text-slate-700"><?php echo htmlspecialchars($group['name']); ?></h3>
                    <div class="flex gap-2">
                        <span class="text-xs font-bold bg-white px-3 py-1 rounded-full border shadow-sm text-emerald-600 count-badge"><?php echo count($group['items']); ?> วิชา</span>
                        <span class="text-xs font-bold bg-white px-3 py-1 rounded-full border shadow-sm text-slate-600 credit-badge"><?php echo $group['credits']; ?> นก.</span>
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
                        <tr class="hover:bg-blue-50/20 transition group" id="row_<?php echo $ps['pls_id']; ?>" data-credit="<?php echo $ps['sub_credit']; ?>" data-hours="<?php echo $ps['sub_hours']; ?>">
                            <td class="px-6 py-3 align-middle text-center whitespace-nowrap"><span class="font-mono font-bold text-cvc-blue bg-blue-50 px-2 py-1 rounded border border-blue-100 shadow-sm text-sm"><?php echo $ps['sub_code']; ?></span></td>
                            <td class="px-6 py-3 align-middle"><div class="font-medium text-slate-700 text-sm"><?php echo $ps['sub_name']; ?></div></td>
                            <td class="px-6 py-3 align-middle text-center"><span class="font-bold text-slate-600 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-200 text-xs"><?php echo $ps['sub_credit']; ?></span></td>
                            <td class="px-6 py-3 align-middle text-center"><span class="font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200 text-xs shadow-sm"><?php echo $ps['sub_hours']; ?></span></td>
                            
                            <td class="px-6 py-3 align-middle" id="teacher_cell_<?php echo $ps['pls_id']; ?>">
                                <?php if($ps['tea_fullname']): ?>
                                    <span class="bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-emerald-100 flex items-center gap-2 shadow-sm w-fit"><i class="fa-solid fa-user-check"></i> <?php echo $ps['tea_fullname']; ?></span>
                                <?php else: ?>
                                    <span class="bg-orange-50 text-orange-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-orange-100 flex items-center gap-2 shadow-sm animate-pulse w-fit"><i class="fa-regular fa-clock"></i> รอการเลือกครูผู้สอน</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-3 align-middle text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <button onclick="openEditTeacher(<?php echo $ps['pls_id']; ?>, '<?php echo $ps['tea_id']; ?>', '<?php echo addslashes($ps['sub_name']); ?>', '<?php echo $ps['sug_id']; ?>')" class="w-9 h-9 rounded-lg border border-amber-200 text-amber-500 hover:bg-amber-500 hover:text-white flex items-center justify-center transition shadow-sm"><i class="fa-solid fa-pen-to-square text-xs"></i></button>
                                    
                                    <button onclick="deleteSubject(<?php echo $ps['pls_id']; ?>, <?php echo $pla_id; ?>, this)" class="w-9 h-9 rounded-lg border border-red-200 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition shadow-sm"><i class="fa-solid fa-trash-can text-xs"></i></button>
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

<div id="teacherModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeEditTeacher()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fa-solid fa-user-pen text-cvc-blue"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">เปลี่ยนครูผู้สอน</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-4" id="modalSubName">วิชา...</p>
                            
                            <form id="modalForm">
                                <input type="hidden" id="modal_pls_id" name="pls_id">
                                <label class="block text-sm font-bold text-slate-700 mb-2">เลือกครูผู้สอน (เฉพาะหมวดนี้)</label>
                                <select id="modal_tea_id" name="tea_id" class="w-full text-sm border border-slate-300 rounded-lg py-2 px-3 focus:ring-cvc-blue focus:border-cvc-blue">
                                    <option value="">-- ปล่อยว่าง (รอเลือกครู) --</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                <button type="button" onclick="saveTeacherChange()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-cvc-blue text-base font-medium text-white hover:bg-blue-800 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">บันทึก</button>
                <button type="button" onclick="closeEditTeacher()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">ยกเลิก</button>
            </div>
        </div>
    </div>
</div>

<script>
const allTeachers = <?php echo json_encode($teachers); ?>;

function toggleTeacherSelect(checkbox, subId) {
    const card = document.getElementById('card_' + subId);
    if (checkbox.checked) {
        card.classList.add('ring-2', 'ring-cvc-blue', 'bg-blue-50');
    } else {
        card.classList.remove('ring-2', 'ring-cvc-blue', 'bg-blue-50');
    }
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
    const originalText = btn.innerText;
    btn.innerText = 'กำลังบันทึก...';
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
                cell.innerHTML = '<span class="bg-orange-50 text-orange-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-orange-100 flex items-center gap-2 shadow-sm animate-pulse w-fit"><i class="fa-regular fa-clock"></i> รอการเลือกครูผู้สอน</span>';
            } else {
                const cleanName = tea_name.replace(' (ต่างหมวด)', '');
                cell.innerHTML = `<span class="bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-emerald-100 flex items-center gap-2 shadow-sm w-fit"><i class="fa-solid fa-user-check"></i> ${cleanName}</span>`;
            }
            closeEditTeacher();
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
            Toast.fire({ icon: 'success', title: 'บันทึกเรียบร้อย' });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'เกิดข้อผิดพลาด', 'error');
    } finally {
        btn.innerText = originalText;
        btn.disabled = false;
    }
}

async function deleteSubject(pls_id, pla_id, btn) {
    const result = await Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "คุณต้องการลบวิชานี้ออกจากแผนหรือไม่",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#cbd5e1',
        confirmButtonText: 'ลบเลย',
        cancelButtonText: 'ยกเลิก'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`delete_plan_subject.php?id=${pls_id}&pla_id=${pla_id}&year=<?php echo $year; ?>&semester=<?php echo $semester; ?>&ajax=1`);
            const data = await response.json();
            if (data.status === 'success') {
                const row = btn.closest('tr');
                row.style.transition = 'all 0.5s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(50px)';
                setTimeout(() => { window.location.reload(); }, 500);
            } else { Swal.fire('Error', data.message, 'error'); }
        } catch (error) { Swal.fire('Error', 'Connect Error', 'error'); }
    }
}

async function submitAddSubjects() {
    const form = document.getElementById('addSubjectsForm');
    const formData = new FormData(form);
    let hasSubject = false;
    for (var pair of formData.entries()) { if (pair[0] === 'sub_ids[]') hasSubject = true; }
    
    if (!hasSubject) { Swal.fire('แจ้งเตือน', 'กรุณาเลือกรายวิชา', 'warning'); return; }

    Swal.fire({ title: 'กำลังบันทึก...', didOpen: () => { Swal.showLoading() } });
    try {
        const response = await fetch('save_plan_subject.php?ajax=1', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.status === 'success') {
            await Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1000, showConfirmButton: false });
            window.location.reload();
        } else { Swal.fire('Error', data.message || 'Error', 'error'); }
    } catch (error) { Swal.fire('Error', 'Connect Error', 'error'); }
}

function updateTotals() {
    let grandTotalSub = 0; let grandTotalCredit = 0; let grandTotalHour = 0;
    document.querySelectorAll('tbody tr.group').forEach(row => {
        grandTotalSub++;
        grandTotalCredit += parseInt(row.getAttribute('data-credit') || 0);
        grandTotalHour += parseInt(row.getAttribute('data-hours') || 0);
    });
    document.getElementById("total-subjects").innerText = grandTotalSub;
    document.getElementById("total-credits").innerText = grandTotalCredit;
    document.getElementById("total-hours").innerText = grandTotalHour;
}

document.addEventListener("DOMContentLoaded", function() {
    const key = 'plan_scroll_<?php echo $pla_id; ?>';
    const pos = sessionStorage.getItem(key);
    if (pos) window.scrollTo(0, pos);
});
window.addEventListener("beforeunload", function() { sessionStorage.setItem('plan_scroll_<?php echo $pla_id; ?>', window.scrollY); });
</script>

<?php require_once '../includes/footer.php'; ?>