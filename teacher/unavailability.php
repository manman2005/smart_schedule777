<?php
// htdocs/teacher/unavailability.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkTeacher();

$tea_id = $_SESSION['user_id'];

// ตรวจสอบสถานะระบบ
$stmt_sys = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'teacher_unavailability'");
$stmt_sys->execute();
$system_status = $stmt_sys->fetchColumn();


// ดึงข้อมูลวันและคาบเรียน
$days = $pdo->query("SELECT * FROM days WHERE day_id BETWEEN 1 AND 5")->fetchAll();
$time_slots = $pdo->query("SELECT * FROM time_slots ORDER BY tim_start ASC")->fetchAll();

// ดึงข้อมูลที่เคยบันทึกไว้
$stmt_busy = $pdo->prepare("SELECT CONCAT(day_id, '-', tim_id) as slot_key FROM teacher_unavailability WHERE tea_id = ?");
$stmt_busy->execute([$tea_id]);
$busy_data = $stmt_busy->fetchAll(PDO::FETCH_COLUMN);

// นับจำนวน
$total_clickable = 0;
$total_busy = count($busy_data);
foreach ($days as $d) {
    foreach ($time_slots as $s) {
        if (strpos($s['tim_range'], '12:00') !== 0)
            $total_clickable++;
    }
}

require_once '../includes/header.php';
?>

