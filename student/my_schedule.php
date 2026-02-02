<?php
// student/my_schedule.php
// แก้ไขล่าสุด: เพิ่ม letterRendering: true เพื่อแก้ปัญหาสระลอย และจัด filename ให้ปลอดภัย

require_once '../config/db.php';
require_once '../includes/auth.php';
checkStudent();

$stu_id = $_SESSION['user_id'];

// 1. ดึงข้อมูลนักเรียน + กลุ่มเรียน + ครูที่ปรึกษา
$stmt = $pdo->prepare("SELECT s.*, c.cla_id, c.cla_name, c.cla_year, c.cla_group_no, 
                              t.tea_fullname AS advisor_name, m.maj_name
                       FROM students s 
                       JOIN class_groups c ON s.cla_id = c.cla_id 
                       LEFT JOIN teachers t ON c.tea_id = t.tea_id
                       LEFT JOIN majors m ON c.cla_major_code = m.maj_code
                       WHERE s.stu_id = ?");
$stmt->execute([$stu_id]);
$student = $stmt->fetch();
$cla_id = $student['cla_id'];

// สร้างชื่อกลุ่มเรียนเต็มรูปแบบ
$current_year_real = date('Y') + 543;
$stu_level = $current_year_real - $student['cla_year'] + 1;
$room_no = intval($student['cla_group_no']);
$cla_name_full = "{$student['maj_name']} {$student['cla_name']}.{$stu_level}/{$room_no}";
$advisor_name = $student['advisor_name'] ?? "..................................................";

// Period Selector Logic
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
$selected_semester = $_GET['semester'] ?? $default_semester;

$time_slots = $pdo->query("SELECT * FROM time_slots ORDER BY tim_start ASC")->fetchAll();

$schedule_data = [];
// 2. ดึงข้อมูลตารางเรียน
$sql = "SELECT sch.*, s.sub_code, s.sub_name, t.tea_fullname, r.roo_id, r.roo_name
        FROM schedule sch
        JOIN subjects s ON sch.sub_id = s.sub_id
        LEFT JOIN teachers t ON sch.tea_id = t.tea_id
        JOIN rooms r ON sch.roo_id = r.roo_id
        WHERE sch.cla_id = ? AND sch.sch_academic_year = ? AND sch.sch_semester = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$cla_id, $selected_year, $selected_semester]);
$rows = $stmt->fetchAll();

foreach ($rows as $row) {
    $schedule_data[$row['day_id']][$row['tim_id']] = [
        'info' => $row,
        'hours' => $row['sch_hours']
    ];
}

// 3. ดึงข้อมูลสรุปรายวิชา (สำหรับตารางมุมขวา)
$subject_summary = [];
$sql_sum = "SELECT DISTINCT s.sub_id, s.sub_code, s.sub_name, s.sub_th_pr_ot, s.sub_credit
            FROM schedule sch
            JOIN subjects s ON sch.sub_id = s.sub_id
            WHERE sch.cla_id = ? AND sch.sch_academic_year = ? AND sch.sch_semester = ?
            ORDER BY s.sub_code ASC";
