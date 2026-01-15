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
    <title>ระบบบริการการศึกษา - วิทยาลัยอาชีวศึกษาเชียงราย</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Prompt:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Sarabun', 'sans-serif'],
                        display: ['Prompt', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        cvc: {
                            blue: '#1e40af',
                            sky: '#38bdf8',
                            navy: '#0f172a',
                            gold: '#d4af37',
                            purple: '#6366f1', 
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #f3f4f6;
            background-image: 
                url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M15 10h10v10H15V10zm35 0h10v10H50V10zm35 0h10v10H85V10zM15 45h10v10H15V45zm35 0h10v10H50V45zm35 0h10v10H85V45zM15 80h10v10H15V80zm35 0h10v10H50V80zm35 0h10v10H85V80zM5 25h90v5H5v-5zm0 35h90v5H5v-5zm0 35h90v5H5v-5zM25 5h5v90h-5V5zm35 0h5v90h-5V5zm35 0h5v90h-5V5z' fill='%239ca3af' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E"), 
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239ca3af' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            background-repeat: repeat;
            background-position: center center;
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .card-premium {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid white;
            border-radius: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }
        
        .btn-cvc {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 8px 24px;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-cvc:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.4);
        }

        input, select, textarea {
            background-color: #fff;
            border: 1px solid #cbd5e1 !important;
            border-radius: 12px !important;
            padding: 10px 14px !important;
            transition: all 0.3s ease;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
            outline: none;
        }
        
        table { border-collapse: separate; border-spacing: 0; width: 100%; }
        th { background-color: #f8fafc; color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; border-bottom: 2px solid #e2e8f0; }
        td { border-bottom: 1px solid #f1f5f9; }
        tr:last-child td { border-bottom: none; }
    </style>
</head>
<body>

    <div class="fixed top-0 left-0 right-0 z-50 flex justify-center pt-5 px-4">
        <nav class="w-full max-w-[85rem] bg-gradient-to-r from-slate-900 via-blue-900 to-slate-900 backdrop-blur-xl rounded-full shadow-[0_8px_30px_rgb(15,23,42,0.4)] border border-slate-700/50 px-6 py-3 flex justify-between items-center transition-all hover:shadow-[0_15px_40px_rgb(15,23,42,0.5)]">
            
            <a href="" class="flex items-center gap-4 pl-2 group">
                <div class="relative z-10 group-hover:scale-110 transition duration-500">
                    <div class="bg-white/10 rounded-full p-2 backdrop-blur-sm"> <img src="/images/cvc_logo.png" alt="CVC Logo" class="w-16 h-16 object-contain filter drop-shadow-md">
                    </div>
                </div>
                <div class="leading-tight">
                    <h1 class="text-2xl font-serif font-bold text-white tracking-wide group-hover:text-blue-200 transition">
                        CVC <span class="text-cvc-gold">SmartSystem</span>
                    </h1>
                    <p class="text-xs text-slate-300 font-sans tracking-widest uppercase font-semibold mt-0.5 group-hover:text-white transition">ChiangRai Vocational College</p>
                </div>
            </a>

            <div class="flex items-center pr-2">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center gap-5">
                        <div class="hidden md:flex flex-col items-end mr-2">
                            <span class="text-base font-bold text-slate-100 leading-none mb-1">
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </span>
                            <span class="text-[10px] bg-white/10 text-blue-200 border border-white/20 px-3 py-0.5 rounded-full font-bold uppercase tracking-wide shadow-sm">
                                <?php echo ucfirst($_SESSION['role']); ?>
                            </span>
                        </div>
                        
                        <a href="../logout.php" class="w-12 h-12 bg-white/10 border border-white/10 text-red-400 rounded-full flex items-center justify-center hover:bg-red-600 hover:text-white hover:border-red-600 transition-all duration-300 shadow-sm group" title="ออกจากระบบ">
                            <i class="fa-solid fa-power-off text-lg group-hover:scale-110 transition"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="bg-white text-blue-900 hover:bg-blue-50 px-8 py-3 rounded-full text-base font-bold transition shadow-lg flex items-center gap-2 transform hover:-translate-y-0.5">
                        <span>เข้าสู่ระบบ</span>
                        <i class="fa-solid fa-arrow-right text-sm opacity-70"></i>
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </div>

    <main class="flex-grow max-w-7xl mx-auto px-4 lg:px-8 pt-36 pb-10 w-full relative z-10">