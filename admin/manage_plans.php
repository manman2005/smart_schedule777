<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

$search = $_GET['search'] ?? '';

// SQL: ดึงเฉพาะข้อมูลแผนและกลุ่มเรียน (ไม่ต้อง Join ไปนับหน่วยกิตแล้ว เพราะย้ายไปหน้าใน)
$sql = "SELECT p.*, c.cla_name, c.cla_year, c.cla_group_no, c.cla_major_code, m.maj_name
        FROM study_plans p 
        LEFT JOIN class_groups c ON p.cla_id = c.cla_id 
        LEFT JOIN majors m ON c.cla_major_code = m.maj_code
        WHERE p.pla_name LIKE ? OR p.pla_code LIKE ? 
        ORDER BY p.pla_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%", "%$search%"]);
$plans = $stmt->fetchAll();

// ปีปัจจุบันสำหรับคำนวณชั้นปี
$current_year = date('Y') + 543;

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

    <div class="card-premium overflow-hidden border-0 shadow-xl shadow-slate-200/50">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
                        <th class="px-6 py-4 w-32 text-xs font-bold text-slate-500 uppercase">รหัสแผน</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ชื่อแผนการเรียน</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">สำหรับกลุ่ม</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase w-40">โครงสร้างแผน</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase w-28">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if (count($plans) > 0): foreach ($plans as $row): ?>
                        <tr class="hover:bg-blue-50/30 transition duration-200 group">
                            <td class="px-6 py-4"><span class="font-mono font-bold text-cvc-blue bg-blue-50 px-2 py-1 rounded text-xs border border-blue-100 shadow-sm"><?php echo $row['pla_code']; ?></span></td>
                            
                            <td class="px-6 py-4 font-bold text-slate-700"><?php echo htmlspecialchars($row['pla_name']); ?></td>
                            
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-600 font-medium">
                                    <?php if ($row['cla_name']): 
                                        $stu_year = $current_year - $row['cla_year'] + 1;
                                    ?>
                                        <div class="flex items-center gap-2">
                                            <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-xs border font-bold"><?php echo htmlspecialchars($row['cla_name']) . $stu_year . '/' . intval($row['cla_group_no']); ?></span>
                                            <span class="text-xs text-slate-400"><?php echo htmlspecialchars($row['maj_name']); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-300 italic">ไม่ระบุกลุ่ม</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <a href="manage_plan_structure.php?id=<?php echo $row['pla_id']; ?>" class="inline-flex items-center justify-center text-xs bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white border border-indigo-200 px-4 py-2 rounded-lg transition font-bold shadow-sm group-hover:border-indigo-600 group-hover:shadow-md">
                                    <i class="fa-solid fa-sitemap mr-2"></i> จัดการโครงสร้าง
                                </a>
                            </td>
                            
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 opacity-60 group-hover:opacity-100 transition">
                                    <a href="manage_plan_form.php?id=<?php echo $row['pla_id']; ?>" class="w-8 h-8 rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50 flex items-center justify-center transition"><i class="fa-solid fa-pen-to-square text-xs"></i></a>
                                    <a href="delete_plan.php?id=<?php echo $row['pla_id']; ?>" onclick="return confirm('ยืนยันลบแผน?');" class="w-8 h-8 rounded-lg border border-slate-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition"><i class="fa-solid fa-trash-can text-xs"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?><tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">ไม่พบข้อมูลแผนการเรียน</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>