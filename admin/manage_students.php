<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

$search = $_GET['search'] ?? '';

// ปีปัจจุบันสำหรับคำนวณชั้นปี
$current_year = date('Y') + 543;

// แก้ไข SQL: เพิ่ม c.cla_year และ c.cla_group_no
$sql = "SELECT s.stu_id, s.stu_fullname, s.stu_img, s.stu_username, s.stu_gender, s.cla_id, 
        c.cla_name, c.cla_year, c.cla_group_no, m.maj_name
        FROM students s 
        LEFT JOIN class_groups c ON s.cla_id = c.cla_id 
        LEFT JOIN majors m ON c.cla_major_code = m.maj_code
        WHERE s.stu_fullname LIKE ? OR s.stu_id LIKE ? 
        ORDER BY s.stu_id DESC LIMIT 50"; 

$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%", "%$search%"]);
$students = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto pb-12">
    
    <div class="mb-8">
        <a href="index.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2">
            <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
        </a>
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <h1 class="text-3xl font-serif font-bold text-slate-800">ข้อมูลนักเรียน</h1>
                <p class="text-slate-500 mt-1">Student Management Database</p>
            </div>
            
            <div class="flex gap-3 w-full md:w-auto">
                <form class="relative flex-1 md:w-64 group">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="ค้นหาชื่อ/รหัส..." 
                           class="w-full pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-full focus:ring-2 focus:ring-blue-100 focus:border-cvc-blue outline-none transition shadow-sm text-sm">
                    
                    <i class="fa-solid fa-search absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-cvc-blue transition pointer-events-none"></i>
                </form>
                
                <a href="manage_student_form.php" class="btn-cvc text-sm shadow-md hover:shadow-lg">
                    <i class="fa-solid fa-plus"></i> <span class="hidden sm:inline">เพิ่มข้อมูล</span>
                </a>
            </div>
        </div>
    </div>

    <div class="card-premium overflow-hidden border-0 shadow-xl shadow-slate-200/50">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-16 text-center">#</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">รหัสประจำตัว</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ชื่อ-นามสกุล</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">กลุ่มเรียน</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">เพศ</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if(count($students) > 0): ?>
                        <?php foreach ($students as $index => $stu): ?>
                        <tr class="hover:bg-blue-50/30 transition duration-150 group">
                            <td class="px-6 py-4 text-slate-400 text-xs text-center"><?php echo $index + 1; ?></td>
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-cvc-blue bg-blue-50 px-2 py-1 rounded text-xs border border-blue-100 shadow-sm">
                                    <?php echo $stu['stu_id']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200 shadow-sm">
                                        <?php if ($stu['stu_img']): ?>
                                            <img src="../uploads/students/<?php echo $stu['stu_img']; ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <i class="fa-solid fa-user text-slate-300 text-xs"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-700 text-sm group-hover:text-cvc-blue transition"><?php echo htmlspecialchars($stu['stu_fullname']); ?></div>
                                        <div class="text-[10px] text-slate-400 font-mono"><?php echo $stu['stu_username']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if($stu['cla_name']): 
                                    // คำนวณชั้นปี และสร้างชื่อเต็ม (เช่น สสส.1/2)
                                    $year_level = $current_year - $stu['cla_year'] + 1;
                                    $display_class = $stu['cla_name'] . "." . $year_level . "/" . intval($stu['cla_group_no']);
                                ?>
                                    <div class="text-sm font-bold text-slate-600"><?php echo $display_class; ?></div>
                                    <div class="text-[10px] text-slate-400"><?php echo $stu['maj_name']; ?></div>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400 italic">ไม่ระบุ</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="px-6 py-4 text-center align-middle">
                                <?php if($stu['stu_gender'] == 'M'): ?>
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm mx-auto shadow-sm" title="ชาย">
                                        <i class="fa-solid fa-mars"></i>
                                    </div>
                                <?php elseif($stu['stu_gender'] == 'F'): ?>
                                    <div class="w-8 h-8 rounded-full bg-pink-100 text-pink-500 flex items-center justify-center text-sm mx-auto shadow-sm" title="หญิง">
                                        <i class="fa-solid fa-venus"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-sm mx-auto" title="ไม่ระบุ">
                                        <i class="fa-solid fa-genderless"></i>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 opacity-60 group-hover:opacity-100 transition">
                                    <a href="manage_student_form.php?id=<?php echo $stu['stu_id']; ?>" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-amber-500 hover:border-amber-500 hover:bg-amber-50 flex items-center justify-center transition shadow-sm">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <a href="delete_student.php?id=<?php echo $stu['stu_id']; ?>" onclick="return confirm('ยืนยันลบ?');" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-red-500 hover:border-red-500 hover:bg-red-50 flex items-center justify-center transition shadow-sm">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 font-light">ไม่พบข้อมูลในระบบ</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>