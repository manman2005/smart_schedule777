<?php

session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin')
        header("Location: admin/index.php");
    elseif ($_SESSION['role'] == 'teacher')
        header("Location: teacher/index.php");
    elseif ($_SESSION['role'] == 'student')
        header("Location: student/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - CVC Smart System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800;900&family=Prompt:wght@300;400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Prompt', 'Sarabun', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        .font-premium { font-family: 'Playfair Display', 'Georgia', serif; }

        body {
            min-height: 100vh;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #334155;
        }

        /* === SPLIT CONTAINER === */
        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            max-width: 960px;
            min-height: 600px;
            border-radius: 28px;
            overflow: hidden;
            box-shadow:
                0 25px 60px rgba(0,0,0,0.12),
                0 8px 20px rgba(0,0,0,0.06);
            margin: 20px;
        }

        /* === LEFT PANEL (Branding) === */
        .panel-left {
            background: linear-gradient(160deg, #7f1d1d 0%, #991b1b 30%, #b91c1c 60%, #dc2626 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            overflow: hidden;
        }

        /* Decorative circles */
        .panel-left::before {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            top: -80px; right: -80px;
        }
        .panel-left::after {
            content: '';
            position: absolute;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            bottom: -50px; left: -50px;
        }

        /* Floating dots */
        .dot {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            animation: floatDot 6s ease-in-out infinite;
        }
        .dot-1 { width: 8px; height: 8px; top: 15%; left: 20%; animation-delay: 0s; }
        .dot-2 { width: 12px; height: 12px; top: 60%; left: 10%; animation-delay: 1.5s; }
        .dot-3 { width: 6px; height: 6px; top: 30%; right: 15%; animation-delay: 3s; }
        .dot-4 { width: 10px; height: 10px; bottom: 25%; right: 25%; animation-delay: 0.8s; }
        .dot-5 { width: 5px; height: 5px; top: 75%; left: 40%; animation-delay: 2.2s; }
        @keyframes floatDot {
            0%, 100% { transform: translateY(0) scale(1); opacity: 0.6; }
            50%      { transform: translateY(-12px) scale(1.3); opacity: 1; }
        }

        .brand-logo {
            position: relative; z-index: 2;
            width: 110px; height: 110px;
            margin-bottom: 24px;
            animation: fadeUp 0.7s ease-out both;
        }
        .brand-logo img {
            width: 100%; height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 8px 24px rgba(0,0,0,0.3));
        }
        .logo-ring {
            position: absolute; inset: -10px;
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 50%;
            animation: ringPulse 3s ease-out infinite;
        }
        .logo-ring-2 {
            position: absolute; inset: -20px;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 50%;
            animation: ringPulse 3s ease-out 0.6s infinite;
        }
        @keyframes ringPulse {
            0%   { transform: scale(1); opacity: 0.7; }
            100% { transform: scale(1.3); opacity: 0; }
        }

        .brand-title {
            position: relative; z-index: 2;
            text-align: center;
            animation: fadeUp 0.7s 0.15s ease-out both;
        }
        .brand-title h1 {
            color: #fff;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.2;
            margin-bottom: 4px;
        }
        .brand-title h1 .gold {
            background: linear-gradient(135deg, #ffd700 0%, #f5c542 40%, #daa520 70%, #ffd700 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: goldShimmer 3s linear infinite;
        }
        @keyframes goldShimmer {
            0%   { background-position: 0% center; }
            100% { background-position: 200% center; }
        }
        .brand-title .subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            font-weight: 400;
            margin-top: 4px;
        }

        .brand-desc {
            position: relative; z-index: 2;
            margin-top: 32px;
            animation: fadeUp 0.7s 0.3s ease-out both;
        }
        .brand-desc .divider-line {
            width: 50px; height: 2px;
            background: rgba(255,255,255,0.3);
            margin: 0 auto 20px;
            border-radius: 2px;
        }
        .brand-desc p {
            color: rgba(255,255,255,0.75);
            font-size: 13px;
            text-align: center;
            line-height: 1.8;
        }

        /* Feature pills */
        .feat-pills {
            position: relative; z-index: 2;
            display: flex;
            gap: 10px;
            margin-top: 28px;
            animation: fadeUp 0.7s 0.45s ease-out both;
        }
        .pill {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 12px;
            padding: 10px 14px;
            text-align: center;
            flex: 1;
            transition: all 0.3s ease;
        }
        .pill:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-3px);
        }
        .pill i {
            color: #fbbf24;
            font-size: 16px;
            display: block;
            margin-bottom: 6px;
        }
        .pill span {
            color: rgba(255,255,255,0.9);
            font-size: 11px;
            font-weight: 600;
        }

        /* === RIGHT PANEL (Form) === */
        .panel-right {
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px 44px;
            position: relative;
        }

        .form-header {
            margin-bottom: 28px;
            animation: fadeUp 0.6s 0.1s ease-out both;
        }
        .form-header h2 {
            color: #1e293b;
            font-size: 22px;
            font-weight: 700;
        }
        .form-header p {
            color: #94a3b8;
            font-size: 13px;
            margin-top: 4px;
        }

        /* Input */
        .input-group {
            margin-bottom: 20px;
        }
        .input-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
            padding-left: 2px;
        }
        .input-field {
            width: 100%;
            background: #f8fafc;
            border: 2px solid rgba(51,65,85,0.15);
            border-radius: 14px;
            color: #334155;
            font-size: 14px;
            padding: 14px 18px;
            transition: all 0.3s ease;
        }
        .input-field:focus {
            outline: none;
            border-color: #b91c1c;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(185,28,28,0.1);
        }
        .input-field::placeholder { color: #94a3b8; }

        /* Button */
        .btn-submit {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(145deg, #dc2626, #b91c1c);
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(220,38,38,0.35);
            position: relative;
            overflow: hidden;
            margin-top: 8px;
        }
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
            transition: left 0.5s ease;
        }
        .btn-submit:hover::before { left: 100%; }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(220,38,38,0.45);
        }

        /* Error */
        .error-box {
            background: #fef2f2;
            border: 1px solid rgba(185,28,28,0.15);
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.4s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%      { transform: translateX(-8px); }
            40%      { transform: translateX(8px); }
            60%      { transform: translateX(-4px); }
            80%      { transform: translateX(4px); }
        }

        /* Eye toggle */
        .eye-btn {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #94a3b8;
            cursor: pointer;
            transition: color 0.2s;
            font-size: 14px;
        }
        .eye-btn:hover { color: #dc2626; }

        /* Misc */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #94a3b8;
            font-size: 13px;
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover { color: #dc2626; }

        .separator {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.06), transparent);
            margin: 20px 0;
        }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .anim { animation: fadeUp 0.6s ease-out both; }
        .anim-d1 { animation: fadeUp 0.6s 0.1s ease-out both; }
        .anim-d2 { animation: fadeUp 0.6s 0.2s ease-out both; }
        .anim-d3 { animation: fadeUp 0.6s 0.3s ease-out both; }
        .anim-d4 { animation: fadeUp 0.6s 0.4s ease-out both; }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 480px;
                min-height: auto;
            }
            .panel-left {
                padding: 36px 28px;
            }
            .brand-logo { width: 80px; height: 80px; margin-bottom: 16px; }
            .brand-title h1 { font-size: 1.5rem; }
            .brand-desc { margin-top: 20px; }
            .feat-pills { flex-direction: column; }
            .panel-right { padding: 32px 28px; }
        }
    </style>