$stmt_sum = $pdo->prepare($sql_sum);
$stmt_sum->execute([$cla_id, $selected_year, $selected_semester]);
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
        't' => $t, 'p' => $p, 'c' => $c, 'h' => $h
    ];
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
    }
    
    #schedule-area {
        font-family: 'Sarabun', sans-serif !important; 
        background-color: #ffffff;
        color: #000000;
        width: 100%;
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
            <h1 class="text-lg font-bold text-slate-700">ตารางเรียนของฉัน</h1>
            <p class="text-xs text-slate-400"><?php echo htmlspecialchars($student['stu_fullname']); ?></p>
        </div>
    </div>

    <div class="card-premium p-4 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div>
                <h2 class="font-bold text-slate-700">ปีการศึกษา / ภาคเรียน</h2>
                <p class="text-xs text-slate-500">เลือกช่วงเวลาที่ต้องการดูตาราง</p>
            </div>
        </div>
        
        <div class="flex gap-2 flex-wrap justify-center">
            <form method="GET" class="flex gap-2">
                <select name="year" class="bg-slate-50 border border-slate-200 text-slate-600 text-sm rounded-lg focus:ring-cvc-blue outline-none" onchange="this.form.submit()">
                    <?php 
                    $unique_years = [];
                    foreach($available_periods as $p) { if(!in_array($p['sch_academic_year'], $unique_years)) $unique_years[] = $p['sch_academic_year']; }
                    if (!in_array($selected_year, $unique_years)) $unique_years[] = $selected_year;
                    sort($unique_years);
                    foreach(array_reverse($unique_years) as $y): ?>
                        <option value="<?php echo $y; ?>" <?php echo $selected_year == $y ? 'selected' : ''; ?>>ปี <?php echo $y; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="semester" class="bg-slate-50 border border-slate-200 text-slate-600 text-sm rounded-lg focus:ring-cvc-blue outline-none" onchange="this.form.submit()">
                    <?php for($s = 1; $s <= 3; $s++): ?>
                        <option value="<?php echo $s; ?>" <?php echo $selected_semester == $s ? 'selected' : ''; ?>>เทอม <?php echo $s; ?></option>
                    <?php endfor; ?>
                </select>
            </form>
            <div class="h-9 w-px bg-slate-200 mx-1 hidden md:block"></div>
            <button onclick="window.print()" class="bg-cvc-blue text-white px-4 py-2 rounded-lg hover:bg-blue-800 text-sm font-bold shadow-md"><i class="fa-solid fa-print"></i></button>
            <button onclick="exportPDF()" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 text-sm font-bold shadow-md"><i class="fa-solid fa-file-pdf"></i></button>
        </div>
    </div>

    <div class="w-full flex justify-center">
        <div id="schedule-area" class="bg-white p-6 shadow-xl border border-slate-200 min-h-[550px]">
            
            <div class="flex flex-row gap-4 mb-2 items-start">
                
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
                            <span class="text-black col-span-7">: <?php echo $cla_id; ?></span>
                        </div>
                        <div class="grid grid-cols-10">
                            <span class="font-bold text-black col-span-3">ชื่อกลุ่มเรียน</span>
                            <span class="text-black col-span-7 font-bold">: <?php echo $cla_name_full; ?></span>
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
                                    
                                    echo "<td class='schedule-cell' colspan='{$hours}'>";
                                    echo "<div class='flex flex-col h-full justify-center items-center gap-0.5 w-full min-w-0'>";
                                    
                                    // 3. ข้อมูลในตาราง (รหัสวิชา, รหัสห้อง, ครูผู้สอน)
                                    echo "<span class='text-code'>{$info['sub_code']}</span>"; 
                                    echo "<span class='text-room'>{$info['roo_id']}</span>"; 
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
            const element = document.getElementById('schedule-area'); 
            
            // แทนที่เครื่องหมาย / ด้วย - เพื่อให้ตั้งชื่อไฟล์ได้ (แก้ Bug filename)
            const filename = 'ตารางเรียน_<?php echo str_replace(['/', '\\'], '-', $cla_name_full); ?>.pdf'; 
            
            const fullWidth  = element.scrollWidth  || 1600;
            const fullHeight = element.scrollHeight || 900;

            const opt = { 
                margin: [5,5,5,5], 
                filename: filename, 
                image: { type: 'jpeg', quality: 1 }, 
                html2canvas: { 
                    scale: 1.8, 
                    useCORS: true,
                    letterRendering: true,
                    scrollY: 0,
                    windowWidth: fullWidth,
                    windowHeight: fullHeight
                }, 
                // ใช้หน้ากระดาษ A3 แนวนอนเพื่อให้เห็นตารางครบทุกช่อง จากนั้นผู้ใช้สามารถสั่งพิมพ์ย่อเป็น A4 ได้
                jsPDF: { unit: 'mm', format: 'a3', orientation: 'landscape' } 
            }; 
            
            // รอให้ฟอนต์โหลดเสร็จก่อนค่อย Generate (สำคัญมากสำหรับ Sarabun)
            document.fonts.ready.then(() => {
                html2pdf().set(opt).from(element).save(); 
            });
        }
    </script>
</div>
<?php require_once '../includes/footer.php'; ?>