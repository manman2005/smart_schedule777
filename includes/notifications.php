<?php
// includes/notifications.php
// Premium Robot Notification System using SweetAlert2
?>

<style>
/* ========== ROBOT NOTIFICATION STYLES ========== */

/* Robot Container */
.robot-notification {
    position: fixed;
    bottom: 100px; /* ขยับขึ้นให้อยู่เหนือปุ่ม AI Chat */
    right: 20px;
    z-index: 9999;
    display: flex;
    align-items: flex-end;
    gap: 0;
    animation: robotSlideIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    pointer-events: none;
}

@keyframes robotSlideIn {
    0% { transform: translateX(150%); opacity: 0; }
    60% { transform: translateX(-10px); }
    100% { transform: translateX(0); opacity: 1; }
}

@keyframes robotSlideOut {
    0% { transform: translateX(0); opacity: 1; }
    100% { transform: translateX(150%); opacity: 0; }
}

/* Robot Character */
.robot-character {
    width: 100px;
    height: 120px;
    position: relative;
    animation: robotBounce 1s ease-in-out infinite;
}

@keyframes robotBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

/* Robot SVG Styling */
.robot-character svg {
    width: 100%;
    height: 100%;
    filter: drop-shadow(0 8px 15px rgba(0,0,0,0.2));
}

/* Sign/Banner that robot holds */
.robot-sign {
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 16px;
    padding: 16px 24px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.05);
    max-width: 280px;
    position: relative;
    margin-bottom: 30px;
    animation: signPop 0.4s 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
    pointer-events: auto;
}

