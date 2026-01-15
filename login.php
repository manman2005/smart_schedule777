<?php 
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') header("Location: admin/dashboard.php");
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
    <title>Sign In - Chiang Rai Vocational College</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,600;1,600&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Sarabun', sans-serif;
            background-color: #f0f9ff;
            background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }
        .backdrop-overlay {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.9), rgba(15, 23, 42, 0.8));
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="absolute inset-0 backdrop-overlay"></div>

    <div class="bg-white/95 backdrop-blur-xl w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row relative z-10 min-h-[550px]">
        
        <div class="w-full md:w-1/2 bg-gradient-to-br from-blue-900 to-slate-900 text-white p-12 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500 rounded-full blur-[100px] opacity-20 -mr-16 -mt-16"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-yellow-500 rounded-full blur-[80px] opacity-20 -ml-10 -mb-10"></div>
            
            <div class="relative z-10 flex flex-col items-start h-full justify-center">
                <div class="w-24 h-24 mb-8 p-3 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-md border border-white/30 shadow-xl">
                     <img src="/images/cvc_logo.png" alt="CVC Logo" class="w-full h-full object-contain drop-shadow-lg">
                </div>
                
                <h2 class="text-4xl font-serif font-bold leading-tight mb-4">Chiang Rai<br>Vocational College</h2>
                <p class="text-blue-200 font-light text-sm leading-relaxed">
                    ระบบบริหารจัดการตารางเรียนและงานวิชาการออนไลน์ เพื่อความเป็นเลิศทางวิชาชีพ
                </p>
            </div>
            
            <div class="relative z-10 text-xs text-blue-300/60 font-mono mt-auto">
                &copy; 2025 CVC Smart System.
            </div>
        </div>

        <div class="w-full md:w-1/2 p-10 md:p-14 flex flex-col justify-center bg-white">
            <div class="mb-8">
                <h3 class="text-2xl font-bold text-slate-800 mb-1">เข้าสู่ระบบ</h3>
                <p class="text-slate-500 text-sm">กรุณาระบุข้อมูลเพื่อยืนยันตัวตน</p>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-50 text-red-600 px-4 py-3 rounded-xl mb-6 text-sm font-medium flex items-center border border-red-100 animate-pulse">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="login_db.php" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Username</label>
                    <div class="relative">
                        <input type="text" name="username" required 
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-blue-500 outline-none transition pl-11"
                            placeholder="ชื่อผู้ใช้งาน">
                        <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Password</label>
                    <div class="relative">
                        <input type="password" name="password" required 
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-blue-500 outline-none transition pl-11"
                            placeholder="รหัสผ่าน">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                </div>

                <button type="submit" 
                    class="w-full py-4 bg-gradient-to-r from-blue-800 to-blue-600 hover:from-blue-900 hover:to-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-0.5 mt-2">
                    Sign In <i class="fa-solid fa-arrow-right ml-2"></i>
                </button>
            </form>
            
            <div class="mt-8 text-center">
                <a href="index.php" class="text-sm text-slate-400 hover:text-blue-600 transition font-medium">← กลับหน้าหลัก</a>
            </div>
        </div>
    </div>

</body>
</html>