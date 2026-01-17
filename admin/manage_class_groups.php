<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

$search = $_GET['search'] ?? '';

// แก้ไข SQL: เพิ่ม t.tea_img เพื่อดึงชื่อไฟล์รูปภาพ
$sql = "SELECT c.*, 
        (SELECT maj_name FROM majors WHERE maj_code = c.cla_major_code LIMIT 1) AS maj_name, 
        (SELECT cg.car_name FROM majors m JOIN career_groups cg ON m.car_id = cg.car_id WHERE m.maj_code = c.cla_major_code LIMIT 1) AS car_name,
        t.tea_fullname AS advisor_name,
        t.tea_img 
        FROM class_groups c 
        LEFT JOIN teachers t ON c.tea_id = t.tea_id
        WHERE c.cla_name LIKE ? OR c.cla_id LIKE ? 
        ORDER BY c.cla_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%", "%$search%"]);
$classes = $stmt->fetchAll();

$current_year = date('Y') + 543;

require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto pb-12">
    <div class="mb-8">
        <a href="index.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2"><i class="fa-solid fa-arrow-left mr-2"></i> Dashboard</a>
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div><h1 class="text-3xl font-serif font-bold text-slate-800">จัดการกลุ่มเรียน</h1><p class="text-slate-500 mt-1">Class Groups Management</p></div>
            <div class="flex gap-3 w-full md:w-auto">
                <form class="relative flex-1 md:w-64 group">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="ค้นหากลุ่มเรียน..." class="w-full pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-full focus:ring-2 focus:ring-blue-100 focus:border-cvc-blue outline-none transition shadow-sm text-sm">
                    <i class="fa-solid fa-search absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                </form>
                <a href="manage_class_group_form.php" class="btn-cvc text-sm shadow-md hover:shadow-lg"><i class="fa-solid fa-plus"></i> <span class="hidden sm:inline">สร้างกลุ่มเรียน</span></a>
            </div>
        </div>
    </div>

    <div class="card-premium overflow-hidden border-0 shadow-xl shadow-slate-200/50">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">รหัสกลุ่ม</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ชื่อกลุ่มเรียน</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ระดับชั้น</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">สาขาวิชา</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ครูที่ปรึกษา</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if(count($classes) > 0): foreach ($classes as $row): 
                        $year_level = $current_year - $row['cla_year'] + 1;
                        $display_name = $row['cla_name'] . "." . $year_level . "/" . intval($row['cla_group_no']);
                    ?>
                        <tr class="hover:bg-blue-50/30 transition duration-200 group">
                            <td class="px-6 py-4"><span class="font-mono font-bold text-cvc-blue bg-blue-50 px-2 py-1 rounded text-xs border border-blue-100 shadow-sm"><?php echo $row['cla_id']; ?></span></td>
                            
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-indigo-700"><?php echo $display_name; ?></div>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">เข้าปี <?php echo $row['cla_year']; ?> (ปี <?php echo $year_level; ?>)</div>
                            </td>
                            
                            <td class="px-6 py-4">
                                <?php $lvl = 'อื่นๆ'; $bg = 'bg-slate-100 text-slate-500'; if ($row['cla_level_code'] == '01') { $lvl = 'ปวช.'; $bg = 'bg-blue-50 text-blue-600 border-blue-100'; } elseif ($row['cla_level_code'] == '02') { $lvl = 'ปวส.'; $bg = 'bg-emerald-50 text-emerald-600 border-emerald-100'; } ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border <?php echo $bg; ?>"><?php echo $lvl; ?></span>
                            </td>
                            
                            <td class="px-6 py-4"><div class="text-sm font-medium text-slate-700"><?php echo $row['maj_name'] ?: '<span class="text-slate-300 italic">ไม่ระบุ</span>'; ?></div></td>
                            
                            <td class="px-6 py-4">
                                <?php if($row['advisor_name']): ?>
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shadow-sm shrink-0">
                                            <?php if (!empty($row['tea_img']) && file_exists("../uploads/teachers/" . $row['tea_img'])): ?>
                                                <img src="../uploads/teachers/<?php echo $row['tea_img']; ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <i class="fa-solid fa-user-tie text-slate-400 text-xs"></i>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div>
                                            <div class="text-sm text-slate-700 font-bold group-hover:text-cvc-blue transition"><?php echo htmlspecialchars($row['advisor_name']); ?></div>
                                            <div class="text-[10px] text-slate-400">ที่ปรึกษา</div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400 italic bg-slate-50 px-2 py-1 rounded">- ไม่ระบุ -</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 opacity-60 group-hover:opacity-100 transition">
                                    <a href="manage_class_group_form.php?id=<?php echo $row['cla_id']; ?>" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-amber-500 hover:border-amber-500 hover:bg-amber-50 flex items-center justify-center transition shadow-sm"><i class="fa-solid fa-pen-to-square text-xs"></i></a>
                                    <a href="delete_class_group.php?id=<?php echo $row['cla_id']; ?>" onclick="return confirm('ยืนยันการลบ?');" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-red-500 hover:border-red-500 hover:bg-red-50 flex items-center justify-center transition shadow-sm"><i class="fa-solid fa-trash-can text-xs"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?><tr><td colspan="7" class="px-6 py-12 text-center text-slate-400 font-light">ไม่พบข้อมูล</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>