<?php
// admin/view_schedule_master.php
// เวอร์ชัน: Print Friendly + Horizontal Scroll on Mobile + Fix Invisible Text
require_once '../config/db.php';

// --- แก้ไข Error 500 / 1104 (Big Selects) ---
try {
    $pdo->exec("SET SQL_BIG_SELECTS=1");
} catch (PDOException $e) {}
// ------------------------------------------

require_once '../includes/auth.php';
checkAdmin();

// รับค่าโหมดและ ID
$mode = $_GET['mode'] ?? 'class'; 
$id = $_GET['id'] ?? '';

// ดึงข้อมูล Master Data
$classes = $pdo->query("SELECT * FROM class_groups ORDER BY cla_id ASC")->fetchAll();
$teachers = $pdo->query("SELECT * FROM teachers ORDER BY tea_fullname ASC")->fetchAll();
$rooms = $pdo->query("SELECT * FROM rooms ORDER BY roo_id ASC")->fetchAll();

// 1. ดึงปีการศึกษา
$years_stmt = $pdo->query("SELECT DISTINCT sch_academic_year FROM schedule ORDER BY sch_academic_year DESC");
$available_years = $years_stmt->fetchAll(PDO::FETCH_COLUMN);

// 2. ดึงภาคเรียน
$sems_stmt = $pdo->query("SELECT DISTINCT sch_semester FROM schedule ORDER BY sch_semester ASC");
$available_semesters = $sems_stmt->fetchAll(PDO::FETCH_COLUMN);

// กำหนดค่า Default
$default_year = !empty($available_years) ? $available_years[0] : (date('Y') + 543);
$default_semester = !empty($available_semesters) ? $available_semesters[0] : 1;

$selected_year = $_GET['year'] ?? $default_year;
$selected_semester = $_GET['semester'] ?? $default_semester;

// ดึงเวลาเรียน
$time_slots = $pdo->query("SELECT * FROM time_slots ORDER BY tim_start ASC")->fetchAll();
$schedule_data = []; 
$subject_summary = []; 

$head_title_1 = "ตารางเรียน";
$head_sub_info = [];

if(empty($id)) {
    if($mode == 'class' && count($classes) > 0) $id = $classes[0]['cla_id'];
    elseif($mode == 'teacher' && count($teachers) > 0) $id = $teachers[0]['tea_id'];
    elseif($mode == 'room' && count($rooms) > 0) $id = $rooms[0]['roo_id'];
}

if ($id) {
    $sql = "SELECT sch.*, s.sub_code, s.sub_name, s.sub_th_pr_ot, s.sub_credit, 
            t.tea_fullname, r.roo_name, r.roo_id, c.cla_name, c.cla_id, c.cla_year, c.cla_group_no, c.cla_level_code,
            adv.tea_fullname AS advisor_name, m.maj_name
            FROM schedule sch
            JOIN subjects s ON sch.sub_id = s.sub_id
            LEFT JOIN teachers t ON sch.tea_id = t.tea_id
            JOIN rooms r ON sch.roo_id = r.roo_id
            JOIN class_groups c ON sch.cla_id = c.cla_id
            LEFT JOIN teachers adv ON c.tea_id = adv.tea_id
            LEFT JOIN majors m ON c.cla_major_code = m.maj_code
            WHERE sch.sch_academic_year = ? AND sch.sch_semester = ? AND ";

    if ($mode == 'class') {
        $sql .= "sch.cla_id = ?";
    } elseif ($mode == 'teacher') {
        $sql .= "sch.tea_id = ?";
        foreach($teachers as $item) {
            if($item['tea_id'] == $id) {
                $head_title_1 = "ตารางสอน";
                $head_sub_info = ['ชื่อ-สกุล' => $item['tea_fullname']];
            }
        }
    } elseif ($mode == 'room') {
        $sql .= "sch.roo_id = ?";
        foreach($rooms as $item) {
            if($item['roo_id'] == $id) {
                $head_title_1 = "ตารางการใช้ห้องเรียน";
                $head_sub_info = ['ห้อง' => $item['roo_id'], 'ชื่อห้อง' => $item['roo_name']];
            }
        }
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$selected_year, $selected_semester, $id]);
    $rows = $stmt->fetchAll();

    if ($mode == 'class') {
        $stmt_c = $pdo->prepare("SELECT c.*, t.tea_fullname AS advisor_name, m.maj_name 
                                 FROM class_groups c 
                                 LEFT JOIN teachers t ON c.tea_id = t.tea_id 
                                 LEFT JOIN majors m ON c.cla_major_code = m.maj_code
                                 WHERE c.cla_id = ?");
        $stmt_c->execute([$id]);
        $c_info = $stmt_c->fetch();
        
        if($c_info) {
            $current_year_real = date('Y') + 543;
            $stu_year = $current_year_real - $c_info['cla_year'] + 1;
            $room_no = intval($c_info['cla_group_no']);
            $full_class_name = "{$c_info['maj_name']} {$c_info['cla_name']}.{$stu_year}/{$room_no}";
            
            $head_title_1 = "วิทยาลัยอาชีวศึกษาเชียงราย";
            $head_sub_info = [
                'ภาคเรียน' => "$selected_semester/$selected_year",
                'ครูที่ปรึกษา' => $c_info['advisor_name'] ? $c_info['advisor_name'] : "..................................................",
                'รหัสกลุ่มเรียน' => $c_info['cla_id'],
                'ชื่อกลุ่มเรียน' => $full_class_name
            ];
        }
    }

    foreach ($rows as $row) {
        $schedule_data[$row['day_id']][$row['tim_id']] = ['info' => $row, 'hours' => $row['sch_hours']];
        if ($mode == 'class' && !isset($subject_summary[$row['sub_id']])) {
            $tpn = explode('-', $row['sub_th_pr_ot']);
            $t = isset($tpn[0]) ? intval($tpn[0]) : 0;
            $p = isset($tpn[1]) ? intval($tpn[1]) : 0;
            $c = isset($tpn[2]) ? intval($tpn[2]) : intval($row['sub_credit']);
            $subject_summary[$row['sub_id']] = [
                'code' => $row['sub_code'], 'name' => $row['sub_name'], 't' => $t, 'p' => $p, 'c' => $c, 'total_hours' => $t + $p
            ];
        }
    }
}

