<?php
// public_schedule.php
// แก้ไขล่าสุด: บังคับเลย์เอาต์แนวนอน (Side-by-Side) เหมือนหน้า Admin
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/db.php';

if (!isset($pdo)) {
    die("<div style='color:red; text-align:center; padding:50px;'>Error: Database Connection Failed.</div>");
}

try {
    $pdo->exec("SET SQL_BIG_SELECTS=1");
} catch (PDOException $e) {}

$mode = $_GET['mode'] ?? ''; 
$id = $_GET['id'] ?? '';

// ถ้าไม่มีข้อมูล ให้กลับหน้าแรก
if(empty($id) || empty($mode)) {
    header("Location: index.php");
    exit;
}

try {
    // --- Period Selector Logic ---
    $stmt_periods = $pdo->query("SELECT DISTINCT sch_academic_year, sch_semester FROM schedule ORDER BY sch_academic_year DESC, sch_semester ASC");
    $available_periods = $stmt_periods->fetchAll();

    if(count($available_periods) > 0) {
        $default_year = $available_periods[0]['sch_academic_year'];
        $default_semester = $available_periods[0]['sch_semester'];
    } else {
        $default_year = date('Y') + 543;
        $default_semester = 1;
    }

    $selected_year = $_GET['year'] ?? $default_year;

    $valid_semesters_for_year = [];
    foreach($available_periods as $p) {
        if($p['sch_academic_year'] == $selected_year) {
            $valid_semesters_for_year[] = $p['sch_semester'];
        }
    }
    sort($valid_semesters_for_year);
    if(empty($valid_semesters_for_year)) $valid_semesters_for_year = [1];

    $req_semester = $_GET['semester'] ?? $default_semester;
    if(in_array($req_semester, $valid_semesters_for_year)) {
        $selected_semester = $req_semester;
    } else {
        $selected_semester = $valid_semesters_for_year[0];
    }

    $time_slots = $pdo->query("SELECT * FROM time_slots ORDER BY tim_start ASC")->fetchAll();

    $schedule_data = [];
    $header_title = "";
    $head_title_1 = ""; 
    $head_sub_info = [];
    $subject_summary = [];

    // --- Query ข้อมูล ---
    if ($mode == 'class') {
        $stmt = $pdo->prepare("SELECT c.*, t.tea_fullname AS advisor_name, m.maj_name 
                               FROM class_groups c 
                               LEFT JOIN teachers t ON c.tea_id = t.tea_id
                               LEFT JOIN majors m ON c.cla_major_code = m.maj_code
                               WHERE c.cla_id = ?");
        $stmt->execute([$id]);
        $class_info = $stmt->fetch();
        
        if ($class_info) {
            $current_year_real = date('Y') + 543;
            $stu_level = max(1, $current_year_real - $class_info['cla_year'] + 1);
            $room_no = intval($class_info['cla_group_no']);
            $class_name_full = "{$class_info['maj_name']} {$class_info['cla_name']}.{$stu_level}/{$room_no}";
            $advisor_name = $class_info['advisor_name'] ?? "-";
            
            $header_title = "ตารางเรียน $class_name_full";
            $head_title_1 = "ตารางเรียนรายบุคคล/กลุ่มเรียน";
            $head_sub_info = [
                'ภาคเรียน' => "$selected_semester / $selected_year",
                'ครูที่ปรึกษา' => $advisor_name,
                'รหัสกลุ่มเรียน' => $class_info['cla_id'],
                'ชื่อกลุ่มเรียน' => $class_name_full
            ];
        } else {
            $header_title = "ไม่พบข้อมูล";
            $head_title_1 = "ไม่พบข้อมูลกลุ่มเรียน";
        }

        $sql = "SELECT sch.*, s.sub_code, s.sub_name, s.sub_th_pr_ot, s.sub_credit, t.tea_fullname, r.roo_id
                FROM schedule sch
                JOIN subjects s ON sch.sub_id = s.sub_id
                LEFT JOIN teachers t ON sch.tea_id = t.tea_id
                JOIN rooms r ON sch.roo_id = r.roo_id
                WHERE sch.cla_id = ? AND sch.sch_academic_year = ? AND sch.sch_semester = ?";
                
        $sql_sum = "SELECT DISTINCT s.sub_id, s.sub_code, s.sub_name, s.sub_th_pr_ot, s.sub_credit
                    FROM schedule sch
                    JOIN subjects s ON sch.sub_id = s.sub_id
                    WHERE sch.cla_id = ? AND sch.sch_academic_year = ? AND sch.sch_semester = ?
                    ORDER BY s.sub_code ASC";

    } else {
        $stmt = $pdo->prepare("SELECT tea_fullname FROM teachers WHERE tea_id = ?");
        $stmt->execute([$id]);
        $teacher_name = $stmt->fetchColumn();
        
        $header_title = "ตารางสอน $teacher_name";
        $head_title_1 = "ตารางสอนรายบุคคล";
        $head_sub_info = [
            'ภาคเรียน' => "$selected_semester / $selected_year",
            'ชื่อผู้สอน' => $teacher_name
        ];

        $sql = "SELECT sch.*, s.sub_code, s.sub_name, s.sub_th_pr_ot, s.sub_credit, 
                       c.cla_name, c.cla_year, c.cla_group_no, r.roo_id
                FROM schedule sch
                JOIN subjects s ON sch.sub_id = s.sub_id
                LEFT JOIN class_groups c ON sch.cla_id = c.cla_id
                JOIN rooms r ON sch.roo_id = r.roo_id
                WHERE sch.tea_id = ? AND sch.sch_academic_year = ? AND sch.sch_semester = ?";
                
        $sql_sum = "SELECT DISTINCT s.sub_id, s.sub_code, s.sub_name, s.sub_th_pr_ot, s.sub_credit
                    FROM schedule sch
                    JOIN subjects s ON sch.sub_id = s.sub_id
                    WHERE sch.tea_id = ? AND sch.sch_academic_year = ? AND sch.sch_semester = ?
                    ORDER BY s.sub_code ASC";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $selected_year, $selected_semester]);
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        if ($mode == 'teacher') {
            if (!empty($row['cla_name']) && !empty($row['cla_year'])) {
                $current_year_real = date('Y') + 543;
                $stu_lev = max(1, $current_year_real - $row['cla_year'] + 1);
                $r_no = intval($row['cla_group_no']);
                $row['tea_fullname'] = "{$row['cla_name']}.{$stu_lev}/{$r_no}";
            } else {
                $row['tea_fullname'] = "-";
            }
        }
        $schedule_data[$row['day_id']][$row['tim_id']] = ['info' => $row, 'hours' => $row['sch_hours']];
    }

    $stmt_sum = $pdo->prepare($sql_sum);
    $stmt_sum->execute([$id, $selected_year, $selected_semester]);
    $raw_summary = $stmt_sum->fetchAll();

    foreach($raw_summary as $sub) {
        $tpn = explode('-', $sub['sub_th_pr_ot'] ?? '0-0-0');
        $t = isset($tpn[0]) ? intval($tpn[0]) : 0;
        $p = isset($tpn[1]) ? intval($tpn[1]) : 0;
        $c = isset($tpn[2]) ? intval($tpn[2]) : intval($sub['sub_credit']);
        $h = $t + $p; 
        $subject_summary[] = ['code'=>$sub['sub_code'], 'name'=>$sub['sub_name'], 't'=>$t, 'p'=>$p, 'c'=>$c, 'h'=>$h];
    }

} catch (PDOException $e) {
    die("<div style='background-color:#fee2e2; color:#991b1b; padding:20px; text-align:center;'>Database Error: " . $e->getMessage() . "</div>");
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $header_title; ?> - Smart Schedule</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Prompt:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Sarabun', 'sans-serif'],
                        display: ['Prompt', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        cvc: {
                            blue: '#1e40af',
                            sky: '#38bdf8',
                            navy: '#0f172a',
                            gold: '#d4af37',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #f3f4f6;
            background-image: 
                url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M15 10h10v10H15V10zm35 0h10v10H50V10zm35 0h10v10H85V10zM15 45h10v10H15V45zm35 0h10v10H50V45zm35 0h10v10H85V45zM15 80h10v10H15V80zm35 0h10v10H50V80zm35 0h10v10H85V80zM5 25h90v5H5v-5zm0 35h90v5H5v-5zm0 35h90v5H5v-5zM25 5h5v90h-5V5zm35 0h5v90h-5V5zm35 0h5v90h-5V5z' fill='%239ca3af' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E"), 
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239ca3af' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            background-repeat: repeat;
            background-position: center center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        #schedule-area {
            background-color: #ffffff;
            width: 100%;
            margin: 0 auto;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 20px;
            border-radius: 4px;
            padding-bottom: 2px;
        }

        #schedule-area * {
            font-family: 'Sarabun', sans-serif !important;
            box-sizing: border-box;
        }

        .schedule-grid { 
            border-collapse: collapse; 
            width: 100%; 
            margin-top: 10px; 
            table-layout: fixed; 
            border: 2px solid #000 !important; 
        }
        .schedule-grid th, .schedule-grid td { 
            border: 1px solid #000 !important; 
        }
        
        .summary-table-sm { width: 100%; border-collapse: collapse; font-size: 10px; border: 1px solid #000; }
        .summary-table-sm th, .summary-table-sm td { border: 1px solid #000 !important; padding: 4px; }
        .summary-table-sm th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        
        .day-header { text-align: center; vertical-align: middle; font-weight: bold; font-size: 14px; color: #000; background-color: #fff; padding: 5px; }
        .writing-vertical { writing-mode: vertical-rl; text-orientation: mixed; transform: rotate(180deg); display: inline-block; }

        .bg-slate-800 { background-color: #333 !important; color: #fff !important; }
        .bg-slate-200 { background-color: #e5e5e5 !important; color: #000 !important; }
        .bg-slate-100 { background-color: #f5f5f5 !important; color: #000 !important; }
        .bg-slate-50  { background-color: #fafafa !important; }
        .schedule-cell { background-color: #ffffff !important; } 
        
        .schedule-text-code { font-size: 10px; font-weight: bold; }
        .schedule-text-name { font-size: 11px; font-weight: bold; }
        .schedule-text-info { font-size: 10px; }

        /* Media Print Settings */
        @media print {
            @page { size: A4 landscape; margin: 5mm; }
            body { background: white !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .no-print { display: none !important; }
            #schedule-area { box-shadow: none; padding: 0; margin: 0; width: 100%; }
            nav { display: none !important; }
            .min-w-\[1000px\] { min-width: 0 !important; width: 100% !important; }
            .overflow-x-auto { overflow: visible !important; }
        }
        
        .card-premium {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid white;
            border-radius: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .btn-cvc {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white; padding: 8px 24px; border-radius: 50px; font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3); transition: all 0.3s ease;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-cvc:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.4); }
    </style>
</head>
<body>

    <div class="fixed top-0 left-0 right-0 z-50 flex justify-center pt-5 px-4 no-print">
        <nav class="w-full max-w-[85rem] bg-gradient-to-r from-slate-900 via-blue-900 to-slate-900 backdrop-blur-xl rounded-full shadow-[0_8px_30px_rgb(15,23,42,0.4)] border border-slate-700/50 px-6 py-3 flex justify-between items-center transition-all hover:shadow-[0_15px_40px_rgb(15,23,42,0.5)]">
            <a href="index.php" class="flex items-center gap-4 pl-2 group">
                <div class="relative z-10 group-hover:scale-110 transition duration-500">
                    <div class="bg-white/10 rounded-full p-2 backdrop-blur-sm"> 
                        <img src="/images/cvc_logo.png" alt="CVC Logo" class="w-12 h-12 object-contain filter drop-shadow-md">
                    </div>
                </div>
                <div class="leading-tight">
                    <h1 class="text-xl font-serif font-bold text-white tracking-wide group-hover:text-blue-200 transition">
                        CVC <span class="text-cvc-gold">SmartSystem</span>
                    </h1>
                    <p class="text-[10px] text-slate-300 font-sans tracking-widest uppercase font-semibold mt-0.5">ChiangRai Vocational College</p>
                </div>
            </a>
            <div class="flex items-center pr-2 gap-3">
                <a href="index.php" class="text-slate-300 hover:text-white text-sm font-bold transition mr-2 hidden md:block">
                    <i class="fa-solid fa-house mr-1"></i> หน้าแรก
                </a>
                <a href="login.php" class="bg-white text-blue-900 hover:bg-blue-50 px-6 py-2 rounded-full text-sm font-bold transition shadow-lg flex items-center gap-2 transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบ
                </a>
            </div>
        </nav>
    </div>

    <main class="flex-grow max-w-7xl mx-auto px-4 lg:px-8 pt-32 pb-10 w-full relative z-10">
        
        <div class="card-premium p-6 mb-8 flex flex-col md:flex-row gap-4 items-center justify-between no-print">
            <div class="flex items-center gap-3">
                <a href="index.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 w-10 h-10 flex items-center justify-center rounded-full transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="font-bold text-slate-800 text-lg"><?php echo $header_title; ?></h2>
                    <p class="text-xs text-slate-500">เลือกปีการศึกษาและภาคเรียน</p>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-2 items-center">
                <form action="" method="GET" class="flex gap-2">
                    <input type="hidden" name="mode" value="<?php echo $mode; ?>">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <select name="year" class="bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-500 font-bold text-slate-700" onchange="this.form.submit()">
                        <?php 
                        $unique_years = [];
                        if(!empty($available_periods)) {
                            foreach($available_periods as $p) { if(!in_array($p['sch_academic_year'], $unique_years)) $unique_years[] = $p['sch_academic_year']; } 
                        }
                        if (!in_array($selected_year, $unique_years)) $unique_years[] = $selected_year;
                        
                        sort($unique_years); 
                        foreach(array_reverse($unique_years) as $y) echo "<option value='$y' ".($selected_year==$y?'selected':'').">ปี $y</option>"; 
                        ?>
                    </select>
                    <select name="semester" class="bg-slate-50 border border-slate-200 rounded-lg px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-500 font-bold text-slate-700" onchange="this.form.submit()">
                        <?php 
                        foreach($valid_semesters_for_year as $s) {
                            $sel = ($selected_semester == $s) ? 'selected' : '';
                            echo "<option value='$s' $sel>เทอม $s</option>";
                        }
                        ?>
                    </select>
                </form>
                <div class="w-px h-8 bg-slate-200 mx-1"></div>
                <button onclick="window.print()" class="btn-cvc bg-gradient-to-r from-slate-700 to-slate-800 shadow-slate-500/30"><i class="fa-solid fa-print mr-2"></i> พิมพ์</button>
                <button onclick="exportPDF()" class="btn-cvc bg-gradient-to-r from-red-500 to-red-600 shadow-red-500/30"><i class="fa-solid fa-file-pdf mr-2"></i> PDF</button>
            </div>
        </div>

        <div class="overflow-x-auto w-full pb-4">
            
            <div id="schedule-area" class="max-w-[297mm] mx-auto min-h-[210mm] min-w-[1000px]">
                
                <div class="flex flex-row gap-4 mb-4 items-start pt-4">
                    
                    <div class="w-[45%] flex flex-col gap-4">
                        <div class="flex items-center gap-4 border-b border-black pb-4">
                            <img src="/images/cvc_logo.png" class="w-20 h-20 object-contain">
                            <div>
                                <h2 class="text-xl font-bold text-black"><?php echo $head_title_1; ?></h2>
                                <p class="text-sm text-black">งานพัฒนาหลักสูตรการเรียนการสอน</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm p-2">
                            <?php foreach($head_sub_info as $label => $value): ?>
                                <div class="grid grid-cols-3">
                                    <span class="font-bold text-black col-span-1"><?php echo $label; ?></span> 
                                    <span class="font-bold text-black col-span-2">: <?php echo $value; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="w-[55%]">
                        <table class="summary-table-sm w-full">
                            <thead>
                                <tr>
                                    <th class="w-8">ที่</th>
                                    <th class="w-20">รหัสวิชา</th>
                                    <th>ชื่อรายวิชา</th>
                                    <th class="w-8">ท.</th>
                                    <th class="w-8">ป.</th>
                                    <th class="w-8">น.</th>
                                    <th class="w-10">ช.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sum_t = 0; $sum_p = 0; $sum_c = 0; $sum_h = 0; $i = 1;
                                usort($subject_summary, function($a, $b) { return strcmp($a['code'], $b['code']); });
                                foreach($subject_summary as $sub): 
                                    $sum_t += $sub['t']; $sum_p += $sub['p']; $sum_c += $sub['c']; $sum_h += $sub['h'];
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $i++; ?></td>
                                    <td class="text-center font-bold"><?php echo $sub['code']; ?></td>
                                    <td class="truncate max-w-[200px]"><?php echo $sub['name']; ?></td>
                                    <td class="text-center"><?php echo $sub['t']; ?></td>
                                    <td class="text-center"><?php echo $sub['p']; ?></td>
                                    <td class="text-center font-bold"><?php echo $sub['c']; ?></td>
                                    <td class="text-center font-bold"><?php echo $sub['h']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="font-bold bg-gray-100">
                                    <td colspan="3" class="text-right pr-2">รวม</td>
                                    <td class="text-center"><?php echo $sum_t; ?></td>
                                    <td class="text-center"><?php echo $sum_p; ?></td>
                                    <td class="text-center"><?php echo $sum_c; ?></td>
                                    <td class="text-center"><?php echo $sum_h; ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="w-full">
                    <table class="schedule-grid text-[10px] table-fixed w-full">
                        <thead>
                            <tr>
                                <th class="p-1 w-[90px] bg-slate-800 text-white font-bold text-center align-middle">วัน</th>
                                <?php 
                                $counter = 1; 
                                foreach ($time_slots as $slot): 
                                    // ขยายความกว้างช่องพักเที่ยงเป็น 40px
                                    if (strpos($slot['tim_range'], '12:00') === 0): 
                                ?>
                                    <th class="p-1 w-[40px] bg-slate-200 text-black text-center align-middle">
                                        <div class="writing-vertical mx-auto font-bold tracking-widest text-[9px]">พัก</div>
                                    </th>
                                <?php else: ?>
                                    <th class="p-1 bg-slate-100 text-black align-middle border border-black">
                                        <div class="font-bold text-xs text-indigo-800 mb-0.5">
                                            คาบที่ <?php echo $counter++; ?>
                                        </div>
                                        <div class="text-[9px] text-black font-mono inline-block px-1">
                                            <?php echo str_replace(':', '.', substr($slot['tim_range'], 0, 11)); ?>
                                        </div>
                                    </th>
                                <?php endif; endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $days_th = [1=>'จันทร์', 2=>'อังคาร', 3=>'พุธ', 4=>'พฤหัสบดี', 5=>'ศุกร์'];
                            for ($d = 1; $d <= 5; $d++): 
                            ?>
                            <tr>
                                <td class="day-header"><?php echo $days_th[$d]; ?></td>
                                
                                <?php $skip_slots = 0; foreach ($time_slots as $slot): 
                                    if ($skip_slots > 0) { $skip_slots--; continue; } 
                                    $t_id = $slot['tim_id'];
                                    
                                    // ใส่ข้อความ พักกลางวัน ทุกช่องที่เป็นเวลา 12:00
                                    if (strpos($slot['tim_range'], '12:00') === 0) { 
                                        echo '<td class="bg-slate-50 text-black text-center align-middle">';
                                        echo '<div class="writing-vertical mx-auto text-[10px] font-bold">พักกลางวัน</div>';
                                        echo '</td>'; 
                                        continue; 
                                    }
                                    
                                    if (isset($schedule_data[$d][$t_id])) {
                                        $info = $schedule_data[$d][$t_id]['info']; 
                                        $hours = $schedule_data[$d][$t_id]['hours']; 
                                        
                                        $current_year_real = date('Y') + 543;
                                        $stu_lev = max(1, $current_year_real - ($info['cla_year'] ?? $current_year_real) + 1);
                                        $r_no = intval($info['cla_group_no'] ?? 0);
                                        $cls_txt = ($info['cla_name'] ?? '') . ".{$stu_lev}/{$r_no}";

                                        // --- ส่วนตัดคำนำหน้าชื่อครู ---
                                        $display_name = $info['tea_fullname'];
                                        if ($mode !== 'teacher') {
                                            $display_name = preg_replace('/^(นาย|นางสาว|นาง|ว่าที่ร้อยตรี|ว่าที่ร\.ต\.|ดร\.|ผศ\.|รศ\.|ศ\.|อ\.|อาจารย์)\s*/u', '', $display_name);
                                        }

                                        echo "<td class='schedule-cell p-1 align-top h-16 overflow-hidden' colspan='{$hours}'>";
                                        echo "<div class='flex flex-col h-full justify-center items-center gap-0.5 w-full'>";
                                        
                                        echo "<span class='schedule-text-code'>{$info['sub_code']}</span>"; 
                                        
                                        if ($mode == 'room') {
                                            echo "<span class='schedule-text-info'>{$display_name}</span>";
                                            echo "<span class='schedule-text-info font-bold'>{$cls_txt}</span>";
                                        } else {
                                            echo "<span class='schedule-text-info'>{$info['roo_id']}</span>";
                                            if ($mode == 'teacher') {
                                                echo "<span class='schedule-text-info font-bold'>{$cls_txt}</span>";
                                            } else {
                                                echo "<span class='schedule-text-info'>{$display_name}</span>"; 
                                            }
                                        }
                                        echo "</div></td>";
                                        $skip_slots = $hours - 1;
                                    } else { 
                                        echo '<td class="bg-white"></td>'; 
                                    }
                                endforeach; ?>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                    
                    <div class="mt-4 text-center text-[10px] text-slate-400 no-print">
                        เอกสารนี้จัดทำโดยระบบสารสนเทศ วิทยาลัยอาชีวศึกษาเชียงราย (CVC Smart System) | ข้อมูล ณ วันที่ <?php echo date("d/m/Y H:i"); ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function exportPDF() {
            var element = document.getElementById('schedule-area');
            var mode = "<?php echo $mode; ?>";
            var id = "<?php echo $id; ?>";
            var safeId = id.replace(/[^a-zA-Z0-9]/g, '_');
            var filename = 'Schedule_' + mode + '_' + safeId + '.pdf';

            var opt = {
                margin:       [5, 5, 5, 5], 
                filename:     filename,
                image:        { type: 'jpeg', quality: 1.0 }, 
                html2canvas:  { 
                    scale: 4,        
                    useCORS: true, 
                    letterRendering: true,
                    scrollY: 0,
                    // แก้ไข: ป้องกันตัดหน้า
                    windowWidth: 1200
                },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
            };

            document.fonts.ready.then(() => {
                html2pdf().set(opt).from(element).save();
            });
        }
    </script>

</body>
</html>