</div> <footer class="bg-slate-900 text-slate-400 py-6 mt-12 border-t border-slate-800 font-sans relative z-10">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center text-white font-bold shadow-lg">
                    <i class="fa-solid fa-graduation-cap text-xs"></i>
                </div>
                <div class="text-sm">
                    <span class="text-slate-200 font-bold">CVC Scheduling System</span>
                    <span class="text-slate-600 mx-2">|</span>
                    <span class="text-xs">&copy; <?php echo date('Y'); ?> All Rights Reserved.</span>
                </div>
            </div>
        </div>
    </footer>

    <div id="ai-widget-container" style="position: fixed; bottom: 30px; right: 30px; z-index: 9999; font-family: 'Sarabun', sans-serif;">
        
        <button onclick="toggleChat()" id="ai-toggle-btn" 
                class="group flex items-center justify-center transition-all duration-300 hover:scale-110"
                style="width: 65px; height: 65px; border-radius: 50%; background: linear-gradient(135deg, #2563eb, #4f46e5); color: white; border: none; box-shadow: 0 10px 25px rgba(37, 99, 235, 0.5); cursor: pointer; position: relative; z-index: 10001;">
            <i class="fa-solid fa-robot text-2xl transition-all duration-300 group-hover:rotate-12" id="ai-icon-closed"></i>
            <i class="fa-solid fa-xmark text-2xl absolute opacity-0 transition-all duration-300" id="ai-icon-open" style="transform: scale(0.5);"></i>
            <span class="absolute top-1 right-1 w-3.5 h-3.5 bg-red-500 border-2 border-white rounded-full animate-bounce"></span>
        </button>

        <div id="ai-chat-box" class="hidden transition-all duration-300 opacity-0 translate-y-4" 
             style="position: absolute; bottom: 85px; right: 0; width: 380px; height: 550px; background: white; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.25); overflow: hidden; border: 1px solid #e2e8f0; z-index: 10000;">
            
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 70px; background: linear-gradient(to right, #2563eb, #4338ca); padding: 16px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 2;">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur rounded-full flex items-center justify-center border border-white/30">
                        <i class="fa-solid fa-wand-magic-sparkles text-yellow-300"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base leading-tight m-0">AI Assistant</h3>
                        <p class="text-xs text-blue-100 flex items-center gap-1 m-0">
                            <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span> Online
                        </p>
                    </div>
                </div>
                <button onclick="toggleChat()" class="text-white/70 hover:text-white transition bg-transparent border-none cursor-pointer">
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
            </div>

            <div id="chat-messages" 
                 class="bg-slate-50"
                 style="position: absolute; top: 70px; bottom: 85px; left: 0; right: 0; overflow-y: auto; padding: 16px; scroll-behavior: smooth; z-index: 1;">
                 
                <div class="flex gap-3 items-start animate-fade-in-up">
                    <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                        <i class="fa-solid fa-robot text-indigo-600 text-sm"></i>
                    </div>
                    <div class="bg-white p-3 rounded-2xl rounded-tl-none text-slate-600 text-sm shadow-sm border border-slate-100 leading-relaxed max-w-[85%]">
                        สวัสดีครับ! ผมคือ AI ผู้ช่วยอัจฉริยะ Gemini 🤖<br>มีอะไรให้ช่วยไหมครับ?
                    </div>
                </div>
            </div>

            <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 85px; background: white; border-top: 1px solid #f1f5f9; padding: 16px; z-index: 2;">
                <div class="flex gap-2 items-end bg-slate-100 p-2 rounded-2xl border border-slate-200 focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition-all">
                    <textarea id="chat-input" rows="1" placeholder="พิมพ์ข้อความ..." 
                              class="w-full bg-transparent border-none text-sm focus:ring-0 text-slate-700 resize-none max-h-24 pt-2"
                              style="outline: none;"
                              oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"
                              onkeypress="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); sendMessage(); }"></textarea>
                    
                    <button onclick="sendMessage()" id="send-btn" 
                            class="w-8 h-8 bg-blue-600 text-white rounded-xl flex items-center justify-center shadow-lg hover:bg-blue-700 transition active:scale-95 mb-0.5 border-none cursor-pointer">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                    </button>
                </div>
                <div class="text-[10px] text-slate-400 text-center mt-2">Powered by Google Gemini AI ✨</div>
            </div>
        </div>
    </div>

    <?php
        $aiActionUrl = 'ai_action.php'; 
        if (file_exists('../ai_action.php')) { $aiActionUrl = '../ai_action.php'; }
        else if (file_exists('../../ai_action.php')) { $aiActionUrl = '../../ai_action.php'; }
    ?>

    <script>
        function toggleChat() {
            const box = document.getElementById('ai-chat-box');
            const iconClosed = document.getElementById('ai-icon-closed');
            const iconOpen = document.getElementById('ai-icon-open');
            
            if (box.classList.contains('hidden')) {
                // เปิด
                box.classList.remove('hidden');
                // รอ Animation
                setTimeout(() => { box.classList.remove('opacity-0', 'translate-y-4'); }, 10);
                
                iconClosed.classList.add('opacity-0', 'rotate-90');
                iconOpen.classList.remove('opacity-0');
                iconOpen.style.transform = 'scale(1)';
                
                // เลื่อนลงล่างสุด และ Focus
                setTimeout(() => {
                    const msgBox = document.getElementById('chat-messages');
                    msgBox.scrollTop = msgBox.scrollHeight;
                    document.getElementById('chat-input').focus();
                }, 300);
            } else {
                // ปิด
                box.classList.add('opacity-0', 'translate-y-4');
                setTimeout(() => { box.classList.add('hidden'); }, 300);
                
                iconClosed.classList.remove('opacity-0', 'rotate-90');
                iconOpen.classList.add('opacity-0');
                iconOpen.style.transform = 'scale(0.5)';
            }
        }

        async function sendMessage() {
            const input = document.getElementById('chat-input');
            const text = input.value.trim();
            if (!text) return;

            addMessage(text, 'user');
            input.value = ''; input.disabled = true;
            input.style.height = 'auto'; // Reset height
            const loadingId = addLoading();

            try {
                const targetUrl = '<?php echo $aiActionUrl; ?>';
                const formData = new URLSearchParams();
                formData.append('prompt', text);

                const res = await fetch(targetUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData
                });

                if (!res.ok) throw new Error(`Server Error: ${res.status}`);

                const rawText = await res.text();
                try {
                    const data = JSON.parse(rawText);
                    removeMessage(loadingId);
                    if (data.status === 'success') {
                        addMessage(data.answer, 'ai');
                    } else {
                        addMessage('ระบบแจ้งว่า: ' + data.message, 'error');
                    }
                } catch (e) {
                    removeMessage(loadingId);
                    let cleanText = rawText.replace(/<[^>]*>?/gm, '').substring(0, 100);
                    addMessage('Format Error: ' + cleanText, 'error');
                }
            } catch (e) {
                removeMessage(loadingId);
                addMessage('การเชื่อมต่อล้มเหลว: ' + e.message, 'error');
            }
            input.disabled = false; input.focus();
        }

        function addMessage(text, type) {
            const messages = document.getElementById('chat-messages');
            const div = document.createElement('div');
            
            if (type === 'user') {
                div.className = "flex justify-end animate-fade-in-up mb-4";
                div.innerHTML = `<div class="bg-blue-600 text-white px-4 py-2 rounded-2xl rounded-tr-none text-sm shadow-md max-w-[85%]">${text.replace(/\n/g, '<br>')}</div>`;
            } else if (type === 'ai') {
                div.className = "flex gap-3 items-start animate-fade-in-up mb-4";
                let formatted = text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>').replace(/\n/g, '<br>');
                div.innerHTML = `<div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1"><i class="fa-solid fa-robot text-indigo-600 text-sm"></i></div><div class="bg-white p-3 rounded-2xl rounded-tl-none text-slate-700 text-sm shadow-sm border border-slate-100 max-w-[85%]">${formatted}</div>`;
            } else {
                div.className = "text-center text-xs text-red-500 bg-red-50 p-2 rounded border border-red-100 my-2";
                div.innerHTML = `<i class="fa-solid fa-triangle-exclamation mr-1"></i> ${text}`;
            }
            messages.appendChild(div);
            // สำคัญ: สั่งให้ Scrollbar เลื่อนลงล่างสุดทันที
            messages.scrollTo({ top: messages.scrollHeight, behavior: 'smooth' });
        }

        function addLoading() {
            const messages = document.getElementById('chat-messages');
            const id = 'loading-' + Date.now();
            const div = document.createElement('div');
            div.id = id;
            div.className = "flex gap-3 items-start animate-fade-in-up mb-4";
            div.innerHTML = `<div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-1"><i class="fa-solid fa-robot text-indigo-600 text-sm"></i></div><div class="bg-white p-3 rounded-2xl rounded-tl-none text-slate-500 text-sm shadow-sm border border-slate-100 flex items-center gap-2"><span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce"></span><span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span><span class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span></div>`;
            messages.appendChild(div);
            messages.scrollTo({ top: messages.scrollHeight, behavior: 'smooth' });
            return id;
        }
        function removeMessage(id) { const el = document.getElementById(id); if(el) el.remove(); }
    </script>

    <style>
        /* Scrollbar Style */
        #chat-messages::-webkit-scrollbar {
            width: 10px; /* ความกว้าง */
        }
        #chat-messages::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        #chat-messages::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 5px;
            border: 2px solid #f1f5f9;
        }
        #chat-messages::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8;
        }

        @keyframes fade-in-up { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in-up { animation: fade-in-up 0.3s ease-out forwards; }
    </style>
</body>
</html>