</head>
<body>

    <div class="login-container">

        <!-- LEFT PANEL: Branding -->
        <div class="panel-left">
            <!-- Floating dots -->
            <div class="dot dot-1"></div>
            <div class="dot dot-2"></div>
            <div class="dot dot-3"></div>
            <div class="dot dot-4"></div>
            <div class="dot dot-5"></div>

            <!-- Logo -->
            <div class="brand-logo">
                <div class="logo-ring"></div>
                <div class="logo-ring-2"></div>
                <img src="images/cvc_logo.png" alt="CVC Logo">
            </div>

            <!-- Title -->
            <div class="brand-title">
                <h1><span class="gold font-premium">CVC</span> Smart System</h1>
                <p class="subtitle">วิทยาลัยอาชีวศึกษาเชียงราย</p>
            </div>

            <!-- Description -->
            <div class="brand-desc">
                <div class="divider-line"></div>
                <p>
                    ระบบจัดตารางเรียนตารางสอนอัจฉริยะ<br>
                    บริหารจัดการง่าย รวดเร็ว แม่นยำ
                </p>
            </div>

            <!-- Feature pills -->
            <div class="feat-pills">
                <div class="pill">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>AI อัตโนมัติ</span>
                </div>
                <div class="pill">
                    <i class="fa-solid fa-bolt"></i>
                    <span>เรียลไทม์</span>
                </div>
                <div class="pill">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>ปลอดภัย</span>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: Login Form -->
        <div class="panel-right">

            <!-- Welcome -->
            <div class="form-header anim">
                <h2><i class="fa-solid fa-right-to-bracket text-red-500 mr-2" style="font-size: 20px;"></i>เข้าสู่ระบบ</h2>
                <p>กรอกข้อมูลของคุณเพื่อดำเนินการต่อ</p>
            </div>

            <!-- Error Message -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-box">
                    <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                    <span class="text-red-700 text-sm font-medium"><?php echo $_SESSION['error'];
    unset($_SESSION['error']); ?></span>
                </div>
            <?php
endif; ?>

            <!-- Login Form -->
            <form action="login_db.php" method="POST">
                
                <!-- Username -->
                <div class="input-group anim-d1">
                    <label class="input-label">
                        <i class="fa-solid fa-user mr-1 text-red-400/60"></i> ชื่อผู้ใช้งาน
                    </label>
                    <input type="text" name="username" required 
                        class="input-field" placeholder="username">
                </div>

                <!-- Password -->
                <div class="input-group anim-d2">
                    <label class="input-label">
                        <i class="fa-solid fa-lock mr-1 text-red-400/60"></i> รหัสผ่าน
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required 
                            class="input-field" placeholder="••••••••" style="padding-right: 44px;">
                        <button type="button" onclick="togglePassword()" class="eye-btn">
                            <i class="fa-solid fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <div class="anim-d3">
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-right-to-bracket mr-2"></i> เข้าสู่ระบบ
                    </button>
                </div>
            </form>

            <!-- Separator -->
            <div class="separator anim-d4"></div>

            <!-- Back Link -->
            <div class="text-center anim-d4">
                <a href="index.php" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i> กลับหน้าหลัก
                </a>
            </div>

        </div>

    </div>

    <script>
        function togglePassword() {
            const pw = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pw.type === 'password') {
                pw.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                pw.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

</body>
</html>