@keyframes signPop {
    0% { transform: scale(0); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.robot-sign::after {
    content: '';
    position: absolute;
    right: -10px;
    bottom: 20px;
    border: 10px solid transparent;
    border-left-color: #ffffff;
    filter: drop-shadow(2px 0 2px rgba(0,0,0,0.05));
}

.robot-sign.success {
    border-left: 4px solid #10b981;
    background: linear-gradient(145deg, #ecfdf5 0%, #d1fae5 100%);
}

.robot-sign.error {
    border-left: 4px solid #ef4444;
    background: linear-gradient(145deg, #fef2f2 0%, #fee2e2 100%);
}

.robot-sign.warning {
    border-left: 4px solid #f59e0b;
    background: linear-gradient(145deg, #fffbeb 0%, #fef3c7 100%);
}

.robot-sign-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    font-size: 18px;
}

.robot-sign.success .robot-sign-icon {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
}

.robot-sign.error .robot-sign-icon {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
}

.robot-sign-title {
    font-family: 'Prompt', 'Sarabun', sans-serif;
    font-weight: 700;
    font-size: 15px;
    color: #1e293b;
    margin-bottom: 2px;
}

.robot-sign-message {
    font-family: 'Sarabun', sans-serif;
    font-size: 13px;
    color: #64748b;
}

.robot-sign-close {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: rgba(0,0,0,0.05);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: #94a3b8;
    transition: all 0.2s;
}

.robot-sign-close:hover {
    background: rgba(0,0,0,0.1);
    color: #475569;
}

/* Progress bar */
.robot-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: linear-gradient(90deg, #10b981, #34d399);
    border-radius: 0 0 0 16px;
    animation: robotProgress 4s linear forwards;
}

.robot-sign.error .robot-progress {
    background: linear-gradient(90deg, #ef4444, #f87171);
}

@keyframes robotProgress {
    0% { width: 100%; }
    100% { width: 0%; }
}

/* ========== PREMIUM CONFIRM DIALOG ========== */
.swal2-popup.premium-confirm {
    border-radius: 24px !important;
    padding: 2rem !important;
    background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%) !important;
    box-shadow: 0 25px 80px rgba(0,0,0,0.25) !important;
    overflow: visible !important;
}

.swal2-popup.premium-confirm .swal2-icon {
    margin: 0 auto 1rem !important;
    border-width: 3px !important;
}

.swal2-popup.premium-confirm .swal2-title {
    font-family: 'Prompt', 'Sarabun', sans-serif !important;
    font-weight: 700 !important;
    color: #1e293b !important;
    font-size: 1.4rem !important;
}

.swal2-popup.premium-confirm .swal2-html-container {
    font-family: 'Sarabun', sans-serif !important;
    color: #64748b !important;
}

.swal2-popup.premium-confirm .swal2-actions {
    gap: 12px !important;
}

/* Hide unwanted elements in SweetAlert */
.swal2-popup .swal2-input,
.swal2-popup .swal2-select,
.swal2-popup.swal2-toast .ps,
.swal2-popup.swal2-toast select {
    display: none !important;
}

.premium-btn-confirm {
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%) !important;
    color: #ffffff !important;
    border-radius: 50px !important;
    font-weight: 700 !important;
    padding: 14px 32px !important;
    font-size: 1rem !important;
    box-shadow: 0 4px 15px rgba(220, 38, 38, 0.35) !important;
    transition: all 0.2s ease !important;
    border: none !important;
}

.premium-btn-confirm:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(220, 38, 38, 0.45) !important;
}

.premium-btn-cancel {
    background: #f1f5f9 !important;
    color: #475569 !important;
    border-radius: 50px !important;
    font-weight: 700 !important;
    padding: 14px 32px !important;
    font-size: 1rem !important;
    border: 2px solid #e2e8f0 !important;
    transition: all 0.2s ease !important;
}

.premium-btn-cancel:hover {
    background: #e2e8f0 !important;
    border-color: #cbd5e1 !important;
}
</style>

<script>
// ========== ROBOT NOTIFICATION SYSTEM ==========

// Robot SVG (cute robot character)
const robotSVG = `
<svg viewBox="0 0 100 120" fill="none" xmlns="http://www.w3.org/2000/svg">
  <!-- Antenna -->
  <circle cx="50" cy="8" r="6" fill="#f59e0b">
    <animate attributeName="r" values="6;8;6" dur="1s" repeatCount="indefinite"/>
  </circle>
  <rect x="48" y="12" width="4" height="12" rx="2" fill="#64748b"/>
  
  <!-- Head -->
  <rect x="20" y="24" width="60" height="45" rx="12" fill="url(#robotHead)"/>
  <rect x="25" y="29" width="50" height="35" rx="8" fill="#1e293b"/>
  
  <!-- Eyes -->
  <circle cx="38" cy="46" r="8" fill="#10b981">
    <animate attributeName="r" values="8;6;8" dur="2s" repeatCount="indefinite"/>
  </circle>
  <circle cx="62" cy="46" r="8" fill="#10b981">
    <animate attributeName="r" values="8;6;8" dur="2s" repeatCount="indefinite"/>
  </circle>
  <circle cx="38" cy="46" r="3" fill="#fff"/>
  <circle cx="62" cy="46" r="3" fill="#fff"/>
  
  <!-- Mouth -->
  <rect x="40" y="55" width="20" height="4" rx="2" fill="#10b981"/>
  
  <!-- Body -->
  <rect x="25" y="72" width="50" height="35" rx="8" fill="url(#robotBody)"/>
  <rect x="35" y="80" width="30" height="8" rx="4" fill="#1e293b"/>
  <circle cx="50" cy="98" r="5" fill="#f59e0b"/>
  
  <!-- Arms -->
  <rect x="8" y="75" width="15" height="8" rx="4" fill="#94a3b8" class="robot-arm-left">
    <animateTransform attributeName="transform" type="rotate" values="0 23 79;-15 23 79;0 23 79" dur="0.6s" repeatCount="indefinite"/>
  </rect>
  <rect x="77" y="75" width="15" height="8" rx="4" fill="#94a3b8" class="robot-arm-right">
    <animateTransform attributeName="transform" type="rotate" values="0 77 79;15 77 79;0 77 79" dur="0.6s" repeatCount="indefinite"/>
  </rect>
  
  <!-- Gradients -->
  <defs>
    <linearGradient id="robotHead" x1="20" y1="24" x2="80" y2="69" gradientUnits="userSpaceOnUse">
      <stop stop-color="#94a3b8"/>
      <stop offset="1" stop-color="#64748b"/>
    </linearGradient>
    <linearGradient id="robotBody" x1="25" y1="72" x2="75" y2="107" gradientUnits="userSpaceOnUse">
      <stop stop-color="#dc2626"/>
      <stop offset="1" stop-color="#991b1b"/>
    </linearGradient>
  </defs>
</svg>`;

// Show Robot Notification
function showRobotNotification(type, title, message) {
    // Remove existing notification
    const existing = document.querySelector('.robot-notification');
    if (existing) existing.remove();
    
    const iconMap = {
        success: 'fa-check',
        error: 'fa-xmark',
        warning: 'fa-exclamation',
        info: 'fa-info'
    };
    
    const container = document.createElement('div');
    container.className = 'robot-notification';
    container.innerHTML = `
        <div class="robot-sign ${type}">
            <button class="robot-sign-close" onclick="this.closest('.robot-notification').remove()">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="robot-sign-icon">
                <i class="fa-solid ${iconMap[type] || 'fa-info'}"></i>
            </div>
            <div class="robot-sign-title">${title}</div>
            <div class="robot-sign-message">${message}</div>
            <div class="robot-progress"></div>
        </div>
        <div class="robot-character">${robotSVG}</div>
    `;
    
    document.body.appendChild(container);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        container.style.animation = 'robotSlideOut 0.5s forwards';
        setTimeout(() => container.remove(), 500);
    }, 4000);
}

