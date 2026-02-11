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
<<<<<<< HEAD
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
=======
    <title>เข้าสู่ระบบ - CVC Smart System</title>
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
            display:flex;align-items:center;justify-content:center;
            padding:20px;
        }
        h1,h2,h3,.fd{font-family:'Prompt',sans-serif;}

        /* ===== BACKGROUND ===== */
        .bg{
            position:fixed;inset:0;z-index:0;
            background:
                radial-gradient(ellipse 120% 70% at 50% 5%, rgba(120,15,30,0.6) 0%, transparent 65%),
                radial-gradient(ellipse 80% 50% at 80% 30%, rgba(150,20,40,0.3) 0%, transparent 55%),
                radial-gradient(ellipse 70% 45% at 15% 70%, rgba(100,10,25,0.25) 0%, transparent 55%),
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
            top:60%;
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

        /* Particles */
        .particles{position:fixed;inset:0;pointer-events:none;z-index:2;overflow:hidden;}
        .particle{position:absolute;border-radius:50%;animation:particleFloat linear infinite;}
        @keyframes particleFloat{
            0%{transform:translateY(100vh) rotate(0deg);opacity:0;}
            10%{opacity:1;}90%{opacity:0.5;}
            100%{transform:translateY(-10vh) rotate(360deg);opacity:0;}
        }

        /* Scrollbar */
        ::-webkit-scrollbar{width:5px;}
        ::-webkit-scrollbar-track{background:rgba(0,0,0,0.2);}
        ::-webkit-scrollbar-thumb{background:rgba(220,40,60,0.3);border-radius:5px;}

        /* ===== CONTAINER ===== */
        .container{
            position:relative;z-index:10;
            width:100%;max-width:440px;
        }

        /* ===== LOGO AREA ===== */
        .logo-area{
            text-align:center;margin-bottom:28px;
        }
        .logo-wrap{
            position:relative;display:inline-block;margin-bottom:14px;
        }
        .logo-glow{
            position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
            width:160px;height:160px;
            background:radial-gradient(circle,rgba(220,40,60,0.15) 0%,rgba(255,150,50,0.05) 50%,transparent 70%);
            border-radius:50%;
            animation:lgPulse 4s ease-in-out infinite;
        }
        @keyframes lgPulse{
            0%,100%{opacity:0.5;transform:translate(-50%,-50%) scale(1);}
            50%{opacity:1;transform:translate(-50%,-50%) scale(1.15);}
        }
        .logo-img{
            position:relative;z-index:1;
            width:90px;height:90px;
            object-fit:contain;
            filter:drop-shadow(0 0 20px rgba(220,40,60,0.3)) drop-shadow(0 0 40px rgba(255,150,50,0.1));
            animation:logoFloat 5s ease-in-out infinite;
        }
        @keyframes logoFloat{0%,100%{transform:translateY(0);}50%{transform:translateY(-6px);}}
        .logo-title{
            font-family:'Prompt',sans-serif;
            font-size:clamp(1.6rem,5vw,2rem);
            font-weight:800;margin-bottom:4px;
            background:linear-gradient(135deg,#fff 0%,#ffd5d5 40%,#fff 100%);
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
        }
        .logo-sub{
            color:rgba(220,170,170,0.4);font-size:13px;
        }

        /* ===== LOGIN CARD ===== */
        .login-card{
            background:rgba(255,255,255,0.06);
            backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);
            border:1px solid rgba(255,255,255,0.1);
            border-radius:22px;
            padding:32px 28px;
            box-shadow:0 25px 70px rgba(0,0,0,0.3),inset 0 1px 0 rgba(255,255,255,0.08),0 0 40px rgba(220,40,60,0.04);
            position:relative;overflow:hidden;
        }
        .login-card::before{
            content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;
            background:radial-gradient(circle at 30% 30%,rgba(220,40,60,0.05),transparent 50%);
            animation:cardGlow 5s ease-in-out infinite;pointer-events:none;
        }
        @keyframes cardGlow{
            0%,100%{opacity:0.3;transform:translate(0,0);}
            50%{opacity:0.8;transform:translate(10%,10%);}
        }

        /* Ornament line */
        .ornament-line{
            width:60px;height:2px;margin:0 auto 24px;
            background:linear-gradient(90deg,transparent,rgba(220,40,60,0.4),rgba(255,140,50,0.25),rgba(220,40,60,0.4),transparent);
            border-radius:99px;
            animation:ornPulse 2.5s ease-in-out infinite;
        }
        @keyframes ornPulse{0%,100%{opacity:0.4;transform:scaleX(0.8);}50%{opacity:1;transform:scaleX(1);}}

        /* ===== ERROR ALERT ===== */
        .error-alert{
            background:rgba(220,40,60,0.1);
            border:1px solid rgba(220,40,60,0.25);
            border-radius:14px;padding:14px 18px;
            margin-bottom:20px;
            display:flex;align-items:center;gap:12px;
            animation:errShake 0.5s ease;
        }
        .error-alert i{color:#ff6b6b;font-size:18px;flex-shrink:0;}
        .error-alert span{color:#ffb0b0;font-size:13.5px;font-weight:600;}
        @keyframes errShake{
            0%,100%{transform:translateX(0);}
            20%{transform:translateX(-10px);}40%{transform:translateX(10px);}
            60%{transform:translateX(-6px);}80%{transform:translateX(6px);}
        }

        /* ===== FORM ===== */
        .form-group{margin-bottom:20px;}
        .form-label{
            display:block;font-size:11.5px;font-weight:700;
            color:rgba(220,170,170,0.5);
            text-transform:uppercase;letter-spacing:1.2px;
            margin-bottom:8px;margin-left:2px;
            transition:all .3s ease;
        }
        .form-label.focused{
            color:rgba(240,80,80,0.8);
            text-shadow:0 0 10px rgba(220,40,60,0.15);
        }
        .form-label i{margin-right:6px;color:rgba(220,80,80,0.5);}
        .input-wrap{position:relative;}
        .field{
            width:100%;
            border-radius:14px;
            border:1.5px solid rgba(255,255,255,0.1);
            background:rgba(0,0,0,0.2);
            color:#fff;padding:14px 16px;
            font-family:'Sarabun',sans-serif;
            font-size:15px;font-weight:500;
            outline:none;transition:all .3s ease;
        }
        .field::placeholder{color:rgba(220,170,170,0.3);font-size:13.5px;}
        .field:hover{border-color:rgba(255,255,255,0.18);}
        .field:focus{
            border-color:rgba(220,60,80,0.5);
            background:rgba(0,0,0,0.3);
            box-shadow:0 0 0 3px rgba(220,40,60,0.08),0 0 20px rgba(220,40,60,0.06);
        }
        .toggle-pw{
            position:absolute;right:14px;top:50%;transform:translateY(-50%);
            background:none;border:none;cursor:pointer;
            color:rgba(255,255,255,0.25);font-size:15px;
            width:34px;height:34px;border-radius:50%;
            display:flex;align-items:center;justify-content:center;
            transition:all .25s ease;
        }
        .toggle-pw:hover{
            color:#ff8a8a;
            background:rgba(220,40,60,0.08);
>>>>>>> 098d10ce91329536a57bdd05b76bb297e520ffbe
        }
        .anim { animation: fadeUp 0.6s ease-out both; }
        .anim-d1 { animation: fadeUp 0.6s 0.1s ease-out both; }
        .anim-d2 { animation: fadeUp 0.6s 0.2s ease-out both; }
        .anim-d3 { animation: fadeUp 0.6s 0.3s ease-out both; }
        .anim-d4 { animation: fadeUp 0.6s 0.4s ease-out both; }

<<<<<<< HEAD
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
=======
        /* ===== SUBMIT BUTTON ===== */
        .btn-submit{
            width:100%;
            display:flex;align-items:center;justify-content:center;gap:10px;
            padding:14px 24px;border-radius:14px;
            border:none;cursor:pointer;
            background:linear-gradient(135deg,#dc2626,#b91c1c,#991b1b);
            color:#fff;font-family:'Prompt',sans-serif;
            font-size:16px;font-weight:700;
            box-shadow:0 8px 30px rgba(220,40,60,0.4);
            transition:all .35s ease;
            position:relative;overflow:hidden;
            margin-top:6px;
        }
        .btn-submit::before{
            content:'';position:absolute;
            top:-2px;left:-100%;width:60%;height:calc(100% + 4px);
            background:linear-gradient(90deg,transparent,rgba(255,255,255,0.35),transparent);
            animation:btnShine 3s ease-in-out infinite;
        }
        @keyframes btnShine{0%,70%,100%{left:-100%;}30%{left:150%;}}
        .btn-submit:hover{
            transform:translateY(-2px);
            box-shadow:0 12px 40px rgba(220,40,60,0.5),0 0 60px rgba(220,40,60,0.1);
        }
        .btn-submit:active{transform:translateY(0) scale(0.97);}

        /* ===== FEATURES ===== */
        .features-row{
            display:flex;gap:10px;
            margin-top:24px;padding-top:20px;
            border-top:1px solid rgba(255,255,255,0.05);
        }
        .feat-item{
            flex:1;text-align:center;
            background:rgba(255,255,255,0.03);
            border:1px solid rgba(255,255,255,0.05);
            border-radius:12px;padding:14px 8px;
            transition:all .35s ease;
        }
        .feat-item:hover{
            background:rgba(220,40,60,0.06);
            border-color:rgba(220,40,60,0.15);
            transform:translateY(-3px);
            box-shadow:0 8px 25px rgba(0,0,0,0.2);
        }
        .feat-item i{
            display:block;font-size:16px;margin-bottom:8px;
        }
        .feat-item span{
            color:rgba(220,170,170,0.4);font-size:10.5px;
            font-weight:600;font-family:'Prompt',sans-serif;
            line-height:1.4;display:block;
        }

        /* ===== FOOTER ===== */
        .footer{
            text-align:center;margin-top:24px;
        }
        .btn-back{
            display:inline-flex;align-items:center;gap:8px;
            color:rgba(255,255,255,0.35);font-size:13px;font-weight:600;
            font-family:'Prompt',sans-serif;
            padding:10px 22px;border-radius:50px;
            background:rgba(255,255,255,0.04);
            border:1px solid rgba(255,255,255,0.07);
            text-decoration:none;transition:all .3s ease;
            margin-bottom:14px;
        }
        .btn-back:hover{
            color:rgba(255,255,255,0.8);
            background:rgba(220,40,60,0.08);
            border-color:rgba(220,40,60,0.2);
            transform:translateX(-4px);
        }
        .footer-text{
            color:rgba(200,160,160,0.2);font-size:11px;
        }

        /* ===== ANIMATIONS ===== */
        .au{opacity:0;transform:translateY(25px);animation:aUp .65s ease forwards;}
        @keyframes aUp{to{opacity:1;transform:translateY(0);}}
        .ad{opacity:0;transform:translateY(-20px);animation:aDown .6s ease forwards;}
        @keyframes aDown{to{opacity:1;transform:translateY(0);}}
        .asi{opacity:0;transform:scale(0.5);animation:aSi .55s cubic-bezier(.34,1.56,.64,1) forwards;}
        @keyframes aSi{to{opacity:1;transform:scale(1);}}
        .d1{animation-delay:.1s;}.d2{animation-delay:.2s;}.d3{animation-delay:.35s;}
        .d4{animation-delay:.5s;}.d5{animation-delay:.65s;}.d6{animation-delay:.8s;}

        /* ===== RESPONSIVE ===== */
        @media(max-width:480px){
            .login-card{padding:24px 20px;border-radius:18px;}
            .logo-img{width:72px;height:72px;}
            .field{font-size:16px;padding:13px 14px;}
            .btn-submit{font-size:15px;padding:13px 20px;}
            .feat-item{padding:10px 6px;}
            .feat-item i{font-size:14px;}
            .feat-item span{font-size:9.5px;}
>>>>>>> 098d10ce91329536a57bdd05b76bb297e520ffbe
        }
    </style>
</head>
<body>

<<<<<<< HEAD
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
=======
<!-- BG -->
<div class="bg"></div>
<div class="aurora">
    <div class="aurora-band a1"></div>
    <div class="aurora-band a2"></div>
</div>
<div class="stars" id="stars"></div>
<div class="particles" id="particles"></div>

<div class="container">

    <!-- Logo -->
    <div class="logo-area">
        <div class="logo-wrap asi">
            <div class="logo-glow"></div>
            <img src="images/cvc_logo.png" alt="CVC Logo" class="logo-img">
        </div>
        <h1 class="logo-title ad d1">เข้าสู่ระบบ</h1>
        <p class="logo-sub ad d2">CVC Smart System • วิทยาลัยอาชีวศึกษาเชียงราย</p>
    </div>

    <!-- Card -->
    <div class="login-card au d3">
        <div class="ornament-line"></div>

        <!-- Error -->
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error-alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="login_db.php" method="POST">
            <div class="form-group">
                <label class="form-label" id="lbl-user">
                    <i class="fa-solid fa-user"></i> ชื่อผู้ใช้งาน
                </label>
                <div class="input-wrap">
                    <input type="text" name="username" required class="field"
                        placeholder="กรอกชื่อผู้ใช้งาน"
                        onfocus="document.getElementById('lbl-user').classList.add('focused')"
                        onblur="document.getElementById('lbl-user').classList.remove('focused')">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" id="lbl-pass">
                    <i class="fa-solid fa-lock"></i> รหัสผ่าน
                </label>
                <div class="input-wrap">
                    <input type="password" name="password" id="password" required class="field"
                        placeholder="กรอกรหัสผ่าน"
                        style="padding-right:50px;"
                        onfocus="document.getElementById('lbl-pass').classList.add('focused')"
                        onblur="document.getElementById('lbl-pass').classList.remove('focused')">
                    <button type="button" onclick="togglePassword()" class="toggle-pw">
                        <i class="fa-solid fa-eye" id="toggleIcon"></i>
                    </button>
>>>>>>> 098d10ce91329536a57bdd05b76bb297e520ffbe
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบ
            </button>
        </form>

        <!-- Features -->
        <div class="features-row">
            <div class="feat-item au d4">
                <i class="fa-solid fa-wand-magic-sparkles" style="color:rgba(220,80,80,0.5)"></i>
                <span>จัดตาราง<br>อัตโนมัติ</span>
            </div>
            <div class="feat-item au d5">
                <i class="fa-solid fa-bolt" style="color:rgba(255,160,50,0.5)"></i>
                <span>อัปเดต<br>เรียลไทม์</span>
            </div>
            <div class="feat-item au d6">
                <i class="fa-solid fa-shield-halved" style="color:rgba(255,200,60,0.5)"></i>
                <span>ปลอดภัย<br>มั่นใจ</span>
            </div>
        </div>
<<<<<<< HEAD

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
=======
    </div>

    <!-- Footer -->
    <div class="footer au d6">
        <a href="index.php" class="btn-back">
            <i class="fa-solid fa-arrow-left" style="font-size:12px;"></i> กลับหน้าหลัก
        </a>
        <p class="footer-text">© 2568 CVC Smart System v2.0</p>
    </div>

</div>
>>>>>>> 098d10ce91329536a57bdd05b76bb297e520ffbe

<script>
// Toggle password
function togglePassword(){
    const pw=document.getElementById('password');
    const icon=document.getElementById('toggleIcon');
    if(pw.type==='password'){pw.type='text';icon.classList.replace('fa-eye','fa-eye-slash');}
    else{pw.type='password';icon.classList.replace('fa-eye-slash','fa-eye');}
}

// Stars
(function(){
    const c=document.getElementById('stars');
    for(let i=0;i<40;i++){
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
    for(let i=0;i<15;i++){
        const p=document.createElement('div');p.className='particle';
        const sz=Math.random()*4+1.5;
        p.style.cssText=`left:${Math.random()*100}%;width:${sz}px;height:${sz}px;background:${colors[Math.floor(Math.random()*colors.length)]};animation-duration:${Math.random()*10+8}s;animation-delay:${Math.random()*12}s;`;
        c.appendChild(p);
    }
})();
</script>
</body>
</html>