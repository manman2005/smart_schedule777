<?php
// htdocs/admin/auto_scheduler.php
require_once '../config/db.php';
require_once '../includes/auth.php';
checkAdmin();
require_once '../includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    div:where(.swal2-container) div:where(.swal2-popup) {
        font-family: 'Sarabun', sans-serif !important;
        border-radius: 1rem !important;
    }
    .swal2-title { font-size: 1.5rem !important; }
    .swal2-html-container { font-size: 1rem !important; }
</style>

<div class="max-w-7xl mx-auto pb-12">
    <div class="mb-8">
        <a href="index.php" class="inline-flex items-center text-slate-400 hover:text-cvc-blue transition text-xs font-bold uppercase tracking-wider mb-2">
            <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
        </a>
        <h1 class="text-3xl font-serif font-bold text-slate-800">
            ระบบจัดตารางอัตโนมัติ <span class="text-cvc-gold text-lg font-sans font-normal">(Auto Scheduler)</span>
        </h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-1">
            <div class="card-premium p-0 overflow-hidden relative border-t-4 border-t-cvc-blue">
                <div class="bg-gradient-to-br from-cvc-blue to-indigo-900 p-8 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                    <div class="relative z-10 flex items-center gap-4 mb-2">
                        <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center text-2xl shadow-inner border border-white/20">
                            <i class="fa-solid fa-wand-magic-sparkles text-yellow-300"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold tracking-wide">AI Generator</h2>
                            <p class="text-blue-200 text-xs font-mono">CVC INTELLIGENT SYSTEM</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-8">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">ปีการศึกษา</label>
                            <input type="number" id="year" value="<?php echo date('Y')+543; ?>" class="w-full font-bold text-center text-lg text-cvc-blue bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-cvc-blue">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">ภาคเรียน</label>
                            <select id="semester" class="w-full font-bold text-center text-lg text-slate-700 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-cvc-blue">
                                <option value="1">ภาคเรียนที่ 1</option>
                                <option value="2">ภาคเรียนที่ 2</option>
                                <option value="3">ภาคเรียนฤดูร้อน</option>
                            </select>
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl flex gap-3 items-start">
                            <i class="fa-solid fa-robot text-blue-500 mt-1"></i>
                            <div class="text-xs text-blue-800 leading-relaxed font-medium">
                                ระบบจะแยกคาบ <b>ทฤษฎี</b> และ <b>ปฏิบัติ</b> ออกจากกัน และจัดห้องเรียนตามที่ระบุไว้ในรายวิชา
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 space-y-3">
                        <button onclick="startScheduler()" id="btnStart" class="btn-cvc w-full justify-center py-3 shadow-lg text-lg">
                            <i class="fa-solid fa-bolt mr-2"></i> เริ่มประมวลผล (Start)
                        </button>
                        
                        <button onclick="checkSystemReadiness()" id="btnCheck" class="w-full py-3 rounded-full border-2 border-amber-100 text-amber-600 font-bold hover:bg-amber-50 hover:border-amber-200 transition shadow-sm flex items-center justify-center gap-2 group">
                            <i class="fa-solid fa-stethoscope group-hover:scale-110 transition"></i> ตรวจสอบความพร้อมข้อมูล
                        </button>

                        <button onclick="clearSchedule()" id="btnClear" class="w-full py-3 rounded-full border-2 border-red-100 text-red-500 font-bold hover:bg-red-50 hover:border-red-200 transition shadow-sm flex items-center justify-center gap-2 group">
                            <i class="fa-solid fa-trash-can group-hover:scale-110 transition"></i> ล้างตารางเดิมทิ้ง (Clear)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-slate-900 rounded-[20px] shadow-2xl overflow-hidden flex flex-col border border-slate-800 relative h-[600px]">
                <div class="bg-slate-800 px-5 py-3 flex items-center justify-between border-b border-slate-700 shrink-0">
                    <div class="flex gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                    </div>
                    <div class="text-xs font-mono text-slate-400 flex items-center gap-2">
                        <i class="fa-solid fa-terminal"></i> scheduler_process.exe
                    </div>
                </div>

                <div id="progressContainer" class="hidden absolute top-[45px] left-0 right-0 bg-slate-900/95 backdrop-blur px-8 py-6 border-b border-blue-500/30 z-20">
                    <div class="flex justify-between text-xs font-bold text-blue-400 mb-2 font-mono tracking-wider">
                        <span id="statusText" class="animate-pulse flex items-center gap-2">
                            <i class="fa-solid fa-circle-notch fa-spin"></i> INITIALIZING...
                        </span>
                        <span id="progressText" class="text-white">0%</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-cyan-400 h-full rounded-full transition-all duration-300 relative" id="progressBar" style="width: 0%">
                            <div class="absolute inset-0 bg-white/50 animate-[shimmer_1s_infinite]"></div>
                        </div>
                    </div>
                </div>

                <div id="logContainer" class="flex-1 p-6 font-mono text-xs md:text-sm overflow-y-auto space-y-2 bg-slate-900 text-slate-300 scrollbar-hide relative">
                    <div class="text-slate-500 italic border-l-2 border-slate-700 pl-3 py-1">Ready to start smart scheduler...</div>
                </div>
                
                <div id="resultOverlay" class="hidden absolute inset-0 bg-slate-900/95 backdrop-blur-md flex flex-col items-center justify-center text-center z-30 p-8">
                    <div id="resultIcon" class="w-20 h-20 rounded-full flex items-center justify-center text-4xl text-white mb-6 shadow-2xl animate-bounce"></div>
                    <h3 id="resultTitle" class="text-2xl font-bold text-white mb-2"></h3>
                    <p id="resultDesc" class="text-slate-300 mb-6 font-medium"></p>
                    <div id="adviceBox" class="hidden bg-white/10 border border-white/20 rounded-xl p-4 max-w-md w-full mb-8 text-left max-h-60 overflow-y-auto custom-scrollbar">
                        <h4 class="text-amber-400 font-bold text-xs uppercase mb-2 flex items-center gap-2 sticky top-0 bg-slate-900/0 backdrop-blur-sm">
                            <i class="fa-solid fa-lightbulb"></i> รายละเอียด / คำแนะนำ:
                        </h4>
                        <ul id="adviceList" class="text-slate-300 text-xs space-y-2 list-disc list-inside"></ul>
                    </div>
                    <a href="view_schedule_master.php" id="resultBtn" class="px-8 py-3 rounded-full text-sm font-bold transition shadow-lg flex items-center gap-2 transform hover:scale-105">
                        ตรวจสอบตาราง <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 3px; }
