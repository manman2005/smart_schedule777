<?php
// admin/manage_plan_cart.php
// หน้าเลือกรายวิชาใส่ตะกร้าของแผนการเรียน
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (session_status() === PHP_SESSION_NONE)
    session_start();

// รับค่า
$pla_id = $_GET['pla_id'] ?? null;
if (!$pla_id) {
    echo "<script>alert('ไม่ได้ระบุแผนการเรียน'); window.location='manage_plans.php';</script>";
    exit;
}

// ข้อมูลแผน
$plan = $pdo->prepare("SELECT * FROM study_plans WHERE pla_id = ?");
$plan->execute([$pla_id]);
$plan_data = $plan->fetch();
if (!$plan_data)
    die("ไม่พบข้อมูลแผนการเรียน");

// สาขาของแผน
$stmt_maj = $pdo->prepare("SELECT m.maj_id FROM study_plan_classes spc JOIN class_groups c ON spc.cla_id = c.cla_id JOIN majors m ON c.cla_major_code = m.maj_code WHERE spc.pla_id = ? LIMIT 1");
$stmt_maj->execute([$pla_id]);
$target_maj_id = $stmt_maj->fetchColumn();

// กลุ่มเรียนที่ใช้แผนนี้
$current_year = date('Y') + 543;
$stmt_cls = $pdo->prepare("SELECT CONCAT(c.cla_name, '.', (? - c.cla_year + 1), '/', CAST(c.cla_group_no AS UNSIGNED)) as display_name FROM study_plan_classes spc JOIN class_groups c ON spc.cla_id = c.cla_id WHERE spc.pla_id = ?");
$stmt_cls->execute([$current_year, $pla_id]);
$plan_classes = $stmt_cls->fetchAll(PDO::FETCH_COLUMN);

// Reset Filters
if (isset($_GET['action']) && $_GET['action'] == 'reset') {
    unset($_SESSION['cart_filters']);
    header("Location: manage_plan_cart.php?pla_id=$pla_id");
    exit;
}

// Filter Logic
$saved_filters = $_SESSION['cart_filters'] ?? [];
$filter_cur = $saved_filters['cur'] ?? '';
$filter_sug = $saved_filters['sug'] ?? '';
$filter_com = $saved_filters['com'] ?? '';
$show_all_majors = $saved_filters['all_majors'] ?? 0;

if (isset($_GET['filter_cur'])) {
    $filter_cur = $_GET['filter_cur'];
    $filter_sug = $_GET['filter_sug'] ?? '';
    $show_all_majors = isset($_GET['show_all_majors']) ? 1 : 0;
    if ($filter_sug == 6 || $filter_sug == 5) {
        $filter_com = '';
    }
    else {
        $filter_com = $_GET['filter_com'] ?? '';
    }
    $_SESSION['cart_filters'] = ['cur' => $filter_cur, 'sug' => $filter_sug, 'com' => $filter_com, 'all_majors' => $show_all_majors];
}

// Master Data สำหรับ filter
$curriculums = $pdo->query("SELECT c.*, l.lev_name FROM curriculums c JOIN levels l ON c.lev_id = l.lev_id ORDER BY l.lev_id ASC, c.cur_year DESC")->fetchAll();
$groups = $pdo->query("SELECT * FROM subject_groups ORDER BY FIELD(sug_name, 'หมวดวิชาสมรรถนะแกนกลาง', 'หมวดวิชาสมรรถนะวิชาชีพ', 'หมวดวิชาเลือกเสรี', 'กิจกรรมเสริมหลักสูตร') ASC")->fetchAll();

// Competencies
$competencies_list = [];
if (!empty($filter_cur) && !empty($filter_sug) && $filter_sug != 6 && $filter_sug != 5) {
    $sql_com = "SELECT DISTINCT sub_competency FROM subjects WHERE cur_id = ? AND sug_id = ? AND sub_competency != '' ORDER BY sub_competency ASC";
    $stmt_com = $pdo->prepare($sql_com);
    $stmt_com->execute([$filter_cur, $filter_sug]);
    $competencies_list = $stmt_com->fetchAll(PDO::FETCH_COLUMN);
}

