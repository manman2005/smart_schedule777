<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// เคลียร์ตัวกรอง
if (isset($_GET['clear'])) {
    unset($_SESSION['sub_filters']); 
    header("Location: manage_subjects.php"); exit();
}

$search = ''; $filter_cur = ''; $filter_sug = ''; $filter_competency = '';

// รับค่าตัวกรองจาก GET หรือ SESSION
if (isset($_GET['search']) || isset($_GET['cur_id']) || isset($_GET['sug_id']) || isset($_GET['competency'])) {
    $search = $_GET['search'] ?? '';
    $filter_cur = $_GET['cur_id'] ?? '';
    $filter_sug = $_GET['sug_id'] ?? '';
    $filter_competency = $_GET['competency'] ?? '';
    
    // ถ้าเปลี่ยนหมวดวิชา ให้รีเซ็ตกลุ่มสมรรถนะ
    if (isset($_GET['sug_id']) && $_GET['sug_id'] !== ($_SESSION['sub_filters']['sug_id'] ?? '')) {
        $filter_competency = '';
    }

    $_SESSION['sub_filters'] = ['search' => $search, 'cur_id' => $filter_cur, 'sug_id' => $filter_sug, 'competency' => $filter_competency];
} elseif (isset($_SESSION['sub_filters'])) {
    $search = $_SESSION['sub_filters']['search'] ?? '';
    $filter_cur = $_SESSION['sub_filters']['cur_id'] ?? '';
    $filter_sug = $_SESSION['sub_filters']['sug_id'] ?? '';
    $filter_competency = $_SESSION['sub_filters']['competency'] ?? '';
}

// 1. ดึง Master Data
$curriculums_list = $pdo->query("SELECT c.*, l.lev_name FROM curriculums c JOIN levels l ON c.lev_id = l.lev_id ORDER BY l.lev_id ASC, c.cur_year DESC")->fetchAll();
$groups_list = $pdo->query("SELECT * FROM subject_groups ORDER BY sug_id ASC")->fetchAll();

// 2. ดึงกลุ่มสมรรถนะ (แสดงเฉพาะที่มีในหมวดที่เลือก)
$sql_comp = "SELECT DISTINCT sub_competency FROM subjects WHERE sub_competency IS NOT NULL AND sub_competency != ''";
$params_comp = [];
if (!empty($filter_sug)) { $sql_comp .= " AND sug_id = ?"; $params_comp[] = $filter_sug; }
$sql_comp .= " ORDER BY FIELD(sub_competency, 'กลุ่มสมรรถนะภาษาและการสื่อสาร', 'กลุ่มสมรรถนะการคิดและการแก้ปัญหา', 'กลุ่มสมรรถนะทางสังคมและการดำรงชีวิต', 'กลุ่มสมรรถนะวิชาชีพพื้นฐาน', 'กลุ่มสมรรถนะวิชาชีพเฉพาะ') ASC, sub_competency ASC";
$stmt_comp = $pdo->prepare($sql_comp);
$stmt_comp->execute($params_comp);
$competencies_list = $stmt_comp->fetchAll(PDO::FETCH_COLUMN);

// 3. ดึงรายวิชา
$show_data = false;
$grouped_subjects = [];

// เงื่อนไข: ต้องมีการค้นหา หรือเลือกตัวกรองอย่างน้อย 1 อย่าง จึงจะแสดงข้อมูล (เพื่อไม่ให้โหลดเยอะเกินไปตอนแรก)
if (!empty($filter_cur) || !empty($search) || !empty($filter_sug) || !empty($filter_competency)) {
    $show_data = true;
    $sql = "SELECT s.*, sg.sug_name, c.cur_year, l.lev_name FROM subjects s 
            LEFT JOIN subject_groups sg ON s.sug_id = sg.sug_id 
            LEFT JOIN curriculums c ON s.cur_id = c.cur_id 
            LEFT JOIN levels l ON c.lev_id = l.lev_id 
            WHERE (s.sub_code LIKE ? OR s.sub_name LIKE ?)";
    $params = ["%$search%", "%$search%"];
    
    if (!empty($filter_cur)) { $sql .= " AND s.cur_id = ?"; $params[] = $filter_cur; }
    if (!empty($filter_sug)) { if ($filter_sug != 6) { $sql .= " AND s.sug_id = ?"; $params[] = $filter_sug; } }
    if (!empty($filter_competency) && $filter_sug != 6 && $filter_sug != 5) { $sql .= " AND s.sub_competency = ?"; $params[] = $filter_competency; }
    
    $sql .= " ORDER BY FIELD(s.sub_competency, 'กลุ่มสมรรถนะภาษาและการสื่อสาร', 'กลุ่มสมรรถนะการคิดและการแก้ปัญหา', 'กลุ่มสมรรถนะทางสังคมและการดำรงชีวิต', 'กลุ่มสมรรถนะวิชาชีพพื้นฐาน', 'กลุ่มสมรรถนะวิชาชีพเฉพาะ') ASC, s.sub_code ASC"; 
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $subjects = $stmt->fetchAll();
    
    // จัดกลุ่มวิชาตามหมวด (sug_id)
    foreach ($subjects as $sub) {
        $gid = $sub['sug_id'] ? $sub['sug_id'] : 999;
        $gname = $sub['sug_name'] ? $sub['sug_name'] : 'ไม่ระบุหมวดวิชา';
        if (!isset($grouped_subjects[$gid])) { $grouped_subjects[$gid] = ['name' => $gname, 'items' => []]; }
        $grouped_subjects[$gid]['items'][] = $sub;
    }
}

