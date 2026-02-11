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
        }

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
        }
    </style>
</head>
<body>

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
    </div>

    <!-- Footer -->
    <div class="footer au d6">
        <a href="index.php" class="btn-back">
            <i class="fa-solid fa-arrow-left" style="font-size:12px;"></i> กลับหน้าหลัก
        </a>
        <p class="footer-text">© 2568 CVC Smart System v2.0</p>
    </div>

</div>

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