<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

$times = $pdo->query("SELECT * FROM time_slots ORDER BY tim_start ASC")->fetchAll();
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto pb-12">
    <div class="mb-8">
        <a href="index.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2"><i class="fa-solid fa-arrow-left mr-2"></i> Dashboard</a>
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div><h1 class="text-3xl font-serif font-bold text-slate-800">จัดการเวลาเรียน</h1><p class="text-slate-500 mt-1">Time Slots Management</p></div>
            <div class="flex gap-3">
                <a href="manage_days.php" class="bg-white border border-slate-200 text-slate-600 px-4 py-2.5 rounded-full hover:bg-slate-50 text-sm font-bold transition shadow-sm"><i class="fa-solid fa-calendar-day mr-2 text-cvc-blue"></i> จัดการชื่อวัน</a>
                <a href="manage_time_slot_form.php" class="btn-cvc text-sm shadow-md hover:shadow-lg"><i class="fa-solid fa-plus"></i> เพิ่มคาบเรียน</a>
            </div>
        </div>
    </div>

    <div class="card-premium overflow-hidden border-0 shadow-xl shadow-slate-200/50">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
                        <th class="px-6 py-4 w-20 text-center text-xs font-bold text-slate-500 uppercase">ลำดับ</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">ช่วงเวลา (Display)</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase">เวลาจริง</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">หมายเหตุ</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase w-32">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if (count($times) > 0): foreach ($times as $index => $row): ?>
                        <tr class="hover:bg-blue-50/30 transition duration-200 group">
                            <td class="px-6 py-4 text-center"><span class="w-8 h-8 rounded-full bg-slate-50 text-slate-500 text-xs font-bold flex items-center justify-center mx-auto border border-slate-100"><?php echo $index + 1; ?></span></td>
                            <td class="px-6 py-4"><span class="font-bold text-slate-700 text-base"><?php echo htmlspecialchars($row['tim_range']); ?></span></td>
                            <td class="px-6 py-4 text-center"><span class="font-mono text-xs font-bold text-cvc-blue bg-blue-50 px-3 py-1 rounded-full border border-blue-100"><?php echo substr($row['tim_start'], 0, 5) . ' - ' . substr($row['tim_end'], 0, 5); ?></span></td>
                            <td class="px-6 py-4">
                                <?php if($row['tim_note']): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100"><i class="fa-regular fa-note-sticky text-amber-500"></i> <?php echo htmlspecialchars($row['tim_note']); ?></span>
                                <?php else: ?><span class="text-slate-300 text-xs">-</span><?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 opacity-60 group-hover:opacity-100 transition">
                                    <a href="manage_time_slot_form.php?id=<?php echo $row['tim_id']; ?>" class="w-8 h-8 rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50 flex items-center justify-center transition"><i class="fa-solid fa-pen-to-square text-xs"></i></a>
                                    <a href="delete_time_slot.php?id=<?php echo $row['tim_id']; ?>" onclick="return confirm('ยืนยันลบคาบเรียนนี้?');" class="w-8 h-8 rounded-lg border border-slate-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition"><i class="fa-solid fa-trash-can text-xs"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?><tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">ยังไม่มีข้อมูลเวลาเรียน</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>