<footer class="text-center mt-5 mb-4 text-secondary" style="font-size: 0.9rem; opacity: 0.8;">
    <strong>CVC Scheduling System</strong> &nbsp;|&nbsp; © 2026 All Rights Reserved.
</footer>

<style>
    /* ================= ANIMATIONS ================= */
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes ping {
        75%, 100% { transform: scale(2); opacity: 0; }
    }
    @keyframes bounceIcon {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    @keyframes typing {
        0%, 100% { transform: translateY(0); opacity: 0.6; }
        50% { transform: translateY(-4px); opacity: 1; }
    }

    /* ================= CHAT BUTTON ================= */
    .ai-chat-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 65px;
        height: 65px;
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); 
        border-radius: 50%;
        box-shadow: 0 8px 20px rgba(220, 38, 38, 0.4); 
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 1050;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 2px solid rgba(255,255,255,0.2);
    }
    .ai-chat-btn:hover { 
        transform: scale(1.1); 
        box-shadow: 0 15px 30px rgba(220, 38, 38, 0.6);
    }
    .ai-chat-btn:hover i {
        animation: bounceIcon 0.5s infinite;
    }
    .ai-chat-btn i { font-size: 30px; color: white; transition: 0.3s; }

    /* Notification Dot */
    .notification-dot {
        position: absolute;
        top: 0;
        right: 0;
        width: 15px;
        height: 15px;
        background: #ef4444;
        border: 2px solid white;
        border-radius: 50%;
    }
    .notification-ping {
        position: absolute;
        top: 0;
        right: 0;
        width: 15px;
        height: 15px;
        background: #ef4444;
        border-radius: 50%;
        animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
        opacity: 0.7;
    }

    /* ================= CHAT WINDOW ================= */
    .ai-chat-window {
        position: fixed;
        bottom: 110px;
        right: 30px;
        width: 350px;
        height: 500px;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        display: none;
        flex-direction: column;
        z-index: 1051;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.05);
        animation: slideUp 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        font-family: 'Prompt', sans-serif;
    }

    .chat-header {
        background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%); 
        padding: 15px 20px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .chat-body {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: #fef2f2;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .chat-footer {
        padding: 15px;
        background: white;
        border-top: 1px solid #eee;
    }
    
    .chat-input-group {
        background: #f3f4f6;
        border-radius: 25px;
        padding: 5px 10px 5px 20px;
        display: flex;
        align-items: center;
        border: 1px solid transparent;
        transition: all 0.2s;
    }
    .chat-input-group:focus-within {
        background: white;
        border-color: #ef4444; 
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }
    .chat-input {
        border: none;
        background: transparent;
        width: 100%;
        outline: none;
        font-size: 0.9rem;
        color: #334155;
    }
    .btn-send-chat {
        background: #dc2626;
        color: white;
        border: none;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.2s;
    }
    .btn-send-chat:hover { background: #991b1b; transform: scale(1.1); }

    .msg-item { display: flex; gap: 10px; margin-bottom: 10px; }
    .msg {
        max-width: 85%;
        padding: 10px 15px;
        border-radius: 15px;
        font-size: 0.9rem;
        line-height: 1.5;
        word-wrap: break-word;
    }
    .msg-ai {
        background: white;
        color: #334155;
        border-bottom-left-radius: 2px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }
    .msg-user {
        background: #dc2626;
        color: white;
        margin-left: auto;
        border-bottom-right-radius: 2px;
        box-shadow: 0 4px 10px rgba(220, 38, 38, 0.2);
    }
    
    .typing-indicator span {
        display: inline-block;
        width: 5px;
        height: 5px;
        background: #ef4444;
        border-radius: 50%;
        animation: typing 1s infinite;
        margin: 0 2px;
    }
    .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
</style>

<div class="ai-chat-btn" onclick="toggleChat()">
    <div class="notification-ping"></div>
    <div class="notification-dot"></div>
    <i class="fas fa-robot"></i>
</div>

<div class="ai-chat-window" id="chatWindow">
    <div class="chat-header">
        <div class="d-flex align-items-center gap-2">
            <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-robot" style="font-size: 18px;"></i>
            </div>
            <div>
                <div class="fw-bold" style="font-size: 0.9rem; line-height: 1.2;">CVC Assistant</div>
                <div style="font-size: 0.65rem; opacity: 0.9;"><i class="fas fa-circle text-success me-1" style="font-size: 5px;"></i>Gemini AI Online</div>
            </div>
        </div>
        <button onclick="toggleChat()" class="btn text-white p-0"><i class="fas fa-chevron-down"></i></button>
    </div>

    <div class="chat-body" id="chatBody">
        <div class="msg-item">
            <div style="width: 28px; height: 28px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-robot" style="font-size: 14px; color: #dc2626;"></i>
            </div>
            <div class="msg msg-ai">
                สวัสดีครับ! ผมคือ AI ผู้ช่วยอัจฉริยะ Gemini 🤖<br>มีอะไรให้ช่วยตรวจสอบไหมครับ?
            </div>
        </div>
    </div>

    <div class="chat-footer">
        <div class="chat-input-group">
            <input type="text" id="userPrompt" class="chat-input" placeholder="พิมพ์ข้อความ..." onkeypress="handleEnter(event)">
            <button class="btn-send-chat" onclick="sendToAI()">
                <i class="fas fa-paper-plane" style="font-size: 12px;"></i>
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ตั้งชื่อ Key สำหรับเก็บประวัติ (แยกตาม User ID หรือใช้ Key กลาง)
    // ใช้ PHP แทรก ID เพื่อให้แต่ละ User มีประวัติของตัวเอง (ถ้าไม่มี Session ให้ใช้ guest)
    const STORAGE_KEY = 'cvc_chat_history_<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest'; ?>';

    $(document).ready(function() {
        // 1. โหลดประวัติแชทเมื่อเปิดหน้าเว็บ
        loadChatHistory();

        // 2. ดักจับการกดปุ่ม Logout เพื่อล้างประวัติแชท
        $('a[href*="logout.php"]').on('click', function() {
            localStorage.removeItem(STORAGE_KEY);
        });
    });

    function toggleChat() {
        const chat = document.getElementById('chatWindow');
        if (chat.style.display === 'none' || chat.style.display === '') {
            chat.style.display = 'flex';
            setTimeout(() => document.getElementById('userPrompt').focus(), 100);
            
            // เลื่อนลงล่างสุดเสมอเมื่อเปิดแชท
            const chatBody = document.getElementById('chatBody');
            chatBody.scrollTop = chatBody.scrollHeight;
        } else {
            chat.style.display = 'none';
        }
    }

    function handleEnter(e) {
        if (e.key === 'Enter') sendToAI();
    }

    function sendToAI() {
        const input = document.getElementById('userPrompt');
        const message = input.value.trim();
        if (!message) return;

        input.value = '';
        appendMessage('user', message, true); // true = บันทึกลง Storage
        showTyping();

        $.ajax({
            url: '../ai_action.php', 
            type: 'POST',
            data: { prompt: message },
            dataType: 'json',
            success: function(response) {
                removeTyping();
                if (response.status === 'success') {
                    appendMessage('ai', formatText(response.answer), true); // บันทึกคำตอบ AI ลง Storage
                } else {
                    appendMessage('ai', '⚠️ ' + response.message, false); // Error ไม่ต้องบันทึกก็ได้
                }
            },
            error: function(xhr, status, error) {
                removeTyping();
                appendMessage('ai', '❌ เชื่อมต่อไม่ได้: ' + error, false);
            }
        });
    }

    // เพิ่มพารามิเตอร์ save เพื่อเลือกว่าจะบันทึกหรือไม่
    function appendMessage(sender, text, save) {
        const chatBody = document.getElementById('chatBody');
        const div = document.createElement('div');
        div.className = 'msg-item';
        
        if (sender === 'ai') {
            div.innerHTML = `
                <div style="width: 28px; height: 28px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-robot" style="font-size: 14px; color: #dc2626;"></i>
                </div>
                <div class="msg msg-ai">${text}</div>
            `;
        } else {
            div.innerHTML = `<div class="msg msg-user">${text}</div>`;
        }
        
        chatBody.appendChild(div);
        chatBody.scrollTop = chatBody.scrollHeight;

        // --- บันทึกลง LocalStorage ---
        if (save) {
            let history = localStorage.getItem(STORAGE_KEY);
            let messages = history ? JSON.parse(history) : [];
            messages.push({ sender: sender, text: text });
            localStorage.setItem(STORAGE_KEY, JSON.stringify(messages));
        }
    }

    function loadChatHistory() {
        const history = localStorage.getItem(STORAGE_KEY);
        if (history) {
            const messages = JSON.parse(history);
            messages.forEach(msg => {
                // เรียก appendMessage แบบ save=false (เพราะโหลดจากความจำ ไม่ต้องบันทึกซ้ำ)
                appendMessage(msg.sender, msg.text, false);
            });
        }
    }

    function showTyping() {
        const chatBody = document.getElementById('chatBody');
        const div = document.createElement('div');
        div.id = 'typingIndicator';
        div.className = 'msg-item';
        div.innerHTML = `
            <div style="width: 28px; height: 28px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-robot" style="font-size: 14px; color: #dc2626;"></i>
            </div>
            <div class="msg msg-ai typing-indicator">
                <span></span><span></span><span></span>
            </div>
        `;
        chatBody.appendChild(div);
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function removeTyping() {
        const typing = document.getElementById('typingIndicator');
        if (typing) typing.remove();
    }

    function formatText(text) {
        let formatted = text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
        formatted = formatted.replace(/\n/g, '<br>');
        return formatted;
    }
</script>

<?php 
// ดักจับกรณี Session หมดอายุแล้ว Redirect มาหน้า Login ให้เคลียร์แชทด้วย (เผื่อกรณี Logout อัตโนมัติ)
if (!isset($_SESSION['user_id'])) {
    echo "<script>localStorage.removeItem('cvc_chat_history_guest');</script>"; 
}
?>