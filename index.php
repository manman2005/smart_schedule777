<?php
// htdocs/index.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/db.php';

if (!isset($pdo)) {
    die("<div style='color:red; text-align:center; padding:50px;'>Error: Database Connection Failed.</div>");
}

try {
    $classes = $pdo->query("SELECT * FROM class_groups ORDER BY cla_id ASC")->fetchAll();
    $teachers = $pdo->query("SELECT * FROM teachers ORDER BY tea_fullname ASC")->fetchAll();
}
catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$current_year_real = date('Y') + 543;

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CVC Smart System - วิทยาลัยอาชีวศึกษาเชียงราย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800;900&family=Sarabun:wght@300;400;500;600;700&family=Prompt:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f3f4f6;
            background-image:
                url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M15 10h10v10H15V10zm35 0h10v10H50V10zm35 0h10v10H85V10zM15 45h10v10H15V45zm35 0h10v10H50V45zm35 0h10v10H85V45zM15 80h10v10H15V80zm35 0h10v10H50V80zm35 0h10v10H85V80zM5 25h90v5H5v-5zm0 35h90v5H5v-5zm0 35h90v5H5v-5zM25 5h5v90h-5V5zm35 0h5v90h-5V5zm35 0h5v90h-5V5z' fill='%239ca3af' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E"),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239ca3af' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            background-repeat: repeat;
            background-attachment: fixed;
            overflow: auto;
            color: #334155;
            height: 100vh;
        }
        h1, h2, h3, .font-display { font-family: 'Prompt', sans-serif; }
        .font-premium { font-family: 'Playfair Display', 'Georgia', serif; }

        /* === BACKGROUND === */
        .hero-bg {
            height: 100vh;
            position: relative;
        }

        /* === PARTICLES (disabled on light bg) === */

        /* === LOGO === */
        .logo-ring {
            position: relative;
            width: 140px; height: 140px;
        }
        .logo-ring::before {
            content: '';
            position: absolute; inset: -8px;
            border: 2px solid rgba(239,68,68,0.25);
            border-radius: 50%;
            animation: ringPulse 3s ease-out infinite;
        }
        .logo-ring::after {
            content: '';
            position: absolute; inset: -16px;
            border: 1px solid rgba(239,68,68,0.1);
            border-radius: 50%;
            animation: ringPulse 3s ease-out 0.6s infinite;
        }
        @keyframes ringPulse {
            0%   { transform: scale(1); opacity: 0.7; }
            100% { transform: scale(1.35); opacity: 0; }
        }

        /* === SEARCH CARD === */
        .search-card {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 24px;
            box-shadow:
                0 20px 50px rgba(0,0,0,0.08),
                0 4px 12px rgba(0,0,0,0.04);
            transition: transform 0.5s ease, box-shadow 0.5s ease;
        }
        .search-card:hover {
            transform: translateY(-4px);
            box-shadow:
                0 28px 60px rgba(0,0,0,0.12),
                0 8px 20px rgba(0,0,0,0.06);
        }

        /* === TAB PILLS === */
        .tab-bar {
            background: #f1f5f9;
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 16px;
            padding: 5px;
        }
        .tab-pill {
            color: #94a3b8;
            border-radius: 12px;
            padding: 10px 0;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.35s ease;
            border: none;
            background: transparent;
        }
        .tab-pill:hover { color: #64748b; }
        .tab-pill.active {
            background: linear-gradient(145deg, #dc2626, #b91c1c);
            color: #fff;
            box-shadow: 0 4px 20px rgba(220,38,38,0.35);
        }

        /* === INPUT === */
        .search-input {
            background: #f8fafc;
            border: 2px solid rgba(51,65,85,0.2);
            border-radius: 14px;
            color: #334155;
            transition: all 0.3s ease;
        }
        .search-input:focus {
            outline: none;
            border-color: #b91c1c;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(185,28,28,0.1);
        }
        .search-input::placeholder { color: #94a3b8; }

        /* === DROPDOWN === */
        .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0; right: 0;
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 16px;
            max-height: 220px;
            overflow-y: auto;
            z-index: 9999;
            box-shadow: 0 16px 40px rgba(0,0,0,0.12);
        }
        .dropdown-item {
            padding: 14px 20px;
            cursor: pointer;
            transition: background 0.2s ease;
            border-bottom: 1px solid #f1f5f9;
        }
        .dropdown-item:last-child { border-bottom: none; }
        .dropdown-item:hover { background: #fef2f2; }
        .dropdown-menu::-webkit-scrollbar { width: 3px; }
        .dropdown-menu::-webkit-scrollbar-track { background: transparent; }
        .dropdown-menu::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        /* === FEATURE CARDS === */
        .feat-card {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: 20px;
            padding: 24px 20px;
            text-align: center;
            transition: all 0.45s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: 0 4px 24px rgba(0,0,0,0.06), inset 0 1px 0 rgba(255,255,255,0.8);
            position: relative;
            overflow: hidden;
        }
        .feat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            border-radius: 20px 20px 0 0;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .feat-card:nth-child(1)::before { background: linear-gradient(90deg, #dc2626, #f87171); }
        .feat-card:nth-child(2)::before { background: linear-gradient(90deg, #d97706, #fbbf24); }
        .feat-card:nth-child(3)::before { background: linear-gradient(90deg, #059669, #34d399); }
        .feat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12), inset 0 1px 0 rgba(255,255,255,0.9);
            border-color: rgba(255,255,255,0.8);
        }
        .feat-card:hover::before {
            opacity: 1;
        }
        .feat-icon {
            width: 56px; height: 56px;
            border-radius: 16px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 24px;
            margin-bottom: 14px;
            position: relative;
            transition: transform 0.4s ease;
        }
        .feat-card:hover .feat-icon {
            transform: scale(1.1) rotate(-5deg);
        }

        /* === LOGIN BUTTON === */
        .btn-login {
            background: linear-gradient(145deg, #dc2626, #b91c1c);
            color: #fff;
            font-weight: 700;
            padding: 10px 24px;
            border-radius: 12px;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(220,38,38,0.35);
            text-decoration: none;
            font-size: 14px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(220,38,38,0.5);
        }

        /* === ANIMATIONS === */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        .anim-fade-up { animation: fadeUp 0.7s ease-out both; }
        .anim-fade-up-d1 { animation: fadeUp 0.7s 0.1s ease-out both; }
        .anim-fade-up-d2 { animation: fadeUp 0.7s 0.2s ease-out both; }
        .anim-fade-up-d3 { animation: fadeUp 0.7s 0.3s ease-out both; }
        .anim-fade-up-d4 { animation: fadeUp 0.7s 0.4s ease-out both; }
        .anim-fade-up-d5 { animation: fadeUp 0.7s 0.5s ease-out both; }
        .anim-fade-in { animation: fadeIn 1s ease-out both; }

        /* === MISC === */
        .text-shimmer {
            background: linear-gradient(90deg, #b91c1c 0%, #ef4444 50%, #b91c1c 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmer 4s linear infinite;
        }
        @keyframes shimmer {
            0%   { background-position: 0% center; }
            100% { background-position: 200% center; }
        }
        @keyframes goldShimmer {
            0%   { background-position: 0% center; }
            100% { background-position: 200% center; }
        }

        .divider {
            width: 60px; height: 3px;
            background: linear-gradient(90deg, #dc2626, transparent);
            border-radius: 2px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-grid { grid-template-columns: 1fr !important; }
            .hero-left { text-align: center; }
            .feat-row { flex-direction: column; align-items: center; }
            body { height: auto; min-height: 100vh; }
            .hero-bg { height: auto; min-height: 100vh; }
            .hero-bg > .h-screen { height: auto !important; min-height: auto !important; padding: 80px 0 60px; }
        }

        /* Panel Transition */
        .search-panel { transition: opacity 0.3s ease, transform 0.3s ease; }
        .search-panel.hidden { display: none; }
    </style>
</head>
<body>

    <div class="hero-bg">

        <!-- Login Button -->
        <div class="fixed top-5 right-5 z-50 anim-fade-in">
            <a href="login.php" class="btn-login flex items-center gap-2">
                <i class="fa-solid fa-right-to-bracket text-sm"></i> เข้าสู่ระบบ
            </a>
        </div>

        <!-- HERO SECTION -->
        <div class="h-screen flex items-center">
            <div class="max-w-6xl mx-auto px-6 w-full py-8">
                <div class="hero-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">

                    <!-- LEFT: Branding -->
                    <div class="hero-left">
                        <!-- Logo + Title Row -->
                        <div class="anim-fade-up mb-4 flex items-center gap-5">
                            <div class="logo-ring flex-shrink-0">
                                <img src="images/cvc_logo.png" alt="CVC Logo" class="w-full h-full object-contain relative z-10" style="filter: drop-shadow(0 0 24px rgba(220,38,38,0.3));">
                            </div>
                            <h1 class="font-premium leading-tight" style="font-size: clamp(2.2rem, 4.5vw, 3.5rem); font-weight: 900; letter-spacing: -0.5px;">
                                <span style="background: linear-gradient(135deg, #b8860b 0%, #ffd700 25%, #daa520 50%, #f5c542 75%, #b8860b 100%); background-size: 200% auto; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; animation: goldShimmer 3s linear infinite; filter: drop-shadow(0 2px 4px rgba(184,134,11,0.3));">CVC</span><br><span class="text-shimmer" style="font-family: 'Playfair Display', serif;">Smart System</span>
                            </h1>
                        </div>


                        <!-- Feature Cards -->
                        <div class="anim-fade-up-d4 feat-row flex gap-4" style="max-width: 480px; margin-top: 28px;">
                            <div class="feat-card flex-1">
                                <div class="feat-icon" style="background: linear-gradient(145deg, rgba(220,38,38,0.15), rgba(248,113,113,0.08)); color: #ef4444; box-shadow: 0 4px 12px rgba(220,38,38,0.15);">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                </div>
                                <div class="text-slate-800 text-sm font-bold mb-1" style="letter-spacing: 0.3px;">อัตโนมัติ</div>
                                <div class="text-slate-400 text-xs" style="line-height: 1.5;">จัดตารางด้วย AI</div>
                            </div>
                            <div class="feat-card flex-1">
                                <div class="feat-icon" style="background: linear-gradient(145deg, rgba(217,119,6,0.15), rgba(251,191,36,0.08)); color: #f59e0b; box-shadow: 0 4px 12px rgba(217,119,6,0.15);">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>
                                <div class="text-slate-800 text-sm font-bold mb-1" style="letter-spacing: 0.3px;">เรียลไทม์</div>
                                <div class="text-slate-400 text-xs" style="line-height: 1.5;">อัพเดททันที</div>
                            </div>
                            <div class="feat-card flex-1">
                                <div class="feat-icon" style="background: linear-gradient(145deg, rgba(5,150,105,0.15), rgba(52,211,153,0.08)); color: #10b981; box-shadow: 0 4px 12px rgba(5,150,105,0.15);">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div class="text-slate-800 text-sm font-bold mb-1" style="letter-spacing: 0.3px;">ปลอดภัย</div>
                                <div class="text-slate-400 text-xs" style="line-height: 1.5;">ข้อมูลเข้ารหัส</div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Search Card -->
                    <div class="anim-fade-up-d3">
                        <div class="search-card p-6">

                            <!-- Tab Pills -->
                            <div class="tab-bar flex mb-4">
                                <button id="tab-student" onclick="switchTab('student')" class="tab-pill active flex-1 flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-user-graduate"></i> นักเรียน
                                </button>
                                <button id="tab-teacher" onclick="switchTab('teacher')" class="tab-pill flex-1 flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-chalkboard-user"></i> ครูผู้สอน
                                </button>
                            </div>

                            <!-- Student Search Panel -->
                            <div id="panel-student" class="search-panel">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg" style="background: linear-gradient(145deg, #dc2626, #991b1b); color: #fff; box-shadow: 0 4px 14px rgba(220,38,38,0.35);">
                                        <i class="fa-solid fa-user-graduate"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-display font-bold text-slate-800">ค้นหาตารางเรียน</h2>
                                        <p class="text-slate-400 text-xs">พิมพ์ชื่อกลุ่มเรียนหรือรหัส</p>
                                    </div>
                                </div>
                                
                                <form action="public_schedule.php" method="GET" id="form-student">
                                    <input type="hidden" name="mode" value="class">
                                    <input type="hidden" name="id" id="student_id_input">
                                    
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" id="student_search" 
                                            class="search-input w-full px-5 py-3 pl-12 text-sm font-medium"
                                            placeholder="พิมพ์ชื่อกลุ่ม หรือรหัส..." 
                                            autocomplete="off">
                                        <div id="student_dropdown" class="dropdown-menu hidden"></div>
                                    </div>
                                </form>
                            </div>

                            <!-- Teacher Search Panel -->
                            <div id="panel-teacher" class="search-panel hidden">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg" style="background: linear-gradient(145deg, #f87171, #dc2626); color: #fff; box-shadow: 0 4px 14px rgba(248,113,113,0.3);">
                                        <i class="fa-solid fa-chalkboard-user"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-display font-bold text-slate-800">ค้นหาตารางสอน</h2>
                                        <p class="text-slate-400 text-xs">พิมพ์ชื่อหรือรหัสอาจารย์</p>
                                    </div>
                                </div>
                                
                                <form action="public_schedule.php" method="GET" id="form-teacher">
                                    <input type="hidden" name="mode" value="teacher">
                                    <input type="hidden" name="id" id="teacher_id_input">
                                    
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" id="teacher_search" 
                                            class="search-input w-full px-5 py-3 pl-12 text-sm font-medium"
                                            placeholder="พิมพ์ชื่ออาจารย์ หรือรหัส..." 
                                            autocomplete="off">
                                        <div id="teacher_dropdown" class="dropdown-menu hidden"></div>
                                    </div>
                                </form>
                            </div>

                            <!-- Hint -->
                            <div class="mt-4 flex items-center gap-2 text-slate-400 text-xs">
                                <i class="fa-solid fa-circle-info"></i>
                                <span>เลือกจากผลลัพธ์เพื่อดูตาราง</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="absolute bottom-3 left-0 right-0 text-center z-10">
            <p class="text-slate-400 text-xs">
                © <?php echo $current_year_real; ?> CVC Smart System • วิทยาลัยอาชีวศึกษาเชียงราย
            </p>
        </div>
    </div>

    <script>

        // === Tab Switching ===
        function switchTab(type) {
            document.getElementById('tab-student').classList.toggle('active', type === 'student');
            document.getElementById('tab-teacher').classList.toggle('active', type === 'teacher');
            document.getElementById('panel-student').classList.toggle('hidden', type !== 'student');
            document.getElementById('panel-teacher').classList.toggle('hidden', type !== 'teacher');
        }

        // === Search Data ===
        const studentsData = [
            <?php foreach ($classes as $c):
    $stu_year = $current_year_real - $c['cla_year'] + 1;
    if ($stu_year < 1)
        $stu_year = 1;
    $room_no = intval($c['cla_group_no']);
    $display_name = $c['cla_name'] . "." . $stu_year . "/" . $room_no;
?>
            { 
                id: "<?php echo $c['cla_id']; ?>", 
                text: "<?php echo $display_name; ?>",
                subtext: "รหัส: <?php echo $c['cla_id']; ?>",
                search: "<?php echo $c['cla_name'] . ' ' . $c['cla_id'] . ' ' . $display_name; ?>" 
            },
            <?php
endforeach; ?>
        ];

        const teachersData = [
            <?php foreach ($teachers as $t): ?>
            { 
                id: "<?php echo $t['tea_id']; ?>", 
                text: "<?php echo $t['tea_fullname']; ?>",
                subtext: "รหัส: <?php echo $t['tea_code']; ?>",
                search: "<?php echo $t['tea_fullname'] . ' ' . $t['tea_code'] . ' ' . $t['tea_username']; ?>" 
            },
            <?php
endforeach; ?>
        ];

        // === Search Function ===
        function setupSearch(inputId, dropdownId, hiddenId, dataList) {
            const input = document.getElementById(inputId);
            const dropdown = document.getElementById(dropdownId);
            const hidden = document.getElementById(hiddenId);
            
            input.addEventListener('input', function() {
                const val = this.value.toLowerCase().trim();
                dropdown.innerHTML = '';
                
                if (!val) {
                    dropdown.classList.add('hidden');
                    return;
                }

                const filtered = dataList.filter(item => item.search.toLowerCase().includes(val));

                if (filtered.length === 0) {
                    dropdown.innerHTML = `
                        <div class="p-8 text-center">
                            <i class="fa-regular fa-face-frown text-3xl text-gray-600 mb-3 block"></i>
                            <span class="text-gray-500 text-sm">ไม่พบข้อมูล</span>
                        </div>`;
                } else {
                    filtered.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'dropdown-item flex justify-between items-center';
                        
                        div.innerHTML = `
                            <div>
                                <div class="font-bold text-slate-700 text-sm">${item.text}</div>
                                <div class="text-xs text-slate-400 mt-0.5">${item.subtext}</div>
                            </div>
                            <i class="fa-solid fa-arrow-right text-slate-300 text-xs"></i>
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
                
                dropdown.classList.remove('hidden');
            });

            input.addEventListener('focus', function() {
                if (this.value.trim() && dropdown.children.length > 0) {
                    dropdown.classList.remove('hidden');
                }
            });

            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        }

        setupSearch('student_search', 'student_dropdown', 'student_id_input', studentsData);
        setupSearch('teacher_search', 'teacher_dropdown', 'teacher_id_input', teachersData);
    </script>