</style>

<script>
let logs = document.getElementById('logContainer');

function log(msg, type = 'info') {
    let color = 'text-slate-300'; let icon = '<i class="fa-solid fa-angle-right text-slate-600 mr-2"></i>';
    if (type === 'error') { color = 'text-red-400'; icon = '<i class="fa-solid fa-xmark text-red-500 mr-2"></i>'; } 
    else if (type === 'success') { color = 'text-emerald-400'; icon = '<i class="fa-solid fa-check text-emerald-500 mr-2"></i>'; }
    else if (type === 'warning') { color = 'text-amber-400'; icon = '<i class="fa-solid fa-triangle-exclamation text-amber-500 mr-2"></i>'; }
    let time = new Date().toLocaleTimeString('th-TH', { hour12: false });
    let div = document.createElement('div');
    div.className = `flex items-start ${color} px-2 py-0.5 hover:bg-white/5 rounded transition`;
    div.innerHTML = `<span class="text-slate-600 min-w-[60px] opacity-50 text-[10px] pt-1">[${time}]</span> <div class="flex-1 break-all">${icon}${msg}</div>`;
    logs.appendChild(div); logs.scrollTop = logs.scrollHeight;
}

function updateProgress(percent, text) {
    document.getElementById('progressBar').style.width = percent + '%';
    document.getElementById('progressText').innerText = percent + '%';
    document.getElementById('statusText').innerHTML = `<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> ${text}`;
}

