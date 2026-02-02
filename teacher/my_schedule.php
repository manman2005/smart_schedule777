<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkTeacher();

$tea_id = $_SESSION['user_id'];
$tea_name = $_SESSION['user_name'];

// Period Selector
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
// Query เลือกข้อมูล (sch.* จะมี roo_id อยู่แล้ว)
$sql = "SELECT sch.*, s.sub_code, s.sub_name, r.roo_name, c.cla_name, c.cla_year, c.cla_group_no
        FROM schedule sch
        JOIN subjects s ON sch.sub_id = s.sub_id
        JOIN rooms r ON sch.roo_id = r.roo_id
        JOIN class_groups c ON sch.cla_id = c.cla_id
        WHERE sch.tea_id = ? AND sch.sch_academic_year = ? AND sch.sch_semester = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$tea_id, $selected_year, $selected_semester]);
$rows = $stmt->fetchAll();

foreach ($rows as $row) {
    $schedule_data[$row['day_id']][$row['tim_id']] = [
        'info' => $row,
        'hours' => $row['sch_hours']
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

    .schedule-grid { 
        border-collapse: collapse; 
        width: 100%; 
        margin-top: 10px; 
        table-layout: fixed; 
        border: 2px solid #000000 !important; 
    }
    
    .schedule-grid th, .schedule-grid td { 
        border: 1px solid #000000 !important; 
    }
    .schedule-grid tbody tr { height: 4rem; }
    .schedule-grid tbody td { min-height: 4rem; height: 4rem; vertical-align: middle; }
    
    .day-header { 
        text-align: center; vertical-align: middle; font-weight: bold; font-size: 14px; 
        color: #000; background-color: #fff; padding: 5px;
    }
    
    /* ปรับจากแนวตั้งเป็นแนวนอน เพื่อไม่ให้ข้อความภาษาไทยเพี้ยนใน PDF */
    .writing-vertical { 
        writing-mode: horizontal-tb; text-orientation: mixed; transform: none; display: inline-block;
    }

    .bg-slate-800 { background-color: #333 !important; color: #fff !important; }
    .bg-slate-200 { background-color: #e5e5e5 !important; color: #000 !important; }
    .bg-slate-100 { background-color: #f5f5f5 !important; color: #000 !important; }
    .bg-slate-50  { background-color: #fafafa !important; }
    .break-cell { overflow: hidden; max-width: 40px; }
    .schedule-cell { background-color: #ffffff !important; vertical-align: top; padding: 4px; } 
    
    /* ปรับขนาดตัวอักษร - ป้องกันชื่อครูเพี้ยน: ใช้ nowrap */
    .schedule-text-code { font-size: 11px; font-weight: bold; display: block; text-align: center; }
    .schedule-text-room { font-size: 11px; display: block; text-align: center; }
    .schedule-text-class { font-size: 11px; font-weight: bold; display: block; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
</style>

<div class="max-w-7xl mx-auto space-y-6 pb-12">
    
    <div class="card-premium p-4 flex flex-col md:flex-row justify-between items-center gap-4 no-print">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-cvc-blue">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <h2 class="font-bold text-slate-700">ตารางสอน (Teacher Schedule)</h2>
                <p class="text-xs text-slate-500">จัดการโดย <?php echo htmlspecialchars($tea_name); ?></p>
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
            <a href="index.php" class="bg-white text-slate-500 border border-slate-200 px-4 py-2 rounded-lg hover:bg-slate-50 text-sm font-bold"><i class="fa-solid fa-house"></i></a>
            <button onclick="window.print()" class="bg-cvc-blue text-white px-4 py-2 rounded-lg hover:bg-blue-800 text-sm font-bold shadow-md"><i class="fa-solid fa-print"></i> พิมพ์</button>
            <button onclick="exportPDF()" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 text-sm font-bold shadow-md"><i class="fa-solid fa-file-pdf"></i> PDF</button>
        </div>
    </div>

    <div id="schedule-area" class="bg-white p-6 shadow-xl border border-slate-200 min-h-[550px]">
        <div class="flex items-center justify-between mb-6 border-b border-black pb-4">
            <div class="flex items-center gap-4">
                <img src="/smart_schedule/images/cvc_logo.png" class="w-16 h-16 object-contain">
                <div>
                    <h2 class="text-xl font-bold text-black">วิทยาลัยอาชีวศึกษาเชียงราย</h2>
                    <h3 class="text-lg font-bold text-black">ตารางสอน: <?php echo $tea_name; ?></h3>
                    <p class="text-sm text-black">ภาคเรียนที่ <?php echo $selected_semester; ?> ปีการศึกษา <?php echo $selected_year; ?></p>
                </div>
            </div>
        </div>

        <div class="w-full">
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
                            if (strpos($slot['tim_range'], '12:00') === 0) { echo '<td class="bg-slate-200 text-black text-center align-middle break-cell"><div class="text-[10px] font-bold leading-tight"><span class="block">พัก</span><span class="block">กลางวัน</span></div></td>'; continue; }
                            
                            if (isset($schedule_data[$d][$t_id])) {
                                $info = $schedule_data[$d][$t_id]['info']; 
                                $hours = $schedule_data[$d][$t_id]['hours']; 
                                
                                $current_year_real = date('Y') + 543;
                                $stu_lev = $current_year_real - $info['cla_year'] + 1;
                                $r_no = intval($info['cla_group_no']);
                                $cls_txt = "{$info['cla_name']}.{$stu_lev}/{$r_no}";

                                echo "<td class='schedule-cell' colspan='{$hours}'>";
                                echo "<div class='flex flex-col h-full justify-center items-center gap-0.5 w-full min-w-0'>";
                                
                                // 1. รหัสวิชา
                                echo "<span class='schedule-text-code'>{$info['sub_code']}</span>"; 
                                // 2. รหัสห้อง (roo_id)
                                echo "<span class='schedule-text-room'>{$info['roo_id']}</span>"; 
                                // 3. ชื่อกลุ่มเรียนที่สอน
                                echo "<span class='schedule-text-class'>{$cls_txt}</span>";
                                
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
    
    <script>
        function exportPDF() { 
            var element = document.getElementById('schedule-area'); 
            var filename = 'ตารางสอน_<?php echo $tea_name; ?>.pdf'; 
            
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
</div>
<?php require_once '../includes/footer.php'; ?>