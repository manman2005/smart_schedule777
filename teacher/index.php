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
}
catch (Exception $e) {
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
                        <?php
else: ?>
                            <i class="fa-solid fa-user-tie text-6xl text-slate-300"></i>
                        <?php
endif; ?>
                    </div>
                </div>
                <a href="profile.php" class="absolute bottom-2 right-2 w-10 h-10 bg-white text-cvc-blue rounded-full flex items-center justify-center shadow-md hover:bg-cvc-blue hover:text-white transition border border-slate-100" title="แก้ไขรูปภาพ">
                    <i class="fa-solid fa-camera"></i>
                </a>
            </div>
            
            <div class="text-center md:text-left flex-1">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-cvc-sky/20 border border-cvc-blue/20 text-cvc-blue text-xs font-bold uppercase tracking-widest mb-3">
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
                    <a href="profile.php" class="btn-cvc text-sm px-6 shadow-lg shadow-red-500/20">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> แก้ไขข้อมูลส่วนตัว
                    </a>
                </div>
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

        /* ========= CARD VARIANTS ========= */
        /* Schedule - Blue */

        .card-schedule::after { background: radial-gradient(circle, rgba(59,130,246,0.15), transparent 70%); }
        .card-schedule .card-icon { background: linear-gradient(145deg, #eef2ff, #dbeafe); color: #3b82f6; box-shadow: 0 4px 16px rgba(59,130,246,0.12); }
        .card-schedule:hover .card-icon { background: linear-gradient(145deg, #3b82f6, #2563eb); color: #fff; box-shadow: 0 10px 32px rgba(59,130,246,0.35); }
        .card-schedule .card-icon-ring { border-color: rgba(59,130,246,0.25); }
        .card-schedule:hover h3 { color: #2563eb; }
        .card-schedule .card-arrow { background: rgba(59,130,246,0.08); color: #3b82f6; }
        .card-schedule:hover .card-arrow { background: #3b82f6; color: #fff; }
        .card-schedule .card-deco { width: 80px; height: 80px; background: rgba(59,130,246,0.04); bottom: -20px; left: 30%; }

        /* Booking - Amber/Gold */

        .card-booking::after { background: radial-gradient(circle, rgba(245,158,11,0.15), transparent 70%); }
        .card-booking .card-icon { background: linear-gradient(145deg, #fffbeb, #fef3c7); color: #d97706; box-shadow: 0 4px 16px rgba(245,158,11,0.12); }
        .card-booking:hover .card-icon { background: linear-gradient(145deg, #f59e0b, #d97706); color: #fff; box-shadow: 0 10px 32px rgba(245,158,11,0.35); }
        .card-booking .card-icon-ring { border-color: rgba(245,158,11,0.25); }
        .card-booking:hover h3 { color: #d97706; }
        .card-booking .card-arrow { background: rgba(245,158,11,0.08); color: #f59e0b; }
        .card-booking:hover .card-arrow { background: #f59e0b; color: #fff; }
        .card-booking .card-deco { width: 60px; height: 60px; background: rgba(245,158,11,0.04); bottom: 10px; left: 20%; }

        /* Student - Purple */

        .card-student::after { background: radial-gradient(circle, rgba(139,92,246,0.15), transparent 70%); }
        .card-student .card-icon { background: linear-gradient(145deg, #f5f3ff, #ede9fe); color: #7c3aed; box-shadow: 0 4px 16px rgba(139,92,246,0.12); }
        .card-student:hover .card-icon { background: linear-gradient(145deg, #8b5cf6, #7c3aed); color: #fff; box-shadow: 0 10px 32px rgba(139,92,246,0.35); }
        .card-student .card-icon-ring { border-color: rgba(139,92,246,0.25); }
        .card-student:hover h3 { color: #7c3aed; }
        .card-student .card-arrow { background: rgba(139,92,246,0.08); color: #8b5cf6; }
        .card-student:hover .card-arrow { background: #8b5cf6; color: #fff; }
        .card-student .card-deco { width: 70px; height: 70px; background: rgba(139,92,246,0.04); bottom: -10px; left: 40%; }

        /* Unavailability - Red */

        .card-unavail::after { background: radial-gradient(circle, rgba(239,68,68,0.15), transparent 70%); }
        .card-unavail .card-icon { background: linear-gradient(145deg, #fef2f2, #fee2e2); color: #dc2626; box-shadow: 0 4px 16px rgba(239,68,68,0.12); }
        .card-unavail:hover .card-icon { background: linear-gradient(145deg, #ef4444, #dc2626); color: #fff; box-shadow: 0 10px 32px rgba(239,68,68,0.35); }
        .card-unavail .card-icon-ring { border-color: rgba(239,68,68,0.25); }
        .card-unavail:hover h3 { color: #dc2626; }
        .card-unavail .card-arrow { background: rgba(239,68,68,0.08); color: #ef4444; }
        .card-unavail:hover .card-arrow { background: #ef4444; color: #fff; }
        .card-unavail .card-deco { width: 90px; height: 90px; background: rgba(239,68,68,0.04); bottom: -25px; left: 25%; }

        /* Disabled */
        .card-disabled { opacity: 0.55; filter: grayscale(0.7) brightness(1.05); }

        .card-disabled::after { display: none !important; }
        .card-disabled .card-icon { background: #f1f5f9 !important; color: #94a3b8 !important; box-shadow: none !important; }
        .card-disabled:hover { transform: none; box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 30px rgba(0,0,0,0.06); }
        .card-disabled:hover .card-icon { transform: none; background: #f1f5f9 !important; color: #94a3b8 !important; box-shadow: none !important; }
        .card-disabled:hover .card-icon-ring { opacity: 0 !important; }
        .card-disabled:hover h3 { color: #64748b !important; }
        .card-disabled:hover .card-arrow { opacity: 0 !important; }

        /* Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 700;
            position: absolute;
            top: 18px; right: 18px;
            z-index: 3;
            backdrop-filter: blur(8px);
            letter-spacing: 0.3px;
        }
        .badge-open {
            background: rgba(16,185,129,0.1);
            color: #059669;
            border: 1px solid rgba(16,185,129,0.2);
        }
        .badge-closed {
            background: rgba(239,68,68,0.08);
            color: #dc2626;
            border: 1px solid rgba(239,68,68,0.15);
        }
    </style>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- ตารางสอนของฉัน -->
        <a href="my_schedule.php" class="dashboard-card card-schedule">
            <div class="card-shimmer"></div>
            <div class="card-deco"></div>
            <div class="card-icon-wrap">
                <div class="card-icon-ring"></div>
                <div class="card-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-slate-800">ตารางสอนของฉัน</h3>
                <p>ตรวจสอบวัน เวลา และห้องเรียนที่คุณต้องทำการสอนในภาคเรียนนี้</p>
            </div>
            <div class="card-arrow"><i class="fa-solid fa-arrow-right"></i></div>
        </a>

        <!-- จองรายวิชาสอน -->
        <?php if ($booking_status == '1'): ?>
            <a href="booking.php" class="dashboard-card card-booking">
                <div class="card-shimmer"></div>
                <div class="card-deco"></div>
                <div class="status-badge badge-open">
                    <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div> เปิดให้จอง
                </div>
                <div class="card-icon-wrap">
                    <div class="card-icon-ring"></div>
                    <div class="card-icon">
                        <i class="fa-solid fa-hand-pointer"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-slate-800">จองรายวิชาสอน</h3>
                    <p>เลือกรายวิชาที่เปิดให้สอนตามแผนการเรียนเพื่อลงทะเบียนสอน</p>
                </div>
                <div class="card-arrow"><i class="fa-solid fa-arrow-right"></i></div>
            </a>
        <?php
else: ?>
            <a href="booking.php" class="dashboard-card card-booking card-disabled">
                <div class="status-badge badge-closed">
                    <i class="fa-solid fa-lock text-[9px]"></i> ปิดระบบ
                </div>
                <div class="card-icon-wrap">
                    <div class="card-icon">
                        <i class="fa-solid fa-hand-pointer"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-slate-500">จองรายวิชาสอน</h3>
                    <p>ขณะนี้ระบบปิดการจองรายวิชา กรุณาติดต่อเจ้าหน้าที่</p>
                </div>
            </a>
        <?php
endif; ?>

        <!-- ค้นหาตารางเรียนนักเรียน -->
        <a href="student_schedule.php" class="dashboard-card card-student">
            <div class="card-shimmer"></div>
            <div class="card-deco"></div>
            <div class="card-icon-wrap">
                <div class="card-icon-ring"></div>
                <div class="card-icon">
                    <i class="fa-solid fa-users-viewfinder"></i>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-slate-800">ค้นหาตารางเรียนนักเรียน</h3>
                <p>ดูตารางเรียนของกลุ่มเรียนต่างๆ ในฐานะครูที่ปรึกษา</p>
            </div>
            <div class="card-arrow"><i class="fa-solid fa-arrow-right"></i></div>
        </a>

        <!-- ระบุเวลาที่ไม่สะดวก -->
        <?php if ($unavail_status == '1'): ?>
            <a href="unavailability.php" class="dashboard-card card-unavail">
                <div class="card-shimmer"></div>
                <div class="card-deco"></div>
                <div class="status-badge badge-open">
                    <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div> เปิดใช้งาน
                </div>
                <div class="card-icon-wrap">
                    <div class="card-icon-ring"></div>
                    <div class="card-icon">
                        <i class="fa-solid fa-user-clock"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-slate-800">ระบุเวลาที่ไม่สะดวก</h3>
                    <p>กำหนดวันและคาบเรียนที่คุณติดภารกิจหรือไม่สามารถทำการสอนได้</p>
                </div>
                <div class="card-arrow"><i class="fa-solid fa-arrow-right"></i></div>
            </a>
        <?php
else: ?>
            <a href="unavailability.php" class="dashboard-card card-unavail card-disabled">
                <div class="status-badge badge-closed">
                    <i class="fa-solid fa-lock text-[9px]"></i> ปิดระบบ
                </div>
                <div class="card-icon-wrap">
                    <div class="card-icon">
                        <i class="fa-solid fa-user-clock"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-slate-500">ระบุเวลาที่ไม่สะดวก</h3>
                    <p>ระบบปิดการแก้ไขข้อมูลวันเวลาที่ไม่สะดวกแล้ว</p>
                </div>
            </a>
        <?php
endif; ?>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
