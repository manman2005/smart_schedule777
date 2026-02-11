<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config/db.php';
if (!isset($pdo)) { die("<div style='color:red;text-align:center;padding:50px;'>Error: Database Connection Failed.</div>"); }
try {
    $classes = $pdo->query("SELECT * FROM class_groups ORDER BY cla_id ASC")->fetchAll();
    $teachers = $pdo->query("SELECT * FROM teachers ORDER BY tea_fullname ASC")->fetchAll();
<<<<<<< HEAD
}
catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$current_year_real = date('Y') + 543;

=======
} catch (PDOException $e) { die("Database Error: " . $e->getMessage()); }
$current_year_real = date('Y') + 543;
>>>>>>> 098d10ce91329536a57bdd05b76bb297e520ffbe
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title>CVC Smart System - วิทยาลัยอาชีวศึกษาเชียงราย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800;900&family=Sarabun:wght@300;400;500;600;700&family=Prompt:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f3f4f6;
            background-image:
                url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M15 10h10v10H15V10zm35 0h10v10H50V10zm35 0h10v10H85V10zM15 45h10v10H15V45zm35 0h10v10H50V45zm35 0h10v10H85V45zM15 80h10v10H15V80zm35 0h10v10H50V80zm35 0h10v10H85V80zM5 25h90v5H5v-5zm0 35h90v5H5v-5zm0 35h90v5H5v-5zM25 5h5v90h-5V5zm35 0h5v90h-5V5zm35 0h5v90h-5V5z' fill='%239ca3af' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E"),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239ca3af' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            background-repeat: repeat;
            background-attachment: fixed;
            overflow: auto;
            color: #334155;
            height: 100vh;
        }
        h1, h2, h3, .font-display { font-family: 'Prompt', sans-serif; }
        .font-premium { font-family: 'Playfair Display', 'Georgia', serif; }

        /* === BACKGROUND === */
        .hero-bg {
            height: 100vh;
            position: relative;
        }

        /* === PARTICLES (disabled on light bg) === */

        /* === LOGO === */
        .logo-ring {
            position: relative;
            width: 140px; height: 140px;
        }
        .logo-ring::before {
            content: '';
            position: absolute; inset: -8px;
            border: 2px solid rgba(239,68,68,0.25);
            border-radius: 50%;
            animation: ringPulse 3s ease-out infinite;
        }
        .logo-ring::after {
            content: '';
            position: absolute; inset: -16px;
            border: 1px solid rgba(239,68,68,0.1);
            border-radius: 50%;
            animation: ringPulse 3s ease-out 0.6s infinite;
        }
        @keyframes ringPulse {
            0%   { transform: scale(1); opacity: 0.7; }
            100% { transform: scale(1.35); opacity: 0; }
        }

        /* === SEARCH CARD === */
        .search-card {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 24px;
            box-shadow:
                0 20px 50px rgba(0,0,0,0.08),
                0 4px 12px rgba(0,0,0,0.04);
            transition: transform 0.5s ease, box-shadow 0.5s ease;
        }
        .search-card:hover {
            transform: translateY(-4px);
            box-shadow:
                0 28px 60px rgba(0,0,0,0.12),
                0 8px 20px rgba(0,0,0,0.06);
        }

        /* === TAB PILLS === */
        .tab-bar {
            background: #f1f5f9;
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 16px;
            padding: 5px;
        }
        .tab-pill {
            color: #94a3b8;
            border-radius: 12px;
            padding: 10px 0;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.35s ease;
            border: none;
            background: transparent;
        }
        .tab-pill:hover { color: #64748b; }
        .tab-pill.active {
            background: linear-gradient(145deg, #dc2626, #b91c1c);
            color: #fff;
            box-shadow: 0 4px 20px rgba(220,38,38,0.35);
        }

        /* === INPUT === */
        .search-input {
            background: #f8fafc;
            border: 2px solid rgba(51,65,85,0.2);
            border-radius: 14px;
            color: #334155;
            transition: all 0.3s ease;
        }
        .search-input:focus {
            outline: none;
            border-color: #b91c1c;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(185,28,28,0.1);
        }
        .search-input::placeholder { color: #94a3b8; }

        /* === DROPDOWN === */
        .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0; right: 0;
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 16px;
            max-height: 220px;
            overflow-y: auto;
            z-index: 9999;
            box-shadow: 0 16px 40px rgba(0,0,0,0.12);
        }
        .dropdown-item {
            padding: 14px 20px;
            cursor: pointer;
            transition: background 0.2s ease;
            border-bottom: 1px solid #f1f5f9;
        }
        .dropdown-item:last-child { border-bottom: none; }
        .dropdown-item:hover { background: #fef2f2; }
        .dropdown-menu::-webkit-scrollbar { width: 3px; }
        .dropdown-menu::-webkit-scrollbar-track { background: transparent; }
        .dropdown-menu::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        /* === FEATURE CARDS === */
        .feat-card {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: 20px;
            padding: 24px 20px;
            text-align: center;
            transition: all 0.45s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: 0 4px 24px rgba(0,0,0,0.06), inset 0 1px 0 rgba(255,255,255,0.8);
            position: relative;
            overflow: hidden;
        }
        .feat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            border-radius: 20px 20px 0 0;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .feat-card:nth-child(1)::before { background: linear-gradient(90deg, #dc2626, #f87171); }
        .feat-card:nth-child(2)::before { background: linear-gradient(90deg, #d97706, #fbbf24); }
        .feat-card:nth-child(3)::before { background: linear-gradient(90deg, #059669, #34d399); }
        .feat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12), inset 0 1px 0 rgba(255,255,255,0.9);
            border-color: rgba(255,255,255,0.8);
        }
        .feat-card:hover::before {
            opacity: 1;
        }
        .feat-icon {
            width: 56px; height: 56px;
            border-radius: 16px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 24px;
            margin-bottom: 14px;
            position: relative;
            transition: transform 0.4s ease;
        }
        .feat-card:hover .feat-icon {
            transform: scale(1.1) rotate(-5deg);
        }

        /* === LOGIN BUTTON === */
        .btn-login {
            background: linear-gradient(145deg, #dc2626, #b91c1c);
            color: #fff;
            font-weight: 700;
            padding: 10px 24px;
            border-radius: 12px;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(220,38,38,0.35);
            text-decoration: none;
            font-size: 14px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(220,38,38,0.5);
        }

        /* === ANIMATIONS === */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
=======
    <title>CVC Smart System - ระบบบริการการศึกษา</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Prompt:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{
            font-family:'Sarabun',sans-serif;
            min-height:100vh;
            background:#0a0508;
            color:#fff;
            overflow-x:hidden;
        }
        h1,h2,h3,.fd{font-family:'Prompt',sans-serif;}

        /* ===== BACKGROUND ===== */
        .bg{
            position:fixed;inset:0;z-index:0;
            background:
                radial-gradient(ellipse 120% 70% at 50% 5%, rgba(120,15,30,0.6) 0%, transparent 65%),
                radial-gradient(ellipse 80% 50% at 80% 30%, rgba(150,20,40,0.3) 0%, transparent 55%),
                radial-gradient(ellipse 70% 45% at 15% 70%, rgba(100,10,25,0.25) 0%, transparent 55%),
                radial-gradient(ellipse 50% 30% at 50% 90%, rgba(180,30,50,0.1) 0%, transparent 50%),
                linear-gradient(180deg, #0a0508 0%, #1a0a10 30%, #1e0c14 55%, #0e0608 100%);
        }

        /* Aurora */
        .aurora{position:fixed;inset:0;pointer-events:none;z-index:1;overflow:hidden;}
        .aurora-band{
            position:absolute;width:200%;height:300px;left:-50%;
            border-radius:50%;filter:blur(80px);opacity:0.12;
            animation:auroraMove 12s ease-in-out infinite;
        }
        .aurora-band.a1{
            top:15%;
            background:linear-gradient(90deg,transparent,rgba(220,40,60,0.4),rgba(255,100,50,0.3),rgba(255,180,60,0.15),transparent);
        }
        .aurora-band.a2{
            top:55%;
            background:linear-gradient(90deg,transparent,rgba(200,30,50,0.3),rgba(180,20,40,0.35),transparent);
            animation-delay:-5s;animation-duration:16s;
        }
        @keyframes auroraMove{
            0%,100%{transform:translateX(-10%) rotate(-1deg);opacity:0.08;}
            50%{transform:translateX(10%) rotate(1deg);opacity:0.18;}
        }

        /* Stars */
        .stars{position:fixed;inset:0;pointer-events:none;z-index:2;overflow:hidden;}
        .star{position:absolute;border-radius:50%;background:rgba(255,255,255,0.8);animation:twinkle ease-in-out infinite;}
        @keyframes twinkle{0%,100%{opacity:0.05;transform:scale(0.6);}50%{opacity:0.6;transform:scale(1.1);}}

        /* Floating particles */
        .particles{position:fixed;inset:0;pointer-events:none;z-index:2;overflow:hidden;}
        .particle{position:absolute;border-radius:50%;animation:particleFloat linear infinite;}
        @keyframes particleFloat{
            0%{transform:translateY(100vh) rotate(0deg);opacity:0;}
            10%{opacity:1;}90%{opacity:0.5;}
            100%{transform:translateY(-10vh) rotate(360deg);opacity:0;}
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar{width:5px;}
        ::-webkit-scrollbar-track{background:rgba(0,0,0,0.2);}
        ::-webkit-scrollbar-thumb{background:rgba(220,40,60,0.3);border-radius:5px;}

        /* ===== MAIN ===== */
        .main{
            position:relative;z-index:10;
            min-height:100vh;display:flex;flex-direction:column;
            align-items:center;justify-content:center;
            padding:50px 24px 60px;
        }

        /* Login */
        .login-btn{
            position:fixed;top:18px;right:18px;z-index:50;
            display:inline-flex;align-items:center;gap:7px;
            padding:9px 20px;border-radius:50px;
            background:rgba(220,40,60,0.1);
            border:1px solid rgba(240,60,80,0.2);
            color:rgba(255,200,200,0.8);font-size:13px;font-weight:600;
            text-decoration:none;font-family:'Prompt',sans-serif;
            backdrop-filter:blur(10px);transition:all .3s ease;
        }
        .login-btn:hover{
            background:rgba(220,40,60,0.2);
            border-color:rgba(250,80,100,0.4);
            box-shadow:0 0 20px rgba(220,40,60,0.15);
            transform:translateY(-1px);
        }

        /* ===== LOGO ===== */
        .logo-area{
            position:relative;margin-bottom:28px;
        }
        .logo-glow{
            position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
            width:200px;height:200px;
            background:radial-gradient(circle,rgba(220,40,60,0.12) 0%,rgba(255,150,50,0.05) 50%,transparent 70%);
            border-radius:50%;
            animation:lgPulse 4s ease-in-out infinite;
        }
        @keyframes lgPulse{
            0%,100%{opacity:0.5;transform:translate(-50%,-50%) scale(1);}
            50%{opacity:1;transform:translate(-50%,-50%) scale(1.15);}
        }
        .logo-img{
            position:relative;z-index:1;
            width:110px;height:110px;
            object-fit:contain;
            filter:drop-shadow(0 0 20px rgba(220,40,60,0.3)) drop-shadow(0 0 50px rgba(255,150,50,0.12));
            animation:logoFloat 5s ease-in-out infinite;
        }
        @keyframes logoFloat{
            0%,100%{transform:translateY(0);}
            50%{transform:translateY(-7px);}
        }

        /* ===== TITLE ===== */
        .title{
            font-family:'Prompt',sans-serif;
            font-size:clamp(2.2rem,7vw,3.5rem);
            font-weight:800;text-align:center;
            margin-bottom:10px;letter-spacing:-0.5px;
            background:linear-gradient(135deg,#fff 0%,#ffd5d5 40%,#fff 60%,#ffe0e0 100%);
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
        }
        .subtitle{
            text-align:center;
            color:rgba(220,160,160,0.5);
            font-size:clamp(0.8rem,2.3vw,0.95rem);
            line-height:1.75;margin-bottom:30px;
        }

        /* ===== TABS ===== */
        .tabs{
            display:inline-flex;align-items:center;gap:3px;
            background:rgba(255,255,255,0.04);
            border:1px solid rgba(255,255,255,0.06);
            border-radius:50px;padding:4px;
            margin-bottom:26px;backdrop-filter:blur(10px);
        }
        .tab{
            display:flex;align-items:center;gap:8px;
            padding:11px 24px;border-radius:50px;
            border:1.5px solid transparent;
            background:transparent;color:rgba(255,255,255,0.35);
            font-family:'Prompt',sans-serif;
            font-weight:600;font-size:14.5px;
            cursor:pointer;transition:all .35s ease;outline:none;
        }
        .tab:hover{color:rgba(255,255,255,0.6);}
        .tab.active{
            color:#fff;
            background:linear-gradient(135deg,rgba(220,40,60,0.2),rgba(255,140,50,0.12));
            border-color:rgba(240,60,80,0.45);
            box-shadow:0 0 15px rgba(220,40,60,0.15),0 0 35px rgba(255,140,50,0.06),inset 0 1px 0 rgba(255,255,255,0.08);
        }
        .tab .ti{
            width:28px;height:28px;border-radius:50%;
            display:flex;align-items:center;justify-content:center;
            font-size:13px;background:rgba(255,255,255,0.04);
        }
        .tab.active .ti{
            background:linear-gradient(135deg,rgba(220,40,60,0.15),rgba(255,140,50,0.1));
>>>>>>> 098d10ce91329536a57bdd05b76bb297e520ffbe
        }
        .anim-fade-up { animation: fadeUp 0.7s ease-out both; }
        .anim-fade-up-d1 { animation: fadeUp 0.7s 0.1s ease-out both; }
        .anim-fade-up-d2 { animation: fadeUp 0.7s 0.2s ease-out both; }
        .anim-fade-up-d3 { animation: fadeUp 0.7s 0.3s ease-out both; }
        .anim-fade-up-d4 { animation: fadeUp 0.7s 0.4s ease-out both; }
        .anim-fade-up-d5 { animation: fadeUp 0.7s 0.5s ease-out both; }
        .anim-fade-in { animation: fadeIn 1s ease-out both; }

<<<<<<< HEAD
        /* === MISC === */
        .text-shimmer {
            background: linear-gradient(90deg, #b91c1c 0%, #ef4444 50%, #b91c1c 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmer 4s linear infinite;
        }
        @keyframes shimmer {
            0%   { background-position: 0% center; }
            100% { background-position: 200% center; }
        }
        @keyframes goldShimmer {
            0%   { background-position: 0% center; }
            100% { background-position: 200% center; }
=======
        /* ===== SEARCH GLASS CARD ===== */
        .search-area{width:100%;max-width:540px;position:relative;z-index:100;}
        .glass-card{
            background:rgba(255,255,255,0.06);
            backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);
            border:1px solid rgba(255,255,255,0.1);
            border-radius:22px;padding:24px;
            box-shadow:0 20px 60px rgba(0,0,0,0.25),inset 0 1px 0 rgba(255,255,255,0.08),0 0 40px rgba(220,40,60,0.04);
            transition:all .4s ease;
        }
        .glass-card:hover{
            border-color:rgba(240,80,100,0.2);
            box-shadow:0 25px 70px rgba(0,0,0,0.3),inset 0 1px 0 rgba(255,255,255,0.1),0 0 50px rgba(220,40,60,0.08);
        }
        .search-label{display:flex;align-items:center;gap:12px;margin-bottom:16px;}
        .search-label .icon-box{
            width:44px;height:44px;border-radius:14px;
            display:flex;align-items:center;justify-content:center;
            font-size:20px;flex-shrink:0;
        }
        .search-label .icon-box.student{
            background:linear-gradient(135deg,rgba(220,40,60,0.3),rgba(180,30,50,0.2));
            color:#ff8a8a;box-shadow:0 4px 15px rgba(220,40,60,0.15);
        }
        .search-label .icon-box.teacher{
            background:linear-gradient(135deg,rgba(255,140,50,0.3),rgba(220,110,30,0.2));
            color:#ffb74d;box-shadow:0 4px 15px rgba(255,140,50,0.15);
        }
        .search-label .lbl-text h3{font-size:16px;font-weight:700;color:#fff;margin-bottom:2px;}
        .search-label .lbl-text p{font-size:11.5px;color:rgba(220,170,170,0.4);}
        .search-input-wrap{
            display:flex;align-items:center;
            background:rgba(0,0,0,0.2);
            border:1.5px solid rgba(255,255,255,0.08);
            border-radius:14px;padding:4px;transition:all .3s ease;
        }
        .search-input-wrap:focus-within{
            border-color:rgba(240,60,80,0.4);
            box-shadow:0 0 0 3px rgba(220,40,60,0.08),0 0 20px rgba(220,40,60,0.06);
        }
        .search-input-wrap .si{padding:0 10px 0 14px;color:rgba(220,170,170,0.4);font-size:17px;flex-shrink:0;}
        .search-input-wrap input{
            flex:1;border:none;outline:none;
            font-family:'Sarabun',sans-serif;font-size:15px;color:#fff;
            background:transparent;padding:11px 4px;min-width:0;
        }
        .search-input-wrap input::placeholder{color:rgba(220,170,170,0.3);font-size:13.5px;}
        .submit-btn{
            width:38px;height:38px;border-radius:12px;flex-shrink:0;
            border:none;cursor:pointer;
            background:linear-gradient(135deg,#dc2626,#b91c1c);
            color:#fff;font-size:14px;
            display:flex;align-items:center;justify-content:center;
            transition:all .25s ease;
            box-shadow:0 4px 15px rgba(220,40,60,0.3);
        }
        .submit-btn:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(220,40,60,0.4);}

        /* ===== DROPDOWN ===== */
        .dd{
            position:absolute;top:calc(100% + 10px);left:0;right:0;
            background:linear-gradient(180deg,#2a0a10 0%,#1e0610 100%);
            border:1px solid rgba(220,40,60,0.2);
            border-radius:16px;max-height:240px;overflow-y:auto;
            z-index:9999;box-shadow:0 16px 50px rgba(0,0,0,0.7),0 0 0 1px rgba(0,0,0,0.3);
        }
        .dd-item{
            padding:13px 18px;cursor:pointer;
            transition:all .2s ease;
            border-bottom:1px solid rgba(255,255,255,0.05);
            display:flex;justify-content:space-between;align-items:center;
            position:relative;
            background:#240910;
        }
        .dd-item:last-child{border-bottom:none;}
        .dd-item:hover{background:rgba(220,40,60,0.06);padding-left:24px;}
        .dd-item::before{
            content:'';position:absolute;left:0;top:0;bottom:0;width:3px;
            background:linear-gradient(180deg,#dc2626,#f97316);
            transform:scaleY(0);transition:transform .3s ease;border-radius:0 3px 3px 0;
        }
        .dd-item:hover::before{transform:scaleY(1);}
        .dd::-webkit-scrollbar{width:4px;}
        .dd::-webkit-scrollbar-thumb{background:rgba(220,40,60,0.25);border-radius:10px;}
        .hidden{display:none;}

        /* Panels */
        .panel{transition:all .3s ease;}
        .panel.hidden{display:none;}

        /* ===== FEATURES ===== */
        .features{
            display:flex;gap:20px;
            margin-top:45px;justify-content:center;flex-wrap:wrap;
        }
        .feat-card{
            background:rgba(255,255,255,0.04);
            backdrop-filter:blur(16px);
            border:1px solid rgba(255,255,255,0.07);
            border-radius:18px;padding:24px 20px;width:155px;
            display:flex;flex-direction:column;align-items:center;gap:12px;
            cursor:default;transition:all .35s ease;
            position:relative;overflow:hidden;
        }
        .feat-card::before{
            content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;
            background:radial-gradient(circle at 30% 30%,rgba(220,40,60,0.06),transparent 50%);
            opacity:0;transition:opacity .4s;pointer-events:none;
        }
        .feat-card:hover{
            transform:translateY(-5px);
            border-color:rgba(240,80,100,0.2);
            box-shadow:0 15px 40px rgba(0,0,0,0.2),0 0 30px rgba(220,40,60,0.06);
        }
        .feat-card:hover::before{opacity:1;}
        .feat-icon-circle{
            width:56px;height:56px;border-radius:50%;
            display:flex;align-items:center;justify-content:center;font-size:22px;
        }
        .feat-icon-circle.fc1{
            background:linear-gradient(135deg,rgba(220,40,60,0.15),rgba(180,30,50,0.1));
            color:#ff8a8a;box-shadow:0 0 20px rgba(220,40,60,0.1);
        }
        .feat-icon-circle.fc2{
            background:linear-gradient(135deg,rgba(255,140,50,0.15),rgba(220,110,30,0.1));
            color:#ffb74d;box-shadow:0 0 20px rgba(255,140,50,0.1);
        }
        .feat-icon-circle.fc3{
            background:linear-gradient(135deg,rgba(255,200,60,0.15),rgba(220,170,30,0.1));
            color:#ffd54f;box-shadow:0 0 20px rgba(255,200,60,0.1);
        }
        .feat-label{
            color:rgba(230,180,180,0.55);font-size:12.5px;font-weight:600;
            text-align:center;line-height:1.5;font-family:'Prompt',sans-serif;
        }

        /* Ornament */
        .ornament{margin-top:40px;text-align:center;}
        .ornament-line{
            width:60px;height:2px;margin:0 auto 12px;
            background:linear-gradient(90deg,transparent,rgba(220,40,60,0.3),rgba(255,140,50,0.2),rgba(220,40,60,0.3),transparent);
            animation:ornShine 3s ease-in-out infinite;
>>>>>>> 098d10ce91329536a57bdd05b76bb297e520ffbe
        }
        @keyframes ornShine{0%,100%{opacity:0.4;}50%{opacity:1;}}
        .ornament p{color:rgba(200,160,160,0.25);font-size:11px;}

<<<<<<< HEAD
        .divider {
            width: 60px; height: 3px;
            background: linear-gradient(90deg, #dc2626, transparent);
            border-radius: 2px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-grid { grid-template-columns: 1fr !important; }
            .hero-left { text-align: center; }
            .feat-row { flex-direction: column; align-items: center; }
            body { height: auto; min-height: 100vh; }
            .hero-bg { height: auto; min-height: 100vh; }
            .hero-bg > .h-screen { height: auto !important; min-height: auto !important; padding: 80px 0 60px; }
        }

        /* Panel Transition */
        .search-panel { transition: opacity 0.3s ease, transform 0.3s ease; }
        .search-panel.hidden { display: none; }
=======
        /* ===== ANIMATIONS ===== */
        .au{opacity:0;transform:translateY(25px);animation:aUp .65s ease forwards;}
        @keyframes aUp{to{opacity:1;transform:translateY(0);}}
        .ad{opacity:0;transform:translateY(-20px);animation:aDown .6s ease forwards;}
        @keyframes aDown{to{opacity:1;transform:translateY(0);}}
        .asi{opacity:0;transform:scale(0.5);animation:aSi .55s cubic-bezier(.34,1.56,.64,1) forwards;}
        @keyframes aSi{to{opacity:1;transform:scale(1);}}
        .d1{animation-delay:.05s;}.d2{animation-delay:.15s;}.d3{animation-delay:.3s;}
        .d4{animation-delay:.45s;}.d5{animation-delay:.6s;}.d6{animation-delay:.8s;}

        /* ===== RESPONSIVE ===== */
        @media(max-width:600px){
            .main{padding:40px 16px 50px;}
            .tab{padding:9px 16px;font-size:13px;gap:5px;}
            .tab .ti{width:24px;height:24px;font-size:11px;}
            .features{gap:12px;}
            .feat-card{width:110px;padding:18px 14px;}
            .feat-icon-circle{width:46px;height:46px;font-size:18px;}
            .feat-label{font-size:11px;}
            .glass-card{padding:18px;}
            .logo-img{width:85px;height:85px;}
        }
>>>>>>> 098d10ce91329536a57bdd05b76bb297e520ffbe
    </style>
</head>
<body>

<<<<<<< HEAD
    <div class="hero-bg">

        <!-- Login Button -->
        <div class="fixed top-5 right-5 z-50 anim-fade-in">
            <a href="login.php" class="btn-login flex items-center gap-2">
                <i class="fa-solid fa-right-to-bracket text-sm"></i> เข้าสู่ระบบ
            </a>
        </div>

        <!-- HERO SECTION -->
        <div class="h-screen flex items-center">
            <div class="max-w-6xl mx-auto px-6 w-full py-8">
                <div class="hero-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">

                    <!-- LEFT: Branding -->
                    <div class="hero-left">
                        <!-- Logo + Title Row -->
                        <div class="anim-fade-up mb-4 flex items-center gap-5">
                            <div class="logo-ring flex-shrink-0">
                                <img src="images/cvc_logo.png" alt="CVC Logo" class="w-full h-full object-contain relative z-10" style="filter: drop-shadow(0 0 24px rgba(220,38,38,0.3));">
                            </div>
                            <h1 class="font-premium leading-tight" style="font-size: clamp(2.2rem, 4.5vw, 3.5rem); font-weight: 900; letter-spacing: -0.5px;">
                                <span style="background: linear-gradient(135deg, #b8860b 0%, #ffd700 25%, #daa520 50%, #f5c542 75%, #b8860b 100%); background-size: 200% auto; -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; animation: goldShimmer 3s linear infinite; filter: drop-shadow(0 2px 4px rgba(184,134,11,0.3));">CVC</span><br><span class="text-shimmer" style="font-family: 'Playfair Display', serif;">Smart System</span>
                            </h1>
                        </div>


                        <!-- Feature Cards -->
                        <div class="anim-fade-up-d4 feat-row flex gap-4" style="max-width: 480px; margin-top: 28px;">
                            <div class="feat-card flex-1">
                                <div class="feat-icon" style="background: linear-gradient(145deg, rgba(220,38,38,0.15), rgba(248,113,113,0.08)); color: #ef4444; box-shadow: 0 4px 12px rgba(220,38,38,0.15);">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                </div>
                                <div class="text-slate-800 text-sm font-bold mb-1" style="letter-spacing: 0.3px;">อัตโนมัติ</div>
                                <div class="text-slate-400 text-xs" style="line-height: 1.5;">จัดตารางด้วย AI</div>
                            </div>
                            <div class="feat-card flex-1">
                                <div class="feat-icon" style="background: linear-gradient(145deg, rgba(217,119,6,0.15), rgba(251,191,36,0.08)); color: #f59e0b; box-shadow: 0 4px 12px rgba(217,119,6,0.15);">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>
                                <div class="text-slate-800 text-sm font-bold mb-1" style="letter-spacing: 0.3px;">เรียลไทม์</div>
                                <div class="text-slate-400 text-xs" style="line-height: 1.5;">อัพเดททันที</div>
                            </div>
                            <div class="feat-card flex-1">
                                <div class="feat-icon" style="background: linear-gradient(145deg, rgba(5,150,105,0.15), rgba(52,211,153,0.08)); color: #10b981; box-shadow: 0 4px 12px rgba(5,150,105,0.15);">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div class="text-slate-800 text-sm font-bold mb-1" style="letter-spacing: 0.3px;">ปลอดภัย</div>
                                <div class="text-slate-400 text-xs" style="line-height: 1.5;">ข้อมูลเข้ารหัส</div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT: Search Card -->
                    <div class="anim-fade-up-d3">
                        <div class="search-card p-6">

                            <!-- Tab Pills -->
                            <div class="tab-bar flex mb-4">
                                <button id="tab-student" onclick="switchTab('student')" class="tab-pill active flex-1 flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-user-graduate"></i> นักเรียน
                                </button>
                                <button id="tab-teacher" onclick="switchTab('teacher')" class="tab-pill flex-1 flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-chalkboard-user"></i> ครูผู้สอน
                                </button>
                            </div>

                            <!-- Student Search Panel -->
                            <div id="panel-student" class="search-panel">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg" style="background: linear-gradient(145deg, #dc2626, #991b1b); color: #fff; box-shadow: 0 4px 14px rgba(220,38,38,0.35);">
                                        <i class="fa-solid fa-user-graduate"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-display font-bold text-slate-800">ค้นหาตารางเรียน</h2>
                                        <p class="text-slate-400 text-xs">พิมพ์ชื่อกลุ่มเรียนหรือรหัส</p>
                                    </div>
                                </div>
                                
                                <form action="public_schedule.php" method="GET" id="form-student">
                                    <input type="hidden" name="mode" value="class">
                                    <input type="hidden" name="id" id="student_id_input">
                                    
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" id="student_search" 
                                            class="search-input w-full px-5 py-3 pl-12 text-sm font-medium"
                                            placeholder="พิมพ์ชื่อกลุ่ม หรือรหัส..." 
                                            autocomplete="off">
                                        <div id="student_dropdown" class="dropdown-menu hidden"></div>
                                    </div>
                                </form>
                            </div>

                            <!-- Teacher Search Panel -->
                            <div id="panel-teacher" class="search-panel hidden">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg" style="background: linear-gradient(145deg, #f87171, #dc2626); color: #fff; box-shadow: 0 4px 14px rgba(248,113,113,0.3);">
                                        <i class="fa-solid fa-chalkboard-user"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-display font-bold text-slate-800">ค้นหาตารางสอน</h2>
                                        <p class="text-slate-400 text-xs">พิมพ์ชื่อหรือรหัสอาจารย์</p>
                                    </div>
                                </div>
                                
                                <form action="public_schedule.php" method="GET" id="form-teacher">
                                    <input type="hidden" name="mode" value="teacher">
                                    <input type="hidden" name="id" id="teacher_id_input">
                                    
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" id="teacher_search" 
                                            class="search-input w-full px-5 py-3 pl-12 text-sm font-medium"
                                            placeholder="พิมพ์ชื่ออาจารย์ หรือรหัส..." 
                                            autocomplete="off">
                                        <div id="teacher_dropdown" class="dropdown-menu hidden"></div>
                                    </div>
                                </form>
                            </div>

                            <!-- Hint -->
                            <div class="mt-4 flex items-center gap-2 text-slate-400 text-xs">
                                <i class="fa-solid fa-circle-info"></i>
                                <span>เลือกจากผลลัพธ์เพื่อดูตาราง</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="absolute bottom-3 left-0 right-0 text-center z-10">
            <p class="text-slate-400 text-xs">
                © <?php echo $current_year_real; ?> CVC Smart System • วิทยาลัยอาชีวศึกษาเชียงราย
            </p>
=======
<!-- BG -->
<div class="bg"></div>
<div class="aurora">
    <div class="aurora-band a1"></div>
    <div class="aurora-band a2"></div>
</div>
<div class="stars" id="stars"></div>
<div class="particles" id="particles"></div>

<!-- Login -->
<a href="login.php" class="login-btn"><i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบ</a>

<div class="main">

    <!-- Logo -->
    <div class="logo-area asi">
        <div class="logo-glow"></div>
        <img src="/images/cvc_logo.png" alt="CVC Logo" class="logo-img">
    </div>

    <!-- Title -->
    <h1 class="title ad d1">ระบบบริการการศึกษา</h1>
    <p class="subtitle ad d2">
        ยกระดับประสบการณ์การเรียนรู้และการสอน<br>
        ด้วยเทคโนโลยีที่ทันสมัย และเข้าถึงได้ทุกที่ทุกเวลา
    </p>

    <!-- Tabs -->
    <div class="tabs au d3">
        <button id="tab-student" onclick="switchTab('student')" class="tab active">
            <span class="ti"><i class="fa-solid fa-user-graduate"></i></span>
            <span>นักศึกษา</span>
        </button>
        <button id="tab-teacher" onclick="switchTab('teacher')" class="tab">
            <span class="ti"><i class="fa-solid fa-chalkboard-user"></i></span>
            <span>อาจารย์</span>
        </button>
    </div>

    <!-- Search -->
    <div class="search-area au d4">
        <div id="panel-student" class="panel">
            <form action="public_schedule.php" method="GET" id="form-student">
                <input type="hidden" name="mode" value="class">
                <input type="hidden" name="id" id="student_id_input">
                <div class="glass-card">
                    <div class="search-label">
                        <div class="icon-box student"><i class="fa-solid fa-user-graduate"></i></div>
                        <div class="lbl-text">
                            <h3 class="fd">ค้นหาตารางเรียน</h3>
                            <p>พิมพ์ชื่อกลุ่มหรือรหัส</p>
                        </div>
                    </div>
                    <div class="search-input-wrap">
                        <i class="fa-solid fa-magnifying-glass si"></i>
                        <input type="text" id="student_search" placeholder="ค้นหาหลักสูตร, ตารางเรียน, เกรด หรือข้อมูลอื่นๆ..." autocomplete="off">
                        <button type="button" class="submit-btn" onclick="document.getElementById('form-student').submit()"><i class="fa-solid fa-arrow-right"></i></button>
                    </div>
                </div>
                <div id="student_dropdown" class="dd hidden"></div>
            </form>
        </div>
        <div id="panel-teacher" class="panel hidden">
            <form action="public_schedule.php" method="GET" id="form-teacher">
                <input type="hidden" name="mode" value="teacher">
                <input type="hidden" name="id" id="teacher_id_input">
                <div class="glass-card">
                    <div class="search-label">
                        <div class="icon-box teacher"><i class="fa-solid fa-chalkboard-user"></i></div>
                        <div class="lbl-text">
                            <h3 class="fd">ค้นหาตารางสอน</h3>
                            <p>พิมพ์ชื่อหรือรหัสอาจารย์</p>
                        </div>
                    </div>
                    <div class="search-input-wrap">
                        <i class="fa-solid fa-magnifying-glass si"></i>
                        <input type="text" id="teacher_search" placeholder="ค้นหาอาจารย์, ตารางสอน หรือข้อมูลอื่นๆ..." autocomplete="off">
                        <button type="button" class="submit-btn" onclick="document.getElementById('form-teacher').submit()"><i class="fa-solid fa-arrow-right"></i></button>
                    </div>
                </div>
                <div id="teacher_dropdown" class="dd hidden"></div>
            </form>
>>>>>>> 098d10ce91329536a57bdd05b76bb297e520ffbe
        </div>
    </div>

    <!-- Features -->
    <div class="features au d6">
        <div class="feat-card">
            <div class="feat-icon-circle fc1"><i class="fa-solid fa-calendar-check"></i></div>
            <span class="feat-label">ตารางและ<br>กิจกรรม</span>
        </div>
        <div class="feat-card">
            <div class="feat-icon-circle fc2"><i class="fa-solid fa-cloud-arrow-up"></i></div>
            <span class="feat-label">ห้องสมุด<br>ดิจิทัล</span>
        </div>
        <div class="feat-card">
            <div class="feat-icon-circle fc3"><i class="fa-solid fa-headset"></i></div>
            <span class="feat-label">ช่วยเหลือและ<br>สนับสนุน</span>
        </div>
    </div>

<<<<<<< HEAD
        // === Tab Switching ===
        function switchTab(type) {
            document.getElementById('tab-student').classList.toggle('active', type === 'student');
            document.getElementById('tab-teacher').classList.toggle('active', type === 'teacher');
            document.getElementById('panel-student').classList.toggle('hidden', type !== 'student');
            document.getElementById('panel-teacher').classList.toggle('hidden', type !== 'teacher');
        }

        // === Search Data ===
        const studentsData = [
            <?php foreach ($classes as $c):
    $stu_year = $current_year_real - $c['cla_year'] + 1;
    if ($stu_year < 1)
        $stu_year = 1;
    $room_no = intval($c['cla_group_no']);
    $display_name = $c['cla_name'] . "." . $stu_year . "/" . $room_no;
?>
            { 
                id: "<?php echo $c['cla_id']; ?>", 
                text: "<?php echo $display_name; ?>",
                subtext: "รหัส: <?php echo $c['cla_id']; ?>",
                search: "<?php echo $c['cla_name'] . ' ' . $c['cla_id'] . ' ' . $display_name; ?>" 
            },
            <?php
endforeach; ?>
        ];

        const teachersData = [
            <?php foreach ($teachers as $t): ?>
            { 
                id: "<?php echo $t['tea_id']; ?>", 
                text: "<?php echo $t['tea_fullname']; ?>",
                subtext: "รหัส: <?php echo $t['tea_code']; ?>",
                search: "<?php echo $t['tea_fullname'] . ' ' . $t['tea_code'] . ' ' . $t['tea_username']; ?>" 
            },
            <?php
endforeach; ?>
        ];

        // === Search Function ===
        function setupSearch(inputId, dropdownId, hiddenId, dataList) {
            const input = document.getElementById(inputId);
            const dropdown = document.getElementById(dropdownId);
            const hidden = document.getElementById(hiddenId);
            
            input.addEventListener('input', function() {
                const val = this.value.toLowerCase().trim();
                dropdown.innerHTML = '';
                
                if (!val) {
                    dropdown.classList.add('hidden');
                    return;
                }

                const filtered = dataList.filter(item => item.search.toLowerCase().includes(val));

                if (filtered.length === 0) {
                    dropdown.innerHTML = `
                        <div class="p-8 text-center">
                            <i class="fa-regular fa-face-frown text-3xl text-gray-600 mb-3 block"></i>
                            <span class="text-gray-500 text-sm">ไม่พบข้อมูล</span>
                        </div>`;
                } else {
                    filtered.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'dropdown-item flex justify-between items-center';
                        
                        div.innerHTML = `
                            <div>
                                <div class="font-bold text-slate-700 text-sm">${item.text}</div>
                                <div class="text-xs text-slate-400 mt-0.5">${item.subtext}</div>
                            </div>
                            <i class="fa-solid fa-arrow-right text-slate-300 text-xs"></i>
                        `;
                        
                        div.onclick = () => {
                            input.value = item.text; 
                            hidden.value = item.id; 
                            dropdown.classList.add('hidden');
                            input.closest('form').submit(); 
                        };
                        dropdown.appendChild(div);
                    });
                }
                
                dropdown.classList.remove('hidden');
=======
    <!-- Ornament -->
    <div class="ornament au d6">
        <div class="ornament-line"></div>
        <p>© 2568 CVC Smart System • วิทยาลัยอาชีวศึกษาเชียงราย</p>
    </div>
</div>

<script>
// Stars
(function(){
    const c=document.getElementById('stars');
    for(let i=0;i<50;i++){
        const s=document.createElement('div');s.className='star';
        const sz=Math.random()*2+0.5;
        s.style.cssText=`left:${Math.random()*100}%;top:${Math.random()*100}%;width:${sz}px;height:${sz}px;animation-duration:${Math.random()*4+2}s;animation-delay:${Math.random()*6}s;`;
        c.appendChild(s);
    }
})();

// Particles
(function(){
    const c=document.getElementById('particles');
    const colors=['rgba(220,40,60,0.4)','rgba(255,140,50,0.3)','rgba(255,255,255,0.15)','rgba(240,80,100,0.3)'];
    for(let i=0;i<18;i++){
        const p=document.createElement('div');p.className='particle';
        const sz=Math.random()*4+1.5;
        p.style.cssText=`left:${Math.random()*100}%;width:${sz}px;height:${sz}px;background:${colors[Math.floor(Math.random()*colors.length)]};animation-duration:${Math.random()*10+8}s;animation-delay:${Math.random()*12}s;`;
        c.appendChild(p);
    }
})();

// Tab switch
function switchTab(t){
    ['student','teacher'].forEach(k=>{
        document.getElementById('tab-'+k).classList.toggle('active',k===t);
        const p=document.getElementById('panel-'+k);
        if(k===t){
            p.classList.remove('hidden');
            p.style.opacity='0';p.style.transform='translateY(10px) scale(0.98)';
            requestAnimationFrame(()=>{
                p.style.transition='opacity .35s ease,transform .35s cubic-bezier(.34,1.56,.64,1)';
                p.style.opacity='1';p.style.transform='translateY(0) scale(1)';
>>>>>>> 098d10ce91329536a57bdd05b76bb297e520ffbe
            });
        }else{p.classList.add('hidden');}
    });
}

// Data
const studentsData=[
    <?php foreach($classes as $c):
        $sy=$current_year_real-$c['cla_year']+1;if($sy<1)$sy=1;
        $rn=intval($c['cla_group_no']);
        $dn=$c['cla_name'].".".$sy."/".$rn;
    ?>
    {id:"<?=$c['cla_id']?>",text:"<?=$dn?>",sub:"รหัส: <?=$c['cla_id']?>",s:"<?=$c['cla_name'].' '.$c['cla_id'].' '.$dn?>"},
    <?php endforeach;?>
];
const teachersData=[
    <?php foreach($teachers as $t):?>
    {id:"<?=$t['tea_id']?>",text:"<?=$t['tea_fullname']?>",sub:"รหัส: <?=$t['tea_code']?>",s:"<?=$t['tea_fullname'].' '.$t['tea_code'].' '.$t['tea_username']?>"},
    <?php endforeach;?>
];

// Search
function setupSearch(iid,did,hid,data){
    const inp=document.getElementById(iid),dd=document.getElementById(did),hdn=document.getElementById(hid);
    inp.addEventListener('input',function(){
        const v=this.value.toLowerCase().trim();dd.innerHTML='';
        if(!v){dd.classList.add('hidden');return;}
        const f=data.filter(x=>x.s.toLowerCase().includes(v));
        if(!f.length){
            dd.innerHTML='<div style="padding:25px;text-align:center"><i class="fa-regular fa-face-frown" style="font-size:24px;color:rgba(220,40,60,0.15);display:block;margin-bottom:8px"></i><span style="color:rgba(220,170,170,0.35);font-size:13px">ไม่พบข้อมูล</span></div>';
        }else{
            f.forEach((item,idx)=>{
                const d=document.createElement('div');d.className='dd-item';
                d.style.cssText='opacity:0;transform:translateX(-8px);position:relative;';
                d.innerHTML=`<div><div style="font-weight:700;color:#fff;font-size:14px">${item.text}</div><div style="font-size:11px;color:rgba(220,170,170,0.35);margin-top:2px">${item.sub}</div></div><i class="fa-solid fa-arrow-right" style="color:rgba(220,40,60,0.25);font-size:12px"></i>`;
                d.onclick=()=>{d.style.background='rgba(220,40,60,0.08)';setTimeout(()=>{inp.value=item.text;hdn.value=item.id;dd.classList.add('hidden');inp.closest('form').submit();},120);};
                dd.appendChild(d);
                setTimeout(()=>{d.style.transition='all .2s cubic-bezier(.34,1.56,.64,1)';d.style.opacity='1';d.style.transform='translateX(0)';},idx*35);
            });
        }
        dd.classList.remove('hidden');
    });
    inp.addEventListener('focus',function(){if(this.value.trim()&&dd.children.length>0)dd.classList.remove('hidden');});
    document.addEventListener('click',function(e){if(!inp.contains(e.target)&&!dd.contains(e.target))dd.classList.add('hidden');});
}
setupSearch('student_search','student_dropdown','student_id_input',studentsData);
setupSearch('teacher_search','teacher_dropdown','teacher_id_input',teachersData);
</script>
</body>
</html>