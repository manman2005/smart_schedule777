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
} catch (PDOException $e) {
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Prompt:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Sarabun', sans-serif; background: #0a0a0a; overflow-x: hidden; }
        h1, h2, h3, .font-display { font-family: 'Prompt', sans-serif; }

        /* Premium Dark Theme */
        .premium-bg {
            min-height: 100vh;
            background: 
                radial-gradient(ellipse at 20% 0%, rgba(127, 29, 29, 0.4) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 100%, rgba(185, 28, 28, 0.3) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(251, 191, 36, 0.05) 0%, transparent 50%),
                linear-gradient(180deg, #0a0a0a 0%, #1a0505 50%, #0a0a0a 100%);
        }

        /* Animated Grid */
        .grid-overlay {
            position: fixed;
            inset: 0;
            background-image: 
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 80px 80px;
            pointer-events: none;
        }

        /* Luxury Rose Gold Glow */
        .glow-gold {
            box-shadow: 
                0 0 40px rgba(183, 110, 121, 0.3), 
                0 0 80px rgba(183, 110, 121, 0.15),
                inset 0 1px 0 rgba(255,255,255,0.2);
        }

        /* Premium Red Glow */
        .glow-red {
            box-shadow: 
                0 0 40px rgba(180, 83, 9, 0.4), 
                0 0 80px rgba(180, 83, 9, 0.2),
                inset 0 1px 0 rgba(255,255,255,0.15);
        }

        /* Logo Glow Animation */
        .logo-glow {
            animation: logoGlow 4s ease-in-out infinite;
            filter: drop-shadow(0 0 30px rgba(220,38,38,0.5));
        }

        @keyframes logoGlow {
            0%, 100% { filter: drop-shadow(0 0 30px rgba(220,38,38,0.5)) drop-shadow(0 0 60px rgba(220,38,38,0.3)); }
            50% { filter: drop-shadow(0 0 50px rgba(220,38,38,0.8)) drop-shadow(0 0 100px rgba(220,38,38,0.5)); }
        }

        /* Floating Animation */
        .float {
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25% { transform: translateY(-15px) rotate(1deg); }
            75% { transform: translateY(-10px) rotate(-1deg); }
        }

        /* Luxury Gold Text Gradient */
        .text-gradient {
            background: linear-gradient(135deg, 
                #D4AF37 0%, 
                #F5E6A0 25%, 
                #D4AF37 50%, 
                #AA8C2C 75%, 
                #D4AF37 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: goldShimmer 5s linear infinite;
        }

        @keyframes goldShimmer {
            0% { background-position: 0% center; }
            100% { background-position: 200% center; }
        }

        /* Premium Glass Card */
        .premium-card {
            background: linear-gradient(145deg, 
                rgba(25,20,15,0.98) 0%, 
                rgba(15,12,10,0.98) 100%);
            border: 1px solid rgba(212,175,55,0.15);
            border-radius: 28px;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 10;
            overflow: visible;
            box-shadow: 
                0 20px 60px rgba(0,0,0,0.5),
                inset 0 1px 0 rgba(255,255,255,0.05),
                inset 0 -1px 0 rgba(0,0,0,0.2);
        }

        .premium-card::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 29px;
            padding: 1px;
            background: linear-gradient(145deg, rgba(212,175,55,0.3), transparent 60%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .premium-card:hover::before {
            opacity: 1;
        }

        .premium-card:hover {
            transform: translateY(-8px);
            border-color: rgba(212,175,55,0.3);
            box-shadow: 
                0 35px 80px rgba(0,0,0,0.6), 
                0 0 60px rgba(212,175,55,0.1),
                inset 0 1px 0 rgba(255,255,255,0.08);
        }

        .premium-card.red:hover {
            border-color: rgba(180,83,9,0.3);
            box-shadow: 
                0 35px 80px rgba(0,0,0,0.6), 
                0 0 60px rgba(180,83,9,0.15);
        }

        /* Luxury Input */
        .premium-input {
            background: rgba(0,0,0,0.4);
            border: 1px solid rgba(212,175,55,0.2);
            color: white;
            transition: all 0.4s ease;
            font-size: 15px;
        }

        .premium-input:focus {
            outline: none;
            border-color: rgba(212,175,55,0.5);
            background: rgba(0,0,0,0.6);
            box-shadow: 
                0 0 30px rgba(212,175,55,0.1),
                inset 0 0 20px rgba(212,175,55,0.03);
        }

        .premium-input::placeholder {
            color: rgba(255,255,255,0.35);
        }

        /* Luxury Dropdown */
        .dropdown-menu {
            position: absolute;
            top: calc(100% + 12px);
            left: 0;
            right: 0;
            background: linear-gradient(180deg, #151210 0%, #0c0a09 100%);
            border: 1px solid rgba(212,175,55,0.2);
            border-radius: 20px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 9999;
            box-shadow: 
                0 30px 80px rgba(0,0,0,0.9),
                0 0 40px rgba(0,0,0,0.5);
        }

        .dropdown-item {
            padding: 18px 24px;
            cursor: pointer;
            transition: all 0.25s ease;
            border-bottom: 1px solid rgba(212,175,55,0.08);
        }

        .dropdown-item:hover {
            background: linear-gradient(90deg, rgba(212,175,55,0.1), transparent);
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        /* Luxury Scrollbar */
        .dropdown-menu::-webkit-scrollbar { width: 4px; }
        .dropdown-menu::-webkit-scrollbar-track { background: transparent; }
        .dropdown-menu::-webkit-scrollbar-thumb { 
            background: linear-gradient(180deg, rgba(212,175,55,0.4), rgba(212,175,55,0.2)); 
            border-radius: 10px; 
        }

        /* Premium Pulse Ring */
        .pulse-ring {
            position: absolute;
            inset: -25px;
            border: 1px solid rgba(212,175,55,0.2);
            border-radius: 50%;
            animation: luxuryPulse 3s ease-out infinite;
        }

        @keyframes luxuryPulse {
            0% { transform: scale(1); opacity: 0.6; }
            100% { transform: scale(1.4); opacity: 0; }
        }

        /* Icon Container */
        .icon-container {
            position: relative;
        }

        .icon-container::before {
            content: '';
            position: absolute;
            inset: -12px;
            background: radial-gradient(circle, rgba(212,175,55,0.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: iconGlow 3s ease-in-out infinite;
        }

        .premium-card.red .icon-container::before {
            background: radial-gradient(circle, rgba(180,83,9,0.25) 0%, transparent 70%);
        }

        @keyframes iconGlow {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.15); opacity: 0.8; }
        }

        /* Luxury Icon Box */
        .icon-box {
            position: relative;
            background: linear-gradient(145deg, #D4AF37, #8B6914);
            border-radius: 16px;
            box-shadow: 
                0 10px 30px rgba(212,175,55,0.3),
                inset 0 2px 0 rgba(255,255,255,0.3),
                inset 0 -2px 0 rgba(0,0,0,0.2);
        }

        .icon-box.amber {
            background: linear-gradient(145deg, #D4AF37, #AA8C2C);
        }

        .icon-box.bronze {
            background: linear-gradient(145deg, #CD7F32, #8B4513);
        }

        /* Luxury Button */
        .btn-luxury {
            background: linear-gradient(145deg, #D4AF37, #8B6914);
            color: #0a0705;
            font-weight: 700;
            padding: 14px 32px;
            border-radius: 50px;
            border: none;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 
                0 8px 25px rgba(212,175,55,0.3),
                inset 0 1px 0 rgba(255,255,255,0.3);
        }

        .btn-luxury::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.6s ease;
        }

        .btn-luxury:hover::before {
            left: 100%;
        }

        .btn-luxury:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 15px 40px rgba(212,175,55,0.4),
                inset 0 1px 0 rgba(255,255,255,0.4);
        }

        /* Feature Badge */
        .feature-badge {
            background: linear-gradient(145deg, rgba(20,16,12,0.9), rgba(10,8,6,0.9));
            border: 1px solid rgba(212,175,55,0.15);
            border-radius: 50px;
            padding: 12px 20px;
            transition: all 0.4s ease;
        }

        .feature-badge:hover {
            border-color: rgba(212,175,55,0.3);
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

        /* Ornament Lines */
        .ornament-line {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(212,175,55,0.3), transparent);
        }

        /* Crown Badge */
        .crown-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            background: linear-gradient(145deg, rgba(212,175,55,0.15), rgba(212,175,55,0.05));
            border: 1px solid rgba(212,175,55,0.3);
            border-radius: 50px;
            color: #D4AF37;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Search Tabs */
        .search-tab {
            color: rgba(255,255,255,0.4);
            background: transparent;
        }

        .search-tab:hover {
            color: rgba(255,255,255,0.7);
            background: rgba(255,255,255,0.05);
        }

        .search-tab.active {
            color: white;
            background: linear-gradient(145deg, #DC2626, #991B1B);
            box-shadow: 0 4px 15px rgba(220,38,38,0.3);
        }

        /* Panel Transition */
        .search-panel {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .search-panel.hidden {
            display: none;
        }
    </style>
</head>
<body>

    <div class="premium-bg">
        <div class="grid-overlay"></div>

        <!-- Login Button -->
        <div class="fixed top-5 right-5 z-50">
            <a href="login.php" class="btn-luxury text-sm flex items-center gap-2">
                <i class="fa-solid fa-crown"></i> เข้าสู่ระบบ
            </a>
        </div>

        <!-- Centered Layout -->
        <div class="relative z-10 min-h-screen flex flex-col items-center justify-center px-4 py-16">
            
            <!-- Logo -->
            <div class="relative mb-6 animate__animated animate__fadeInDown">
                <div class="w-28 h-28 relative float">
                    <div class="pulse-ring"></div>
                    <div class="pulse-ring" style="animation-delay: 0.5s;"></div>
                    <img src="/images/cvc_logo.png" alt="CVC Logo" class="w-full h-full object-contain logo-glow relative z-10">
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-3xl md:text-5xl font-display font-extrabold text-white mb-3 leading-tight text-center animate__animated animate__fadeInDown">
                ระบบบริการ<span class="text-gradient">การศึกษา</span>
            </h1>

            <p class="text-gray-400 text-sm md:text-base text-center mb-8 animate__animated animate__fadeInDown">
                วิทยาลัยอาชีวศึกษาเชียงราย
            </p>

            <!-- Search Card -->
            <div class="w-full max-w-md animate__animated animate__fadeInUp">
                
                <!-- Features -->
                <div class="flex flex-wrap gap-2 justify-center mb-4">
                    <div class="feature-badge flex items-center gap-2">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-400 text-xs"></i>
                        <span class="text-amber-200/70 text-xs font-medium">จัดตารางอัตโนมัติ</span>
                    </div>
                    <div class="feature-badge flex items-center gap-2">
                        <i class="fa-solid fa-bolt text-amber-400 text-xs"></i>
                        <span class="text-amber-200/70 text-xs font-medium">เรียลไทม์</span>
                    </div>
                    <div class="feature-badge flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-amber-400 text-xs"></i>
                        <span class="text-amber-200/70 text-xs font-medium">ปลอดภัย</span>
                    </div>
                </div>

                <!-- Search Tabs -->
                <div class="flex mb-5 bg-black/30 rounded-2xl p-1.5 border border-white/5">
                    <button id="tab-student" onclick="switchTab('student')" class="search-tab active flex-1 py-3 px-4 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-user-graduate"></i>
                        <span>นักเรียน</span>
                    </button>
                    <button id="tab-teacher" onclick="switchTab('teacher')" class="search-tab flex-1 py-3 px-4 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-chalkboard-user"></i>
                        <span>ครูผู้สอน</span>
                    </button>
                </div>

                <!-- Student Search Panel -->
                <div id="panel-student" class="search-panel">
                    <div class="premium-card p-6">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-500 to-red-800 flex items-center justify-center text-white text-2xl glow-red">
                                <i class="fa-solid fa-user-graduate"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-display font-bold text-white">ค้นหาตารางเรียน</h2>
                                <p class="text-gray-500 text-xs">พิมพ์ชื่อกลุ่มหรือรหัส</p>
                            </div>
                        </div>
                        
                        <form action="public_schedule.php" method="GET" id="form-student">
                            <input type="hidden" name="mode" value="class">
                            <input type="hidden" name="id" id="student_id_input">
                            
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                                <input type="text" id="student_search" 
                                    class="premium-input w-full px-5 py-3.5 pl-12 rounded-xl text-sm font-medium"
                                    placeholder="พิมพ์ชื่อกลุ่ม หรือรหัส..." 
                                    autocomplete="off">
                                <div id="student_dropdown" class="dropdown-menu hidden"></div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Teacher Search Panel -->
                <div id="panel-teacher" class="search-panel hidden">
                    <div class="premium-card p-6">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-700 flex items-center justify-center text-white text-2xl glow-gold">
                                <i class="fa-solid fa-chalkboard-user"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-display font-bold text-white">ค้นหาตารางสอน</h2>
                                <p class="text-gray-500 text-xs">พิมพ์ชื่อหรือรหัสอาจารย์</p>
                            </div>
                        </div>
                        
                        <form action="public_schedule.php" method="GET" id="form-teacher">
                            <input type="hidden" name="mode" value="teacher">
                            <input type="hidden" name="id" id="teacher_id_input">
                            
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                                <input type="text" id="teacher_search" 
                                    class="premium-input w-full px-5 py-3.5 pl-12 rounded-xl text-sm font-medium"
                                    placeholder="พิมพ์ชื่ออาจารย์ หรือรหัส..." 
                                    autocomplete="off">
                                <div id="teacher_dropdown" class="dropdown-menu hidden"></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-10">
                <div class="ornament-line w-32 mx-auto mb-4"></div>
                <p class="text-amber-200/30 text-xs text-center">
                    © 2568 CVC Smart System • วิทยาลัยอาชีวศึกษาเชียงราย
                </p>
        </div>
    </div>

    <script>

        // Tab Switching
        function switchTab(type) {
            // Toggle tabs
            document.getElementById('tab-student').classList.toggle('active', type === 'student');
            document.getElementById('tab-teacher').classList.toggle('active', type === 'teacher');
            
            // Toggle panels
            document.getElementById('panel-student').classList.toggle('hidden', type !== 'student');
            document.getElementById('panel-teacher').classList.toggle('hidden', type !== 'teacher');
        }

        // Search Data
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
                subtext: "รหัส: <?php echo $c['cla_id']; ?>",
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

        // Search Function
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
                                <div class="font-bold text-white text-lg">${item.text}</div>
                                <div class="text-xs text-gray-500 mt-1">${item.subtext}</div>
                            </div>
                            <i class="fa-solid fa-arrow-right text-gray-600"></i>
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

</body>
</html>