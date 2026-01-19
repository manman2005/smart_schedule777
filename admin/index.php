<?php
// htdocs/admin/index.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();

// --- ส่วนที่ 1: ดึงสถิติต่างๆ (เหมือนเดิม) ---
$cnt_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$cnt_teachers = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
$cnt_subjects = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
$cnt_rooms =    $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$cnt_classes =  $pdo->query("SELECT COUNT(*) FROM class_groups")->fetchColumn();
$cnt_plans =    $pdo->query("SELECT COUNT(*) FROM study_plans")->fetchColumn();

// --- ส่วนที่ 2: [NEW] เช็คสถานะระบบจอง/ตาราง ---
$sys_btn_label = "เปิด-ปิดระบบ";
$sys_btn_sub = "ตั้งค่าการจอง";
$sys_btn_color = "slate";
$sys_btn_icon = "fa-sliders";
$sys_status_indicator = ""; // จุดสีแสดงสถานะ

try {
    // ลองดึงค่าจากตาราง system_settings
    $stmt_sys = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'teacher_booking'");
    
    if ($stmt_sys) {
        $booking_status = $stmt_sys->fetchColumn();
        
        if ($booking_status === false) {
            // มีตาราง แต่ไม่มีข้อมูล
            $sys_btn_sub = "ยังไม่ตั้งค่า";
        } elseif ($booking_status == '1') {
            // ระบบเปิดอยู่
            $sys_btn_color = "emerald";
            $sys_btn_icon = "fa-door-open";
            $sys_btn_label = "ระบบเปิดอยู่";
            $sys_btn_sub = "ครูจองวิชาได้";
            $sys_status_indicator = '<div class="absolute top-3 right-3 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white shadow-sm animate-pulse" title="Online"></div>';
        } else {
            // ระบบปิดอยู่
            $sys_btn_color = "rose"; // สีแดงชมพู
            $sys_btn_icon = "fa-lock";
            $sys_btn_label = "ระบบปิดอยู่";
            $sys_btn_sub = "ห้ามจอง/แก้ไข";
            $sys_status_indicator = '<div class="absolute top-3 right-3 w-3 h-3 bg-rose-500 rounded-full border-2 border-white shadow-sm" title="Offline"></div>';
        }
    }
} catch (Exception $e) {
    // กรณีหาตารางไม่เจอ (Database Error)
    $sys_btn_color = "amber";
    $sys_btn_icon = "fa-triangle-exclamation";
    $sys_btn_label = "ฐานข้อมูลมีปัญหา";
    $sys_btn_sub = "คลิกเพื่อซ่อมแซม";
    $sys_status_indicator = '<div class="absolute top-3 right-3 text-amber-500 animate-bounce"><i class="fa-solid fa-triangle-exclamation"></i></div>';
    
    // ถ้าตารางหาย ให้ปุ่มนี้วิ่งไปหน้าซ่อมไฟล์ (ถ้าคุณมี fix_db.php) หรือไปหน้า setting เพื่อรัน SQL ใหม่
    // ในที่นี้ให้ไป system_settings.php เหมือนเดิม เพราะผมแก้โค้ดให้มันสร้างตารางอัตโนมัติแล้ว
}

require_once '../includes/header.php'; 
?>