function showResult(status, title, desc, advice = []) {
    const overlay = document.getElementById('resultOverlay'); 
    const iconDiv = document.getElementById('resultIcon'); 
    const btn = document.getElementById('resultBtn'); 
    const adviceBox = document.getElementById('adviceBox'); 
    const adviceList = document.getElementById('adviceList');
    
    overlay.classList.remove('hidden'); 
    document.getElementById('resultTitle').innerText = title; 
    document.getElementById('resultDesc').innerText = desc;
    
    if (status === 'success') {
        iconDiv.className = "w-20 h-20 bg-emerald-500 rounded-full flex items-center justify-center text-4xl text-white mb-6 shadow-[0_0_30px_rgba(16,185,129,0.5)] animate-bounce"; 
        iconDiv.innerHTML = '<i class="fa-solid fa-check"></i>';
        btn.className = "bg-white text-emerald-700 px-8 py-3 rounded-full text-sm font-bold hover:bg-emerald-50 transition shadow-lg flex items-center gap-2 transform hover:scale-105"; 
        btn.innerHTML = 'ตรวจสอบตาราง <i class="fa-solid fa-arrow-right"></i>'; 
        btn.href = "view_schedule_master.php"; 
        adviceBox.classList.add('hidden');
    } else {
        let color = status === 'warning' ? 'amber' : 'red'; 
        let icon = status === 'warning' ? 'triangle-exclamation' : 'xmark';
        iconDiv.className = `w-20 h-20 bg-${color}-500 rounded-full flex items-center justify-center text-4xl text-white mb-6 shadow-[0_0_30px_rgba(239,68,68,0.5)] animate-pulse`; 
        iconDiv.innerHTML = `<i class="fa-solid fa-${icon}"></i>`;
        
        btn.className = "bg-white/10 text-white border border-white/20 px-8 py-3 rounded-full text-sm font-bold hover:bg-white/20 transition shadow-lg flex items-center gap-2"; 
        btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> ลองใหม่อีกครั้ง'; 
        btn.href = "javascript:location.reload()";
        
        if (advice.length > 0) { 
            adviceList.innerHTML = advice.map(a => `<li class="leading-relaxed">${a}</li>`).join(''); 
            adviceBox.classList.remove('hidden'); 
        } else { 
            adviceBox.classList.add('hidden'); 
        }
    }
}

