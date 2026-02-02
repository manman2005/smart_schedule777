<?php
// htdocs/index.php
// --- 1. เปิดแสดง Error ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/db.php';

// ตรวจสอบ DB
if (!isset($pdo)) {
    die("<div style='color:red; text-align:center; padding:50px;'>Error: Database Connection Failed.</div>");
}

try {
    // ดึงข้อมูลกลุ่มเรียน
    $classes = $pdo->query("SELECT * FROM class_groups ORDER BY cla_id ASC")->fetchAll();
    $teachers = $pdo->query("SELECT * FROM teachers ORDER BY tea_fullname ASC")->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// กำหนดปีปัจจุบัน
$current_year_real = date('Y') + 543; 
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบบริการการศึกษา - วิทยาลัยอาชีวศึกษาเชียงราย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Prompt:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Sarabun', 'sans-serif'], serif: ['Playfair Display', 'serif'] },
                    // ปรับ Palette สีใหม่: เปลี่ยน Blue เป็น Red tone
                    colors: { 
                        cvc: { 
                            blue: '#b91c1c',  // Primary Red (Red-700)
                            sky: '#f87171',   // Lighter Red (Red-400)
                            navy: '#450a0a',  // Dark Maroon (Red-950)
                            gold: '#fbbf24'   // Gold (Keep same)
                        } 
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #f8fafc; font-family: 'Sarabun', sans-serif; }
        .hero-image {
            /* เปลี่ยน Gradient พื้นหลังเป็น แดง -> แดงเลือดหมูเข้ม */
            background-image: linear-gradient(to right bottom, rgba(185, 28, 28, 0.9), rgba(69, 10, 10, 0.95)), 
                              url('https://images.unsplash.com/photo-1562774053-701939374585?q=80&w=2586&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }
        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; border: 2px solid #fff; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <div class="fixed top-0 left-0 right-0 z-50 flex justify-center pt-5 px-4 no-print">
        <nav class="w-full max-w-[85rem] bg-gradient-to-r from-red-900 via-red-700 to-red-950 backdrop-blur-xl rounded-full shadow-[0_8px_30px_rgb(127,29,29,0.4)] border border-red-800/50 px-6 py-3 flex justify-between items-center transition-all hover:shadow-[0_15px_40px_rgb(127,29,29,0.6)]">
            <a href="index.php" class="flex items-center gap-4 pl-2 group">
                <div class="relative z-10 group-hover:scale-110 transition duration-500">
                    <div class="bg-white/10 rounded-full p-2 backdrop-blur-sm"> 
                        <img src="/images/cvc_logo.png" alt="CVC Logo" class="w-12 h-12 object-contain filter drop-shadow-md">
                    </div>
                </div>
                <div class="leading-tight">
                    <h1 class="text-xl font-serif font-bold text-white tracking-wide group-hover:text-red-200 transition">
                        CVC <span class="text-cvc-gold">SmartSystem</span>
                    </h1>
                    <p class="text-[10px] text-red-100/80 font-sans tracking-widest uppercase font-semibold mt-0.5">ChiangRai Vocational College</p>
                </div>
            </a>
            <div class="flex items-center pr-2 gap-3">
                <a href="login.php" class="bg-white text-red-900 hover:bg-red-50 px-6 py-2 rounded-full text-sm font-bold transition shadow-lg flex items-center gap-2 transform hover:-translate-y-0.5 border border-red-100">
                    <i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบ
                </a>
            </div>
        </nav>
    </div>

    <div class="flex-grow flex flex-col items-center justify-center relative min-h-[600px]">
        
        <div class="absolute inset-0 overflow-hidden hero-image">
            <div class="absolute top-0 left-0 w-96 h-96 bg-red-600 rounded-full blur-[120px] opacity-30 -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-cvc-gold rounded-full blur-[120px] opacity-20 translate-x-1/2 translate-y-1/2"></div>
        </div>

        <div class="relative z-10 w-full px-4 text-center mt-24 pb-12">
            
            <div class="max-w-4xl mx-auto">
                <div class="inline-block mb-4 px-4 py-1 rounded-full bg-red-900/40 border border-red-400/30 text-red-100 text-xs font-bold uppercase tracking-widest backdrop-blur-md shadow-lg">
                    Academic Service Platform
                </div>
                <h1 class="text-5xl md:text-7xl font-serif font-bold mb-6 leading-tight text-transparent bg-clip-text bg-gradient-to-b from-white to-red-100 drop-shadow-md">
                    ระบบบริหารจัดการ<br>ตารางเรียนออนไลน์
                </h1>
                <p class="text-lg text-red-50 mb-12 font-light max-w-2xl mx-auto drop-shadow">
                    ยกระดับการศึกษาด้วยเทคโนโลยีที่ทันสมัย สะดวก รวดเร็ว เชื่อมโยงข้อมูลตารางเรียนและตารางสอนสำหรับนักเรียนและบุคลากร
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full text-left">
                    
                    <div class="relative group rounded-[32px] p-1 bg-gradient-to-br from-red-400 via-red-600 to-red-900 shadow-2xl hover:shadow-red-600/50 transition-all duration-300 hover:-translate-y-2 z-10 hover:z-50">
                        <div class="bg-white rounded-[28px] h-full relative">
                            <div class="absolute inset-0 overflow-hidden rounded-[28px] pointer-events-none">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-full blur-3xl -mr-10 -mt-10 transition group-hover:bg-red-100"></div>
                            </div>

                            <div class="relative z-10 p-8 flex flex-col h-full">
                                <div class="flex items-center gap-5 mb-8">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-red-500 to-red-800 text-white flex items-center justify-center text-3xl shadow-lg shadow-red-500/30">
                                        <i class="fa-solid fa-user-graduate"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-slate-800">สำหรับนักเรียน</h3>
                                        <p class="text-sm text-slate-500">Student Schedule</p>
                                    </div>
                                </div>
                                
                                <div class="space-y-3 relative">
                                    <label class="text-xs font-bold text-red-700 uppercase tracking-wider ml-1">ค้นหาตารางเรียน</label>
                                    <form action="public_schedule.php" method="GET" class="relative" id="form-student">
                                        <input type="hidden" name="mode" value="class">
                                        <input type="hidden" name="id" id="student_id_input">
                                        
                                        <div class="relative group/input">
                                            <input type="text" 
                                                id="student_search"
                                                class="w-full bg-slate-50 border-2 border-slate-100 hover:border-red-300 rounded-2xl px-5 py-4 pl-12 text-base focus:ring-4 focus:ring-red-100 focus:border-red-500 outline-none text-slate-700 font-bold transition shadow-sm placeholder:font-normal"
                                                placeholder="พิมพ์ชื่อกลุ่ม หรือรหัสกลุ่ม..."
                                                autocomplete="off">
                                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/input:text-red-500 transition-colors">
                                                <i class="fa-solid fa-magnifying-glass text-lg"></i>
                                            </div>
                                            <div class="absolute right-4 top-1/2 -translate-y-1/2">
                                                <div id="student_spinner" class="hidden w-5 h-5 border-2 border-red-500 border-t-transparent rounded-full animate-spin"></div>
                                            </div>
                                        </div>

                                        <div id="student_dropdown" class="hidden absolute bottom-full left-0 right-0 mb-3 bg-white rounded-[24px] shadow-[0_-10px_40px_-10px_rgba(0,0,0,0.2)] border border-slate-100 max-h-80 overflow-y-auto custom-scrollbar z-[100] p-2">
                                            </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative group rounded-[32px] p-1 bg-gradient-to-br from-yellow-300 via-amber-500 to-orange-600 shadow-2xl hover:shadow-amber-500/50 transition-all duration-300 hover:-translate-y-2 z-10 hover:z-50">
                        <div class="bg-white rounded-[28px] h-full relative">
                            <div class="absolute inset-0 overflow-hidden rounded-[28px] pointer-events-none">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-full blur-3xl -mr-10 -mt-10 transition group-hover:bg-amber-100"></div>
                            </div>
                            
                            <div class="relative z-10 p-8 flex flex-col h-full">
                                <div class="flex items-center gap-5 mb-8">
                                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-600 text-white flex items-center justify-center text-3xl shadow-lg shadow-amber-500/30">
                                        <i class="fa-solid fa-chalkboard-user"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-slate-800">สำหรับครูผู้สอน</h3>
                                        <p class="text-sm text-slate-500">Teacher Schedule</p>
                                    </div>
                                </div>
                                
                                <div class="space-y-3 relative">
                                    <label class="text-xs font-bold text-amber-600 uppercase tracking-wider ml-1">ค้นหาตารางสอน</label>
                                    <form action="public_schedule.php" method="GET" class="relative" id="form-teacher">
                                        <input type="hidden" name="mode" value="teacher">
                                        <input type="hidden" name="id" id="teacher_id_input">
                                        
                                        <div class="relative group/input">
                                            <input type="text" 
                                                id="teacher_search"
                                                class="w-full bg-slate-50 border-2 border-slate-100 hover:border-amber-300 rounded-2xl px-5 py-4 pl-12 text-base focus:ring-4 focus:ring-amber-100 focus:border-amber-500 outline-none text-slate-700 font-bold transition shadow-sm placeholder:font-normal"
                                                placeholder="พิมพ์ชื่ออาจารย์ หรือรหัส..."
                                                autocomplete="off">
                                            <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/input:text-amber-500 transition-colors">
                                                <i class="fa-solid fa-magnifying-glass text-lg"></i>
                                            </div>
                                            <div class="absolute right-4 top-1/2 -translate-y-1/2">
                                                <div id="teacher_spinner" class="hidden w-5 h-5 border-2 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
                                            </div>
                                        </div>

                                        <div id="teacher_dropdown" class="hidden absolute bottom-full left-0 right-0 mb-3 bg-white rounded-[24px] shadow-[0_-10px_40px_-10px_rgba(0,0,0,0.2)] border border-slate-100 max-h-80 overflow-y-auto custom-scrollbar z-[100] p-2">
                                            </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        // 1. เตรียมข้อมูล
        const studentsData = [
            <?php foreach($classes as $c): 
                $stu_year = $current_year_real - $c['cla_year'] + 1;
                if ($stu_year < 1) $stu_year = 1;
                $room_no = intval($c['cla_group_no']);
                $display_name = $c['cla_name'] . "." . $stu_year . "/" . $room_no;
            ?>
            { 
                id: "<?php echo $c['cla_id']; ?>", 
                text: "<?php echo $display_name; ?>",
                subtext: "รหัสกลุ่ม: <?php echo $c['cla_id']; ?>",
                search: "<?php echo $c['cla_name'] . ' ' . $c['cla_id'] . ' ' . $display_name; ?>" 
            },
            <?php endforeach; ?>
        ];

        const teachersData = [
            <?php foreach($teachers as $t): ?>
            { 
                id: "<?php echo $t['tea_id']; ?>", 
                text: "<?php echo $t['tea_fullname']; ?>",
                subtext: "รหัส: <?php echo $t['tea_code']; ?>",
                search: "<?php echo $t['tea_fullname'] . ' ' . $t['tea_code'] . ' ' . $t['tea_username']; ?>" 
            },
            <?php endforeach; ?>
        ];

        // 2. ฟังก์ชันจัดการค้นหา
        function setupSearch(inputId, dropdownId, hiddenId, spinnerId, dataList, colorClass) {
            const input = document.getElementById(inputId);
            const dropdown = document.getElementById(dropdownId);
            const hidden = document.getElementById(hiddenId);
            const spinner = document.getElementById(spinnerId);
            
            input.addEventListener('input', function() {
                const val = this.value.toLowerCase().trim();
                dropdown.innerHTML = '';
                
                if (!val) {
                    dropdown.classList.add('hidden');
                    return;
                }

                spinner.classList.remove('hidden');

                setTimeout(() => {
                    const filtered = dataList.filter(item => item.search.toLowerCase().includes(val));

                    if (filtered.length === 0) {
                        dropdown.innerHTML = `
                            <div class="p-6 text-center text-slate-400">
                                <i class="fa-regular fa-face-frown text-3xl mb-2 block opacity-50"></i>
                                <span class="text-sm font-medium">ไม่พบข้อมูล</span>
                            </div>`;
                    } else {
                        filtered.forEach(item => {
                            const div = document.createElement('div');
                            // ปรับ Hover effect ให้เป็นสีแดง (เมื่อ colorClass = 'red')
                            div.className = `p-4 mb-1 cursor-pointer rounded-xl transition-all duration-200 group flex justify-between items-center hover:bg-${colorClass}-50`;
                            
                            div.innerHTML = `
                                <div>
                                    <div class="font-bold text-slate-700 text-lg group-hover:text-${colorClass}-700 leading-tight">${item.text}</div>
                                    <div class="text-xs text-slate-400 font-mono group-hover:text-${colorClass}-500 mt-1">${item.subtext}</div>
                                </div>
                                <div class="w-8 h-8 rounded-full flex items-center justify-center bg-white border border-slate-100 shadow-sm opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all text-${colorClass}-500">
                                    <i class="fa-solid fa-chevron-right text-xs"></i>
                                </div>
                            `;
                            
                            div.onclick = () => {
                                input.value = item.text; 
                                hidden.value = item.id; 
                                dropdown.classList.add('hidden');
                                input.closest('form').submit(); 
                            };
                            dropdown.appendChild(div);
                        });
                    }
                    
                    spinner.classList.add('hidden');
                    dropdown.classList.remove('hidden');
                }, 50); 
            });

            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }

        // 3. เริ่มทำงาน
        // เปลี่ยนพารามิเตอร์ student เป็น 'red'
        setupSearch('student_search', 'student_dropdown', 'student_id_input', 'student_spinner', studentsData, 'red');
        setupSearch('teacher_search', 'teacher_dropdown', 'teacher_id_input', 'teacher_spinner', teachersData, 'amber');

    </script>

</body>
</html>