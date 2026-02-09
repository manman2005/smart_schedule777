<?php 
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') header("Location: admin/index.php");
    elseif ($_SESSION['role'] == 'teacher') header("Location: teacher/index.php");
    elseif ($_SESSION['role'] == 'student') header("Location: student/index.php");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Prompt', 'Sarabun', sans-serif; }
        
        body {
            background: linear-gradient(135deg, #450a0a 0%, #7f1d1d 50%, #450a0a 100%);
            min-height: 100vh;
            overflow: hidden;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .floating-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: float 10s ease-in-out infinite;
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            top: -150px;
            left: -150px;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            bottom: -100px;
            right: -100px;
            animation-delay: -5s;
        }

        .orb-3 {
            width: 250px;
            height: 250px;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            top: 40%;
            right: 15%;
            animation-delay: -2.5s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-40px) scale(1.05); }
        }

        /* Grid Pattern */
        .grid-pattern {
            position: fixed;
            inset: 0;
            background-image: 
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            z-index: 1;
        }

        /* Login Card */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.1);
        }

        /* Input Fields */
        .input-field {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            color: #334155;
            transition: all 0.3s ease;
        }

        .input-field:focus {
            background: #ffffff;
            border-color: #b91c1c;
            box-shadow: 0 0 0 4px rgba(185, 28, 28, 0.12);
            outline: none;
        }

        .input-field::placeholder {
            color: #94a3b8;
        }

        /* Submit Button */
        .btn-submit {
            background: linear-gradient(135deg, #b91c1c 0%, #450a0a 100%);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(185, 28, 28, 0.35);
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(185, 28, 28, 0.45);
        }

        /* Logo Animation */
        .logo-container {
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        /* Particles */
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(251, 191, 36, 0.5);
            border-radius: 50%;
            animation: particleFloat 5s linear infinite;
        }

        @keyframes particleFloat {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) scale(1); opacity: 0; }
        }

        /* CVC Brand Header */
        .brand-header {
            background: linear-gradient(135deg, #b91c1c 0%, #450a0a 100%);
        }

        /* Error Alert */
        .error-alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
        }

        /* Decorative Elements */
        .corner-decoration {
            position: absolute;
            width: 150px;
            height: 150px;
            border: 3px solid rgba(251, 191, 36, 0.2);
        }

        .corner-top-left {
            top: -2px;
            left: -2px;
            border-right: none;
            border-bottom: none;
            border-radius: 24px 0 0 0;
        }

        .corner-bottom-right {
            bottom: -2px;
            right: -2px;
            border-left: none;
            border-top: none;
            border-radius: 0 0 24px 0;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">

    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="floating-orb orb-1"></div>
        <div class="floating-orb orb-2"></div>
        <div class="floating-orb orb-3"></div>
    </div>
    <div class="grid-pattern"></div>

    <!-- Particles -->
    <div class="bg-animation" id="particles"></div>

    <!-- Main Container -->
    <div class="relative z-10 w-full max-w-5xl animate__animated animate__fadeIn">
        
        <div class="login-card rounded-3xl overflow-hidden flex flex-col lg:flex-row shadow-2xl">
            
            <!-- Left Side - Branding -->
            <div class="brand-header lg:w-1/2 p-10 lg:p-12 text-white relative overflow-hidden">
                <!-- Decorative Circles -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-[80px] -mr-20 -mt-20"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-yellow-500/20 rounded-full blur-[60px] -ml-10 -mb-10"></div>
                
                <div class="relative z-10 h-full flex flex-col justify-center">
                    <!-- Logo -->
                    <div class="logo-container mb-8">
                        <div class="w-36 h-36 inline-block">
                            <img src="/images/cvc_logo.png" alt="CVC Logo" class="w-full h-full object-contain drop-shadow-2xl">
                        </div>
                    </div>
                    
                    <!-- Text -->
                    <h1 class="text-3xl lg:text-4xl font-bold mb-4 leading-tight">
                        Chiang Rai<br>Vocational College
                    </h1>
                    <p class="text-red-100/80 text-sm leading-relaxed mb-8">
                        ระบบบริหารจัดการตารางเรียนตารางสอน<br>และงานวิชาการออนไลน์
                    </p>
                    
                    <!-- Features -->
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center gap-3 text-red-100/80">
                            <i class="fa-solid fa-check-circle text-yellow-400"></i>
                            <span>จัดตารางอัตโนมัติด้วย AI</span>
                        </div>
                        <div class="flex items-center gap-3 text-red-100/80">
                            <i class="fa-solid fa-check-circle text-yellow-400"></i>
                            <span>ดูตารางเรียน/สอนออนไลน์</span>
                        </div>
                        <div class="flex items-center gap-3 text-red-100/80">
                            <i class="fa-solid fa-check-circle text-yellow-400"></i>
                            <span>บริหารจัดการข้อมูลครบวงจร</span>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="relative z-10 mt-auto pt-8 text-xs text-red-200/50 font-mono">
                    © 2025 CVC Smart System v2.0
                </div>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="lg:w-1/2 p-10 lg:p-12 bg-white relative">
                
                <!-- Corner Decorations -->
                <div class="corner-decoration corner-top-left"></div>
                <div class="corner-decoration corner-bottom-right"></div>
                
                <!-- Header -->
                <div class="mb-8 relative z-10">
                    <h2 class="text-2xl lg:text-3xl font-bold text-slate-800 mb-2">ยินดีต้อนรับ 👋</h2>
                    <p class="text-slate-500">กรุณาเข้าสู่ระบบเพื่อดำเนินการต่อ</p>
                </div>

                <!-- Error Message -->
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="error-alert rounded-xl px-4 py-3 mb-6 flex items-center gap-3 animate__animated animate__shakeX">
                        <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                        <span class="text-red-600 text-sm font-medium"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form action="login_db.php" method="POST" class="space-y-5 relative z-10">
                    
                    <!-- Username -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">
                            <i class="fa-solid fa-user mr-1 text-red-500"></i> Username
                        </label>
                        <input type="text" name="username" required 
                            class="input-field w-full px-5 py-4 rounded-xl text-sm font-medium"
                            placeholder="ชื่อผู้ใช้งาน">
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">
                            <i class="fa-solid fa-lock mr-1 text-red-500"></i> Password
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required 
                                class="input-field w-full px-5 py-4 rounded-xl text-sm font-medium pr-12"
                                placeholder="รหัสผ่าน">
                            <button type="button" onclick="togglePassword()" 
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 transition">
                                <i class="fa-solid fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit w-full py-4 rounded-xl font-bold text-white text-lg mt-4">
                        <i class="fa-solid fa-right-to-bracket mr-2"></i> เข้าสู่ระบบ
                    </button>
                </form>
                
                <!-- Back Link -->
                <div class="text-center mt-10 relative z-10">
                    <a href="index.php" class="text-slate-400 text-sm hover:text-red-600 transition inline-flex items-center gap-2 font-medium">
                        <i class="fa-solid fa-arrow-left"></i> กลับหน้าหลัก
                    </a>
                </div>
            </div>
        </div>
        
    </div>

    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const password = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Create Particles
        function createParticles() {
            const container = document.getElementById('particles');
            for (let i = 0; i < 15; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 5 + 's';
                particle.style.animationDuration = (Math.random() * 4 + 4) + 's';
                container.appendChild(particle);
            }
        }
        createParticles();
    </script>

</body>
</html>