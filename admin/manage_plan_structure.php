<?php
// admin/manage_plan_structure.php
// หน้าแสดงโครงสร้างแผน (ปีการศึกษา/เทอม) - Design: 3D Cards
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/header.php';
require_once '../config/db.php';

// รับค่า ID แผน (ใช้ isset เพื่อรองรับเลข 0)
if (!isset($_GET['id'])) {
    echo "<script>window.location='manage_plans.php';</script>";
    exit;
}
$pla_id = $_GET['id'];

try {
    // 1. ดึงข้อมูลหัวแผน (Header)
    $stmt = $pdo->prepare("SELECT p.*, c.cla_name, m.maj_name, l.lev_name 
                           FROM study_plans p
                           LEFT JOIN study_plan_classes spc ON p.pla_id = spc.pla_id
                           LEFT JOIN class_groups c ON spc.cla_id = c.cla_id
                           LEFT JOIN majors m ON c.cla_major_code = m.maj_code
                           LEFT JOIN levels l ON c.cla_level_code = l.lev_code
                           WHERE p.pla_id = ?
                           LIMIT 1");
    $stmt->execute([$pla_id]);
    $plan = $stmt->fetch();

    if (!$plan) {
        echo "<div class='p-10 text-center text-red-500'>ไม่พบข้อมูลแผน (ID: $pla_id)</div>";
        require_once '../includes/footer.php';
        exit;
    }

    // 2. ดึงข้อมูลเทอมที่มีอยู่จริงในระบบ
    $stmt_semesters = $pdo->prepare("SELECT pls_academic_year, pls_semester, 
                                            COUNT(*) as total_subjects, 
                                            SUM(s.sub_credit) as total_credits,
                                            SUM(s.sub_hours) as total_hours
                                     FROM plan_subjects ps
                                     JOIN subjects s ON ps.sub_id = s.sub_id
                                     WHERE ps.pla_id = ?
                                     GROUP BY pls_academic_year, pls_semester
                                     ORDER BY pls_academic_year ASC, pls_semester ASC");
    $stmt_semesters->execute([$pla_id]);
    $existing_semesters = $stmt_semesters->fetchAll();

    // 3. ดึงกลุ่มเรียนทั้งหมดที่ใช้แผนนี้
    $current_year = date('Y') + 543;
    $stmt_classes = $pdo->prepare("SELECT c.cla_id, CONCAT(c.cla_name, '.', (? - c.cla_year + 1), '/', CAST(c.cla_group_no AS UNSIGNED)) as display_name 
                                   FROM study_plan_classes spc 
                                   JOIN class_groups c ON spc.cla_id = c.cla_id 
                                   WHERE spc.pla_id = ? 
                                   ORDER BY c.cla_id");
    $stmt_classes->execute([$current_year, $pla_id]);
    $plan_classes = $stmt_classes->fetchAll();

}
catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <div class="mb-8">
        <a href="manage_plans.php" class="text-slate-500 hover:text-cvc-blue text-sm mb-2 inline-block transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> กลับไปหน้ารวมแผน
        </a>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-cvc-sky/20 rounded-full blur-3xl -mr-16 -mt-16"></div>
            
            <div class="relative z-10">
                <h1 class="text-2xl font-bold text-slate-800">โครงสร้างแผนการเรียน</h1>
                <div class="flex items-baseline gap-2 mt-1">
                    <h2 class="text-lg text-cvc-blue font-bold"><?php echo htmlspecialchars($plan['pla_name']); ?></h2>
                    <span class="text-sm text-slate-400 font-mono">(<?php echo htmlspecialchars($plan['pla_code']); ?>)</span>
                </div>
                <div class="flex flex-wrap gap-2 mt-3 text-xs font-medium">
                    <span class="bg-cvc-sky/20 text-cvc-blue px-3 py-1 rounded-full border border-cvc-blue/20">
                        <?php echo htmlspecialchars($plan['lev_name'] ?? '-'); ?>
                    </span>
                    <span class="bg-slate-50 text-slate-600 px-3 py-1 rounded-full border border-slate-200">
                        สาขา: <?php echo htmlspecialchars($plan['maj_name'] ?? '-'); ?>
                    </span>
                    <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full border border-green-200">
                        เริ่มใช้ปี: <b><?php echo $plan['pla_start_year']; ?></b>
                    </span>
                </div>
                <?php if (!empty($plan_classes)): ?>
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <span class="text-xs text-slate-500 font-bold"><i class="fa-solid fa-users mr-1"></i> ใช้กับ:</span>
                    <?php foreach ($plan_classes as $pc): ?>
                        <span class="bg-amber-50 text-amber-700 px-3 py-1 rounded-full text-xs font-bold border border-amber-200 shadow-sm">
                            <?php echo htmlspecialchars($pc['display_name']); ?>
                        </span>
                    <?php
    endforeach; ?>
                </div>
                <?php
endif; ?>
            </div>
            
            <button onclick="openAddSemesterModal()" 
                    class="relative z-10 bg-cvc-blue hover:bg-cvc-navy text-white px-6 py-3 rounded-xl shadow-lg hover:shadow-red-200 transition-all transform hover:-translate-y-0.5 flex items-center gap-2 font-bold">
                <i class="fa-solid fa-plus-circle text-lg"></i> เพิ่มภาคเรียนใหม่
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <?php if (count($existing_semesters) > 0): ?>
            <?php foreach ($existing_semesters as $sem):
        // คำนวณชั้นปี (Class Level)
        $plan_start = intval($plan['pla_start_year']);
        $current_sem_year = intval($sem['pls_academic_year']);
        $class_level = $current_sem_year - $plan_start + 1;
        if ($class_level < 1)
            $class_level = 1;
?>
            <div class="group relative bg-white rounded-2xl shadow-sm hover:shadow-xl border border-slate-100 transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                
                <!-- Background Decorations -->
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-cvc-sky/20 rounded-full opacity-50 group-hover:bg-cvc-sky/30 transition-colors"></div>
                <div class="absolute -left-6 -bottom-6 w-24 h-24 bg-cvc-sky/15 rounded-full opacity-50 group-hover:bg-cvc-sky/25 transition-colors"></div>

                <!-- Delete Button -->
                <button type="button"
                   onclick="confirmDeleteSemester(<?php echo $pla_id; ?>, <?php echo $sem['pls_academic_year']; ?>, <?php echo $sem['pls_semester']; ?>)"
                   class="absolute top-3 right-3 z-20 w-8 h-8 flex items-center justify-center rounded-full bg-white/80 backdrop-blur text-slate-300 hover:text-red-500 hover:bg-red-50 shadow-sm border border-slate-100 transition-all opacity-0 group-hover:opacity-100"
                   title="ลบภาคเรียนนี้">
                    <i class="fa-solid fa-trash-can text-sm"></i>
                </button>

                <div class="p-6 relative z-10">
                    <!-- Header: Year & Level -->
                    <div class="flex justify-between items-end mb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">ปีการศึกษา</p>
                                <span class="bg-indigo-50 text-indigo-600 border border-indigo-100 text-[10px] px-2 py-0.5 rounded-full font-bold">ชั้นปีที่ <?php echo $class_level; ?></span>
                            </div>
                            <h3 class="text-3xl font-black text-slate-800 leading-none font-serif tracking-tight">
                                <?php echo $sem['pls_academic_year']; ?>
                            </h3>
                        </div>
                        <div class="text-center">
                            <div class="bg-gradient-to-br from-cvc-blue to-cvc-navy text-white px-4 py-2 rounded-lg shadow-md transform rotate-3 group-hover:rotate-0 transition-transform">
                                <span class="text-[10px] uppercase font-bold opacity-80 block leading-none mb-0.5 text-blue-100">เทอม</span>
                                <span class="text-xl font-bold leading-none"><?php echo $sem['pls_semester']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="h-px w-full bg-gradient-to-r from-transparent via-slate-200 to-transparent my-4"></div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-3 gap-2 text-center mb-6 divide-x divide-slate-100">
                        <div>
                            <span class="block text-xl font-bold text-cvc-blue group-hover:scale-110 transition-transform"><?php echo $sem['total_subjects']; ?></span>
                            <span class="text-[10px] text-slate-500 font-bold">รายวิชา</span>
                        </div>
                        <div>
                            <span class="block text-xl font-bold text-emerald-600 group-hover:scale-110 transition-transform"><?php echo (int)$sem['total_credits']; ?></span>
                            <span class="text-[10px] text-slate-500 font-bold">หน่วยกิต</span>
                        </div>
                        <div>
                            <span class="block text-xl font-bold text-amber-600 group-hover:scale-110 transition-transform"><?php echo (int)$sem['total_hours']; ?></span>
                            <span class="text-[10px] text-slate-500 font-bold">ชั่วโมง</span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <a href="manage_plan_subjects.php?pla_id=<?php echo $pla_id; ?>&year=<?php echo $sem['pls_academic_year']; ?>&semester=<?php echo $sem['pls_semester']; ?>" 
                       class="block w-full text-center py-2.5 rounded-xl font-bold text-sm transition-all duration-300
                              bg-white border-2 border-cvc-blue/20 text-cvc-blue shadow-sm
                              hover:bg-cvc-blue hover:text-white hover:border-cvc-blue hover:shadow-md hover:-translate-y-0.5 group-hover:shadow-red-200">
                        <i class="fa-solid fa-layer-group mr-2"></i> จัดการรายวิชา
                    </a>
                </div>
            </div>
            <?php
    endforeach; ?>
        <?php
else: ?>
            <div class="col-span-full py-16 text-center bg-white rounded-2xl border-2 border-dashed border-slate-200">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-2xl animate-bounce">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-600">ยังไม่มีข้อมูลโครงสร้างแผน</h3>
                <p class="text-slate-400 mb-6">เริ่มโดยการกดปุ่ม "เพิ่มภาคเรียนใหม่" ด้านบน</p>
                <button onclick="openAddSemesterModal()" class="text-cvc-blue hover:text-cvc-navy font-bold underline decoration-2 underline-offset-4">
                    + เพิ่มภาคเรียนแรกเลย
                </button>
            </div>
        <?php
endif; ?>

    </div>
</div>

<!-- Modal เพิ่มภาคเรียน -->
<div id="addSemesterModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeAddSemesterModal()"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
                
                <form action="manage_plan_subjects.php" method="GET">
                    <input type="hidden" name="pla_id" value="<?php echo $pla_id; ?>">
                    
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-8 sm:pb-6">
                        <div class="text-center sm:text-left">
                            <div class="mx-auto flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-red-50 sm:mx-0 mb-4 border-4 border-white shadow-lg relative">
                                <i class="fa-solid fa-calendar-plus text-2xl text-red-500"></i>
                            </div>
                            <h3 class="text-xl font-bold leading-6 text-gray-900 mb-2">เพิ่มภาคเรียนใหม่</h3>
                            <p class="text-sm text-gray-500 mb-6">ระบุปีการศึกษาและเทอมที่ต้องการจัดการรายวิชา</p>
                            
                            <div class="grid grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1.5">ปีการศึกษา</label>
                                    <input type="number" name="year" required 
                                           value="<?php echo $plan['pla_start_year'] ? $plan['pla_start_year'] : date('Y') + 543; ?>" 
                                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-cvc-blue focus:border-cvc-blue outline-none transition font-bold text-slate-700 text-center">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1.5">เทอม</label>
                                    <select name="semester" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-cvc-blue focus:border-cvc-blue outline-none transition font-bold text-slate-700 text-center cursor-pointer bg-white">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3 (ฤดูร้อน)</option>
                                    </select>
                                </div>
                            </div>

                            <?php if (!empty($plan_classes)): ?>
                            <div class="mt-5">
                                <label class="block text-sm font-bold text-slate-700 mb-2"><i class="fa-solid fa-users text-cvc-blue mr-1"></i> เลือกห้องที่จะใช้</label>
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2">
                                    <?php foreach ($plan_classes as $pc): ?>
                                    <label class="flex items-center gap-3 cursor-pointer hover:bg-white rounded-lg px-3 py-2 transition group">
                                        <input type="checkbox" name="cla_ids[]" value="<?php echo $pc['cla_id']; ?>" checked
                                               class="w-5 h-5 text-cvc-blue rounded-md focus:ring-cvc-blue border-gray-300 cursor-pointer">
                                        <span class="text-sm font-bold text-slate-700 group-hover:text-cvc-blue transition"><?php echo htmlspecialchars($pc['display_name']); ?></span>
                                    </label>
                                    <?php
    endforeach; ?>
                                </div>
                            </div>
                            <?php
endif; ?>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 px-4 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 gap-3">
                        <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-cvc-blue px-5 py-3 text-sm font-bold text-white shadow-lg hover:shadow-red-200 hover:bg-cvc-navy sm:w-auto transition-all">
                            ไปยังหน้าจัดการวิชา <i class="fa-solid fa-arrow-right ml-2 mt-0.5"></i>
                        </button>
                        <button type="button" onclick="closeAddSemesterModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-3 text-sm font-bold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-all">
                            ยกเลิก
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openAddSemesterModal() {
    document.getElementById('addSemesterModal').classList.remove('hidden');
}
function closeAddSemesterModal() {
    document.getElementById('addSemesterModal').classList.add('hidden');
}

function confirmDeleteSemester(planId, year, semester) {
    Swal.fire({
        title: 'ยืนยันการลบ?',
        html: `ต้องการลบข้อมูลปีการศึกษา <b>${year}</b> เทอม <b>${semester}</b><br><span class="text-sm text-red-500">รายวิชาทั้งหมดในเทอมนี้จะหายไป</span>`,
        iconHtml: '<div class="flex items-center justify-center w-24 h-24 bg-red-100 rounded-full border-4 border-white shadow-lg p-2"><i class="fa-solid fa-robot text-4xl text-red-500 animate-bounce"></i></div>',
        customClass: {
            icon: 'border-0',
            popup: 'rounded-3xl font-sans',
            confirmButton: 'rounded-full px-6 py-2 shadow-lg',
            cancelButton: 'rounded-full px-6 py-2'
        },
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fa-solid fa-trash-can mr-2"></i>ใช่, ลบเลย',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete_plan_semester.php?plan_id=${planId}&year=${year}&semester=${semester}`;
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
