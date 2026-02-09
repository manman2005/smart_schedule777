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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php include_once __DIR__ . '/notifications.php'; ?>
    
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
        .btn-base { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; font-weight: 700; border-radius: 9999px; padding: 0.6rem 1rem; transition: all 0.2s ease; }
        .btn-cvc { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; font-weight: 700; border-radius: 9999px; padding: 0.6rem 1rem; background: linear-gradient(135deg, #b91c1c 0%, #450a0a 100%); color: #ffffff; box-shadow: 0 6px 16px rgba(185, 28, 28, 0.25); transition: all 0.2s ease; }
        .btn-cvc:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(185, 28, 28, 0.35); }
        .btn-soft { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; font-weight: 700; border-radius: 9999px; padding: 0.6rem 1rem; background: #ffffff; color: #334155; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 4px 10px rgba(0,0,0,0.06); transition: all 0.2s ease; }
        .btn-soft:hover { border-color: rgba(185,28,28,0.35); color: #b91c1c; }
        .btn-outline { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; font-weight: 700; border-radius: 9999px; padding: 0.5rem 0.9rem; background: transparent; color: #b91c1c; border: 2px solid #b91c1c; transition: all 0.2s ease; }
        .btn-outline:hover { background: rgba(185,28,28,0.08); }
        .btn-icon { width: 2.5rem; height: 2.5rem; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center; }
        button { border-radius: 9999px !important; }
        a[class*="px-"][class*="py-"] { border-radius: 9999px; }
        .field-modern { border-radius: 14px; border: 2px solid rgba(51,65,85,0.25); background: #ffffff; color: #334155; padding: 0.5rem 0.9rem; min-height: 42px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.04), 0 2px 10px rgba(0,0,0,0.04); outline: none; transition: all 0.2s ease; }
        .field-modern:focus { border-color: #b91c1c; box-shadow: inset 0 1px 2px rgba(0,0,0,0.04), 0 0 0 4px rgba(185,28,28,0.12); }
        .field-modern:hover { border-color: rgba(185,28,28,0.35); }
        .field-modern::placeholder { color: #94a3b8; }
        .field-modern[readonly] { background-color: #f8fafc; color: #64748b; }
        .field-modern:disabled { background-color: #f8fafc; color: #94a3b8; border-color: rgba(51,65,85,0.15); cursor: not-allowed; opacity: 0.85; }
        textarea.field-modern { min-height: 96px; resize: vertical; }
        select.field-modern { -webkit-appearance: none; appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg width='16' height='16' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6 9l6 6 6-6' stroke='%23450a0a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 16px; padding-right: 2rem; }
        select.field-modern:focus { background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg width='16' height='16' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6 9l6 6 6-6' stroke='%23b91c1c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"); }
        select.field-modern:disabled { background-color: #f8fafc; color: #94a3b8; border-color: rgba(51,65,85,0.15); cursor: not-allowed; opacity: 0.85; }
        .chip-select { border-radius: 14px; border: 2px solid rgba(51,65,85,0.25); background: #ffffff; color: #334155; padding: 0.5rem 0.9rem; font-weight: 700; box-shadow: 0 6px 14px rgba(0,0,0,0.06); transition: all 0.2s ease; }
        .chip-select:focus { border-color: #b91c1c; box-shadow: 0 10px 20px rgba(185,28,28,0.12); }
        .ps { position: relative; width: 100%; }
        .ps-native { position: absolute; opacity: 0; pointer-events: none; height: 0; width: 0; }
        .ps-control { width: 100%; display: flex; align-items: center; justify-content: space-between; }
        .ps-value { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 700; color: #334155; }
        .ps-arrow { margin-left: 0.5rem; width: 18px; height: 18px; background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg width='18' height='18' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M6 9l6 6 6-6' stroke='%23450a0a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: center; background-size: 18px; transition: transform 0.2s ease; }
        .ps-open .ps-arrow { transform: rotate(180deg); }
        .ps-menu { position: fixed; background: #ffffff; border-radius: 14px; border: 1px solid rgba(51,65,85,0.15); box-shadow: 0 12px 28px rgba(0,0,0,0.12); padding: 6px; z-index: 9999; display: none; }
        .ps-list { max-height: 260px; overflow: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; }
        .ps-option { padding: 8px 10px; border-radius: 10px; font-weight: 700; color: #334155; cursor: pointer; }
        .ps-option:hover { background: rgba(185,28,28,0.08); color: #b91c1c; }
        .ps-option.ps-selected { background: rgba(185,28,28,0.14); color: #b91c1c; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const applyPremium = (el) => {
                if (!el.classList.contains('field-modern')) el.classList.add('field-modern');
            };
            document.querySelectorAll('select:not(.no-premium)').forEach(applyPremium);
            const disallowed = ['checkbox','radio','file','hidden','submit','button','range','color','image','reset'];
            document.querySelectorAll('input:not(.no-premium), textarea:not(.no-premium)').forEach(el => {
                if (el.tagName.toLowerCase() === 'textarea') { applyPremium(el); return; }
                const type = (el.getAttribute('type') || 'text').toLowerCase();
                if (disallowed.includes(type)) return;
                applyPremium(el);
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const isTouch = navigator.maxTouchPoints > 0;
            if (isTouch) return;
            document.querySelectorAll('select:not(.no-premium)').forEach(sel => {
                if (sel.multiple) return;
                if (sel.dataset.premiumSelectInit === '1') return;
                sel.dataset.premiumSelectInit = '1';
                const wrapper = document.createElement('div');
                wrapper.className = 'ps';
                sel.parentNode.insertBefore(wrapper, sel);
                wrapper.appendChild(sel);
                sel.classList.add('ps-native');
                sel.classList.remove('field-modern');
                const control = document.createElement('button');
                control.type = 'button';
                control.className = 'ps-control field-modern';
                control.disabled = sel.disabled;
                if (sel.disabled) { control.classList.add('opacity-60','cursor-not-allowed'); }
                const value = document.createElement('span');
                value.className = 'ps-value';
                value.textContent = sel.options[sel.selectedIndex]?.text || '';
                const arrow = document.createElement('span');
                arrow.className = 'ps-arrow';
                control.appendChild(value);
                control.appendChild(arrow);
                wrapper.appendChild(control);
                const menu = document.createElement('div');
                menu.className = 'ps-menu';
                const list = document.createElement('ul');
                list.className = 'ps-list';
                menu.appendChild(list);
                document.body.appendChild(menu);
                function build() {
                    list.innerHTML = '';
                    Array.from(sel.options).forEach((opt, idx) => {
                        const li = document.createElement('li');
                        li.className = 'ps-option' + (opt.selected ? ' ps-selected' : '');
                        li.textContent = opt.text;
                        li.dataset.value = opt.value;
                        li.addEventListener('click', () => {
                            sel.selectedIndex = idx;
                            value.textContent = opt.text;
                            sel.dispatchEvent(new Event('input', { bubbles: true }));
                            sel.dispatchEvent(new Event('change', { bubbles: true }));
                            close();
                        });
                        list.appendChild(li);
                    });
                }
                build();
                const observer = new MutationObserver(() => {
                    control.disabled = sel.disabled;
                    control.classList.toggle('cursor-not-allowed', sel.disabled);
                    control.classList.toggle('opacity-60', sel.disabled);
                    const prevText = value.textContent;
                    build();
                    value.textContent = sel.options[sel.selectedIndex]?.text || prevText || '';
                    if (wrapper.classList.contains('ps-open')) reposition();
                });
                observer.observe(sel, { childList: true, attributes: true });
                function reposition() {
                    const rect = control.getBoundingClientRect();
                    const spaceBelow = window.innerHeight - rect.bottom - 16;
                    const maxH = Math.max(160, Math.min(300, spaceBelow));
                    menu.style.width = `${rect.width}px`;
                    menu.style.left = `${rect.left}px`;
                    menu.style.maxHeight = `${maxH}px`;
                    list.style.maxHeight = `${maxH - 12}px`;
                    const desiredTop = rect.bottom + 6;
                    const menuHeight = menu.offsetHeight || maxH;
                    if (spaceBelow < 160) {
                        menu.style.top = `${Math.max(12, rect.top - menuHeight - 6)}px`;
                    } else {
                        menu.style.top = `${desiredTop}px`;
                    }
                }
                function open() {
                    if (sel.disabled || control.disabled) return;
                    wrapper.classList.add('ps-open');
                    menu.style.display = 'block';
                    reposition();
                    const selected = list.querySelector('.ps-selected');
                    if (selected) selected.scrollIntoView({ block: 'nearest' });
                }
                function close() {
                    wrapper.classList.remove('ps-open');
                    menu.style.display = 'none';
                }
                control.addEventListener('click', () => {
                    if (wrapper.classList.contains('ps-open')) close(); else open();
                });
                document.addEventListener('click', (e) => {
                    if (!wrapper.contains(e.target)) close();
                });
                window.addEventListener('scroll', () => { if (wrapper.classList.contains('ps-open')) reposition(); }, true);
                window.addEventListener('resize', () => { if (wrapper.classList.contains('ps-open')) reposition(); });
                control.addEventListener('keydown', (e) => {
                    const opts = Array.from(list.querySelectorAll('.ps-option'));
                    let idx = sel.selectedIndex;
                    if (e.key === 'ArrowDown') { e.preventDefault(); idx = Math.min(idx + 1, opts.length - 1); }
                    if (e.key === 'ArrowUp') { e.preventDefault(); idx = Math.max(idx - 1, 0); }
                    if (e.key === 'Enter') { e.preventDefault(); close(); return; }
                    if (e.key === 'Escape') { e.preventDefault(); close(); return; }
                    if (idx !== sel.selectedIndex) {
                        sel.selectedIndex = idx;
                        value.textContent = sel.options[idx].text;
                        sel.dispatchEvent(new Event('input', { bubbles: true }));
                        sel.dispatchEvent(new Event('change', { bubbles: true }));
                        open();
                    }
                });
                const sync = () => {
                    value.textContent = sel.options[sel.selectedIndex]?.text || '';
                    Array.from(list.children).forEach((li, i) => {
                        li.classList.toggle('ps-selected', i === sel.selectedIndex);
                    });
                };
                sel.addEventListener('change', sync);
                sel.addEventListener('input', sync);
            });
        });
    </script>
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
