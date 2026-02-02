<?php
// teacher/student_schedule.php
// เวอร์ชัน: Responsive Scroll + New Layout (เหมือน Admin)
require_once '../config/db.php';
require_once '../includes/auth.php';
checkTeacher();

// --- [FIX ERROR] แก้ปัญหา MAX_JOIN_SIZE บน Hosting ---
try {
    $pdo->exec("SET SQL_BIG_SELECTS=1");
} catch (Exception $e) {}
// -----------------------------------------------------

// ดึงรายชื่อกลุ่มเรียนทั้งหมด
$class_groups = $pdo->query("SELECT * FROM class_groups ORDER BY cla_name ASC")->fetchAll();

// ปีปัจจุบันสำหรับคำนวณชั้นปี
$current_year_real = date('Y') + 543;

// ดึงปีการศึกษาและเทอมที่มีในระบบ
$stmt_periods = $pdo->query("SELECT DISTINCT sch_academic_year, sch_semester FROM schedule ORDER BY sch_academic_year DESC, sch_semester ASC");
$available_periods = $stmt_periods->fetchAll();

if(count($available_periods) > 0) { 
    $default_year = $available_periods[0]['sch_academic_year']; 
    $default_semester = $available_periods[0]['sch_semester']; 
} else { 
    $default_year = $current_year_real; 
    $default_semester = 1; 
}

$selected_year = $_GET['year'] ?? $default_year;
$selected_semester = $_GET['semester'] ?? $default_semester;
$selected_cla_id = $_GET['cla_id'] ?? null;

$time_slots = $pdo->query("SELECT * FROM time_slots ORDER BY tim_start ASC")->fetchAll();

$schedule_data = []; 
$class_info = null;
$class_name_full = "";
$advisor_name = "..................................................";
$subject_summary = [];

