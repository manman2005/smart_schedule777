<footer class="text-center mt-5 mb-4 text-secondary" style="font-size: 0.9rem; opacity: 0.8;">
    <strong>CVC Scheduling System</strong> &nbsp;|&nbsp; © 2026 All Rights Reserved.
</footer>

<style>
    /* ปุ่มเปิดแชท */
    .ai-chat-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 65px;
        height: 65px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border-radius: 50%;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 1050; /* อยู่บนสุด */
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 2px solid rgba(255,255,255,0.2);
    }
    .ai-chat-btn:hover { transform: scale(1.1) rotate(-5deg); }
    .ai-chat-btn i { font-size: 28px; color: white; }

    /* หน้าต่างแชท */
    .ai-chat-window {
        position: fixed;
        bottom: 110px;
        right: 30px;
        width: 350px; /* ขนาดกำลังดี */
        height: 500px;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        display: none;
        flex-direction: column;
        z-index: 1051;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.05);
        animation: slideUp 0.3s ease-out;
        font-family: 'Prompt', sans-serif; /* บังคับฟอนต์ */
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .chat-header {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
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
        background: #f8fafc;
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
        background: #f1f5f9;
        border-radius: 25px;
        padding: 5px 10px 5px 20px;
        display: flex;
        align-items: center;
        border: 1px solid transparent;
        transition: all 0.2s;
    }
    .chat-input-group:focus-within {
        background: white;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
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
        background: #2563eb;
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
    .btn-send-chat:hover { background: #1d4ed8; transform: scale(1.05); }

    /* ข้อความแชท */
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
        background: #2563eb;
        color: white;
        margin-left: auto; /* ชิดขวา */
        border-bottom-right-radius: 2px;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
    }
    
    /* Animation จุดๆๆ เวลาพิมพ์ */
    .typing-indicator span {
        display: inline-block;
        width: 5px;
        height: 5px;
        background: #94a3b8;
        border-radius: 50%;
        animation: typing 1s infinite;
        margin: 0 2px;
    }
    .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typing {
        0%, 100% { transform: translateY(0); opacity: 0.6; }
        50% { transform: translateY(-4px); opacity: 1; }
    }
</style>

<div class="ai-chat-btn" onclick="toggleChat()">
    <i class="fas fa-wand-magic-sparkles"></i>
</div>

<div class="ai-chat-window" id="chatWindow">
    <div class="chat-header">
        <div class="d-flex align-items-center gap-2">
            <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-wand-magic-sparkles" style="font-size: 16px;"></i>
            </div>
            <div>
                <div class="fw-bold" style="font-size: 0.9rem; line-height: 1.2;">AI Assistant</div>
                <div style="font-size: 0.65rem; opacity: 0.9;"><i class="fas fa-circle text-success me-1" style="font-size: 5px;"></i>Online</div>
            </div>
        </div>
        <button onclick="toggleChat()" class="btn text-white p-0"><i class="fas fa-chevron-down"></i></button>
    </div>

    <div class="chat-body" id="chatBody">
        <div class="msg-item">
            <div style="width: 28px; height: 28px; background: #e0e7ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-robot text-primary" style="font-size: 14px;"></i>
            </div>
            <div class="msg msg-ai">
                สวัสดีครับ! ผมคือ AI ผู้ช่วยอัจฉริยะ Gemini 🤖<br>มีอะไรให้ช่วยไหมครับ?
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
    // 1. เปิด/ปิด แชท
    function toggleChat() {
        const chat = document.getElementById('chatWindow');
        if (chat.style.display === 'none' || chat.style.display === '') {
            chat.style.display = 'flex';
            setTimeout(() => document.getElementById('userPrompt').focus(), 100);
        } else {
            chat.style.display = 'none';
        }
    }

    // 2. กด Enter เพื่อส่ง
    function handleEnter(e) {
        if (e.key === 'Enter') sendToAI();
    }

    // 3. ฟังก์ชันส่งข้อความ
    function sendToAI() {
        const input = document.getElementById('userPrompt');
        const message = input.value.trim();
        if (!message) return;

        input.value = ''; // เคลียร์ช่องพิมพ์
        appendMessage('user', message); // โชว์ข้อความเรา
        showTyping(); // โชว์จุดๆๆ

        // ส่งข้อมูลไปหา AI
        $.ajax({
            url: '../ai_action.php', // ⚠️ เช็ก path นี้ให้ถูกกับโครงสร้างโฟลเดอร์คุณ
            type: 'POST',
            data: { prompt: message },
            dataType: 'json',
            success: function(response) {
                removeTyping();
                if (response.status === 'success') {
                    appendMessage('ai', formatText(response.answer));
                } else {
                    appendMessage('ai', '⚠️ ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                removeTyping();
                appendMessage('ai', '❌ เชื่อมต่อไม่ได้: ' + error);
            }
        });
    }

    // 4. แสดงข้อความในแชท
    function appendMessage(sender, text) {
        const chatBody = document.getElementById('chatBody');
        const div = document.createElement('div');
        div.className = 'msg-item';
        
        if (sender === 'ai') {
            div.innerHTML = `
                <div style="width: 28px; height: 28px; background: #e0e7ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-robot text-primary" style="font-size: 14px;"></i>
                </div>
                <div class="msg msg-ai">${text}</div>
            `;
        } else {
            div.innerHTML = `<div class="msg msg-user">${text}</div>`;
        }
        
        chatBody.appendChild(div);
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    // 5. Typing Indicator
    function showTyping() {
        const chatBody = document.getElementById('chatBody');
        const div = document.createElement('div');
        div.id = 'typingIndicator';
        div.className = 'msg-item';
        div.innerHTML = `
            <div style="width: 28px; height: 28px; background: #e0e7ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-robot text-primary" style="font-size: 14px;"></i>
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

    // 6. จัดรูปแบบข้อความ
    function formatText(text) {
        let formatted = text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
        formatted = formatted.replace(/\n/g, '<br>');
        return formatted;
    }
</script>