require_once '../includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    /* CSS สำหรับการพิมพ์ */
    @media print { 
        @page { size: A4 landscape; margin: 5mm; } 
        body * { visibility: hidden; } 
        #schedule-area, #schedule-area * { visibility: visible; } 
        #schedule-area { position: absolute; left: 0; top: 0; width: 100%; border: none !important; box-shadow: none !important; padding: 0 !important; overflow: visible !important; } 
        .no-print { display: none !important; } 
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        
        /* ปิด Scrollbar เวลาปริ้น */
        .overflow-x-auto { overflow: visible !important; }
        .min-w-\[1000px\] { min-width: 0 !important; width: 100% !important; }
    }

    #schedule-area {
        font-family: 'Sarabun', sans-serif !important; 
        background-color: #ffffff;
        color: #000000 !important; /* บังคับสีดำ */
        line-height: 1.2;
        padding-bottom: 2px;
        text-rendering: geometricPrecision; /* ช่วยให้ Render Text ชัดขึ้น */
    }
    #schedule-area * {
        font-family: 'Sarabun', sans-serif !important;
        box-sizing: border-box;
    }

    /* Grid Table Styles */
    .schedule-grid { 
        border-collapse: collapse; 
        width: 100%; 
        margin-top: 10px; 
        table-layout: fixed; 
        border-top: 2px solid #000000 !important; 
        border-left: 2px solid #000000 !important; 
        border-right: 2px solid #000000 !important; 
        border-bottom: 2px solid #000000 !important; 
    }
    
    .schedule-grid th, .schedule-grid td { 
        border: 1px solid #000000 !important; 
    }
    
    .summary-table-sm { width: 100%; border-collapse: collapse; font-size: 10px; border: 1px solid #000; }
    .summary-table-sm th, .summary-table-sm td { border: 1px solid #000 !important; padding: 4px; }
    .summary-table-sm th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
    
    .day-header { 
        text-align: center; 
        vertical-align: middle;
        font-weight: bold; 
        font-size: 14px; 
        color: #000 !important; 
        background-color: #fff; 
        padding: 5px;
    }
    
    .writing-vertical { 
        writing-mode: vertical-rl; 
        text-orientation: mixed; 
        transform: rotate(180deg);
        display: inline-block;
    }

    .bg-slate-800 { background-color: #333 !important; color: #fff !important; }
    .bg-slate-200 { background-color: #e5e5e5 !important; color: #000 !important; }
    .bg-slate-100 { background-color: #f5f5f5 !important; color: #000 !important; }
    .bg-slate-50  { background-color: #fafafa !important; }
    .schedule-cell { background-color: #ffffff !important; } 
    
    /* เพิ่ม color: #000 !important เพื่อบังคับสีดำใน span */
    .schedule-text-code { font-size: 10px; font-weight: bold; color: #000 !important; }
    .schedule-text-name { font-size: 11px; font-weight: bold; color: #000 !important; }
    .schedule-text-info { font-size: 10px; color: #000 !important; }

</style>

<div class="w-full px-4 space-y-6 pb-12">
    
    <div class="flex justify-between items-center mb-4 no-print mt-4">
        <div class="flex items-center gap-2">
           <a href="index.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2">
            <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
        </a>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-cvc-blue text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-blue-800 transition"><i class="fa-solid fa-print mr-1"></i> พิมพ์ (Browser)</button>
            <button onclick="exportPDF()" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-red-600 transition"><i class="fa-solid fa-file-pdf mr-1"></i> ดาวน์โหลด PDF</button>
        </div>
    </div>

    <div class="card-premium p-6 no-print border-l-4 border-l-cvc-blue mb-6">
        <div class="flex space-x-1 bg-slate-100 p-1 rounded-xl w-fit mb-4 mx-auto md:mx-0">
            <a href="?mode=class" class="px-6 py-2 rounded-lg text-sm font-bold transition <?php echo $mode == 'class' ? 'bg-white text-cvc-blue shadow-sm' : 'text-slate-500 hover:text-slate-700'; ?>">กลุ่มเรียน</a>
            <a href="?mode=teacher" class="px-6 py-2 rounded-lg text-sm font-bold transition <?php echo $mode == 'teacher' ? 'bg-white text-cvc-blue shadow-sm' : 'text-slate-500 hover:text-slate-700'; ?>">ครูผู้สอน</a>
            <a href="?mode=room" class="px-6 py-2 rounded-lg text-sm font-bold transition <?php echo $mode == 'room' ? 'bg-white text-cvc-blue shadow-sm' : 'text-slate-500 hover:text-slate-700'; ?>">ห้องเรียน</a>
        </div>
        
        <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <input type="hidden" name="mode" value="<?php echo $mode; ?>">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">ปีการศึกษา</label>
                <select name="year" class="w-full text-sm" onchange="this.form.submit()">
                    <?php if (!empty($available_years)) { foreach($available_years as $y) { $sel = ($selected_year == $y) ? 'selected' : ''; echo "<option value='$y' $sel>$y</option>"; } } else { echo "<option value='$selected_year'>$selected_year</option>"; } ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">ภาคเรียน</label>
                <select name="semester" class="w-full text-sm" onchange="this.form.submit()">
                    <?php if (!empty($available_semesters)) { foreach($available_semesters as $s) { $sel = ($selected_semester == $s) ? 'selected' : ''; echo "<option value='$s' $sel>เทอม $s</option>"; } } else { echo "<option value='1'>เทอม 1</option>"; } ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">เลือกเป้าหมาย</label>
                <select name="id" class="w-full text-sm font-bold text-cvc-blue" onchange="this.form.submit()">
                    <?php 
                    if ($mode == 'class') foreach ($classes as $c) {
                        $current_year_real = date('Y') + 543;
                        $stu_year = $current_year_real - $c['cla_year'] + 1;
                        echo "<option value='{$c['cla_id']}' ".($id==$c['cla_id']?'selected':'').">{$c['cla_name']}{$stu_year}/".intval($c['cla_group_no'])."</option>";
                    }
                    elseif ($mode == 'teacher') foreach ($teachers as $t) echo "<option value='{$t['tea_id']}' ".($id==$t['tea_id']?'selected':'').">{$t['tea_fullname']}</option>";
                    elseif ($mode == 'room') foreach ($rooms as $r) echo "<option value='{$r['roo_id']}' ".($id==$r['roo_id']?'selected':'').">{$r['roo_name']}</option>";
                    ?>
                </select>
            </div>
            <button type="submit" class="btn-cvc w-full justify-center text-sm">แสดงข้อมูล</button>
        </form>
    </div>

    <div class="overflow-x-auto w-full pb-4"> 
        <div id="schedule-area" class="bg-white p-6 shadow-xl min-h-[800px] border border-slate-200 min-w-[1000px]"> <?php if ($mode == 'class'): ?>
                <div class="flex flex-row gap-4 mb-4 items-start">
                    <div class="w-[40%] flex flex-col gap-4">
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
                    <div class="w-[60%]">
                        <table class="summary-table-sm w-full">
                            <thead>
                                <tr>
                                    <th class="w-8 text-black">ที่</th>
                                    <th class="w-20 text-black">รหัสวิชา</th>
                                    <th class="text-black">ชื่อรายวิชา</th>
                                    <th class="w-8 text-black">ท.</th>
                                    <th class="w-8 text-black">ป.</th>
                                    <th class="w-8 text-black">น.</th>
                                    <th class="w-10 text-black">ช.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sum_t = 0; $sum_p = 0; $sum_c = 0; $sum_h = 0; $i = 1;
                                usort($subject_summary, function($a, $b) { return strcmp($a['code'], $b['code']); });
                                foreach($subject_summary as $sub): 
                                    $sum_t += $sub['t']; $sum_p += $sub['p']; $sum_c += $sub['c']; $sum_h += $sub['total_hours'];
                                ?>
                                <tr>
                                    <td class="text-center text-black"><?php echo $i++; ?></td>
                                    <td class="text-center font-bold text-black"><?php echo $sub['code']; ?></td>
                                    <td class="truncate max-w-[200px] text-black"><?php echo $sub['name']; ?></td>
                                    <td class="text-center text-black"><?php echo $sub['t']; ?></td>
                                    <td class="text-center text-black"><?php echo $sub['p']; ?></td>
                                    <td class="text-center font-bold text-black"><?php echo $sub['c']; ?></td>
                                    <td class="text-center font-bold text-black"><?php echo $sub['total_hours']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="font-bold bg-gray-100">
                                    <td colspan="3" class="text-right pr-2 text-black">รวม</td>
                                    <td class="text-center text-black"><?php echo $sum_t; ?></td>
                                    <td class="text-center text-black"><?php echo $sum_p; ?></td>
                                    <td class="text-center text-black"><?php echo $sum_c; ?></td>
                                    <td class="text-center text-black"><?php echo $sum_h; ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex items-center justify-between mb-6 border-b border-black pb-4">
                    <div class="flex items-center gap-4">
                        <img src="/images/cvc_logo.png" class="w-14 h-14 object-contain">
                        <div>
                            <h2 class="text-xl font-bold text-black">วิทยาลัยอาชีวศึกษาเชียงราย</h2>
                            <h3 class="text-lg font-bold text-black"><?php echo $head_title_1; ?></h3>
                            <p class="text-sm text-black">ภาคเรียนที่ <?php echo $selected_semester; ?> ปีการศึกษา <?php echo $selected_year; ?></p>
                        </div>
                    </div>
                    <div class="text-right text-sm">
                        <?php foreach($head_sub_info as $label => $value): ?>
                            <div class="mb-1"><span class="font-bold text-black"><?php echo $label; ?>:</span> <span class="font-bold text-black text-lg ml-2"><?php echo $value; ?></span></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

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
                                    <div class="writing-vertical mx-auto font-bold tracking-widest text-[9px] text-black">พัก</div>
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
                                    echo '<div class="writing-vertical mx-auto text-[10px] font-bold text-black">พักกลางวัน</div>';
                                    echo '</td>'; 
                                    continue; 
                                }
                                
                                if (isset($schedule_data[$d][$t_id])) {
                                    $info = $schedule_data[$d][$t_id]['info']; 
                                    $hours = $schedule_data[$d][$t_id]['hours']; 
                                    
                                    $current_year_real = date('Y') + 543;
                                    $stu_lev = $current_year_real - $info['cla_year'] + 1;
                                    $r_no = intval($info['cla_group_no']);
                                    $cls_txt = "{$info['cla_name']}.{$stu_lev}/{$r_no}";

                                    echo "<td class='schedule-cell p-1 align-top h-16 overflow-hidden' colspan='{$hours}'>";
                                    echo "<div class='flex flex-col h-full justify-center items-center gap-0.5 w-full'>";
                                    
                                    // *** จุดแก้ไขสำคัญ: เพิ่ม class text-black ในทุก span ***
                                    echo "<span class='schedule-text-code text-black'>{$info['sub_code']}</span>"; 
                                    
                                    if ($mode == 'room') {
                                        echo "<span class='schedule-text-info text-black'>{$info['tea_fullname']}</span>";
                                        echo "<span class='schedule-text-info font-bold text-black'>{$cls_txt}</span>";
                                    } else {
                                        echo "<span class='schedule-text-info text-black'>{$info['roo_id']}</span>";
                                        if ($mode == 'teacher') {
                                            echo "<span class='schedule-text-info font-bold text-black'>{$cls_txt}</span>";
                                        } else {
                                            echo "<span class='schedule-text-info text-black'>{$info['tea_fullname']}</span>"; 
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
            </div>
        </div>
    </div> </div>

<script>
    function exportPDF() {
        var element = document.getElementById('schedule-area');
        var mode = "<?php echo $mode; ?>";
        var id = "<?php echo $id; ?>";
        var filename = 'Schedule_' + mode + '_' + id + '.pdf';

        var opt = {
            margin:       [5, 5, 5, 5], 
            filename:     filename,
            image:        { type: 'jpeg', quality: 1.0 }, 
            html2canvas:  { 
                scale: 4,        
                useCORS: true, 
                letterRendering: true,
                scrollY: 0,
                // ป้องกันปัญหาตัดหน้าในบาง browser
                windowWidth: 1200 
            },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
        };

        // สั่งพิมพ์ทันทีที่ฟอนต์โหลดเสร็จ เพื่อกันฟอนต์เพี้ยน
        document.fonts.ready.then(() => {
            html2pdf().set(opt).from(element).save();
        });
    }
</script>

<?php require_once '../includes/footer.php'; ?>