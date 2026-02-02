<?php
// htdocs/admin/index.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();
require_once '../includes/header.php';

// ดึงข้อมูลสถิติ
try {
    $stats = [
        'students' => $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
        'teachers' => $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn(),
        'subjects' => $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn(),
        'rooms'    => $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn(),
        'classes'  => $pdo->query("SELECT COUNT(*) FROM class_groups")->fetchColumn(),
        'plans'    => $pdo->query("SELECT COUNT(*) FROM study_plans")->fetchColumn(),
    ];
} catch (PDOException $e) {
    $stats = array_fill_keys(['students', 'teachers', 'subjects', 'rooms', 'classes', 'plans'], 0);
}
?>

<style>
    .admin-banner-bg {
        background-image: linear-gradient(to right bottom, rgba(153, 27, 27, 0.9), rgba(69, 10, 10, 0.95)), 
                          url('https://images.unsplash.com/photo-1562774053-701939374585?q=80&w=2586&auto=format&fit=crop');
        background-size: cover;
        background-position: center;
    }
</style>

<div class="max-w-7xl mx-auto pb-24 pt-15">
    
    <div class="mb-10 pl-2">
        <h1 class="text-3xl font-serif font-bold text-slate-800 mb-2 border-l-4 border-red-700 pl-3">
            Admin Dashboard
        </h1>
        <p class="text-slate-500 mb-6 font-light pl-4">ภาพรวมระบบและการจัดการข้อมูล</p>

        <div class="relative rounded-[2rem] overflow-hidden admin-banner-bg shadow-2xl text-white group border border-red-900/20">
            <div class="absolute top-0 right-0 w-96 h-96 bg-red-500 rounded-full blur-[100px] opacity-20 group-hover:opacity-30 transition duration-1000 -mr-20 -mt-20 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-orange-500 rounded-full blur-[80px] opacity-10 -ml-10 -mb-10 pointer-events-none"></div>
            
            <div class="relative z-10 p-8 md:p-10 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="max-w-xl">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/10 backdrop-blur-md text-red-100 text-xs font-bold uppercase tracking-wider mb-4 shadow-sm">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                        </span>
                        Core System
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold mb-3 leading-tight drop-shadow-md">
                        ระบบจัดตารางสอน<br><span class="text-red-200">อัตโนมัติ (AI Scheduler)</span>
                    </h2>
                    <p class="text-red-100 font-light text-lg opacity-90 drop-shadow-sm">
                        จัดการตารางเรียนที่ซับซ้อนให้เป็นเรื่องง่าย ด้วยระบบประมวลผลอัจฉริยะ ตรวจสอบเงื่อนไขห้องและเวลาได้ทันที
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                    <a href="auto_scheduler.php" class="relative group/btn flex items-center justify-center gap-3 bg-white text-red-900 px-8 py-4 rounded-2xl font-bold shadow-[0_10px_20px_-5px_rgba(0,0,0,0.3)] hover:shadow-[0_20px_30px_-10px_rgba(255,255,255,0.4)] hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-red-50 to-white opacity-0 group-hover/btn:opacity-100 transition duration-300"></div>
                        <i class="fa-solid fa-wand-magic-sparkles text-xl text-red-600 group-hover/btn:rotate-12 transition transform"></i>
                        <span class="relative z-10">เริ่มจัดตาราง</span>
                    </a>
                    <a href="view_schedule_master.php" class="relative group/btn flex items-center justify-center gap-3 bg-black/20 hover:bg-black/30 backdrop-blur-md border border-white/20 hover:border-white/40 text-white px-8 py-4 rounded-2xl font-bold transition-all duration-300 hover:-translate-y-1 shadow-lg">
                        <i class="fa-regular fa-calendar-check text-xl opacity-80 group-hover/btn:scale-110 transition"></i>
                        <span>ดูตารางรวม</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-10">
        <?php 
        function renderMiniStat($label, $value, $icon, $colorClass, $bgClass) {
            echo "
            <div class='bg-white p-5 rounded-2xl shadow-sm border border-slate-200 hover:shadow-lg hover:border-red-200 hover:-translate-y-1 transition-all duration-300 group cursor-default'>
                <div class='w-12 h-12 rounded-xl $bgClass $colorClass flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition duration-300 shadow-sm'>
                    <i class='fa-solid $icon'></i>
                </div>
                <div class='text-2xl font-bold text-slate-700 group-hover:text-red-900 transition'>".number_format($value)."</div>
                <div class='text-xs text-slate-400 font-medium uppercase tracking-wide'>$label</div>
            </div>";
        }
        renderMiniStat('นักเรียน', $stats['students'], 'fa-user-graduate', 'text-blue-600', 'bg-blue-50');
        renderMiniStat('ครูอาจารย์', $stats['teachers'], 'fa-chalkboard-user', 'text-amber-600', 'bg-amber-50');
        renderMiniStat('รายวิชา', $stats['subjects'], 'fa-book', 'text-emerald-600', 'bg-emerald-50');
        renderMiniStat('ห้องเรียน', $stats['rooms'], 'fa-door-open', 'text-purple-600', 'bg-purple-50');
        renderMiniStat('กลุ่มเรียน', $stats['classes'], 'fa-users', 'text-cyan-600', 'bg-cyan-50');
        renderMiniStat('แผนการเรียน', $stats['plans'], 'fa-file-lines', 'text-rose-600', 'bg-rose-50');
        ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php function renderMenuSection($title, $icon, $items) { ?>
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl hover:border-red-200 transition-all duration-300 group">
            <div class="bg-slate-50 p-5 border-b border-slate-100 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-red-600 flex items-center justify-center text-lg shadow-sm group-hover:bg-red-600 group-hover:text-white transition duration-300">
                    <i class="fa-solid <?=$icon?>"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-700 group-hover:text-red-800 transition"><?=$title?></h3>
            </div>
            <div class="p-3">
                <?php foreach($items as $item): ?>
                <a href="<?=$item[1]?>" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-red-50 transition-colors duration-200 group/link">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-sm group-hover/link:bg-red-100 group-hover/link:text-red-600 transition">
                        <i class="fa-solid <?=$item[2]?>"></i>
                    </div>
                    <div class="font-bold text-slate-600 text-sm group-hover/link:text-red-700 transition flex-1"><?=$item[0]?></div>
                    <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover/link:text-red-400 group-hover/link:translate-x-1 transition transform"></i>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php } ?>

        <?php renderMenuSection('งานหลักสูตร', 'fa-graduation-cap', [
            ['จัดการแผนการเรียน', 'manage_plans.php', 'fa-table-list'],
            ['ข้อมูลหลักสูตร', 'manage_curriculums.php', 'fa-scroll'],
            ['จัดการระดับชั้น', 'manage_levels.php', 'fa-layer-group'],
            ['กลุ่มอาชีพ/แผนก', 'manage_career_groups.php', 'fa-briefcase'],
            ['สาขาวิชา', 'manage_majors.php', 'fa-diagram-project']
        ]); ?>

        <?php renderMenuSection('ข้อมูลพื้นฐาน', 'fa-database', [
            ['จัดการครูผู้สอน', 'manage_teachers.php', 'fa-chalkboard-user'],
            ['จัดการนักเรียน', 'manage_students.php', 'fa-user-graduate'],
            ['จัดการกลุ่มเรียน', 'manage_class_groups.php', 'fa-users-rectangle'],
            ['รายวิชาทั้งหมด', 'manage_subjects.php', 'fa-book-open'],
            ['หมวดวิชา', 'manage_subject_groups.php', 'fa-tags'],
            ['ประเภทวิชา', 'manage_subject_types.php', 'fa-filter']
        ]); ?>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl hover:border-red-200 transition-all duration-300 group">
            <div class="bg-slate-50 p-5 border-b border-slate-100 flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-red-600 flex items-center justify-center text-lg shadow-sm group-hover:bg-red-600 group-hover:text-white transition duration-300">
                    <i class="fa-solid fa-gears"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-700 group-hover:text-red-800 transition">ตั้งค่าระบบ</h3>
            </div>
            <div class="p-3">
                <?php 
                $settings = [
                    ['ห้องเรียน/ปฏิบัติการ', 'manage_rooms.php', 'fa-door-open'],
                    ['คาบเรียน (Time Slots)', 'manage_time_slots.php', 'fa-clock'],
                    ['วันทำการ (Days)', 'manage_days.php', 'fa-calendar-days'],
                    ['ตั้งค่าทั่วไป', 'system_settings.php', 'fa-sliders']
                ];
                foreach($settings as $item) { ?>
                <a href="<?=$item[1]?>" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-red-50 transition-colors duration-200 group/link">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-sm group-hover/link:bg-red-100 group-hover/link:text-red-600 transition">
                        <i class="fa-solid <?=$item[2]?>"></i>
                    </div>
                    <div class="font-bold text-slate-600 text-sm group-hover/link:text-red-700 transition flex-1"><?=$item[0]?></div>
                    <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover/link:text-red-400 group-hover/link:translate-x-1 transition transform"></i>
                </a>
                <?php } ?>
                
                <div class="border-t border-slate-100 mt-2 pt-2">
                     <div class="px-3 py-2 text-xs text-slate-400 flex justify-between">
                        <span>Database Status:</span>
                        <span class="text-emerald-500 font-bold"><i class="fa-solid fa-circle text-[6px] align-middle mr-1"></i>Online</span>
                     </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>