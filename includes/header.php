<?php
// smart_schedule/includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CVC Smart Schedule</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 
                        sans: ['Sarabun', 'sans-serif'], 
                        display: ['Prompt', 'sans-serif'] 
                    },
                    colors: { 
                        cvc: { 
                            blue: '#b91c1c',  /* เปลี่ยนเป็นแดง */
                            sky: '#f87171',   /* แดงอ่อน */
                            navy: '#450a0a',  /* แดงเลือดหมู */
                            gold: '#fbbf24'
                        } 
                    }
                }
            }
        }
    </script>

    <style>
        body {
            /* พื้นหลัง Pattern แบบเดิมที่คุณต้องการ */
            background-color: #f3f4f6;
            background-image: 
                url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M15 10h10v10H15V10zm35 0h10v10H50V10zm35 0h10v10H85V10zM15 45h10v10H15V45zm35 0h10v10H50V45zm35 0h10v10H85V45zM15 80h10v10H15V80zm35 0h10v10H50V80zm35 0h10v10H85V80zM5 25h90v5H5v-5zm0 35h90v5H5v-5zm0 35h90v5H5v-5zM25 5h5v90h-5V5zm35 0h5v90h-5V5zm35 0h5v90h-5V5z' fill='%239ca3af' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E"), 
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239ca3af' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            background-repeat: repeat;
            background-position: center center;
            background-attachment: fixed; /* ล็อคพื้นหลังเวลาเลื่อน */
            color: #334155;
            min-height: 100vh;
        }
        .swal2-popup { font-family: 'Sarabun', sans-serif !important; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #999; }
    </style>
</head>
<body>

    <div class="fixed top-0 left-0 right-0 z-50 flex justify-center pt-5 px-4 print:hidden">
        <nav class="w-full max-w-[85rem] bg-gradient-to-r from-red-900 via-red-800 to-red-950 backdrop-blur-xl rounded-full shadow-[0_8px_30px_rgb(69,10,10,0.4)] border border-red-700/50 px-6 py-3 flex justify-between items-center transition-all hover:shadow-[0_15px_40px_rgb(69,10,10,0.5)]">
            
            <div class="flex items-center gap-3">
                <a href="../index.php" class="flex-shrink-0 bg-white/10 p-1.5 rounded-full hover:bg-white/20 transition hover:scale-105">
                    <img class="h-10 w-10 object-contain filter drop-shadow-md" src="../images/cvc_logo.png" alt="Logo">
                </a>
                <div class="hidden md:block leading-tight">
                    <div class="text-white font-bold text-lg tracking-wide">
                        CVC <span class="text-cvc-gold">SmartSystem</span>
                    </div>
                    <div class="text-red-200 text-[10px] font-light uppercase tracking-wider">
                        ChiangRai Vocational College
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center gap-3 bg-black/20 py-1.5 px-2 pr-4 rounded-full border border-white/10 hover:bg-black/30 transition cursor-default">
                        <div class="h-9 w-9 rounded-full bg-white text-red-800 flex items-center justify-center font-bold shadow-sm text-sm overflow-hidden border-2 border-red-100">
                            <?php 
                                if(isset($_SESSION['user_img']) && !empty($_SESSION['user_img'])) {
                                    echo "<img src='../uploads/".$_SESSION['role']."s/".$_SESSION['user_img']."' class='w-full h-full object-cover'>";
                                } else {
                                    echo "<i class='fa-solid fa-user'></i>";
                                }
                            ?>
                        </div>
                        <div class="text-right hidden sm:block">
                            <p class="text-[10px] text-red-200 font-light uppercase tracking-wider">ยินดีต้อนรับ</p>
                            <p class="text-xs text-white font-bold truncate max-w-[120px]">
                                <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User'; ?>
                            </p>
                        </div>
                    </div>
                    
                    <a href="../logout.php" class="h-10 w-10 flex items-center justify-center rounded-full bg-red-800 hover:bg-red-600 text-red-100 hover:text-white transition shadow-lg border border-red-700" title="ออกจากระบบ">
                        <i class="fa-solid fa-power-off"></i>
                    </a>
                <?php else: ?>
                    <a href="../login.php" class="text-red-900 text-sm font-bold bg-white hover:bg-red-50 px-6 py-2.5 rounded-full transition border border-red-100 shadow-lg flex items-center gap-2">
                        เข้าสู่ระบบ
                    </a>
                <?php endif; ?>
            </div>

        </nav>
    </div>
    
    <div class="h-32 print:hidden"></div>
    
    <div class="min-h-screen px-4 pb-12 md:px-6 lg:px-8 max-w-7xl mx-auto">