<style>
    .unavail-page { max-width: 72rem; margin: 0 auto; padding-bottom: 80px; }

    /* === HERO HEADER (RED THEME) === */
    .hero-header {
        position: relative;
        background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 30%, #b91c1c 60%, #dc2626 100%);
        border-radius: 28px;
        padding: 40px 44px;
        margin-bottom: 32px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(127,29,29,0.3);
    }
    .hero-header::before {
        content: '';
        position: absolute;
        width: 400px; height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.08), transparent 70%);
        top: -150px; right: -100px;
        border-radius: 50%;
    }
    .hero-header::after {
        content: '';
        position: absolute;
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(251,191,36,0.08), transparent 70%);
        bottom: -120px; left: -80px;
        border-radius: 50%;
    }
    .hero-grid {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        z-index: 2;
    }
    .hero-left { display: flex; align-items: center; gap: 20px; }
    .hero-icon {
        width: 64px; height: 64px;
        border-radius: 20px;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.2);
        display: flex; align-items: center; justify-content: center;
        font-size: 28px;
        color: #fff;
        flex-shrink: 0;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }
    .hero-title {
        font-size: 1.65rem; font-weight: 800; color: #fff;
        letter-spacing: -0.3px; margin-bottom: 4px;
    }
    .hero-sub { font-size: 13px; color: rgba(255,255,255,0.7); }
    .hero-back {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 22px; border-radius: 14px;
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.2);
        color: #fecaca; font-weight: 700; font-size: 13px;
        text-decoration: none; transition: all 0.3s ease;
    }
    .hero-back:hover {
        background: rgba(255,255,255,0.2); color: #fff;
        transform: translateX(-3px);
    }

    /* === STATS CARDS === */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px; margin-bottom: 28px;
    }
    .stat-card {
        background: #fff; border-radius: 20px;
        padding: 22px 24px;
        display: flex; align-items: center; gap: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 24px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
        transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 40px rgba(0,0,0,0.1);
    }
    .stat-icon {
        width: 52px; height: 52px; border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; flex-shrink: 0;
    }
    .stat-value { font-size: 1.8rem; font-weight: 800; letter-spacing: -0.5px; line-height: 1; }
    .stat-label { font-size: 12px; font-weight: 600; color: #94a3b8; margin-top: 4px; }

    /* === GUIDE BANNER (RED) === */
    .guide-banner {
        position: relative;
        background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 50%, #fef2f2 100%);
        border: 1px solid #fecaca;
        border-radius: 20px;
        padding: 22px 28px; margin-bottom: 28px;
        display: flex; align-items: center; gap: 18px;
        overflow: hidden;
    }
    .guide-banner::before {
        content: '';
        position: absolute; top: 0; right: 0;
        width: 120px; height: 120px;
        background: radial-gradient(circle, rgba(220,38,38,0.06), transparent 70%);
        border-radius: 50%;
    }
    .guide-pulse {
        width: 48px; height: 48px; min-width: 48px;
        border-radius: 16px;
        background: linear-gradient(145deg, #dc2626, #b91c1c);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        box-shadow: 0 6px 20px rgba(185,28,28,0.3);
        animation: guidePulse 3s ease-in-out infinite;
        position: relative; z-index: 2;
    }
    @keyframes guidePulse {
        0%, 100% { box-shadow: 0 6px 20px rgba(185,28,28,0.3); }
        50% { box-shadow: 0 6px 28px rgba(185,28,28,0.5); }
    }
    .guide-text {
        font-size: 14px; color: #334155; line-height: 1.7;
        position: relative; z-index: 2;
    }
    .guide-text strong { color: #b91c1c; }

    /* === LEGEND === */
    .legend-bar {
        display: flex; gap: 6px; margin-bottom: 24px; flex-wrap: wrap;
    }
    .legend-chip {
        display: flex; align-items: center; gap: 8px;
        padding: 8px 16px; border-radius: 12px;
        background: #fff; border: 1px solid #e2e8f0;
        font-size: 13px; font-weight: 700; color: #475569;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .legend-swatch { width: 18px; height: 18px; border-radius: 6px; }

    /* === TABLE CARD === */
    .table-card {
        background: #fff; border-radius: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 12px 40px rgba(0,0,0,0.07);
        border: 1px solid rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .table-inner { padding: 32px; }

    /* === SCHEDULE TABLE === */
    .sched-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 5px;
    }
    .sched-table th { padding: 0; }

    /* Corner cell */
    .th-corner {
        background: linear-gradient(145deg, #991b1b, #7f1d1d);
        border-radius: 14px;
        padding: 14px; min-width: 100px;
        color: #fecaca;
        font-size: 12px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        text-align: center;
    }

    /* Time header */
    .th-time {
        background: linear-gradient(180deg, #fef2f2, #fee2e2);
        border-radius: 14px;
        padding: 14px 6px;
        text-align: center; min-width: 74px;
        border: 1px solid #fecaca;
    }
    .th-time .t-start {
        display: block; font-size: 15px; font-weight: 800; color: #991b1b;
        letter-spacing: -0.3px;
    }
    .th-time .t-end {
        display: block; font-size: 10px; font-weight: 500; color: #dc2626;
        margin-top: 2px; opacity: 0.6;
    }

    /* Day cell */
    .td-day { padding: 0; }
    .day-pill {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 16px 14px; border-radius: 14px;
        font-size: 15px; font-weight: 800; color: #991b1b;
        background: linear-gradient(180deg, #fef2f2, #fee2e2);
        border: 1px solid #fecaca;
        white-space: nowrap;
    }

    /* === SLOT CELLS === */
    .slot {
        position: relative; height: 60px;
        border-radius: 14px; text-align: center;
        transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
        cursor: pointer; user-select: none;
        overflow: hidden;
    }

    /* Available */
    .slot-free {
        background: #ffffff;
        border: 2px solid #e2e8f0;
    }
    .slot-free::after {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(145deg, transparent 40%, rgba(255,255,255,0.5));
        border-radius: 12px; pointer-events: none;
    }
    .slot-free:hover {
        border-color: #fca5a5;
        background: #fff5f5;
        transform: scale(1.08);
        box-shadow: 0 8px 24px rgba(220,38,38,0.12);
        z-index: 5;
    }
    .slot-free .slot-ico {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        color: #d4d4d8; font-size: 16px; z-index: 2;
        transition: all 0.3s ease;
    }
    .slot-free:hover .slot-ico {
        color: #ef4444;
        transform: scale(1.15);
    }

    /* Busy */
    .slot-busy {
        background: linear-gradient(145deg, #ef4444, #dc2626);
        border: 2px solid #b91c1c;
        box-shadow: 0 4px 16px rgba(220,38,38,0.3), inset 0 1px 0 rgba(255,255,255,0.15);
    }
    .slot-busy::after {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(145deg, rgba(255,255,255,0.12) 0%, transparent 50%);
        border-radius: 12px; pointer-events: none;
    }
    .slot-busy:hover {
        background: linear-gradient(145deg, #f87171, #ef4444);
        transform: scale(1.08);
        box-shadow: 0 10px 32px rgba(220,38,38,0.4);
        z-index: 5;
    }
    .slot-busy .slot-ico {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 20px; z-index: 2;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        transition: all 0.3s ease;
    }
    .slot-busy:hover .slot-ico { transform: scale(1.2) rotate(90deg); }

    /* Lunch */
    .slot-lunch {
        background: repeating-linear-gradient(-45deg, #fef2f2, #fef2f2 5px, #fee2e2 5px, #fee2e2 10px);
        border: 2px solid #fecaca; cursor: default;
    }
    .slot-lunch .slot-ico {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        color: #f87171; font-size: 15px; opacity: 0.5;
    }

    /* Disabled */
    .slot-locked {
        cursor: not-allowed !important; filter: saturate(0.5) brightness(1.05);
    }
    .slot-locked:hover { transform: none !important; box-shadow: none !important; }

    /* Pop animation */
    @keyframes slotPop {
        0% { transform: scale(0.9); }
        50% { transform: scale(1.12); }
        100% { transform: scale(1); }
    }
    .slot-pop { animation: slotPop 0.35s cubic-bezier(0.23, 1, 0.32, 1); }

    /* Entrance */
    @keyframes fadeSlotIn {
        from { opacity: 0; transform: scale(0.85); }
        to { opacity: 1; transform: scale(1); }
    }
    .sched-table tbody td.slot {
        animation: fadeSlotIn 0.4s ease-out both;
    }
    <?php
foreach ($days as $di => $d):
    foreach ($time_slots as $si => $s):
        $delay = ($di * 0.04) + ($si * 0.02);
?>
    .sched-table tbody tr:nth-child(<?php echo $di + 1; ?>) td:nth-child(<?php echo $si + 2; ?>) {
        animation-delay: <?php echo $delay; ?>s;
    }
    <?php
    endforeach;
endforeach; ?>

    /* === BOTTOM BAR === */
    .bottom-bar {
        background: linear-gradient(135deg, #fef2f2, #fff1f2);
        border-top: 1px solid #fecaca;
        padding: 24px 32px;
        display: flex; align-items: center;
        justify-content: space-between; gap: 20px; flex-wrap: wrap;
    }
    .summary-chips { display: flex; gap: 12px; }
    .summary-chip {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 20px; border-radius: 14px;
        background: #fff; border: 1px solid #fecaca;
        box-shadow: 0 2px 8px rgba(220,38,38,0.06);
    }
    .chip-count { font-size: 1.25rem; font-weight: 800; letter-spacing: -0.5px; }
    .chip-label { font-size: 12px; font-weight: 600; }

    /* Save button */
    .btn-save {
        position: relative;
        display: inline-flex; align-items: center; gap: 12px;
        padding: 14px 44px; border-radius: 16px;
        background: linear-gradient(145deg, #dc2626, #991b1b);
        color: #fff; font-weight: 800; font-size: 16px;
        border: none; cursor: pointer;
        box-shadow: 0 8px 28px rgba(185,28,28,0.35), inset 0 1px 0 rgba(255,255,255,0.15);
        transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        overflow: hidden; letter-spacing: -0.2px;
    }
    .btn-save::before {
        content: '';
        position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
    }
    .btn-save:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 16px 44px rgba(185,28,28,0.45);
    }
    .btn-save:hover::before { animation: btnShine 0.7s ease forwards; }
    @keyframes btnShine { 0% { left: -50%; } 100% { left: 120%; } }
    .btn-save:active { transform: translateY(-1px) scale(1); }

    .btn-save-off {
        display: inline-flex; align-items: center; gap: 12px;
        padding: 14px 44px; border-radius: 16px;
        background: #e2e8f0; color: #94a3b8;
        font-weight: 800; font-size: 16px;
        border: none; cursor: not-allowed;
    }

    /* Alerts */
    .alert-lock {
        position: relative;
        background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);
        border: 1px solid #fecaca; border-radius: 20px;
        padding: 22px 28px; margin-bottom: 28px;
        display: flex; align-items: center; gap: 18px;
        overflow: hidden;
    }
    .alert-lock::before {
        content: '';
        position: absolute; width: 180px; height: 180px;
        background: radial-gradient(circle, rgba(220,38,38,0.06), transparent 70%);
        top: -60px; right: -40px; border-radius: 50%;
    }
    .alert-lock-ico {
        width: 52px; height: 52px; min-width: 52px;
        border-radius: 16px;
        background: linear-gradient(145deg, #fee2e2, #fecaca);
        color: #dc2626;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        box-shadow: 0 4px 12px rgba(220,38,38,0.12);
        position: relative; z-index: 2;
    }
    .alert-ok {
        background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
        border: 1px solid #bbf7d0; border-radius: 20px;
        padding: 18px 28px; margin-bottom: 28px;
        display: flex; align-items: center; gap: 14px;
        animation: alertIn 0.5s ease-out;
    }
    @keyframes alertIn {
        from { opacity: 0; transform: translateY(-12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive */
    @media (max-width: 900px) {
        .hero-grid { flex-direction: column; gap: 16px; align-items: flex-start; }
        .hero-header { padding: 28px 24px; border-radius: 20px; }
        .stats-row { grid-template-columns: 1fr; }
        .table-inner { padding: 16px; }
        .sched-table { border-spacing: 3px; }
        .slot { height: 48px; border-radius: 10px; }
        .th-time { border-radius: 10px; min-width: 54px; padding: 10px 4px; }
        .th-time .t-start { font-size: 12px; }
        .th-corner { border-radius: 10px; min-width: 72px; }
        .day-pill { border-radius: 10px; padding: 12px 8px; font-size: 13px; }
        .bottom-bar { flex-direction: column; padding: 20px 16px; }
        .legend-bar { gap: 4px; }
        .legend-chip { padding: 6px 10px; font-size: 11px; }
    }
</style>

<div class="unavail-page">

    <!-- HERO HEADER -->
    <div class="hero-header">
        <div class="hero-grid">
            <div class="hero-left">
                <div class="hero-icon"><i class="fa-solid fa-user-clock"></i></div>
                <div>
                    <h1 class="hero-title">กำหนดเวลาที่ไม่สะดวกสอน</h1>
                    <p class="hero-sub">เลือกช่วงเวลาที่คุณไม่สามารถทำการสอนได้ ระบบจะหลีกเลี่ยงคาบเหล่านี้ในการจัดตาราง</p>
                </div>
            </div>
            <a href="index.php" class="hero-back">
                <i class="fa-solid fa-arrow-left"></i> กลับหน้าหลัก
            </a>
        </div>
    </div>

    <?php if ($system_status == '0'): ?>
        <div class="alert-lock">
            <div class="alert-lock-ico"><i class="fa-solid fa-lock"></i></div>
            <div style="position:relative;z-index:2;">
                <h3 style="font-size:16px;font-weight:800;color:#991b1b;margin-bottom:4px;">ระบบปิดการแก้ไขแล้ว</h3>
                <p style="font-size:13px;color:#b91c1c;line-height:1.6;">ขณะนี้ไม่สามารถแก้ไขข้อมูลได้ หากต้องการเปลี่ยนแปลงโปรดติดต่อเจ้าหน้าที่</p>
            </div>
        </div>
    <?php
elseif (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="alert-ok">
            <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(145deg,#22c55e,#16a34a);color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 4px 12px rgba(22,163,74,0.3);flex-shrink:0;">
                <i class="fa-solid fa-check"></i>
            </div>
            <span style="font-weight:700;color:#15803d;font-size:15px;">บันทึกข้อมูลเรียบร้อยแล้ว!</span>
        </div>
    <?php
endif; ?>

    <!-- STATS -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(145deg,#fef2f2,#fee2e2);color:#dc2626;">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div>
                <div class="stat-value" style="color:#b91c1c;"><?php echo $total_clickable; ?></div>
                <div class="stat-label">คาบทั้งหมด</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(145deg,#fef2f2,#fee2e2);color:#f87171;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="stat-value" style="color:#991b1b;" id="statAvail"><?php echo $total_clickable - $total_busy; ?></div>
                <div class="stat-label">คาบที่ว่าง</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(145deg,#fee2e2,#fecaca);color:#dc2626;">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <div>
                <div class="stat-value" style="color:#dc2626;" id="statBusy"><?php echo $total_busy; ?></div>
                <div class="stat-label">คาบที่ไม่สะดวก</div>
            </div>
        </div>
    </div>

    <?php if ($system_status == '1'): ?>
    <div class="guide-banner">
        <div class="guide-pulse"><i class="fa-solid fa-hand-pointer"></i></div>
        <p class="guide-text">
            <strong>คลิก</strong>ที่ช่องเพื่อระบุว่า<strong>ไม่สะดวกสอน</strong> — ช่องจะเปลี่ยนเป็นสีแดง คลิกอีกครั้งเพื่อเปลี่ยนกลับ
        </p>
    </div>
    <?php
endif; ?>

    <!-- LEGEND -->
    <div class="legend-bar">
        <div class="legend-chip">
            <div class="legend-swatch" style="background:#fff;border:2px solid #e2e8f0;"></div>
            ว่างสอน
        </div>
        <div class="legend-chip">
            <div class="legend-swatch" style="background:linear-gradient(145deg,#ef4444,#dc2626);border:2px solid #b91c1c;"></div>
            ไม่สะดวก
        </div>
        <div class="legend-chip">
            <div class="legend-swatch" style="background:repeating-linear-gradient(-45deg,#fef2f2,#fef2f2 4px,#fee2e2 4px,#fee2e2 8px);border:2px solid #fecaca;"></div>
            พักเที่ยง
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <form action="save_unavailability.php" method="POST" id="unavailForm">
            <div class="table-inner">
                <div class="overflow-x-auto" style="padding-bottom:4px;">
                    <table class="sched-table">
                        <thead>
                            <tr>
                                <th><div class="th-corner"><i class="fa-regular fa-clock" style="margin-right:4px;"></i> วัน/เวลา</div></th>
                                <?php foreach ($time_slots as $slot):
    $parts = explode('-', $slot['tim_range']);
    $start = trim($parts[0] ?? '');
    $end = trim($parts[1] ?? '');
?>
                                <th>
                                    <div class="th-time">
                                        <span class="t-start"><?php echo substr($start, 0, 5); ?></span>
                                        <span class="t-end"><?php echo substr($end, 0, 5); ?></span>
                                    </div>
                                </th>
                                <?php
endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($days as $di => $day): ?>
                            <tr>
                                <td class="td-day">
                                    <div class="day-pill"><?php echo $day['day_name']; ?></div>
                                </td>
                                <?php foreach ($time_slots as $slot):
        $slot_key = $day['day_id'] . '-' . $slot['tim_id'];
        $is_busy = in_array($slot_key, $busy_data);
        $is_lunch = (strpos($slot['tim_range'], '12:00') === 0);
        $is_disabled = ($system_status == '0');
?>
                                    <?php if ($is_lunch): ?>
                                        <td class="slot slot-lunch" data-key="<?php echo $slot_key; ?>">
                                            <div class="slot-ico"><i class="fa-solid fa-utensils"></i></div>
                                        </td>
                                    <?php
        else: ?>
                                        <td class="slot <?php echo $is_busy ? 'slot-busy' : 'slot-free'; ?> <?php echo $is_disabled ? 'slot-locked' : ''; ?>" 
                                            data-key="<?php echo $slot_key; ?>"
                                            <?php if (!$is_disabled): ?>onclick="toggleSlot(this)"<?php
            endif; ?>>
                                            <input type="checkbox" name="busy_slots[]" value="<?php echo $slot_key; ?>" 
                                                style="position:absolute;opacity:0;pointer-events:none;" 
                                                <?php echo $is_busy ? 'checked' : ''; ?>
                                                <?php echo $is_disabled ? 'disabled' : ''; ?>>
                                            <div class="slot-ico">
                                                <?php if ($is_disabled && $is_busy): ?>
                                                    <i class="fa-solid fa-lock"></i>
                                                <?php
            elseif ($is_busy): ?>
                                                    <i class="fa-solid fa-xmark"></i>
                                                <?php
            else: ?>
                                                    <i class="fa-solid fa-minus" style="opacity:0.2;font-size:12px;"></i>
                                                <?php
            endif; ?>
                                            </div>
                                        </td>
                                    <?php
        endif; ?>
                                <?php
    endforeach; ?>
                            </tr>
                            <?php
endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BOTTOM BAR -->
            <div class="bottom-bar">
                <div class="summary-chips">
                    <div class="summary-chip">
                        <div class="chip-count" style="color:#991b1b;" id="cntFree"><?php echo $total_clickable - $total_busy; ?></div>
                        <div class="chip-label" style="color:#b91c1c;">คาบว่าง</div>
                    </div>
                    <div class="summary-chip">
                        <div class="chip-count" style="color:#dc2626;" id="cntBusy"><?php echo $total_busy; ?></div>
                        <div class="chip-label" style="color:#ef4444;">ไม่สะดวก</div>
                    </div>
                </div>
                <?php if ($system_status == '1'): ?>
                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> บันทึกข้อมูล
                    </button>
                <?php
else: ?>
                    <button type="button" class="btn-save-off" disabled>
                        <i class="fa-solid fa-lock"></i> ระบบปิดการแก้ไข
                    </button>
                <?php
endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
    const totalClickable = <?php echo $total_clickable; ?>;

    function toggleSlot(cell) {
        if(cell.classList.contains('slot-lunch') || cell.classList.contains('slot-locked')) return;

        const checkbox = cell.querySelector('input[type="checkbox"]');
        const iconDiv = cell.querySelector('.slot-ico');

        cell.classList.remove('slot-pop');
        void cell.offsetWidth;
        cell.classList.add('slot-pop');

        if (checkbox.checked) {
            checkbox.checked = false;
            cell.classList.remove('slot-busy');
            cell.classList.add('slot-free');
            iconDiv.innerHTML = '<i class="fa-solid fa-minus" style="opacity:0.2;font-size:12px;"></i>';
        } else {
            checkbox.checked = true;
            cell.classList.remove('slot-free');
            cell.classList.add('slot-busy');
            iconDiv.innerHTML = '<i class="fa-solid fa-xmark"></i>';
        }
        
        updateCounts();
    }

    function updateCounts() {
        let busy = 0;
        document.querySelectorAll('.slot:not(.slot-lunch)').forEach(cell => {
            const cb = cell.querySelector('input[type="checkbox"]');
            if (cb && cb.checked) busy++;
        });
        const free = totalClickable - busy;
        document.getElementById('cntBusy').textContent = busy;
        document.getElementById('cntFree').textContent = free;
        document.getElementById('statBusy').textContent = busy;
        document.getElementById('statAvail').textContent = free;
    }
</script>

<?php require_once '../includes/footer.php'; ?>