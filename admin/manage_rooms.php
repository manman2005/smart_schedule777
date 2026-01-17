<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM rooms WHERE roo_name LIKE ? OR roo_id LIKE ? ORDER BY roo_id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute(["%$search%", "%$search%"]);
$rooms = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto pb-12">
    <div class="mb-8">
        <a href="index.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2"><i class="fa-solid fa-arrow-left mr-2"></i> Dashboard</a>
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div><h1 class="text-3xl font-serif font-bold text-slate-800">จัดการห้องเรียน</h1><p class="text-slate-500 mt-1">Facility Management</p></div>
            <div class="flex gap-3 w-full md:w-auto">
                <form class="relative flex-1 md:w-64 group">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="ค้นหาห้อง..." class="w-full pl-4 pr-10 py-2.5 bg-white border border-slate-200 rounded-full focus:ring-2 focus:ring-blue-100 focus:border-cvc-blue outline-none transition shadow-sm text-sm">
                    <i class="fa-solid fa-search absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                </form>
                <a href="manage_room_form.php" class="btn-cvc text-sm shadow-md hover:shadow-lg"><i class="fa-solid fa-plus"></i> เพิ่มห้องใหม่</a>
            </div>
        </div>
    </div>

    <div class="card-premium overflow-hidden border-0 shadow-xl shadow-slate-200/50">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
                        <th class="px-6 py-4 w-24 text-xs font-bold text-slate-500 uppercase">รหัสห้อง</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ชื่อห้องเรียน</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">สถานที่</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ประเภท</th> 
                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase">ความจุ</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase w-32">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if (count($rooms) > 0): foreach ($rooms as $room): ?>
                        <tr class="hover:bg-blue-50/30 transition duration-200 group">
                            <td class="px-6 py-4"><span class="font-mono font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded border border-rose-100 shadow-sm"><?php echo $room['roo_id']; ?></span></td>
                            <td class="px-6 py-4"><span class="font-bold text-slate-700"><?php echo htmlspecialchars($room['roo_name']); ?></span></td>
                            <td class="px-6 py-4"><div class="flex items-center gap-2 text-xs font-medium text-slate-500"><span class="bg-slate-50 px-2 py-1 rounded border border-slate-200"><i class="fa-regular fa-building mr-1 text-slate-400"></i><?php echo htmlspecialchars($room['roo_building']); ?></span><span class="bg-slate-50 px-2 py-1 rounded border border-slate-200">ชั้น <?php echo htmlspecialchars($room['roo_floor']); ?></span></div></td>
                            <td class="px-6 py-4">
                                <?php $type_color='bg-slate-50 border-slate-100 text-slate-500'; $icon='fa-tag'; if($room['roo_type']=='ห้องปฎิบัติการ'){$type_color='bg-purple-50 border-purple-100 text-purple-600'; $icon='fa-flask';} elseif($room['roo_type']=='ห้องเรียนสามัญ'){$type_color='bg-emerald-50 border-emerald-100 text-emerald-600'; $icon='fa-book-open';} elseif($room['roo_type']=='ห้องประชุม'){$type_color='bg-orange-50 border-orange-100 text-orange-600'; $icon='fa-microphone';} ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border <?php echo $type_color; ?>"><i class="fa-solid <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($room['roo_type']); ?></span>
                            </td>
                            <td class="px-6 py-4 text-center"><span class="font-bold text-slate-600"><?php echo $room['roo_capacity']; ?></span></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 opacity-60 group-hover:opacity-100 transition">
                                    <a href="manage_room_form.php?id=<?php echo $room['roo_id']; ?>" class="w-8 h-8 rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50 flex items-center justify-center transition"><i class="fa-solid fa-pen-to-square text-xs"></i></a>
                                    <a href="delete_room.php?id=<?php echo $room['roo_id']; ?>" onclick="return confirm('ยืนยันลบ?');" class="w-8 h-8 rounded-lg border border-slate-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition"><i class="fa-solid fa-trash-can text-xs"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?><tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">ไม่พบข้อมูล</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>