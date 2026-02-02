<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkStudent();

$stu_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT s.*, c.cla_name FROM students s JOIN class_groups c ON s.cla_id = c.cla_id WHERE s.stu_id = ?");
$stmt->execute([$stu_id]);
$student = $stmt->fetch();
$profile_pic = $student['stu_img'] ?? null;

require_once '../includes/header.php';
?>

<div class="max-w-5xl mx-auto">
    
    <div class="relative rounded-[2.5rem] bg-gradient-to-r from-cvc-blue via-blue-800 to-cvc-blue p-1 shadow-2xl mb-10 overflow-hidden">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-cvc-gold/20 rounded-full blur-[80px]"></div>

        <div class="relative bg-white/95 rounded-[2.3rem] p-8 md:p-12 flex flex-col md:flex-row items-center gap-8 backdrop-blur-xl">
            
            <div class="relative">
                <div class="w-36 h-36 rounded-full p-[3px] bg-gradient-to-b from-cvc-gold to-transparent">
                    <div class="w-full h-full rounded-full bg-slate-100 overflow-hidden flex items-center justify-center">
                        <?php if (!empty($profile_pic) && file_exists("../uploads/students/" . $profile_pic)): ?>
                            <img src="../uploads/students/<?php echo $profile_pic; ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fa-solid fa-user-graduate text-5xl text-slate-300"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="profile.php" class="absolute bottom-0 right-0 w-10 h-10 bg-slate-900 text-white rounded-full flex items-center justify-center hover:bg-cvc-gold transition shadow-lg border-4 border-white">
                    <i class="fa-solid fa-camera text-xs"></i>
                </a>
            </div>

            <div class="text-center md:text-left flex-1">
                <span class="text-xs font-bold text-cvc-gold tracking-[0.2em] uppercase mb-2 block">Vocational Student</span>
                <h1 class="text-3xl md:text-4xl font-serif font-bold text-slate-800 mb-2"><?php echo htmlspecialchars($student['stu_fullname']); ?></h1>
                <div class="flex flex-col md:flex-row items-center gap-3 text-slate-500 text-sm mb-6">
                    <span class="bg-slate-100 px-3 py-1 rounded-full"><i class="fa-regular fa-id-card mr-1"></i> <?php echo $student['stu_id']; ?></span>
                    <span class="hidden md:inline text-slate-300">•</span>
                    <span class="text-cvc-blue font-bold">กลุ่มเรียน: <?php echo $student['cla_name']; ?></span>
                </div>
                <a href="profile.php" class="inline-flex items-center text-sm font-bold text-slate-400 hover:text-cvc-blue transition border-b border-transparent hover:border-cvc-blue pb-0.5">
                    แก้ไขข้อมูลส่วนตัว <i class="fa-solid fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="my_schedule.php" class="group card-premium p-8 flex flex-col justify-between min-h-[200px] hover:border-cvc-blue/30 transition">
            <div class="flex justify-between items-start">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-cvc-blue flex items-center justify-center text-2xl group-hover:scale-110 transition duration-500 shadow-sm">
                    <i class="fa-solid fa-table-list"></i>
                </div>
                <div class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-300 group-hover:bg-cvc-blue group-hover:text-white group-hover:border-transparent transition">
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </div>
            </div>
            <div>
                <h3 class="text-2xl font-serif font-bold text-slate-800 mb-2 mt-4 group-hover:text-cvc-blue transition">ตารางเรียนของฉัน</h3>
                <p class="text-sm text-slate-500">ตรวจสอบรายวิชา ห้องเรียน และเวลาเรียนประจำภาคการศึกษานี้</p>
            </div>
        </a>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>