<div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-4">
    <div>
        <h2 class="text-3xl font-serif font-bold text-slate-800">
            <span class="text-cvc-blue">Executive</span> Dashboard
        </h2>
        <p class="text-slate-500 mt-2 font-light">ภาพรวมระบบสารสนเทศ วิทยาลัยอาชีวศึกษาเชียงราย</p>
    </div>
    <div class="bg-white px-5 py-2.5 rounded-full text-sm font-bold text-indigo-800 shadow-sm border border-indigo-100 flex items-center gap-2">
        <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
        <?php echo date('d F Y'); ?>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-6 mb-12">
    <?php
    $stats = [
        ['title' => 'นักเรียน', 'count' => $cnt_students, 'icon' => 'fa-user-graduate', 'color' => 'blue'],
        ['title' => 'ครูอาจารย์', 'count' => $cnt_teachers, 'icon' => 'fa-chalkboard-user', 'color' => 'sky'],
        ['title' => 'กลุ่มเรียน', 'count' => $cnt_classes, 'icon' => 'fa-people-roof', 'color' => 'indigo'],
        ['title' => 'รายวิชา', 'count' => $cnt_subjects, 'icon' => 'fa-book-open', 'color' => 'amber'],
        ['title' => 'แผนการเรียน', 'count' => $cnt_plans, 'icon' => 'fa-clipboard-list', 'color' => 'emerald'],
        ['title' => 'ห้องเรียน', 'count' => $cnt_rooms, 'icon' => 'fa-door-open', 'color' => 'rose'],
    ];
    foreach($stats as $stat): 
    ?>
    <div class="card-premium p-6 relative overflow-hidden group border border-blue-50 hover:border-<?php echo $stat['color']; ?>-300 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
        <div class="relative z-10">
            <div class="w-12 h-12 rounded-2xl bg-<?php echo $stat['color']; ?>-50 text-<?php echo $stat['color']; ?>-600 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition duration-300 shadow-sm">
                <i class="fa-solid <?php echo $stat['icon']; ?>"></i>
            </div>
            <h3 class="text-3xl font-serif font-bold text-slate-800 mb-1"><?php echo number_format($stat['count']); ?></h3>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider group-hover:text-<?php echo $stat['color']; ?>-600 transition"><?php echo $stat['title']; ?></p>
        </div>
        <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-<?php echo $stat['color']; ?>-500/5 rounded-full blur-2xl group-hover:bg-<?php echo $stat['color']; ?>-500/10 transition"></div>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-2 space-y-8">
        
        <div>
            <h3 class="text-lg font-bold text-slate-700 mb-6 flex items-center gap-2">
                <div class="w-1 h-6 bg-cvc-blue rounded-full"></div>
                ฐานข้อมูลหลัก
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <a href="manage_students.php" class="card-premium p-5 flex items-center gap-5 border border-blue-50 hover:border-blue-500 hover:shadow-blue-100 transition group hover:-translate-y-1 duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shadow-inner group-hover:bg-blue-600 group-hover:text-white transition duration-300">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-700 text-lg group-hover:text-blue-700">ข้อมูลนักเรียน</h4>
                        <p class="text-sm text-slate-400 font-light">จัดการรายชื่อและสถานะ</p>
                    </div>
                    <i class="fa-solid fa-chevron-right ml-auto text-blue-100 group-hover:text-blue-500 transition"></i>
                </a>

                <a href="manage_teachers.php" class="card-premium p-5 flex items-center gap-5 border border-blue-50 hover:border-sky-500 hover:shadow-sky-100 transition group hover:-translate-y-1 duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-2xl shadow-inner group-hover:bg-sky-600 group-hover:text-white transition duration-300">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-700 text-lg group-hover:text-sky-700">ข้อมูลครูผู้สอน</h4>
                        <p class="text-sm text-slate-400 font-light">บุคลากรและภาระงาน</p>
                    </div>
                    <i class="fa-solid fa-chevron-right ml-auto text-sky-100 group-hover:text-sky-500 transition"></i>
                </a>

                <a href="manage_class_groups.php" class="card-premium p-5 flex items-center gap-5 border border-blue-50 hover:border-indigo-500 hover:shadow-indigo-100 transition group hover:-translate-y-1 duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl shadow-inner group-hover:bg-indigo-600 group-hover:text-white transition duration-300">
                        <i class="fa-solid fa-people-roof"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-700 text-lg group-hover:text-indigo-700">กลุ่มเรียน</h4>
                        <p class="text-sm text-slate-400 font-light">ระดับชั้นและสาขาวิชา</p>
                    </div>
                    <i class="fa-solid fa-chevron-right ml-auto text-indigo-100 group-hover:text-indigo-500 transition"></i>
                </a>

                <a href="manage_subjects.php" class="card-premium p-5 flex items-center gap-5 border border-blue-50 hover:border-amber-500 hover:shadow-amber-100 transition group hover:-translate-y-1 duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl shadow-inner group-hover:bg-amber-600 group-hover:text-white transition duration-300">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-700 text-lg group-hover:text-amber-700">รายวิชา</h4>
                        <p class="text-sm text-slate-400 font-light">หลักสูตรและหน่วยกิต</p>
                    </div>
                    <i class="fa-solid fa-chevron-right ml-auto text-amber-100 group-hover:text-amber-500 transition"></i>
                </a>
            </div>
        </div>

        <div>
            <h3 class="text-lg font-bold text-slate-700 mb-6 flex items-center gap-2">
                <div class="w-1 h-6 bg-slate-400 rounded-full"></div>
                ตั้งค่าระบบ (Configuration)
            </h3>
            
            <div class="card-premium p-8 relative overflow-hidden border border-blue-50">
                <div class="absolute top-0 right-0 w-64 h-64 bg-slate-50/50 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 relative z-10">
                    
                    <a href="system_settings.php" class="relative flex flex-col items-center justify-center p-4 rounded-2xl bg-<?php echo $sys_btn_color; ?>-50/40 hover:bg-<?php echo $sys_btn_color; ?>-50 border border-<?php echo $sys_btn_color; ?>-100/50 hover:border-<?php echo $sys_btn_color; ?>-200 transition-all duration-300 group cursor-pointer text-center shadow-sm hover:shadow-md hover:-translate-y-1">
                        
                        <?php echo $sys_status_indicator; ?>

                        <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center shadow-sm mb-3 group-hover:scale-110 transition duration-300 ring-4 ring-<?php echo $sys_btn_color; ?>-50 group-hover:ring-<?php echo $sys_btn_color; ?>-100">
                            <i class="fa-solid <?php echo $sys_btn_icon; ?> text-xl text-<?php echo $sys_btn_color; ?>-500 group-hover:text-<?php echo $sys_btn_color; ?>-600"></i>
                        </div>
                        
                        <span class="text-xs font-bold text-slate-700 group-hover:text-<?php echo $sys_btn_color; ?>-700 transition"><?php echo $sys_btn_label; ?></span>
                        <span class="text-[10px] text-slate-400 font-light mt-1"><?php echo $sys_btn_sub; ?></span>
                    </a>

                    <?php
                    $configs = [
                        ['url' => 'manage_levels.php', 'label' => 'ระดับชั้น', 'icon' => 'fa-layer-group', 'color' => 'blue'],
                        ['url' => 'manage_curriculums.php', 'label' => 'หลักสูตร', 'icon' => 'fa-file-contract', 'color' => 'cyan'],
                        ['url' => 'manage_subject_types.php', 'label' => 'ประเภทวิชา', 'icon' => 'fa-tags', 'color' => 'emerald'],
                        ['url' => 'manage_career_groups.php', 'label' => 'กลุ่มอาชีพ', 'icon' => 'fa-briefcase', 'color' => 'amber'],
                        ['url' => 'manage_majors.php', 'label' => 'สาขาวิชา', 'icon' => 'fa-graduation-cap', 'color' => 'violet'],
                        ['url' => 'manage_subject_groups.php', 'label' => 'หมวดวิชา', 'icon' => 'fa-folder-tree', 'color' => 'pink'],
                        ['url' => 'manage_rooms.php', 'label' => 'ห้องเรียน', 'icon' => 'fa-door-open', 'color' => 'rose'],
                        ['url' => 'manage_time_slots.php', 'label' => 'คาบเรียน', 'icon' => 'fa-clock', 'color' => 'indigo'],
                    ];
                    
                    foreach($configs as $cfg):
                        $c = $cfg['color'];
                    ?>
                    <a href="<?php echo $cfg['url']; ?>" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-<?php echo $c; ?>-50/40 hover:bg-<?php echo $c; ?>-50 border border-<?php echo $c; ?>-100/50 hover:border-<?php echo $c; ?>-200 transition-all duration-300 group cursor-pointer text-center shadow-sm hover:shadow-md hover:-translate-y-1">
                        <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center shadow-sm mb-3 group-hover:scale-110 transition duration-300 ring-4 ring-<?php echo $c; ?>-50 group-hover:ring-<?php echo $c; ?>-100">
                            <i class="fa-solid <?php echo $cfg['icon']; ?> text-xl text-<?php echo $c; ?>-500 group-hover:text-<?php echo $c; ?>-600"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-500 group-hover:text-<?php echo $c; ?>-700 transition"><?php echo $cfg['label']; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        
        <a href="auto_scheduler.php" class="block relative overflow-hidden rounded-[24px] p-8 bg-gradient-to-br from-cvc-blue to-slate-900 text-white shadow-xl group transition hover:scale-[1.02] border border-blue-800/50 hover:shadow-blue-900/30">
            <div class="absolute top-0 right-0 w-48 h-48 bg-white/10 rounded-full blur-3xl -mr-10 -mt-10 group-hover:bg-white/20 transition"></div>
            
            <div class="relative z-10">
                <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center text-cvc-gold mb-6 backdrop-blur-sm border border-white/10 shadow-inner group-hover:scale-110 transition duration-500">
                    <i class="fa-solid fa-wand-magic-sparkles text-2xl"></i>
                </div>
                <h4 class="text-2xl font-serif font-bold mb-2">Auto Scheduler</h4>
                <p class="text-blue-200 text-sm font-light mb-6 leading-relaxed">ระบบจัดตารางเรียนอัตโนมัติด้วย AI ลดความซ้ำซ้อน รวดเร็ว</p>
                <div class="inline-flex items-center text-xs font-bold uppercase tracking-widest text-white bg-white/20 px-4 py-2 rounded-full hover:bg-white hover:text-blue-900 transition border border-white/30 group-hover:pl-6 duration-300">
                    Start Process <i class="fa-solid fa-arrow-right ml-2"></i>
                </div>
            </div>
        </a>

        <div class="card-premium p-6 border-l-4 border-l-cvc-gold border-t border-r border-b border-blue-50">
            <h4 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Quick Tools</h4>
            <div class="space-y-3">
                <a href="manage_plans.php" class="flex items-center justify-between p-3 rounded-xl hover:bg-indigo-50/50 transition cursor-pointer group border border-transparent hover:border-indigo-100 hover:shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm shadow-sm group-hover:bg-indigo-500 group-hover:text-white transition"><i class="fa-solid fa-list-check"></i></div>
                        <span class="text-sm font-bold text-slate-600 group-hover:text-indigo-700">แผนการเรียน</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-indigo-400 group-hover:translate-x-1 transition"></i>
                </a>
                <a href="view_schedule_master.php" class="flex items-center justify-between p-3 rounded-xl hover:bg-emerald-50/50 transition cursor-pointer group border border-transparent hover:border-emerald-100 hover:shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm shadow-sm group-hover:bg-emerald-500 group-hover:text-white transition"><i class="fa-solid fa-calendar-days"></i></div>
                        <span class="text-sm font-bold text-slate-600 group-hover:text-emerald-700">ตรวจสอบตาราง</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-emerald-400 group-hover:translate-x-1 transition"></i>
                </a>
                
                <a href="system_settings.php" class="flex items-center justify-between p-3 rounded-xl hover:bg-<?php echo $sys_btn_color; ?>-50/50 transition cursor-pointer group border border-transparent hover:border-<?php echo $sys_btn_color; ?>-100 hover:shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-<?php echo $sys_btn_color; ?>-100 text-<?php echo $sys_btn_color; ?>-600 flex items-center justify-center text-sm shadow-sm group-hover:bg-<?php echo $sys_btn_color; ?>-500 group-hover:text-white transition"><i class="fa-solid <?php echo $sys_btn_icon; ?>"></i></div>
                        <span class="text-sm font-bold text-slate-600 group-hover:text-<?php echo $sys_btn_color; ?>-700"><?php echo $sys_btn_label; ?></span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-<?php echo $sys_btn_color; ?>-400 group-hover:translate-x-1 transition"></i>
                </a>

            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>