// ดึงวิชาที่อยู่ในตะกร้าแล้ว
$stmt_cart = $pdo->prepare("SELECT sub_id FROM plan_subject_cart WHERE pla_id = ?");
$stmt_cart->execute([$pla_id]);
$cart_sub_ids = $stmt_cart->fetchAll(PDO::FETCH_COLUMN);

// ดึงวิชาสำหรับแสดงใน filter
$subjects = [];
if (!empty($filter_cur) && !empty($filter_sug)) {
    $params_sub = [];
    $sql_sub = "SELECT s.*, sg.sug_name FROM subjects s LEFT JOIN subject_groups sg ON s.sug_id = sg.sug_id WHERE s.cur_id = ?";
    $params_sub[] = $filter_cur;

    if ($filter_sug != 6) {
        $sql_sub .= " AND s.sug_id = ?";
        $params_sub[] = $filter_sug;
    }
    if ($filter_sug != 6 && $filter_sug != 5 && !empty($filter_com)) {
        $sql_sub .= " AND s.sub_competency = ?";
        $params_sub[] = $filter_com;
    }
    if (!$show_all_majors && $target_maj_id) {
        $sql_sub .= " AND (s.maj_id IS NULL OR s.maj_id = '' OR s.maj_id = ?)";
        $params_sub[] = $target_maj_id;
    }
    $sql_sub .= " ORDER BY s.sub_code ASC";
    $stmt_sub = $pdo->prepare($sql_sub);
    $stmt_sub->execute($params_sub);
    $subjects = $stmt_sub->fetchAll();
}