// Premium Success with Robot
function showSuccessToast(message) {
    showRobotNotification('success', 'สำเร็จ! 🎉', message);
}

// Premium Error with Robot
function showErrorToast(message) {
    showRobotNotification('error', 'เกิดข้อผิดพลาด', message);
}

// Premium Info with Robot
function showInfoToast(message) {
    showRobotNotification('info', 'แจ้งเตือน', message);
}

// Premium Delete Confirmation with Robot
function confirmDelete(url, itemName = 'รายการนี้') {
    Swal.fire({
        title: '🤖 ยืนยันการลบ?',
        html: `
            <div style="text-align: center; margin: 10px 0;">
                <div style="width: 80px; height: 80px; margin: 0 auto 15px;">
                    ${robotSVG.replace('#10b981', '#f59e0b').replace('10b981', 'f59e0b')}
                </div>
                <p style="color: #64748b; font-size: 15px;">คุณต้องการลบ <strong style="color: #dc2626;">${itemName}</strong> หรือไม่?</p>
                <p style="color: #94a3b8; font-size: 12px; margin-top: 8px;">⚠️ การดำเนินการนี้ไม่สามารถย้อนกลับได้</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-trash-can" style="margin-right: 8px;"></i>ลบข้อมูล',
        cancelButtonText: '<i class="fa-solid fa-xmark" style="margin-right: 8px;"></i>ยกเลิก',
        customClass: {
            popup: 'premium-confirm',
            confirmButton: 'premium-btn-confirm',
            cancelButton: 'premium-btn-cancel'
        },
        buttonsStyling: false,
        reverseButtons: true,
        focusCancel: true,
        showClass: {
            popup: 'animate__animated animate__zoomIn animate__faster'
        },
        hideClass: {
            popup: 'animate__animated animate__zoomOut animate__faster'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                html: `
                    <div style="text-align: center;">
                        <div style="width: 60px; height: 60px; margin: 0 auto 15px;">
                            ${robotSVG}
                        </div>
                        <p style="color: #64748b; font-weight: 600;">กำลังลบข้อมูล...</p>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                customClass: { popup: 'premium-confirm' },
                didOpen: () => {
                    window.location.href = url;
                }
            });
        }
    });
    return false;
}
</script>

<?php
// Auto-trigger notifications from Session
if (isset($_SESSION['success'])) {
    $msg = addslashes($_SESSION['success']);
    echo "<script>document.addEventListener('DOMContentLoaded', () => showSuccessToast('$msg'));</script>";
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $msg = addslashes($_SESSION['error']);
    echo "<script>document.addEventListener('DOMContentLoaded', () => showErrorToast('$msg'));</script>";
    unset($_SESSION['error']);
}

// Auto-trigger notifications from URL params  
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    $msg = isset($_GET['msg']) ? addslashes($_GET['msg']) : '';
    
    if ($status === 'deleted') {
        echo "<script>document.addEventListener('DOMContentLoaded', () => showSuccessToast('ลบข้อมูลเรียบร้อยแล้ว'));</script>";
    } elseif ($status === 'saved') {
        echo "<script>document.addEventListener('DOMContentLoaded', () => showSuccessToast('บันทึกข้อมูลเรียบร้อยแล้ว'));</script>";
    } elseif ($status === 'error' && $msg) {
        echo "<script>document.addEventListener('DOMContentLoaded', () => showErrorToast('$msg'));</script>";
    } elseif ($status === 'error') {
        echo "<script>document.addEventListener('DOMContentLoaded', () => showErrorToast('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง'));</script>";
    }
}
?>