// ถ้ามีการเลือกกลุ่มเรียน
if ($selected_cla_id) {
    // 1. ดึงข้อมูลกลุ่มเรียน + ครูที่ปรึกษา
    $stmt_class = $pdo->prepare("SELECT c.*, t.tea_fullname AS advisor_name, m.maj_name 
                                 FROM class_groups c 
                                 LEFT JOIN teachers t ON c.tea_id = t.tea_id 
                                 LEFT JOIN majors m ON c.cla_major_code = m.maj_code
                                 WHERE c.cla_id = ?"); 
    $stmt_class->execute([$selected_cla_id]); 
    $class_info = $stmt_class->fetch();
    
    if ($class_info) {
        $stu_level = max(1, $current_year_real - $class_info['cla_year'] + 1);
        $room_no = intval($class_info['cla_group_no']);
        $class_name_full = "{$class_info['maj_name']} {$class_info['cla_name']}.{$stu_level}/{$room_no}";
        if ($class_info['advisor_name']) $advisor_name = $class_info['advisor_name'];
    }
    
    // 2. ดึงข้อมูลตารางเรียน
    $sql = "SELECT sch.*, t.tea_fullname, s.sub_code, s.sub_name, r.roo_id, r.roo_name 
            FROM schedule sch 
            LEFT JOIN teachers t ON sch.tea_id = t.tea_id 
            JOIN subjects s ON sch.sub_id = s.sub_id 
            JOIN rooms r ON sch.roo_id = r.roo_id 
            WHERE sch.cla_id = ? AND sch.sch_academic_year = ? AND sch.sch_semester = ?";
            
    $stmt = $pdo->prepare($sql); 
    $stmt->execute([$selected_cla_id, $selected_year, $selected_semester]); 
    $rows = $stmt->fetchAll();
    
    foreach ($rows as $row) { 
        $schedule_data[$row['day_id']][$row['tim_id']] = ['info' => $row, 'hours' => $row['sch_hours']]; 
    }

    // 3. ดึงข้อมูลสรุปรายวิชา (สำหรับตารางมุมขวาบน)
    $sql_sum = "SELECT DISTINCT s.sub_id, s.sub_code, s.sub_name, s.sub_th_pr_ot, s.sub_credit, s.sub_hours
                FROM schedule sch
                JOIN subjects s ON sch.sub_id = s.sub_id
                WHERE sch.cla_id = ? AND sch.sch_academic_year = ? AND sch.sch_semester = ?
                ORDER BY s.sub_code ASC";
    $stmt_sum = $pdo->prepare($sql_sum);
    $stmt_sum->execute([$selected_cla_id, $selected_year, $selected_semester]);
    $raw_summary = $stmt_sum->fetchAll();

    foreach($raw_summary as $sub) {
        $tpn = explode('-', $sub['sub_th_pr_ot']);
        $t = isset($tpn[0]) ? intval($tpn[0]) : 0;
        $p = isset($tpn[1]) ? intval($tpn[1]) : 0;
        $c = isset($tpn[2]) ? intval($tpn[2]) : intval($sub['sub_credit']);
        $h = $t + $p; 
        
        $subject_summary[] = [
            'code' => $sub['sub_code'],
            'name' => $sub['sub_name'],
            't' => $t,
            'p' => $p,
            'c' => $c,
            'h' => $h
        ];
    }
}
?>

<?php require_once '../includes/header.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">

<style>
    @media print { 
        @page { size: A4 landscape; margin: 5mm; } 
        body * { visibility: hidden; } 
        #schedule-area, #schedule-area * { visibility: visible; } 
        #schedule-area { position: absolute; left: 0; top: 0; width: 100%; border: none !important; box-shadow: none !important; padding: 0 !important; } 
        .no-print { display: none !important; } 
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        
        /* Reset min-width for print */
        .min-w-\[1000px\] { min-width: 0 !important; width: 100% !important; }
        .overflow-x-auto { overflow: visible !important; }
    }
    
    #schedule-area {
        font-family: 'Sarabun', sans-serif !important; 
        background-color: #ffffff;
        color: #000000;
        /* ไม่ Fix width ที่นี่ แต่จะใช้ min-width ใน wrapper แทน */
        line-height: 1.2;
        padding-bottom: 2px;
        text-rendering: optimizeLegibility;
    }
    #schedule-area * {
        font-family: 'Sarabun', sans-serif !important;
        box-sizing: border-box;
    }

    /* Grid Styles */
    .schedule-grid { border-collapse: collapse; width: 100%; margin-top: 10px; table-layout: fixed; border: 2px solid #000000 !important; }
    .schedule-grid th, .schedule-grid td { border: 1px solid #000000 !important; }
    
    /* Summary Table Styles */
    .summary-table { width: 100%; border-collapse: collapse; font-size: 10px; border: 1px solid #000; }
    .summary-table th, .summary-table td { border: 1px solid #000 !important; padding: 2px 4px; }
    .summary-table th { background-color: #f0f0f0 !important; text-align: center; font-weight: bold; }
    
    .day-header { text-align: center; vertical-align: middle; font-weight: bold; font-size: 14px; color: #000; background-color: #fff; padding: 5px; }
    /* ปรับจากแนวตั้งเป็นแนวนอน เพื่อไม่ให้ข้อความภาษาไทยเพี้ยนใน PDF */
    .writing-vertical { writing-mode: horizontal-tb; text-orientation: mixed; transform: none; display: inline-block; }
    
    .bg-slate-800 { background-color: #333 !important; color: #fff !important; }
    .bg-slate-200 { background-color: #e5e5e5 !important; color: #000 !important; }
    .bg-slate-100 { background-color: #f5f5f5 !important; color: #000 !important; }
    
    .break-cell { overflow: hidden; max-width: 40px; }
    .schedule-cell { background-color: #ffffff !important; vertical-align: top; padding: 2px; } 
    
    /* Font sizes inside grid - ป้องกันชื่อครูเพี้ยน: ใช้ nowrap */
    .text-code { font-size: 11px; font-weight: bold; display: block; text-align: center; }
    .text-room { font-size: 11px; display: block; text-align: center; }
    .text-teacher { font-size: 10px; display: block; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
</style>

<div class="max-w-7xl mx-auto space-y-6 pb-12">
    
    <div class="flex justify-between items-center no-print">
        <a href="index.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-cvc-blue transition font-bold text-sm bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm hover:shadow-md">
            <i class="fa-solid fa-arrow-left"></i> กลับหน้าหลัก
        </a>
        <div class="text-right hidden md:block">
            <h1 class="text-lg font-bold text-slate-700">ค้นหาตารางเรียน</h1>
            <p class="text-xs text-slate-400">สำหรับครูที่ปรึกษา</p>
        </div>
    </div>

    <div class="card-premium p-6 no-print border-l-4 border-l-cvc-blue">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">ปีการศึกษา</label>
                <select name="year" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3 text-sm focus:ring-2 focus:ring-cvc-blue outline-none">
                    <?php 
                    $unique_years = [];
                    foreach($available_periods as $p) {
                        if(!in_array($p['sch_academic_year'], $unique_years)) $unique_years[] = $p['sch_academic_year'];
                    }
                    if(!in_array($selected_year, $unique_years)) $unique_years[] = $selected_year;
                    sort($unique_years);
                    foreach(array_reverse($unique_years) as $y): 
                    ?>
                        <option value="<?php echo $y; ?>" <?php echo $selected_year == $y ? 'selected' : ''; ?>>ปี <?php echo $y; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">ภาคเรียน</label>
                <select name="semester" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3 text-sm focus:ring-2 focus:ring-cvc-blue outline-none">
                    <?php for($s=1; $s<=3; $s++): ?>
                        <option value="<?php echo $s; ?>" <?php echo $selected_semester == $s ? 'selected' : ''; ?>>เทอม <?php echo $s; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">กลุ่มเรียน</label>
                <select name="cla_id" class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2.5 px-3 text-sm focus:ring-2 focus:ring-cvc-blue outline-none font-bold text-cvc-blue">
                    <option value="">-- เลือกกลุ่มเรียน --</option>
                    <?php foreach ($class_groups as $g): 
                        $stu_year = max(1, $current_year_real - $g['cla_year'] + 1);
                        $room_no = intval($g['cla_group_no']);
                        $display_name = $g['cla_name'] . $stu_year . '/' . $room_no;
                    ?>
                        <option value="<?php echo $g['cla_id']; ?>" <?php echo ($selected_cla_id == $g['cla_id']) ? 'selected' : ''; ?>>
                            <?php echo $display_name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-cvc flex-1 h-[42px] text-sm shadow-md"><i class="fa-solid fa-search mr-2"></i> ค้นหา</button>
                <?php if ($selected_cla_id): ?>
                <button type="button" onclick="exportPDF()" class="bg-red-500 text-white px-4 py-2 rounded-full h-[42px] hover:bg-red-600 transition shadow-md"><i class="fa-solid fa-file-pdf"></i></button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if ($selected_cla_id && $class_info): ?>
    
    <div class="overflow-x-auto w-full pb-4">
        
        <div id="schedule-area" class="bg-white p-6 shadow-xl border border-slate-200 min-h-[550px] min-w-[1000px]">
            
            <div class="flex flex-row gap-4 mb-2 items-start pt-2">
                
                <div class="w-[45%] flex flex-col gap-2">
                    <div class="flex items-center gap-3 border-b-2 border-black pb-2 mb-2">
                        <img src="/images/cvc_logo.png" class="w-16 h-16 object-contain">
                        <div>
                            <h2 class="text-xl font-bold text-black leading-tight">วิทยาลัยอาชีวศึกษาเชียงราย</h2>
                            <p class="text-sm text-black">งานพัฒนาหลักสูตรการเรียนการสอน</p>
                        </div>
                    </div>
                    
                    <div class="text-sm space-y-1 pl-2">
                        <div class="grid grid-cols-10">
                            <span class="font-bold text-black col-span-3">ภาคเรียน</span>
                            <span class="text-black col-span-7">: <?php echo "$selected_semester / $selected_year"; ?></span>
                        </div>
                        <div class="grid grid-cols-10">
                            <span class="font-bold text-black col-span-3">ครูที่ปรึกษา</span>
                            <span class="text-black col-span-7 font-bold">: <?php echo $advisor_name; ?></span>
                        </div>
                        <div class="grid grid-cols-10">
                            <span class="font-bold text-black col-span-3">รหัสกลุ่มเรียน</span>
                            <span class="text-black col-span-7">: <?php echo $selected_cla_id; ?></span>
                        </div>
                        <div class="grid grid-cols-10">
                            <span class="font-bold text-black col-span-3">ชื่อกลุ่มเรียน</span>
                            <span class="text-black col-span-7 font-bold">: <?php echo $class_name_full; ?></span>
                        </div>
                    </div>
                </div>

                <div class="w-[55%]">
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th class="w-8">ที่</th>
                                <th class="w-20">รหัสวิชา</th>
                                <th>ชื่อรายวิชา</th>
                                <th class="w-6">ท.</th>
                                <th class="w-6">ป.</th>
                                <th class="w-6">น.</th>
                                <th class="w-6">ช.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1; $sum_t=0; $sum_p=0; $sum_c=0; $sum_h=0;
                            if(count($subject_summary) > 0):
                                foreach($subject_summary as $sub):
                                    $sum_t += $sub['t']; $sum_p += $sub['p']; $sum_c += $sub['c']; $sum_h += $sub['h'];
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $i++; ?></td>
                                <td class="text-center font-bold"><?php echo $sub['code']; ?></td>
                                <td class="truncate max-w-[150px]"><?php echo $sub['name']; ?></td>
                                <td class="text-center"><?php echo $sub['t']; ?></td>
                                <td class="text-center"><?php echo $sub['p']; ?></td>
                                <td class="text-center font-bold"><?php echo $sub['c']; ?></td>
                                <td class="text-center font-bold"><?php echo $sub['h']; ?></td>
                            </tr>
                            <?php endforeach; 
                            else: ?>
                            <tr><td colspan="7" class="text-center p-2">- ไม่มีรายวิชา -</td></tr>
                            <?php endif; ?>
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

            <div class="w-full mt-4">
                <table class="schedule-grid text-[10px] table-fixed w-full">
                    <thead>
                        <tr>
                            <th class="p-1 w-[90px] bg-slate-800 text-white font-bold text-center align-middle">วัน</th>
                            <?php $counter = 1; foreach ($time_slots as $slot): 
                                if (strpos($slot['tim_range'], '12:00') === 0): ?>
                                <th class="p-1 w-[40px] bg-slate-200 text-black text-center align-middle"><div class="writing-vertical mx-auto font-bold text-[10px] leading-tight py-1">พัก</div></th>
                            <?php else: ?>
                                <th class="p-1 bg-slate-100 text-black align-middle border border-black">
                                    <div class="font-bold text-xs text-indigo-800 mb-0.5">คาบที่ <?php echo $counter++; ?></div>
                                    <div class="text-[9px] text-black font-mono inline-block px-1"><?php echo str_replace(':', '.', substr($slot['tim_range'], 0, 11)); ?></div>
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
                                
                                // พักเที่ยง
                                if (strpos($slot['tim_range'], '12:00') === 0) { 
                                    echo '<td class="bg-slate-200 text-black text-center align-middle break-cell"><div class="text-[10px] font-bold leading-tight"><span class="block">พัก</span><span class="block">กลางวัน</span></div></td>'; 
                                    continue; 
                                }
                                
                                if (isset($schedule_data[$d][$t_id])) {
                                    $info = $schedule_data[$d][$t_id]['info']; 
                                    $hours = $schedule_data[$d][$t_id]['hours']; 
                                    
                                    // ลบส่วนที่ error ออกแล้ว

                                    echo "<td class='schedule-cell' colspan='{$hours}'>";
                                    echo "<div class='flex flex-col h-full justify-center items-center gap-0.5 w-full min-w-0'>";
                                    
                                    // 1. รหัสวิชา
                                    echo "<span class='text-code'>{$info['sub_code']}</span>"; 
                                    // 2. รหัสห้อง
                                    echo "<span class='text-room'>{$info['roo_id']}</span>"; 
                                    // 3. ครูผู้สอน
                                    echo "<span class='text-teacher'>" . ($info['tea_fullname'] ? stripThaiPrefix($info['tea_fullname']) : 'รอครูสอน') . "</span>";
                                    
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
            
            <div class="mt-4 text-center text-xs text-black no-print">
                เอกสารนี้จัดทำโดยระบบสารสนเทศ วิทยาลัยอาชีวศึกษาเชียงราย (CVC Smart System)
            </div>
        </div>
    </div>
    
    <script>
        function exportPDF() { 
            var element = document.getElementById('schedule-area'); 
            var filename = 'ตารางเรียน_<?php echo $class_info['cla_name']; ?>.pdf'; 
            
            var opt = { 
                margin: [5,5,5,5], 
                filename: filename, 
                image: { type: 'jpeg', quality: 1 }, 
html2canvas: { 
                scale: 2, 
                useCORS: true,
                letterRendering: true,
                scrollY: 0
            },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' } 
            }; 
            
            document.fonts.ready.then(() => {
                html2pdf().set(opt).from(element).save(); 
            });
        }
    </script>
    
    <?php elseif(isset($_GET['cla_id'])): ?>
        <div class="card-premium p-16 text-center border-dashed border-2 border-slate-300 bg-slate-50/50">
            <i class="fa-regular fa-folder-open text-4xl text-slate-300 mb-4 block"></i>
            <p class="text-slate-500 font-bold text-lg">ไม่พบข้อมูลตารางเรียน</p>
            <p class="text-slate-400 text-sm">กรุณาตรวจสอบปีการศึกษาและเทอมที่เลือก</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>