<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config/db.php';
if (!isset($pdo)) { die("<div style='color:red;text-align:center;padding:50px;'>Error: Database Connection Failed.</div>"); }
try {
    $classes = $pdo->query("SELECT * FROM class_groups ORDER BY cla_id ASC")->fetchAll();
    $teachers = $pdo->query("SELECT * FROM teachers ORDER BY tea_fullname ASC")->fetchAll();
} catch (PDOException $e) { die("Database Error: " . $e->getMessage()); }
$current_year_real = date('Y') + 543;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        }

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
        }
        @keyframes ornShine{0%,100%{opacity:0.4;}50%{opacity:1;}}
        .ornament p{color:rgba(200,160,160,0.25);font-size:11px;}

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