// ฟังก์ชันใหม่: ตรวจสอบความพร้อมของข้อมูล (System Check)
async function checkSystemReadiness() {
    const year = document.getElementById('year').value;
    const semester = document.getElementById('semester').value;
    
    // แสดงสถานะกำลังโหลด
    Swal.fire({
        title: 'กำลังตรวจสอบ...',
        text: 'ระบบกำลังวิเคราะห์ข้อมูลรายวิชาและทรัพยากร',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    try {
        const response = await fetch(`api_scheduler_data.php?year=${year}&semester=${semester}`);
        const data = await response.json();
        
        if(data.error) throw new Error(data.error);

        // วิเคราะห์ข้อมูล
        const tasks = data.tasks || [];
        const rooms = data.rooms || [];
        
        // เช็ควิชาที่ไม่มีครู
        const missingTeachers = tasks.filter(t => !t.tea_id);
        
        // เช็ควิชาที่ไม่มีห้องเรียน (ระบุเฉพาะเจาะจงแต่หาห้องไม่เจอ) - *ใน API นี้อาจจะยังไม่ส่งมา แต่เราเช็คเบื้องต้นได้
        
        // คำนวณชั่วโมงรวม
        const totalHours = tasks.reduce((sum, t) => sum + (parseInt(t.sub_hours) || 0), 0);
        
        let statusHtml = `
            <div class="text-left text-sm space-y-3">
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                    <h4 class="font-bold text-slate-700 mb-2 border-b pb-1">📊 ข้อมูลพื้นฐาน</h4>
                    <p>📅 ปีการศึกษา: <b>${year}/${semester}</b></p>
                    <p>📚 รายวิชาที่ต้องจัด: <b>${tasks.length}</b> วิชา</p>
                    <p>⏱️ ชั่วโมงรวมทั้งหมด: <b>${totalHours}</b> คาบ</p>
                </div>

                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                    <h4 class="font-bold text-slate-700 mb-2 border-b pb-1">🏫 ทรัพยากรห้องเรียน</h4>
                    <p>🚪 จำนวนห้องเรียนทั้งหมด: <b>${rooms.length}</b> ห้อง</p>
                </div>

                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                    <h4 class="font-bold text-slate-700 mb-2 border-b pb-1">👨‍🏫 ความพร้อมครูผู้สอน</h4>
                    <p class="${missingTeachers.length > 0 ? 'text-red-500 font-bold' : 'text-emerald-600'}">
                        ${missingTeachers.length > 0 ? `<i class="fa-solid fa-triangle-exclamation"></i> พบวิชาขาดผู้สอน: ${missingTeachers.length} รายการ` : '<i class="fa-solid fa-check-circle"></i> ระบุผู้สอนครบถ้วน'}
                    </p>
                </div>
            </div>
        `;

        let icon = 'info';
        let title = 'ผลการตรวจสอบระบบ';
        let confirmBtnColor = '#3b82f6';
        
        if(tasks.length === 0) {
            icon = 'warning';
            title = 'ไม่พบข้อมูลรายวิชา';
            statusHtml += '<p class="mt-3 text-red-500 text-xs text-center font-bold">❌ ไม่พบรายวิชาในแผนการเรียนสำหรับเทอมนี้</p>';
            confirmBtnColor = '#f59e0b';
        } else if(missingTeachers.length > 0) {
            icon = 'warning';
            title = 'ข้อมูลยังไม่สมบูรณ์';
            statusHtml += '<p class="mt-3 text-amber-600 text-xs text-center font-bold">⚠️ กรุณากำหนดครูผู้สอนให้ครบก่อนเริ่มจัดตาราง เพื่อประสิทธิภาพสูงสุด</p>';
            confirmBtnColor = '#f59e0b';
        } else {
            icon = 'success';
            title = 'ข้อมูลพร้อมใช้งาน';
            statusHtml += '<p class="mt-3 text-emerald-600 text-xs font-bold text-center"><i class="fa-solid fa-check"></i> ข้อมูลครบถ้วน พร้อมสำหรับการประมวลผล</p>';
            confirmBtnColor = '#10b981';
        }

        Swal.fire({
            title: title,
            html: statusHtml,
            icon: icon,
            confirmButtonText: 'รับทราบ',
            confirmButtonColor: confirmBtnColor
        });

    } catch (e) {
        Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อฐานข้อมูลหรือ API ได้<br><span class="text-xs text-red-400">'+e.message+'</span>', 'error');
    }
}

async function clearSchedule() {
    const year = document.getElementById('year').value; 
    const semester = document.getElementById('semester').value;
    
    // Popup ยืนยันก่อนลบ
    const result = await Swal.fire({
        title: 'ยืนยันการล้างข้อมูล?',
        html: `ต้องการลบตารางสอนทั้งหมดของ<br>ปี <b>${year}</b> ภาคเรียนที่ <b>${semester}</b><br><span class="text-red-500 font-bold text-sm mt-2 block">⚠️ การกระทำนี้ไม่สามารถเรียกคืนได้!</span>`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fa-solid fa-trash-can mr-2"></i>ยืนยันลบข้อมูล',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true,
        focusCancel: true
    });

    if (!result.isConfirmed) return;
    
    document.getElementById('progressContainer').classList.remove('hidden'); 
    document.getElementById('resultOverlay').classList.add('hidden'); 
    logs.innerHTML = '';
    updateProgress(50, "Clearing Database...");
    
    try {
        const res = await fetch('api_clear_schedule.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({year, semester}) });
        const data = await res.json();
        if(data.status === 'success') { 
            updateProgress(100, "Done"); 
            log(data.message, 'success'); 
            showResult('success', 'ล้างข้อมูลสำเร็จ', data.message); 
        } else { 
            throw new Error(data.message); 
        }
    } catch(e) { 
        log(e.message, 'error'); 
        showResult('error', 'เกิดข้อผิดพลาด', e.message); 
    }
}

function splitHours(totalHours) {
    if (totalHours <= 4) return [totalHours]; 
    let chunks = [];
    while (totalHours > 0) {
        let chunk = (totalHours >= 4) ? 4 : totalHours;
        chunks.push(chunk);
        totalHours -= chunk;
    }
    return chunks;
}

function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}

async function startScheduler() {
    const year = document.getElementById('year').value;
    const semester = document.getElementById('semester').value;
    const btn = document.getElementById('btnStart');
    
    // Popup ยืนยันก่อนเริ่ม
    const result = await Swal.fire({
        title: 'ยืนยันการจัดตารางใหม่?',
        html: `สำหรับปี <b>${year}</b> ภาคเรียนที่ <b>${semester}</b><br><span class="text-amber-600 text-sm mt-2 block"><i class="fa-solid fa-triangle-exclamation mr-1"></i> ข้อมูลตารางสอนเดิมในเทอมนี้จะถูกล้างและจัดใหม่</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#d33',
        confirmButtonText: '<i class="fa-solid fa-bolt mr-2"></i>ใช่, เริ่มประมวลผล',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true
    });

    if (!result.isConfirmed) return;

    btn.disabled = true; btn.classList.add('opacity-50', 'cursor-not-allowed');
    document.getElementById('progressContainer').classList.remove('hidden');
    document.getElementById('resultOverlay').classList.add('hidden');
    logs.innerHTML = '';
    
    try {
        updateProgress(10, "Fetching Data...");
        const response = await fetch(`api_scheduler_data.php?year=${year}&semester=${semester}`);
        
        if (!response.ok) {
            throw new Error(`Server returned ${response.status} ${response.statusText}`);
        }
        
        const data = await response.json();
        if(data.error) throw new Error(data.error);

        if(!data.tasks || data.tasks.length === 0) {
            showResult('warning', 'ไม่พบข้อมูลรายวิชา', 'ไม่มีรายวิชาที่ต้องจัดในเทอมนี้ หรือข้อมูลอาจยังไม่ถูกเพิ่มลงในแผนการเรียน');
            return;
        }

        const rawTasks = data.tasks; 
        const rooms = data.rooms; 
        const times = data.times; 
        const days = data.days;
        const busySlots = data.busy_slots || []; 

        log(`โหลดข้อมูลสำเร็จ: ${rawTasks.length} วิชา, ${rooms.length} ห้อง`, 'success');

        // --- สร้างชื่อกลุ่มเรียนแบบเต็ม ---
        rawTasks.forEach(t => {
            if(t.cla_year && t.cla_group_no) {
                let level = Math.max(1, parseInt(year) - parseInt(t.cla_year) + 1);
                t.full_cla_name = `${t.cla_name}.${level}/${parseInt(t.cla_group_no)}`;
            } else {
                t.full_cla_name = t.cla_name;
            }
        });

        // --- 1. ตรวจสอบรายวิชาที่ไม่มีครู ---
        let missingTeacherSubjects = rawTasks.filter(t => !t.tea_id);
        
        if (missingTeacherSubjects.length > 0) {
            let errorList = missingTeacherSubjects.map(t => 
                `<span class="text-amber-400 font-bold">${t.sub_code}</span> ${t.sub_name} (กลุ่ม: ${t.full_cla_name})`
            );
            errorList.unshift('<span class="text-white font-bold">⚠️ กรุณากำหนดครูผู้สอนให้ครบทุกวิชาก่อนเริ่มจัดตาราง</span>');
            
            log('STOP: พบรายวิชาที่ยังไม่ระบุผู้สอน', 'error');
            
            showResult('warning', 'ข้อมูลไม่พร้อม (Missing Teachers)', 
                `พบรายวิชาจำนวน ${missingTeacherSubjects.length} รายการ ที่ยังไม่ได้ระบุครูผู้สอน`, 
                errorList
            );
            
            document.getElementById('btnStart').disabled = false;
            document.getElementById('btnStart').classList.remove('opacity-50', 'cursor-not-allowed');
            return; 
        }

        let tasks = [];
        rawTasks.forEach(t => { 
            let tpn = t.sub_th_pr_ot ? t.sub_th_pr_ot.split('-') : [0,0,0]; 
            let theoryHours = parseInt(tpn[0]) || 0;
            let practiceHours = parseInt(tpn[1]) || 0;
            
            if (theoryHours === 0 && practiceHours === 0) {
                theoryHours = parseInt(t.sub_hours) || 1;
            }

            if (theoryHours > 0) {
                splitHours(theoryHours).forEach((chunk, idx) => {
                    tasks.push({ ...t, hoursNeeded: chunk, taskType: 'Theory', preferredRoom: t.sub_room_theory, splitPart: idx + 1 });
                });
            }

            if (practiceHours > 0) {
                splitHours(practiceHours).forEach((chunk, idx) => {
                    tasks.push({ ...t, hoursNeeded: chunk, taskType: 'Practice', preferredRoom: t.sub_room_practice, splitPart: idx + 1 });
                });
            }
        });

        tasks.sort((a, b) => b.hoursNeeded - a.hoursNeeded);

        let scheduled = []; 
        let conflictMap = []; 
        let failCount = 0;
        let failedDetails = [];

        for (let i = 0; i < tasks.length; i++) {
            let task = tasks[i];
            let percent = 10 + Math.round(((i+1)/tasks.length) * 80);
            updateProgress(percent, `Scheduling: ${task.sub_code}`);

            let possibleRooms = [];
            if(task.preferredRoom) {
                let exactRoom = rooms.find(r => r.roo_id == task.preferredRoom);
                if(exactRoom) possibleRooms = [exactRoom];
                else possibleRooms = rooms.filter(r => r.roo_type == task.preferredRoom);
            }

            if(possibleRooms.length == 0) {
                if (task.taskType === 'Practice') possibleRooms = rooms.filter(r => r.roo_type.includes('ปฏิบัติ') || r.roo_type.includes('Lab'));
                else possibleRooms = rooms.filter(r => r.roo_type === 'ห้องเรียนสามัญ');
            }
            
            if(possibleRooms.length == 0) possibleRooms = rooms;
            
            shuffleArray(possibleRooms); 
            let isPlaced = false;
            let checkDays = shuffleArray([...days]);

            dayLoop:
            for(let day of checkDays) {
                for(let room of possibleRooms) {
                    for(let tIdx = 0; tIdx <= times.length - task.hoursNeeded; tIdx++) {
                        let slots = []; 
                        let conflict = false;

                        for(let k=0; k<task.hoursNeeded; k++) {
                            let slot = times[tIdx+k];
                            // 1. เช็คพักเที่ยง
                            if(slot.tim_range.startsWith('12:00')) { conflict = true; break; }

                            // 2. เช็คเวลาครูไม่ว่าง
                            if (task.tea_id) {
                                let isTeacherBusy = busySlots.some(b => 
                                    b.tea_id == task.tea_id && 
                                    b.day_id == day && 
                                    b.tim_id == slot.tim_id
                                );
                                if (isTeacherBusy) { conflict = true; break; }
                            }
                            
                            // 3. เช็คตารางชน
                            let isConflict = conflictMap.some(c => 
                                c.day == day && c.time == slot.tim_id && (
                                    (c.type == 'room' && c.id == room.roo_id) ||      
                                    (c.type == 'teacher' && c.id == task.tea_id) ||   
                                    (c.type == 'class' && c.id == task.cla_id)        
                                )
                            );

                            if(isConflict) { conflict = true; break; }
                            slots.push(slot.tim_id);
                        }

                        if(!conflict) {
                            slots.forEach(sid => {
                                scheduled.push({
                                    cla_id: task.cla_id, sub_id: task.sub_id, tea_id: task.tea_id,
                                    roo_id: room.roo_id, day_id: day, tim_id: sid, sch_hours: task.hoursNeeded
                                });
                                conflictMap.push({day:day, time:sid, type:'room', id:room.roo_id});
                                conflictMap.push({day:day, time:sid, type:'class', id:task.cla_id});
                                if(task.tea_id) conflictMap.push({day:day, time:sid, type:'teacher', id:task.tea_id});
                            });
                            isPlaced = true;
                            break dayLoop; 
                        }
                    }
                }
            }

            if(!isPlaced) { 
                failCount++; 
                let reason = `<b>${task.sub_code}</b>: `;
                if(task.tea_id) {
                    let teacherName = task.tea_fullname || 'ครู';
                    reason += `<span class="text-red-300">${teacherName}</span> ติดสอน/ไม่ว่าง/ `;
                }
                if(task.preferredRoom) reason += `หาห้อง ${task.preferredRoom} ไม่ได้/ `;
                reason += `กลุ่ม ${task.full_cla_name} เต็ม`;
                
                log(`FAILED: ${task.sub_code} - Resource Conflict`, 'error'); 
                failedDetails.push(reason);
            }
        }

        updateProgress(95, "Saving Data...");
        const saveRes = await fetch('api_scheduler_save.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({year, semester, schedules: scheduled})
        });
        
        if (!saveRes.ok) {
             throw new Error(`Save failed: ${saveRes.status}`);
        }
        
        const saveJson = await saveRes.json();

        if(saveJson.status === 'success') {
            updateProgress(100, "เสร็จสิ้น");
            if (failCount > 0) {
                let advice = [
                    "ลองตรวจสอบรายวิชาที่มีเงื่อนไขห้องเรียนเฉพาะเจาะจง",
                    "ตรวจสอบภาระงานสอนของครู (อาจมีสอนซ้อนกัน หรือครูระบุวันไม่ว่างมากเกินไป)",
                    "ลองเพิ่มจำนวนห้องเรียน หรือขยายเวลาเรียน",
                    "<b>รายการที่จัดไม่ได้:</b>"
                ];
                failedDetails.slice(0, 10).forEach(d => advice.push(d));
                if(failedDetails.length > 10) advice.push(`...และอีก ${failedDetails.length - 10} รายการ`);

                showResult('warning', 'จัดตารางได้ไม่ครบถ้วน', `สำเร็จ ${tasks.length - failCount} / ล้มเหลว ${failCount} รายการ`, advice);
            } else {
                showResult('success', 'ประมวลผลเสร็จสิ้น!', 'จัดตารางเรียนเรียบร้อยแล้ว 100%');
            }
        } else {
            throw new Error(saveJson.message);
        }

    } catch (e) {
        log(e.message, 'error');
        let title = 'เกิดข้อผิดพลาด!';
        let desc = e.message;
        let advice = [];

        if (e.message.includes('Unexpected token')) {
            desc = 'ข้อมูลจากเซิร์ฟเวอร์ผิดพลาด (JSON Error)';
            advice.push('อาจเกิดจากไฟล์ PHP มีข้อผิดพลาด (Syntax Error)');
            advice.push('ลองตรวจสอบไฟล์ api_scheduler_data.php');
        } else if (e.message.includes('Server returned 500')) {
            desc = 'เซิร์ฟเวอร์ภายในขัดข้อง (Error 500)';
            advice.push('ตรวจสอบ Code PHP ในไฟล์ api_scheduler_data.php');
            advice.push('อาจเกิดจากไม่ได้ระบุครูผู้สอนในบางรายวิชา');
        } else if (e.message.includes('Failed to fetch')) {
            desc = 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้';
            advice.push('ตรวจสอบอินเทอร์เน็ต');
        }

        showResult('error', title, desc, advice);
        document.getElementById('btnStart').disabled = false;
        document.getElementById('btnStart').classList.remove('opacity-50', 'cursor-not-allowed');
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>