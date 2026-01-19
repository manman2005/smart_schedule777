<?php
// htdocs/teacher/index.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkTeacher();

$tea_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM teachers WHERE tea_id = ?");
$stmt->execute([$tea_id]);
$teacher = $stmt->fetch();
$profile_pic = $teacher['tea_img'] ?? null;

// --- [NEW] ดึงค่าสถานะระบบ (เปิด/ปิด) มาเช็คก่อนแสดงผล ---
try {
    $stmt_sys = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    $settings = $stmt_sys->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // ถ้าหาไม่เจอ ให้ค่าเริ่มต้นเป็น 1 (เปิด) หรือ 0 (ปิด) ตามที่คุณต้องการ
    $booking_status = $settings['teacher_booking'] ?? '1'; 
    $unavail_status = $settings['teacher_unavailability'] ?? '1';
} catch (Exception $e) {
    // กรณี Database Error หรือยังไม่สร้างตาราง
    $booking_status = '0';
    $unavail_status = '0';
}
// -----------------------------------------------------

require_once '../includes/header.php';
?>

<div class="max-w-5xl mx-auto">
    
    <div class="relative overflow-hidden rounded-[2rem] bg-white shadow-xl border border-slate-100 mb-10 group">
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-cvc-blue/10 to-cvc-gold/10 rounded-full blur-3xl -mr-16 -mt-16 transition duration-700 group-hover:scale-125"></div>
        
        <div class="relative z-10 p-8 md:p-10 flex flex-col md:flex-row items-center gap-10">
            <div class="relative">
                <div class="w-40 h-40 rounded-full p-1 bg-gradient-to-tr from-cvc-blue via-cvc-sky to-cvc-gold shadow-lg">
                    <div class="w-full h-full rounded-full bg-white overflow-hidden flex items-center justify-center relative">
                        <?php if (!empty($profile_pic) && file_exists("../uploads/teachers/" . $profile_pic)): ?>
                            <img src="../uploads/teachers/<?php echo $profile_pic; ?>" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                        <?php else: ?>
                            <i class="fa-solid fa-user-tie text-6xl text-slate-300"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="profile.php" class="absolute bottom-2 right-2 w-10 h-10 bg-white text-cvc-blue rounded-full flex items-center justify-center shadow-md hover:bg-cvc-blue hover:text-white transition border border-slate-100" title="แก้ไขรูปภาพ">
                    <i class="fa-solid fa-camera"></i>
                </a>
            </div>
            
            <div class="text-center md:text-left flex-1">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 border border-blue-100 text-cvc-blue text-xs font-bold uppercase tracking-widest mb-3">
                    <i class="fa-solid fa-chalkboard-user"></i> Teacher Profile
                </div>
                <h1 class="text-4xl font-serif font-bold text-slate-800 mb-2 leading-tight">
                    <?php echo htmlspecialchars($teacher['tea_fullname']); ?>
                </h1>
                <p class="text-slate-500 font-medium mb-6 flex items-center justify-center md:justify-start gap-3">
                    <span class="bg-slate-100 px-2 py-1 rounded text-xs">ID: <?php echo $teacher['tea_code']; ?></span>
                    <span class="text-slate-300">|</span>
                    <span><?php echo $teacher['tea_email'] ? $teacher['tea_email'] : 'ยังไม่ระบุอีเมล'; ?></span>
                </p>
                
                <div class="flex flex-wrap justify-center md:justify-start gap-4">
                    <a href="profile.php" class="btn-cvc text-sm px-6 shadow-lg shadow-blue-500/20">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> แก้ไขข้อมูลส่วนตัว
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <a href="my_schedule.php" class="card-premium p-8 flex items-start gap-6 group cursor-pointer border-l-4 border-l-cvc-blue">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-cvc-blue flex items-center justify-center text-3xl shadow-inner group-hover:bg-cvc-blue group-hover:text-white transition duration-300">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-cvc-blue transition font-serif">ตารางสอนของฉัน</h3>
                <p class="text-sm text-slate-500 font-light leading-relaxed">ตรวจสอบวัน เวลา และห้องเรียนที่คุณต้องทำการสอนในภาคเรียนนี้</p>
            </div>
        </a>

        <?php if ($booking_status == '1'): ?>
            <a href="booking.php" class="card-premium p-8 flex items-start gap-6 group cursor-pointer border-l-4 border-l-cvc-gold relative overflow-hidden">
                <div class="absolute top-3 right-3 flex items-center gap-1 bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded text-[10px] font-bold border border-emerald-200">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div> เปิดให้จอง
                </div>
                <div class="w-16 h-16 rounded-2xl bg-yellow-50 text-cvc-gold flex items-center justify-center text-3xl shadow-inner group-hover:bg-cvc-gold group-hover:text-white transition duration-300">
                    <i class="fa-solid fa-hand-pointer"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-cvc-gold transition font-serif">จองรายวิชาสอน</h3>
                    <p class="text-sm text-slate-500 font-light leading-relaxed">เลือกรายวิชาที่เปิดให้สอนตามแผนการเรียนเพื่อลงทะเบียนสอน</p>
                </div>
            </a>
        <?php else: ?>
            <a href="booking.php" class="card-premium p-8 flex items-start gap-6 group cursor-pointer border-l-4 border-l-slate-300 bg-slate-50 relative overflow-hidden opacity-90 grayscale">
                <div class="absolute top-3 right-3 flex items-center gap-1 bg-red-100 text-red-700 px-2 py-0.5 rounded text-[10px] font-bold border border-red-200">
                    <i class="fa-solid fa-lock text-[10px]"></i> ปิดระบบ
                </div>
                <div class="w-16 h-16 rounded-2xl bg-slate-200 text-slate-400 flex items-center justify-center text-3xl shadow-inner">
                    <i class="fa-solid fa-hand-pointer"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-500 mb-2 font-serif">จองรายวิชาสอน</h3>
                    <p class="text-sm text-slate-400 font-light leading-relaxed">ขณะนี้ระบบปิดการจองรายวิชา กรุณาติดต่อเจ้าหน้าที่</p>
                </div>
            </a>
        <?php endif; ?>

        <a href="student_schedule.php" class="card-premium p-8 flex items-start gap-6 group cursor-pointer border-l-4 border-l-sky-500">
            <div class="w-16 h-16 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-3xl shadow-inner group-hover:bg-sky-500 group-hover:text-white transition duration-300">
                <i class="fa-solid fa-users-viewfinder"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-sky-600 transition font-serif">ค้นหาตารางเรียนนักเรียน</h3>
                <p class="text-sm text-slate-500 font-light leading-relaxed">ดูตารางเรียนของกลุ่มเรียนต่างๆ ในฐานะครูที่ปรึกษา</p>
            </div>
        </a>

        <?php if ($unavail_status == '1'): ?>
            <a href="unavailability.php" class="card-premium p-8 flex items-start gap-6 group cursor-pointer border-l-4 border-l-red-500 relative overflow-hidden">
                <div class="absolute top-3 right-3 flex items-center gap-1 bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded text-[10px] font-bold border border-emerald-200">
                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div> เปิดใช้งาน
                </div>
                <div class="w-16 h-16 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center text-3xl shadow-inner group-hover:bg-red-500 group-hover:text-white transition duration-300">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-red-500 transition font-serif">ระบุเวลาที่ไม่สะดวก</h3>
                    <p class="text-sm text-slate-500 font-light leading-relaxed">กำหนดวันและคาบเรียนที่คุณติดภารกิจหรือไม่สามารถทำการสอนได้</p>
                </div>
            </a>
        <?php else: ?>
            <a href="unavailability.php" class="card-premium p-8 flex items-start gap-6 group cursor-pointer border-l-4 border-l-slate-300 bg-slate-50 relative overflow-hidden opacity-90 grayscale">
                <div class="absolute top-3 right-3 flex items-center gap-1 bg-red-100 text-red-700 px-2 py-0.5 rounded text-[10px] font-bold border border-red-200">
                     <i class="fa-solid fa-lock text-[10px]"></i> ปิดระบบ
                </div>
                <div class="w-16 h-16 rounded-2xl bg-slate-200 text-slate-400 flex items-center justify-center text-3xl shadow-inner">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-500 mb-2 font-serif">ระบุเวลาที่ไม่สะดวก</h3>
                    <p class="text-sm text-slate-400 font-light leading-relaxed">ระบบปิดการแก้ไขข้อมูลวันเวลาที่ไม่สะดวกแล้ว</p>
                </div>
            </a>
        <?php endif; ?>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>