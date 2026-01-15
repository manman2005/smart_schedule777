<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkTeacher();

$tea_id = $_SESSION['user_id'];

// 1. ดึงข้อมูลว่าอาจารย์สังกัดหมวดวิชาอะไร (sug_id)
$stmt_tea = $pdo->prepare("SELECT t.sug_id, sg.sug_name 
                           FROM teachers t 
                           LEFT JOIN subject_groups sg ON t.sug_id = sg.sug_id 
                           WHERE t.tea_id = ?");
$stmt_tea->execute([$tea_id]);
$teacher_info = $stmt_tea->fetch();

$teacher_sug_id = $teacher_info['sug_id'];
$teacher_sug_name = $teacher_info['sug_name'] ?? 'ไม่ระบุสังกัด';

// 2. ดึงรายวิชาที่เปิดให้จอง (ยังไม่มีคนสอน) และ **ตรงกับหมวดวิชาของอาจารย์**
// เพิ่มเงื่อนไข AND s.sug_id = ?
$sql = "SELECT ps.*, s.sub_code, s.sub_name, s.sub_credit, s.sub_th_pr_ot,
               p.pla_name, p.pla_start_year, p.pla_semester, 
               c.cla_name, c.cla_level_code, sg.sug_name
        FROM plan_subjects ps
        JOIN subjects s ON ps.sub_id = s.sub_id
        JOIN study_plans p ON ps.pla_id = p.pla_id
        JOIN class_groups c ON p.cla_id = c.cla_id
        LEFT JOIN subject_groups sg ON s.sug_id = sg.sug_id
        WHERE ps.tea_id IS NULL 
        AND s.sug_id = ? 
        ORDER BY p.pla_start_year DESC, p.pla_semester ASC, c.cla_id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$teacher_sug_id]);
$open_subjects = $stmt->fetchAll();
?>

<?php require_once '../includes/header.php'; ?>

<div class="max-w-7xl mx-auto px-4 py-8">
    
    <div class="mb-6 flex justify-between items-center">
        <a href="index.php" class="text-slate-400 hover:text-cvc-blue text-sm font-bold transition flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> หน้าหลัก
        </a>
        <h2 class="text-2xl font-serif font-bold text-slate-800">จองรายวิชาสอน</h2>
    </div>
    
    <div class="card-premium overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-white flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-cvc-blue flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-sm"><i class="fa-solid fa-list-check"></i></div>
                    รายวิชาที่เปิดให้จอง
                </h2>
                <p class="text-sm text-slate-500 mt-1 ml-10">
                    แสดงเฉพาะวิชาในสังกัด: <span class="font-bold text-amber-600 underline decoration-amber-200"><?php echo htmlspecialchars($teacher_sug_name); ?></span>
                </p>
            </div>
            <div class="text-right">
                <span class="bg-white border border-slate-200 px-3 py-1 rounded-full text-xs font-bold text-slate-500 shadow-sm">
                    <i class="fa-regular fa-clock mr-1 text-cvc-gold"></i> ข้อมูลล่าสุด: <?php echo date("d/m/Y H:i"); ?>
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase font-extrabold tracking-wide">
                    <tr>
                        <th class="p-4 w-32 text-center">ปี/เทอม</th>
                        <th class="p-4 w-40">กลุ่มเรียน</th>
                        <th class="p-4 w-32">รหัสวิชา</th>
                        <th class="p-4">ชื่อวิชา</th>
                        <th class="p-4 text-center w-32">หน่วยกิต (ชม.)</th>
                        <th class="p-4 text-center w-32">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm bg-white">
                    <?php if (count($open_subjects) > 0): ?>
                        <?php foreach ($open_subjects as $row): ?>
                        <tr class="hover:bg-blue-50/30 transition group">
                            <td class="p-4 text-center align-middle">
                                <span class="font-bold text-cvc-blue bg-blue-50 px-2 py-1 rounded text-xs border border-blue-100 shadow-sm whitespace-nowrap">
                                    <?php echo $row['pla_semester']; ?> / <?php echo $row['pla_start_year']; ?>
                                </span>
                            </td>
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-users"></i>
                                    </div>
                                    <span class="font-bold text-slate-700"><?php echo $row['cla_name']; ?></span>
                                </div>
                            </td>
                            <td class="p-4 align-middle">
                                <span class="font-mono font-bold text-slate-600 bg-slate-50 px-2 py-1 rounded border border-slate-100">
                                    <?php echo $row['sub_code']; ?>
                                </span>
                            </td>
                            <td class="p-4 align-middle">
                                <div class="font-bold text-slate-800 text-base mb-0.5 group-hover:text-cvc-blue transition">
                                    <?php echo $row['sub_name']; ?>
                                </div>
                                <div class="text-xs text-slate-400 flex items-center gap-1">
                                    <i class="fa-solid fa-layer-group text-[10px]"></i> 
                                    <?php echo $row['sug_name'] ?: 'ไม่ระบุหมวด'; ?>
                                </div>
                            </td>
                            <td class="p-4 text-center align-middle">
                                <span class="bg-white text-slate-600 px-3 py-1 rounded-full border border-slate-200 text-xs font-bold shadow-sm whitespace-nowrap">
                                    <?php echo $row['sub_credit']; ?> นก. (<?php echo $row['sub_th_pr_ot']; ?>)
                                </span>
                            </td>
                            <td class="p-4 text-center align-middle">
                                <form action="booking_db.php" method="POST" onsubmit="return confirm('ยืนยันที่จะสอนรายวิชานี้?\n\nวิชา: <?php echo $row['sub_name']; ?>\nกลุ่ม: <?php echo $row['cla_name']; ?>');">
                                    <input type="hidden" name="pls_id" value="<?php echo $row['pls_id']; ?>">
                                    <button type="submit" class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 text-xs font-bold flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-check-circle"></i> เลือกสอน
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-16 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-300">
                                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-solid fa-clipboard-check text-4xl"></i>
                                    </div>
                                    <p class="text-lg font-bold text-slate-500">ไม่พบรายวิชาที่เปิดให้จอง</p>
                                    <p class="text-sm text-slate-400 mt-1">
                                        ในหมวดวิชา: <span class="text-amber-500"><?php echo $teacher_sug_name; ?></span>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>