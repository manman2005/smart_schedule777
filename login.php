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
    <title>Sign In - Chiang Rai Vocational College</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,600;1,600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Sarabun', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        cvc: {
                            blue: '#b91c1c',  /* ใช้โทนแดงให้ตรงกับหน้า index แอดมิน */
                            sky: '#f87171',
                            navy: '#450a0a',
                            gold: '#fbbf24',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            font-family: 'Sarabun', sans-serif;
            background-color: #f3f4f6;
            background-image: 
                linear-gradient(to right bottom, rgba(185, 28, 28, 0.95), rgba(69, 10, 10, 0.98)),
                url('https://images.unsplash.com/photo-1562774053-701939374585?q=80&w=2586&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .backdrop-overlay {
            background: radial-gradient(circle at top left, rgba(248, 250, 252, 0.18), transparent 55%),
                        radial-gradient(circle at bottom right, rgba(248, 250, 252, 0.15), transparent 55%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="absolute inset-0 backdrop-overlay"></div>

    <div class="bg-white/95 backdrop-blur-xl w-full max-w-4xl rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row relative z-10 min-h-[550px]">
        
        <div class="w-full md:w-1/2 bg-gradient-to-br from-red-900 via-red-800 to-red-950 text-white p-12 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-red-500 rounded-full blur-[100px] opacity-20 -mr-16 -mt-16"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-yellow-500 rounded-full blur-[80px] opacity-20 -ml-10 -mb-10"></div>
            
            <div class="relative z-10 flex flex-col items-start h-full justify-center">
                <div class="w-24 h-24 mb-8 p-3 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur-md border border-white/30 shadow-xl">
                     <img src="/images/cvc_logo.png" alt="CVC Logo" class="w-full h-full object-contain drop-shadow-lg">
                </div>
                
                <h2 class="text-4xl font-serif font-bold leading-tight mb-4">Chiang Rai<br>Vocational College</h2>
                <p class="text-red-100 font-light text-sm leading-relaxed">
                    ระบบบริหารจัดการตารางเรียนตารางสอนและงานวิชาการออนไลน์ เพื่อความเป็นเลิศทางวิชาชีพ
                </p>
            </div>
            
            <div class="relative z-10 text-xs text-red-200/60 font-mono mt-auto">
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
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-red-500 outline-none transition pl-11"
                            placeholder="ชื่อผู้ใช้งาน">
                        <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Password</label>
                    <div class="relative">
                        <input type="password" name="password" required 
                            class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-red-500 outline-none transition pl-11"
                            placeholder="รหัสผ่าน">
                        <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                </div>

                <button type="submit" 
                    class="w-full py-4 bg-gradient-to-r from-red-700 to-red-500 hover:from-red-800 hover:to-red-600 text-white rounded-xl font-bold shadow-lg shadow-red-500/30 transition transform hover:-translate-y-0.5 mt-2">
                    Sign In <i class="fa-solid fa-arrow-right ml-2"></i>
                </button>
            </form>
            
            <div class="mt-8 text-center">
                <a href="index.php" class="text-sm text-slate-400 hover:text-red-600 transition font-medium">← กลับหน้าหลัก</a>
            </div>
        </div>
    </div>

</body>
</html>