// ดึงรายวิชาในตะกร้าพร้อมรายละเอียด (สำหรับแสดงด้านล่าง)
$cart_subjects = [];
$cart_grouped = [];
if (!empty($cart_sub_ids)) {
    $ph = implode(',', array_fill(0, count($cart_sub_ids), '?'));
    $stmt_cs = $pdo->prepare("SELECT s.*, sg.sug_name, COALESCE(NULLIF(s.sub_competency, ''), sg.sug_name, 'ไม่ระบุหมวด') as comp_group
                               FROM subjects s LEFT JOIN subject_groups sg ON s.sug_id = sg.sug_id
                               WHERE s.sub_id IN ($ph) ORDER BY s.sug_id ASC, s.sub_competency ASC, s.sub_code ASC");
    $stmt_cs->execute($cart_sub_ids);
    $cart_subjects = $stmt_cs->fetchAll();

    foreach ($cart_subjects as $cs) {
        $cg = $cs['comp_group'];
        if (!isset($cart_grouped[$cg]))
            $cart_grouped[$cg] = [];
        $cart_grouped[$cg][] = $cs;
    }
}

$total_cart = count($cart_sub_ids);
$total_credits = 0;
foreach ($cart_subjects as $cs)
    $total_credits += intval($cs['sub_credit']);

// สีของกลุ่มสมรรถนะ
$comp_colors = [
    'กลุ่มสมรรถนะภาษาและการสื่อสาร' => ['dot' => 'bg-orange-400', 'bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'border' => 'border-orange-200'],
    'กลุ่มสมรรถนะการคิดและการแก้ปัญหา' => ['dot' => 'bg-blue-400', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200'],
    'กลุ่มสมรรถนะทางสังคมและการดำรงชีวิต' => ['dot' => 'bg-green-400', 'bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-200'],
    'กลุ่มสมรรถนะวิชาชีพพื้นฐาน' => ['dot' => 'bg-indigo-400', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200'],
    'กลุ่มสมรรถนะวิชาชีพเฉพาะ' => ['dot' => 'bg-purple-400', 'bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200'],
    'หมวดวิชาสมรรถนะแกนกลาง' => ['dot' => 'bg-orange-400', 'bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'border' => 'border-orange-200'],
    'หมวดวิชาสมรรถนะวิชาชีพ' => ['dot' => 'bg-indigo-400', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200'],
    'หมวดวิชาเลือกเสรี' => ['dot' => 'bg-teal-400', 'bg' => 'bg-teal-50', 'text' => 'text-teal-700', 'border' => 'border-teal-200'],
    'กิจกรรมเสริมหลักสูตร' => ['dot' => 'bg-pink-400', 'bg' => 'bg-pink-50', 'text' => 'text-pink-700', 'border' => 'border-pink-200'],
];
$default_color = ['dot' => 'bg-slate-400', 'bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'border' => 'border-slate-200'];

require_once '../includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .cart-item-enter { animation: slideIn 0.3s ease-out; }
    .cart-item-exit { animation: slideOut 0.3s ease-out forwards; }
    @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes slideOut { from { opacity: 1; transform: translateX(0); } to { opacity: 0; transform: translateX(30px); } }
    .subject-card { transition: all 0.2s ease; }
    .subject-card:hover { transform: translateY(-2px); }
    .subject-card.in-cart { opacity: 0.4; pointer-events: none; }
    .subject-card.in-cart::after {
        content: '✓ อยู่ในตะกร้า';
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,0.7);
        font-weight: bold; color: #059669; font-size: 0.75rem;
        border-radius: 0.5rem;
    }
</style>

<div class="max-w-7xl mx-auto space-y-6 pb-12" id="app">

    <!-- Header -->
    <div>
        <a href="manage_plans.php" class="text-slate-400 hover:text-cvc-blue text-sm font-bold flex items-center gap-2 mb-2">
            <i class="fa-solid fa-arrow-left"></i> กลับหน้ารวมแผน
        </a>
        <div class="card-premium p-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-cvc-sky/20 rounded-full blur-3xl -mr-16 -mt-16"></div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800"><i class="fa-solid fa-cart-shopping text-cvc-blue mr-2"></i>เลือกรายวิชาใส่ตะกร้า</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm text-slate-500">แผน:</span>
                        <span class="text-sm font-bold text-indigo-700"><?php echo htmlspecialchars($plan_data['pla_name']); ?></span>
                        <span class="font-mono text-xs text-slate-400">(<?php echo $plan_data['pla_code']; ?>)</span>
                    </div>
                    <?php if (!empty($plan_classes)): ?>
                    <div class="flex flex-wrap items-center gap-1.5 mt-2">
                        <span class="text-xs text-slate-500"><i class="fa-solid fa-users mr-1"></i>ใช้กับ:</span>
                        <?php foreach ($plan_classes as $pc): ?>
                            <span class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full text-xs font-bold border border-amber-200"><?php echo htmlspecialchars($pc); ?></span>
                        <?php
    endforeach; ?>
                    </div>
                    <?php
endif; ?>
                </div>
                <div class="flex gap-3 items-center">
                    <div class="bg-emerald-50 border border-emerald-200 px-4 py-2 rounded-xl text-center shadow-sm">
                        <div class="text-[10px] text-emerald-500 uppercase font-bold">ในตะกร้า</div>
                        <div class="text-2xl font-black text-emerald-600" id="cart-count"><?php echo $total_cart; ?></div>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 px-4 py-2 rounded-xl text-center shadow-sm">
                        <div class="text-[10px] text-blue-500 uppercase font-bold">หน่วยกิต</div>
                        <div class="text-2xl font-black text-blue-600" id="cart-credits"><?php echo $total_credits; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter + รายวิชาให้เลือก -->
    <div class="card-premium p-0 overflow-hidden border border-slate-200">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="font-bold text-slate-700 flex items-center gap-2"><i class="fa-solid fa-filter text-cvc-blue"></i> เลือกรายวิชาเพิ่ม</h3>
            <?php if ($filter_cur || $filter_sug): ?>
                <a href="?pla_id=<?php echo $pla_id; ?>&action=reset" class="text-xs text-red-500 hover:underline"><i class="fa-solid fa-rotate-left"></i> รีเซ็ตตัวกรอง</a>
            <?php
endif; ?>
        </div>
        <div class="p-6">
            <form action="" method="GET" id="filterForm">
                <input type="hidden" name="pla_id" value="<?php echo $pla_id; ?>">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 items-end">
                    <div><label class="block text-xs font-bold text-slate-500 mb-1">1. หลักสูตร</label><select name="filter_cur" class="w-full text-sm" onchange="document.getElementById('filterForm').submit()"><option value="">-- เลือกหลักสูตร --</option><?php foreach ($curriculums as $c): ?><option value="<?php echo $c['cur_id']; ?>" <?php echo $filter_cur == $c['cur_id'] ? 'selected' : ''; ?>><?php echo $c['lev_name'] . ' ' . $c['cur_year']; ?></option><?php
endforeach; ?></select></div>
                    <div><label class="block text-xs font-bold text-slate-500 mb-1">2. หมวดวิชา</label><select name="filter_sug" class="w-full text-sm disabled:bg-slate-100" onchange="document.getElementById('filterForm').submit()" <?php echo empty($filter_cur) ? 'disabled' : ''; ?>><option value="">-- เลือกหมวด --</option><?php foreach ($groups as $g): ?><option value="<?php echo $g['sug_id']; ?>" <?php echo $filter_sug == $g['sug_id'] ? 'selected' : ''; ?>><?php echo $g['sug_name']; ?></option><?php
endforeach; ?></select></div>
                    <div><label class="block text-xs font-bold text-slate-500 mb-1">3. สมรรถนะ</label><select name="filter_com" class="w-full text-sm disabled:bg-slate-100" onchange="document.getElementById('filterForm').submit()" <?php echo(empty($filter_cur) || empty($filter_sug) || $filter_sug == 6 || $filter_sug == 5) ? 'disabled' : ''; ?>><option value="">-- ทั้งหมด --</option><?php foreach ($competencies_list as $com): ?><option value="<?php echo $com; ?>" <?php echo $filter_com == $com ? 'selected' : ''; ?>><?php echo $com; ?></option><?php
endforeach; ?></select></div>
                    <div class="pb-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="show_all_majors" value="1" onchange="document.getElementById('filterForm').submit()" <?php echo $show_all_majors ? 'checked' : ''; ?> class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 border-gray-300">
                            <span class="ml-2 text-sm text-slate-600 font-bold">แสดงวิชาทุกสาขา</span>
                        </label>
                    </div>
                </div>
            </form>

            <?php if (!empty($filter_cur) && !empty($filter_sug)): ?>
            <div class="mb-3 flex justify-between items-center">
                <span class="text-xs text-slate-400"><?php echo count($subjects); ?> รายวิชาพบ</span>
                <button onclick="addAllVisible()" class="text-xs bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-3 py-1.5 rounded-lg border border-emerald-200 font-bold transition">
                    <i class="fa-solid fa-plus-circle mr-1"></i> เพิ่มทั้งหมดที่แสดง
                </button>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 max-h-[400px] overflow-y-auto custom-scrollbar">
                <?php if (empty($subjects)): ?>
                    <div class="flex flex-col items-center justify-center text-slate-400 py-8">
                        <i class="fa-regular fa-folder-open text-4xl mb-2"></i>
                        <p>ไม่พบรายวิชาตามเงื่อนไข</p>
                    </div>
                <?php
    else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <?php foreach ($subjects as $s):
            $in_cart = in_array($s['sub_id'], $cart_sub_ids);
?>
                            <div class="subject-card bg-white border border-slate-200 rounded-lg p-3 cursor-pointer relative shadow-sm hover:shadow-md <?php echo $in_cart ? 'in-cart' : ''; ?>"
                                 id="sub_<?php echo $s['sub_id']; ?>"
                                 onclick="addToCart(<?php echo $s['sub_id']; ?>, '<?php echo addslashes($s['sub_code']); ?>', '<?php echo addslashes($s['sub_name']); ?>', <?php echo intval($s['sub_credit']); ?>)">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="font-mono font-bold text-xs text-cvc-blue bg-blue-100 px-1.5 rounded"><?php echo $s['sub_code']; ?></span>
                                    <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 rounded"><?php echo $s['sub_th_pr_ot']; ?></span>
                                </div>
                                <div class="text-sm font-bold text-slate-700 leading-tight"><?php echo htmlspecialchars($s['sub_name']); ?></div>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-[10px] text-slate-400"><?php echo $s['sub_credit']; ?> นก. / <?php echo $s['sub_hours']; ?> ชม.</span>
                                    <span class="text-emerald-500 text-xs font-bold"><i class="fa-solid fa-plus-circle"></i></span>
                                </div>
                            </div>
                        <?php
        endforeach; ?>
                    </div>
                <?php
    endif; ?>
            </div>
            <?php
elseif (empty($filter_cur) || empty($filter_sug)): ?>
                <div class="p-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-300 text-slate-400">
                    <i class="fa-solid fa-arrow-up text-3xl mb-2 animate-bounce"></i>
                    <p class="font-bold">กรุณาเลือก "หลักสูตร" และ "หมวดวิชา" ด้านบน</p>
                </div>
            <?php
endif; ?>
        </div>
    </div>

    <!-- ตะกร้า: รายวิชาที่เลือกแล้ว -->
    <div class="card-premium overflow-hidden border border-slate-200">
        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-4 border-b border-emerald-200 flex justify-between items-center">
            <h3 class="font-bold text-emerald-800 flex items-center gap-2">
                <i class="fa-solid fa-cart-shopping text-emerald-500"></i> ตะกร้ารายวิชาที่เลือก
                <span class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full text-xs font-bold border border-emerald-200" id="cart-badge"><?php echo $total_cart; ?></span>
            </h3>
            <?php if ($total_cart > 0): ?>
            <button onclick="clearCart()" class="text-xs text-red-500 hover:text-red-700 font-bold transition">
                <i class="fa-solid fa-trash-can mr-1"></i> ล้างตะกร้า
            </button>
            <?php
endif; ?>
        </div>
        <div class="p-6" id="cart-container">
            <?php if (empty($cart_grouped)): ?>
                <div class="py-8 text-center text-slate-300" id="cart-empty">
                    <i class="fa-solid fa-cart-shopping text-4xl mb-2"></i>
                    <p class="font-bold">ยังไม่มีรายวิชาในตะกร้า</p>
                    <p class="text-xs mt-1">เลือกหลักสูตร → หมวดวิชา แล้วคลิกรายวิชาด้านบนเพื่อเพิ่ม</p>
                </div>
            <?php
else: ?>
                <div class="space-y-4" id="cart-list">
                    <?php foreach ($cart_grouped as $comp_name => $comp_subs):
        $color = $comp_colors[$comp_name] ?? $default_color;
        $group_credits = 0;
        foreach ($comp_subs as $cs)
            $group_credits += intval($cs['sub_credit']);
?>
                    <div class="cart-group" data-group="<?php echo htmlspecialchars($comp_name); ?>">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-2.5 h-2.5 rounded-full <?php echo $color['dot']; ?>"></span>
                            <span class="text-xs font-bold <?php echo $color['text']; ?> uppercase tracking-wide"><?php echo htmlspecialchars($comp_name); ?></span>
                            <span class="text-[10px] text-slate-400 font-bold group-count">(<?php echo count($comp_subs); ?> วิชา, <?php echo $group_credits; ?> นก.)</span>
                        </div>
                        <div class="space-y-1.5 ml-5">
                            <?php foreach ($comp_subs as $cs): ?>
                            <div class="cart-item flex items-center justify-between <?php echo $color['bg']; ?> <?php echo $color['border']; ?> border rounded-lg px-3 py-2 group" id="cart_<?php echo $cs['sub_id']; ?>" data-credit="<?php echo $cs['sub_credit']; ?>">
                                <div class="flex items-center gap-3">
                                    <span class="font-mono font-bold text-xs <?php echo $color['text']; ?>"><?php echo $cs['sub_code']; ?></span>
                                    <span class="text-sm text-slate-700"><?php echo htmlspecialchars($cs['sub_name']); ?></span>
                                    <span class="text-xs text-slate-400">(<?php echo $cs['sub_credit']; ?> นก.)</span>
                                </div>
                                <button onclick="removeFromCart(<?php echo $cs['sub_id']; ?>, <?php echo intval($cs['sub_credit']); ?>)" class="w-7 h-7 rounded-full border border-red-200 text-red-400 hover:bg-red-500 hover:text-white flex items-center justify-center transition opacity-0 group-hover:opacity-100 flex-shrink-0">
                                    <i class="fa-solid fa-times text-xs"></i>
                                </button>
                            </div>
                            <?php
        endforeach; ?>
                        </div>
                    </div>
                    <?php
    endforeach; ?>
                </div>
            <?php
endif; ?>
        </div>
    </div>
</div>

<script>
const plaId = <?php echo $pla_id; ?>;

async function addToCart(subId, subCode, subName, credits) {
    const card = document.getElementById('sub_' + subId);
    if (card.classList.contains('in-cart')) return;

    try {
        const fd = new FormData();
        fd.append('action', 'add');
        fd.append('pla_id', plaId);
        fd.append('sub_id', subId);
        const res = await fetch('save_plan_cart.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.status === 'success') {
            // อัปเดต UI ของ card
            card.classList.add('in-cart');

            // อัปเดตตัวเลข
            const countEl = document.getElementById('cart-count');
            const creditsEl = document.getElementById('cart-credits');
            const badgeEl = document.getElementById('cart-badge');
            countEl.textContent = parseInt(countEl.textContent) + 1;
            creditsEl.textContent = parseInt(creditsEl.textContent) + credits;
            badgeEl.textContent = parseInt(badgeEl.textContent) + 1;

            // Toast
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1200 });
            Toast.fire({ icon: 'success', title: `เพิ่ม ${subCode} แล้ว` });
        }
    } catch (e) {
        Swal.fire('Error', 'เกิดข้อผิดพลาด', 'error');
    }
}

async function addAllVisible() {
    const cards = document.querySelectorAll('.subject-card:not(.in-cart)');
    if (cards.length === 0) {
        Swal.fire('แจ้งเตือน', 'ไม่มีวิชาให้เพิ่มแล้ว', 'info');
        return;
    }

    const result = await Swal.fire({
        title: `เพิ่มทั้งหมด ${cards.length} วิชา?`,
        text: 'จะเพิ่มรายวิชาที่แสดงทั้งหมดลงตะกร้า',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'เพิ่มเลย',
        cancelButtonText: 'ยกเลิก'
    });

    if (result.isConfirmed) {
        const subIds = [];
        cards.forEach(c => {
            const id = c.id.replace('sub_', '');
            subIds.push(id);
        });

        const fd = new FormData();
        fd.append('action', 'add_multiple');
        fd.append('pla_id', plaId);
        subIds.forEach(id => fd.append('sub_ids[]', id));

        try {
            const res = await fetch('save_plan_cart.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status === 'success') {
                await Swal.fire({ icon: 'success', title: `เพิ่ม ${data.count} วิชาแล้ว`, timer: 1200, showConfirmButton: false });
                window.location.reload();
            }
        } catch (e) {
            Swal.fire('Error', 'เกิดข้อผิดพลาด', 'error');
        }
    }
}

async function removeFromCart(subId, credits) {
    try {
        const fd = new FormData();
        fd.append('action', 'remove');
        fd.append('pla_id', plaId);
        fd.append('sub_id', subId);
        const res = await fetch('save_plan_cart.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.status === 'success') {
            // ลบ item ออกจาก cart UI
            const item = document.getElementById('cart_' + subId);
            if (item) {
                item.classList.add('cart-item-exit');
                setTimeout(() => item.remove(), 300);
            }

            // อัปเดต subject card (ถ้ามีอยู่ในหน้า)
            const card = document.getElementById('sub_' + subId);
            if (card) card.classList.remove('in-cart');

            // อัปเดตตัวเลข
            const countEl = document.getElementById('cart-count');
            const creditsEl = document.getElementById('cart-credits');
            const badgeEl = document.getElementById('cart-badge');
            countEl.textContent = Math.max(0, parseInt(countEl.textContent) - 1);
            creditsEl.textContent = Math.max(0, parseInt(creditsEl.textContent) - credits);
            badgeEl.textContent = Math.max(0, parseInt(badgeEl.textContent) - 1);

            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1000 });
            Toast.fire({ icon: 'info', title: 'ลบออกแล้ว' });
        }
    } catch (e) {
        Swal.fire('Error', 'เกิดข้อผิดพลาด', 'error');
    }
}

async function clearCart() {
    const result = await Swal.fire({
        title: 'ล้างตะกร้าทั้งหมด?',
        text: 'จะลบรายวิชาทั้งหมดออกจากตะกร้าของแผนนี้',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'ล้างเลย',
        cancelButtonText: 'ยกเลิก'
    });

    if (result.isConfirmed) {
        const fd = new FormData();
        fd.append('action', 'clear');
        fd.append('pla_id', plaId);

        try {
            const res = await fetch('save_plan_cart.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.status === 'success') {
                await Swal.fire({ icon: 'success', title: 'ล้างตะกร้าแล้ว', timer: 1000, showConfirmButton: false });
                window.location.reload();
            }
        } catch (e) {
            Swal.fire('Error', 'เกิดข้อผิดพลาด', 'error');
        }
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