// ธีมสีสำหรับแต่ละหมวดวิชา (ใช้โทนสีพาสเทลที่เข้ากับธีมหลัก)
$group_colors = [
    1 => ['border' => 'border-orange-400', 'bg' => 'bg-orange-50', 'badge' => 'bg-orange-100 text-orange-700'], 
    2 => ['border' => 'border-blue-400', 'bg' => 'bg-blue-50', 'badge' => 'bg-blue-100 text-blue-700'],   
    3 => ['border' => 'border-emerald-400', 'bg' => 'bg-emerald-50', 'badge' => 'bg-emerald-100 text-emerald-700'],  
    4 => ['border' => 'border-purple-400', 'bg' => 'bg-purple-50', 'badge' => 'bg-purple-100 text-purple-700'], 
    5 => ['border' => 'border-pink-400', 'bg' => 'bg-pink-50', 'badge' => 'bg-pink-100 text-pink-700'],   
    6 => ['border' => 'border-teal-400', 'bg' => 'bg-teal-50', 'badge' => 'bg-teal-100 text-teal-700'],   
    999 => ['border' => 'border-slate-400', 'bg' => 'bg-slate-50', 'badge' => 'bg-slate-100 text-slate-700']  
];

require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto pb-12">
    
    <div class="mb-8">
        <a href="index.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2">
            <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
        </a>
        <h1 class="text-3xl font-serif font-bold text-slate-800">
            ข้อมูลรายวิชา <span class="text-slate-400 text-lg font-sans font-normal">(Subjects)</span>
        </h1>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-start mb-6 gap-4">
        <div class="flex-1 w-full">
            <div class="card-premium p-5 shadow-sm border border-slate-100">
                <form action="" method="GET" class="flex flex-wrap gap-4 items-end">
                    
                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">1. หลักสูตร</label>
                        <select name="cur_id" class="w-full text-sm py-2 bg-slate-50 border-slate-200 rounded-lg focus:ring-cvc-blue focus:border-cvc-blue" onchange="this.form.submit()">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach($curriculums_list as $c): ?>
                                <option value="<?php echo $c['cur_id']; ?>" <?php echo $filter_cur == $c['cur_id'] ? 'selected' : ''; ?>>
                                    <?php echo $c['lev_name'] . ' ' . $c['cur_year']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">2. หมวดวิชา</label>
                        <select name="sug_id" id="sug_select" class="w-full text-sm py-2 bg-slate-50 border-slate-200 rounded-lg focus:ring-cvc-blue focus:border-cvc-blue" onchange="this.form.submit()">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach($groups_list as $g): ?>
                                <option value="<?php echo $g['sug_id']; ?>" <?php echo $filter_sug == $g['sug_id'] ? 'selected' : ''; ?>>
                                    <?php echo $g['sug_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">3. กลุ่มสมรรถนะ</label>
                        <select name="competency" id="comp_select" class="w-full text-sm py-2 bg-slate-50 border-slate-200 rounded-lg focus:ring-cvc-blue focus:border-cvc-blue disabled:opacity-50" onchange="this.form.submit()" <?php echo empty($filter_sug) ? 'disabled' : ''; ?>>
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach($competencies_list as $comp): ?>
                                <option value="<?php echo $comp; ?>" <?php echo $filter_competency == $comp ? 'selected' : ''; ?>>
                                    <?php echo $comp; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-bold text-slate-500 mb-1 ml-1">ค้นหา</label>
                        <div class="relative">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="รหัส / ชื่อวิชา" 
                                   class="w-full pl-4 pr-10 text-sm py-2 bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-cvc-blue transition shadow-sm">
                            <i class="fa-solid fa-search absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <div class="flex gap-2 self-end md:self-center">
            <?php if($show_data): ?>
                <a href="?clear=1" class="px-4 py-2.5 rounded-full border border-slate-300 text-slate-500 hover:bg-slate-50 text-sm font-bold transition flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-rotate-left"></i> ล้างตัวกรอง
                </a>
            <?php endif; ?>
            <a href="manage_subject_form.php" class="btn-cvc text-sm shadow-md hover:shadow-lg px-6 py-2.5">
                <i class="fa-solid fa-plus"></i> เพิ่มรายวิชา
            </a>
        </div>
    </div>

    <div class="space-y-6">
        <?php if (!$show_data): ?>
            <div class="card-premium p-16 text-center border-dashed border-2 border-slate-300 bg-slate-50/50">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm text-cvc-gold text-4xl animate-bounce">
                    <i class="fa-solid fa-filter"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700">กรุณาเลือกตัวกรอง</h3>
                <p class="text-slate-500">เลือกหลักสูตรหรือหมวดวิชาด้านบนเพื่อแสดงข้อมูลรายวิชา</p>
            </div>

        <?php elseif (empty($grouped_subjects)): ?>
            <div class="card-premium p-16 text-center border-dashed border-2 border-slate-300 bg-slate-50/50">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm text-slate-300 text-4xl">
                    <i class="fa-regular fa-folder-open"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700">ไม่พบข้อมูล</h3>
                <p class="text-slate-500">ไม่มีรายวิชาที่ตรงกับเงื่อนไขการค้นหา</p>
            </div>

        <?php else: ?>
            <?php foreach ($grouped_subjects as $gid => $group): 
                $theme = $group_colors[$gid % 7] ?? $group_colors[999];
            ?>
                <div class="card-premium overflow-hidden border-0 shadow-lg">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3 <?php echo $theme['bg']; ?> border-l-4 <?php echo $theme['border']; ?>">
                        <h3 class="font-bold text-slate-700 text-lg"><?php echo htmlspecialchars($group['name']); ?></h3>
                        <span class="px-3 py-1 rounded-full text-xs font-bold border <?php echo $theme['badge']; ?>">
                            <?php echo count($group['items']); ?> วิชา
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase font-extrabold tracking-wide">
                                <tr>
                                    <th class="px-6 py-4 w-32 whitespace-nowrap bg-white text-center">รหัสวิชา</th>
                                    <th class="px-6 py-4 min-w-[250px] bg-white">ชื่อวิชา</th>
                                    <th class="px-6 py-4 text-center w-24 whitespace-nowrap bg-white">ท-ป-น</th>
                                    <th class="px-6 py-4 w-40 bg-white">หลักสูตร</th>
                                    <th class="px-6 py-4 w-48 bg-white">กลุ่มสมรรถนะ</th>
                                    <th class="px-6 py-4 text-center w-28 whitespace-nowrap bg-white">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm">
                                <?php foreach ($group['items'] as $sub): ?>
                                <tr class="hover:bg-blue-50/20 transition duration-200 group">
                                    <td class="px-6 py-4 align-top whitespace-nowrap text-center">
                                        <span class="font-mono font-bold text-cvc-blue bg-blue-50 px-2 py-1 rounded border border-blue-100 shadow-sm">
                                            <?php echo $sub['sub_code']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 align-top font-bold text-slate-700">
                                        <?php echo htmlspecialchars($sub['sub_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 align-top text-center whitespace-nowrap">
                                        <span class="bg-slate-50 text-slate-600 px-2 py-1 rounded text-xs font-mono font-bold border border-slate-200">
                                            <?php echo htmlspecialchars($sub['sub_th_pr_ot']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <?php if ($sub['lev_name']): ?>
                                            <div class="flex flex-col text-xs">
                                                <span class="font-bold text-slate-600"><?php echo $sub['lev_name']; ?></span>
                                                <span class="text-slate-400 font-mono">หลักสูตร <?php echo $sub['cur_year']; ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-slate-300">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <?php if ($sub['sub_competency']): ?>
                                            <span class="text-[10px] text-slate-600 bg-slate-100 px-2 py-1 rounded border border-slate-200 inline-block leading-relaxed">
                                                <?php echo htmlspecialchars($sub['sub_competency']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-300 italic text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 align-top text-center whitespace-nowrap">
                                        <div class="flex justify-center gap-2 opacity-60 group-hover:opacity-100 transition duration-200">
                                            <a href="manage_subject_form.php?id=<?php echo $sub['sub_id']; ?>" 
                                               class="w-8 h-8 rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50 flex items-center justify-center transition" title="แก้ไข">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <a href="javascript:void(0)" 
                                               onclick="confirmDelete('delete_subject.php?id=<?php echo $sub['sub_id']; ?>', '<?php echo addslashes($sub['sub_name']); ?>')" 
                                               class="w-8 h-8 rounded-lg border border-slate-200 text-red-500 hover:bg-red-50 flex items-center justify-center transition" title="ลบ">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>