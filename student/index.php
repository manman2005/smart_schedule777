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
    
    <div class="relative rounded-[2.5rem] bg-gradient-to-r from-cvc-blue via-cvc-navy to-cvc-blue p-1 shadow-2xl mb-10 overflow-hidden">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-cvc-gold/20 rounded-full blur-[80px]"></div>

        <div class="relative bg-white/95 rounded-[2.3rem] p-8 md:p-12 flex flex-col md:flex-row items-center gap-8 backdrop-blur-xl">
            
            <div class="relative">
                <div class="w-36 h-36 rounded-full p-[3px] bg-gradient-to-b from-cvc-gold to-transparent">
                    <div class="w-full h-full rounded-full bg-slate-100 overflow-hidden flex items-center justify-center">
                        <?php if (!empty($profile_pic) && file_exists("../uploads/students/" . $profile_pic)): ?>
                            <img src="../uploads/students/<?php echo $profile_pic; ?>" class="w-full h-full object-cover">
                        <?php
else: ?>
                            <i class="fa-solid fa-user-graduate text-5xl text-slate-300"></i>
                        <?php
endif; ?>
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

    <style>
        /* === ULTRA PREMIUM DASHBOARD CARDS === */
        .dashboard-card {
            position: relative;
            background: #ffffff;
            border-radius: 24px;
            padding: 32px 30px 28px;
            display: flex;
            align-items: flex-start;
            gap: 22px;
            text-decoration: none;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
            box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 30px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.04);
        }

        /* Hover glow orb */
        .dashboard-card::after {
            content: '';
            position: absolute;
            width: 180px; height: 180px;
            border-radius: 50%;
            top: -60px; right: -60px;
            opacity: 0;
            transition: all 0.6s ease;
            filter: blur(40px);
            pointer-events: none;
        }

        .dashboard-card:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: 0 24px 64px rgba(0,0,0,0.1), 0 8px 24px rgba(0,0,0,0.06);
            border-color: transparent;
        }
        .dashboard-card:hover::after { opacity: 1; transform: scale(1.2); }

        /* Shimmer sweep on hover */
        .card-shimmer {
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: none;
            pointer-events: none;
        }
        .dashboard-card:hover .card-shimmer {
            animation: sweepShimmer 0.8s ease forwards;
        }
        @keyframes sweepShimmer {
            0%   { left: -50%; }
            100% { left: 120%; }
        }

        /* Decorative floating shape */
        .card-deco {
            position: absolute;
            border-radius: 50%;
            opacity: 0;
            transition: all 0.6s ease;
            pointer-events: none;
        }
        .dashboard-card:hover .card-deco { opacity: 1; }

        /* Icon */
        .dashboard-card .card-icon-wrap {
            position: relative;
        }
        .dashboard-card .card-icon {
            width: 64px; height: 64px;
            min-width: 64px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
            position: relative;
            z-index: 2;
        }
        .card-icon-ring {
            position: absolute;
            inset: -6px;
            border-radius: 24px;
            border: 2px dashed transparent;
            opacity: 0;
            transition: all 0.5s ease;
            z-index: 1;
        }
        .dashboard-card:hover .card-icon {
            transform: scale(1.1) rotate(-5deg);
        }
        .dashboard-card:hover .card-icon-ring {
            opacity: 1;
            animation: spinRing 8s linear infinite;
        }
        @keyframes spinRing {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Arrow */
        .dashboard-card .card-arrow {
            position: absolute;
            bottom: 24px; right: 28px;
            width: 36px; height: 36px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            opacity: 0;
            transform: translateX(-6px);
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            z-index: 2;
        }
        .dashboard-card:hover .card-arrow {
            opacity: 1;
            transform: translateX(0);
        }

        /* Text */
        .dashboard-card h3 {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 6px;
            transition: color 0.3s ease;
            position: relative;
            z-index: 2;
            letter-spacing: -0.2px;
        }
        .dashboard-card p {
            font-size: 0.82rem;
            color: #94a3b8;
            line-height: 1.7;
            position: relative;
            z-index: 2;
        }

        /* Schedule - Blue */
        .card-schedule::after { background: radial-gradient(circle, rgba(59,130,246,0.15), transparent 70%); }
        .card-schedule .card-icon { background: linear-gradient(145deg, #eef2ff, #dbeafe); color: #3b82f6; box-shadow: 0 4px 16px rgba(59,130,246,0.12); }
        .card-schedule:hover .card-icon { background: linear-gradient(145deg, #3b82f6, #2563eb); color: #fff; box-shadow: 0 10px 32px rgba(59,130,246,0.35); }
        .card-schedule .card-icon-ring { border-color: rgba(59,130,246,0.25); }
        .card-schedule:hover h3 { color: #2563eb; }
        .card-schedule .card-arrow { background: rgba(59,130,246,0.08); color: #3b82f6; }
        .card-schedule:hover .card-arrow { background: #3b82f6; color: #fff; }
        .card-schedule .card-deco { width: 80px; height: 80px; background: rgba(59,130,246,0.04); bottom: -20px; left: 30%; }
    </style>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- ตารางเรียนของฉัน -->
        <a href="my_schedule.php" class="dashboard-card card-schedule">
            <div class="card-shimmer"></div>
            <div class="card-deco"></div>
            <div class="card-icon-wrap">
                <div class="card-icon-ring"></div>
                <div class="card-icon">
                    <i class="fa-solid fa-table-list"></i>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-slate-800">ตารางเรียนของฉัน</h3>
                <p>ตรวจสอบรายวิชา ห้องเรียน และเวลาเรียนประจำภาคการศึกษานี้</p>
            </div>
            <div class="card-arrow"><i class="fa-solid fa-arrow-right"></i></div>
        </a>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
