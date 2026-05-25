<?php
include 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="theme_engine.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>SK AI | Business Intelligence</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --gemini-grad: linear-gradient(135deg, #4285f4, #9b72cb, #d96570);
            --deepseek-grad: linear-gradient(135deg, #4f46e5, #7c3aed, #a855f7);
            --bg: #0b0f1a;
            --surface: #1a1e2e;
            --surface-hover: #222840;
            --primary: #8b5cf6;
            --text: #ffffff;
            --text-muted: #94a3b8;
            --border: rgba(255,255,255,0.08);
            --shadow: 0 8px 32px rgba(0,0,0,0.3);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        body {
            margin: 0; font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg); color: var(--text);
            height: 100vh; display: flex; flex-direction: column;
            padding-top: env(safe-area-inset-top, 0px);
            overflow: hidden;
        }

        header {
            padding: calc(12px + env(safe-area-inset-top, 0px)) 20px 16px;
            background: rgba(11, 15, 26, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            z-index: 10;
            flex-shrink: 0;
        }

        .header-left {
            display: flex; align-items: center; gap: 12px;
        }

        .header-actions {
            display: flex; align-items: center; gap: 8px;
        }

        .chat-container {
            flex: 1; overflow-y: auto; padding: 20px 16px;
            display: flex; flex-direction: column; gap: 16px;
            scroll-behavior: smooth;
        }
        .chat-container::-webkit-scrollbar { width: 4px; }
        .chat-container::-webkit-scrollbar-track { background: transparent; }
        .chat-container::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 10px; }

        .msg {
            max-width: 88%; padding: 16px 18px;
            font-size: 14.5px; line-height: 1.7;
            position: relative;
            animation: msgIn 0.35s ease-out;
        }

        @keyframes msgIn {
            from { opacity: 0; transform: translateY(12px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .msg-ai {
            background: var(--surface);
            border-left: 3px solid var(--primary);
            align-self: flex-start;
            border-radius: 2px 16px 16px 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .msg-user {
            align-self: flex-end;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: white;
            border-radius: 16px 16px 2px 16px;
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.25);
        }
        .msg-user::before {
            content: '👤';
            position: absolute;
            top: -10px; right: 4px;
            font-size: 14px;
            opacity: 0.6;
        }
        .msg-ai::before {
            content: '🤖';
            position: absolute;
            top: -10px; left: 4px;
            font-size: 14px;
            opacity: 0.6;
        }

        .msg-time {
            font-size: 10px; color: rgba(255,255,255,0.35);
            margin-top: 8px; display: flex; align-items: center; gap: 8px;
        }
        .msg-user .msg-time {
            justify-content: flex-end;
        }
        .msg-ai .msg-time {
            justify-content: flex-start;
        }

        .msg-tools {
            display: inline-flex; gap: 6px; opacity: 0;
            transition: opacity 0.2s;
        }
        .msg:hover .msg-tools {
            opacity: 1;
        }
        .msg-tools button {
            background: rgba(255,255,255,0.1);
            border: none; color: var(--text-muted);
            border-radius: 6px; padding: 3px 8px;
            font-size: 11px; cursor: pointer;
            transition: all 0.2s;
        }
        .msg-tools button:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .action-btn {
            display: inline-block;
            margin-top: 8px;
            padding: 7px 16px;
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            color: white;
            border-radius: 20px;
            font-size: 12px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.25s ease;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
            filter: brightness(1.1);
        }

        .input-area {
            padding: 14px 16px 18px;
            background: rgba(26, 30, 46, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-top: 1px solid var(--border);
            display: flex; gap: 10px; align-items: center;
            flex-shrink: 0;
        }

        input {
            flex: 1; background: #0b0f1a; border: 1.5px solid var(--border);
            padding: 16px 22px; border-radius: 28px; color: white; font-size: 15px; outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
        }
        input::placeholder { color: #5a6480; }

        .send-btn {
            width: 50px; height: 50px; border-radius: 50%;
            background: var(--deepseek-grad);
            border: none; color: white; font-size: 18px; cursor: pointer;
            transition: all 0.3s; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .send-btn:hover { transform: scale(1.08); box-shadow: 0 4px 20px rgba(79, 70, 229, 0.4); }
        .send-btn:active { transform: scale(0.95); }

        .typing {
            font-size: 12px; color: var(--text-muted);
            padding: 0 16px 6px;
            display: flex; align-items: center; gap: 8px;
            flex-shrink: 0;
            height: 0; overflow: hidden; opacity: 0;
            transition: all 0.3s;
        }
        .typing.active {
            height: 24px; opacity: 1;
            margin-bottom: 2px;
        }
        .typing i { font-size: 10px; animation: pulse 1.2s infinite; }
        @keyframes pulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        .ai-title {
            background: var(--deepseek-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800; font-family: 'Outfit'; font-size: 22px;
            letter-spacing: -0.5px;
        }
        .ai-title i { -webkit-text-fill-color: initial; margin-right: 6px; }

        /* Export Dialog */
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.7); z-index: 1000;
            justify-content: center; align-items: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: #1a1e2e; border-radius: 20px; padding: 25px;
            width: 90%; max-width: 380px; border: 1px solid rgba(255,255,255,0.1);
        }
        .modal-box h3 { margin: 0 0 15px 0; font-family: 'Outfit'; }
        .modal-box label { font-size: 12px; color: #94a3b8; display: block; margin-top: 12px; margin-bottom: 4px; }
        .modal-box select, .modal-box input {
            width: 100%; background: #0b0f1a; border: 1px solid rgba(255,255,255,0.15);
            padding: 10px 14px; border-radius: 12px; color: white; font-size: 14px; outline: none;
            box-sizing: border-box;
        }
        .modal-box .btn-row { display: flex; gap: 10px; margin-top: 18px; }
        .modal-box .btn-row button {
            flex: 1; padding: 12px; border-radius: 14px; border: none; font-size: 14px;
            font-weight: bold; cursor: pointer; transition: 0.3s;
        }
        .btn-download { background: var(--gemini-grad); color: white; }
        .btn-cancel { background: rgba(255,255,255,0.1); color: white; }
        .modal-box .format-row { display: flex; gap: 10px; margin-top: 10px; }
        .modal-box .format-btn {
            flex: 1; padding: 10px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.2);
            background: transparent; color: white; font-size: 13px; cursor: pointer; text-align: center; transition: 0.3s;
        }
        .modal-box .format-btn.active { background: var(--primary); border-color: var(--primary); }

        /* 🆕 Suggestion Chips */
        .suggestion-area {
            padding: 0 16px 8px;
            display: flex; gap: 8px; flex-wrap: wrap;
            flex-shrink: 0;
        }
        .suggestion-chip {
            padding: 6px 14px;
            border-radius: 16px;
            background: rgba(139, 92, 246, 0.15);
            border: 1px solid rgba(139, 92, 246, 0.3);
            color: var(--text-muted);
            font-size: 11px;
            cursor: pointer;
            transition: all 0.25s;
            white-space: nowrap;
        }
        .suggestion-chip:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-1px);
        }
        .suggestion-chip i {
            margin-right: 4px;
            font-size: 10px;
        }

        /* 🌐 Network Indicator */
        .network-status {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 10px; color: var(--text-muted); padding: 4px 10px;
            border-radius: 12px; background: rgba(255,255,255,0.05);
        }
        .network-status.online { color: var(--success); }
        .network-status.offline { color: var(--danger); }
        .network-status .dot {
            width: 6px; height: 6px; border-radius: 50%;
            display: inline-block;
        }
        .network-status.online .dot { background: var(--success); }
        .network-status.offline .dot { background: var(--danger); }

        /* 🔲 Inline Table Styling */
        .inline-table {
            width: 100%; border-collapse: collapse;
            margin: 8px 0; font-size: 12px;
        }
        .inline-table th {
            background: rgba(139, 92, 246, 0.2);
            padding: 8px 10px; text-align: left;
            font-weight: 600;
            border-bottom: 1px solid var(--border);
        }
        .inline-table td {
            padding: 6px 10px;
            border-bottom: 1px solid var(--border);
        }
        .inline-table tr:last-child td {
            border-bottom: none;
        }
        .inline-table tr:hover td {
            background: rgba(139, 92, 246, 0.08);
        }
    </style>
</head>
<body>

    <header>
        <div class="header-left">
            <a href="index.html" style="color:white; font-size:20px;"><i class="fa-solid fa-chevron-left"></i></a>
            <div class="ai-title">SK AI</div>
            <span class="network-status" id="networkStatus">
                <span class="dot"></span>
                <span id="networkLabel">Online</span>
            </span>
        </div>
        <div class="header-actions">
            <select id="aiModelSelect" onchange="changeModel(this.value)" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:white; border-radius:20px; padding:6px 12px; font-size:11px; cursor:pointer; outline:none;">
                <option value="gemini" style="background:#1a1e2e; color:white;">✨ Gemini</option>
                <option value="deepseek" style="background:#1a1e2e; color:white;">🧠 DeepSeek</option>
            </select>
            <button onclick="clearChat()" title="Clear Chat" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:white; border-radius:50%; width:32px; height:32px; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                <i class="fa-solid fa-eraser"></i>
            </button>
            <button onclick="showExportDialog()" title="Download Reports" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:white; border-radius:50%; width:32px; height:32px; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                <i class="fa-solid fa-download"></i>
            </button>
        </div>
    </header>

    <div class="chat-container" id="chatBox">
        <!-- Initial message added dynamically from JS -->
    </div>

    <div id="typing" class="typing">
        <i class="fa-solid fa-circle"></i><i class="fa-solid fa-circle" style="animation-delay:0.3s"></i><i class="fa-solid fa-circle" style="animation-delay:0.6s"></i>
        <span>SK AI partner data analyze panni answer ready pannitu irukku 🤝</span>
    </div>

    <div class="suggestion-area" id="suggestionArea"></div>

    <div class="input-area">
        <button id="voiceBtn" class="send-btn" style="background: rgba(255,255,255,0.1); width: 45px; height: 45px;"><i class="fa-solid fa-microphone"></i></button>
        <input type="text" id="userInput" placeholder="Ask about vehicle, invoice, renewal, dealer..." onkeypress="if(event.key === 'Enter') sendMessage()">
        <button class="send-btn" onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
    </div>

    <!-- 📥 Export Report Modal -->
    <div class="modal-overlay" id="exportModal">
        <div class="modal-box">
            <h3><i class="fa-solid fa-download"></i> Download Report</h3>
            
            <label>Report Type</label>
            <select id="exportType">
                <option value="sales">📊 Sales</option>
                <option value="renewal">🔄 Renewals</option>
                <option value="stock">📦 Stock</option>
                <option value="dealers">🏪 Dealers</option>
                <option value="profit">💰 Profit</option>
                <option value="customers">👥 Customers</option>
            </select>

            <label>From Date</label>
            <input type="date" id="exportStartDate">

            <label>To Date</label>
            <input type="date" id="exportEndDate">

            <label>Format</label>
            <div class="format-row">
                <div class="format-btn active" id="fmtCsv" onclick="setFormat('csv')"><i class="fa-solid fa-file-csv"></i> CSV</div>
                <div class="format-btn" id="fmtPdf" onclick="setFormat('pdf')"><i class="fa-solid fa-file-pdf"></i> PDF</div>
            </div>

            <div class="btn-row">
                <button class="btn-cancel" onclick="closeExportDialog()">Cancel</button>
                <button class="btn-download" onclick="downloadReport()"><i class="fa-solid fa-download"></i> Download</button>
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // STATE
        // ============================================
        const chatBox = document.getElementById('chatBox');
        const userInput = document.getElementById('userInput');
        const typingEl = document.getElementById('typing');
        const suggestionArea = document.getElementById('suggestionArea');
        const aiModelSelect = document.getElementById('aiModelSelect');
        const networkStatus = document.getElementById('networkStatus');
        const networkLabel = document.getElementById('networkLabel');

        let currentModel = localStorage.getItem('sk_ai_model') || 'gemini';
        let chatHistory = JSON.parse(localStorage.getItem('sk_ai_chat_history') || '[]');
        let sessionMessages = []; // For typing effect tracking
        let isProcessing = false;

        aiModelSelect.value = currentModel;

        // ============================================
        // UTILITY FUNCTIONS
        // ============================================
        function getTimeLabel() {
            return new Date().toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
        }

        function getApiEndpoint() {
            return currentModel === 'deepseek' ? 'api_deepseek.php' : 'api_gemini.php';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ============================================
        // PERSISTENT CHAT HISTORY
        // ============================================
        function saveChatHistory() {
            // Keep only last 50 messages for performance
            const trimmed = chatHistory.slice(-50);
            localStorage.setItem('sk_ai_chat_history', JSON.stringify(trimmed));
            chatHistory = trimmed;
        }

        function loadChatHistory() {
            chatBox.innerHTML = '';
            if (chatHistory.length === 0) {
                // Show default welcome message
                const welcome = "Vanakkam! I am your SK Logic AI assistant. \n\nDB-la irukka sales, renewal, invoice, IMEI, dealer, stock, customer data table-la irundhu find pannitu direct-ah reply kuduppen. \nTry: <b>\"Inniku collection evlo?\"</b>, <b>\"TN33BZ9713 renewal status\"</b>, <b>\"RINV invoice details\"</b>, or <b>\"customerdatas la Vimal number enna?\"</b>";
                addMessage(welcome, 'ai', false);
                chatHistory.push({ side: 'ai', text: welcome, time: getTimeLabel() });
                saveChatHistory();
            } else {
                chatHistory.forEach(msg => {
                    addMessage(msg.text, msg.side, false);
                });
            }
        }

        // ============================================
        // TYPING EFFECT
        // ============================================
        async function typeMessage(text, side) {
            const div = document.createElement('div');
            div.className = `msg msg-${side}`;
            
            const contentSpan = document.createElement('span');
            contentSpan.className = 'msg-content';
            div.appendChild(contentSpan);
            
            const timeDiv = document.createElement('div');
            timeDiv.className = 'msg-time';
            const timeNow = getTimeLabel();
            timeDiv.innerHTML = `<span>${timeNow}</span>`;
            
            // Copy button
            const toolsSpan = document.createElement('span');
            toolsSpan.className = 'msg-tools';
            toolsSpan.innerHTML = `<button onclick="copyMessage(this)" title="Copy"><i class="fa-solid fa-copy"></i></button>`;
            timeDiv.appendChild(toolsSpan);
            
            div.appendChild(timeDiv);
            chatBox.appendChild(div);
            chatBox.scrollTop = chatBox.scrollHeight;

            // Progressive typing effect: show word by word
            const cleanText = String(text);
            const parts = cleanText.split(/(\n|\[OPEN:[^\]]+\]|<[^>]+>)/g);
            let displayed = '';
            
            for (const part of parts) {
                if (part === '\n') {
                    displayed += '<br>';
                    contentSpan.innerHTML = displayed;
                    continue;
                }
                if (part.startsWith('[') || part.startsWith('<')) {
                    displayed += part;
                    contentSpan.innerHTML = displayed;
                    continue;
                }
                
                const words = part.split(' ');
                for (const word of words) {
                    displayed += escapeHtml(word) + ' ';
                    contentSpan.innerHTML = displayed;
                    // Small delay for realistic typing feel (skip if many words)
                    await new Promise(r => setTimeout(r, 8 + Math.random() * 12));
                }
            }

            // Final render with full formatting
            let formatted = String(text).replace(/\n/g, '<br>');
            formatted = formatted.replace(/\[OPEN:([^\]]+)\]/g, (match, p1) => {
                return `<br><button class="action-btn" onclick="openRecord('${escapeHtml(p1)}')"><i class="fa-solid fa-eye"></i> View ${escapeHtml(p1)}</button>`;
            });
            // Convert table-like data into styled tables
            formatted = formatTables(formatted);
            contentSpan.innerHTML = formatted;

            chatBox.scrollTop = chatBox.scrollHeight;
            return { text, time: timeNow };
        }

        function formatTables(text) {
            // If text contains pipe-separated data, wrap in table
            if (text.includes('|') && text.includes('\n')) {
                const lines = text.split('<br>');
                if (lines.some(l => l.includes('|'))) {
                    let tableHtml = '<table class="inline-table">';
                    lines.forEach((line, idx) => {
                        if (line.includes('|')) {
                            const cells = line.split('|').map(c => c.trim()).filter(c => c);
                            if (cells.length >= 2) {
                                const tag = idx === 0 ? 'th' : 'td';
                                tableHtml += '<tr>' + cells.map(c => `<${tag}>${c}</${tag}>`).join('') + '</tr>';
                            }
                        }
                    });
                    tableHtml += '</table>';
                    // Replace pipe lines with table
                    return tableHtml;
                }
            }
            return text;
        }

        // ============================================
        // ADD MESSAGE (non-typing instant version)
        // ============================================
        function addMessage(text, side, shouldSave = true) {
            const div = document.createElement('div');
            div.className = `msg msg-${side}`;
            
            let formatted = String(text).replace(/\n/g, '<br>');
            formatted = formatted.replace(/\[OPEN:([^\]]+)\]/g, (match, p1) => {
                return `<br><button class="action-btn" onclick="openRecord('${escapeHtml(p1)}')"><i class="fa-solid fa-eye"></i> View ${escapeHtml(p1)}</button>`;
            });
            formatted = formatTables(formatted);

            const timeNow = getTimeLabel();
            formatted += `<div class="msg-time"><span>${timeNow}</span><span class="msg-tools"><button onclick="copyMessage(this)" title="Copy"><i class="fa-solid fa-copy"></i></button></span></div>`;

            div.innerHTML = formatted;
            chatBox.appendChild(div);
            chatBox.scrollTop = chatBox.scrollHeight;

            if (shouldSave) {
                chatHistory.push({ side, text, time: timeNow });
                saveChatHistory();
            }
        }

        // ============================================
        // NETWORK STATUS
        // ============================================
        function updateNetworkStatus() {
            if (navigator.onLine) {
                networkStatus.className = 'network-status online';
                networkLabel.textContent = 'Online';
            } else {
                networkStatus.className = 'network-status offline';
                networkLabel.textContent = 'Offline';
            }
        }
        window.addEventListener('online', updateNetworkStatus);
        window.addEventListener('offline', updateNetworkStatus);
        updateNetworkStatus();

        // ============================================
        // SUGGESTION CHIPS
        // ============================================
        const SUGGESTIONS = [
            { label: '📊 Today Collection', query: 'Inniku collection evlo?' },
            { label: '📈 Month Profit', query: 'Indha masam profit evlo?' },
            { label: '📦 Stock Status', query: 'Stock la enna irukku?' },
            { label: '🔔 Pending Renewals', query: 'Pending renewal ethana?' },
            { label: '🏪 Top Dealers', query: 'Top dealers yaru?' },
            { label: '📅 Today Renewals', query: 'Inniku renewal due list' },
        ];

        function renderSuggestions() {
            suggestionArea.innerHTML = '';
            SUGGESTIONS.forEach(s => {
                const chip = document.createElement('span');
                chip.className = 'suggestion-chip';
                chip.innerHTML = s.label;
                chip.onclick = () => {
                    userInput.value = s.query;
                    sendMessage();
                };
                suggestionArea.appendChild(chip);
            });
        }
        renderSuggestions();

        // ============================================
        // CHANGE MODEL
        // ============================================
        function changeModel(value) {
            currentModel = value;
            localStorage.setItem('sk_ai_model', value);
            const modelName = currentModel === 'deepseek' ? 'DeepSeek AI' : 'Gemini AI';
            addMessage('🔄 Switched to <b>' + modelName + '</b>. Ippo ' + modelName + ' use panni data analyze pannuvom!', 'ai');
        }

        // ============================================
        // SEND MESSAGE
        // ============================================
        async function sendMessage() {
            const msg = userInput.value.trim();
            if (!msg || isProcessing) return;

            isProcessing = true;
            addMessage(msg, 'user');
            chatHistory.push({ side: 'user', text: msg, time: getTimeLabel() });
            saveChatHistory();

            userInput.value = '';
            typingEl.classList.add('active');
            chatBox.scrollTop = chatBox.scrollHeight;

            try {
                const formData = new FormData();
                formData.append('q', msg);

                // Send recent history for context (last 8 messages)
                const recentHistory = chatHistory.slice(-10);
                formData.append('history', JSON.stringify(recentHistory.map(m => ({ side: m.side, text: m.text }))));

                const res = await fetch(getApiEndpoint(), {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                let aiAnswer = data.answer || "Data reply varala.";

                // Use typing effect for AI response
                const result = await typeMessage(aiAnswer, 'ai');
                chatHistory.push({ side: 'ai', text: aiAnswer, time: result.time });
                saveChatHistory();

                // Auto-suggest follow-up chips based on answer content
                updateContextSuggestions(msg, aiAnswer);

            } catch (e) {
                addMessage("❌ AI connect aagala. Please check server setup.", 'ai');
            } finally {
                typingEl.classList.remove('active');
                chatBox.scrollTop = chatBox.scrollHeight;
                isProcessing = false;
            }
        }

        // ============================================
        // CONTEXTUAL SUGGESTIONS
        // ============================================
        function updateContextSuggestions(query, answer) {
            const lower = query.toLowerCase();
            const newChips = [];

            if (lower.includes('collection') || lower.includes('sale') || lower.includes('inniku') || lower.includes('evlo')) {
                newChips.push({ label: '📈 Month Report', query: 'Indha masam sales report kudu' });
                newChips.push({ label: '💰 Profit Analysis', query: 'Month profit detail' });
                newChips.push({ label: '📅 Yesterday', query: 'Nethu collection evlo?' });
            }
            if (lower.includes('stock') || lower.includes('iruppu') || lower.includes('device')) {
                newChips.push({ label: '📦 By Model', query: 'Device model wise stock' });
                newChips.push({ label: '➕ Add Stock', query: 'Stock add panna pathi' });
            }
            if (lower.includes('renewal') || lower.includes('pending') || lower.includes('due')) {
                newChips.push({ label: '📅 Tomorrow Due', query: 'Nalaiku renewal due' });
                newChips.push({ label: '🔔 All Pending', query: 'Total pending renewals' });
            }
            if (lower.includes('dealer') || lower.includes('dealer_ledger')) {
                newChips.push({ label: '🏪 Dealer Wise', query: 'Dealer wise sales detail' });
            }
            if (lower.match(/tn\d{2}/i) || lower.match(/\d{15}/)) {
                newChips.push({ label: '🔍 Full Details', query: query + ' full details' });
                newChips.push({ label: '📋 Invoice', query: query + ' invoice' });
            }

            if (newChips.length > 0) {
                suggestionArea.innerHTML = '';
                newChips.slice(0, 4).forEach(s => {
                    const chip = document.createElement('span');
                    chip.className = 'suggestion-chip';
                    chip.innerHTML = s.label;
                    chip.onclick = () => {
                        userInput.value = s.query;
                        sendMessage();
                    };
                    suggestionArea.appendChild(chip);
                });
            } else {
                renderSuggestions();
            }
        }

        // ============================================
        // COPY MESSAGE
        // ============================================
        function copyMessage(btn) {
            const msgDiv = btn.closest('.msg');
            const content = msgDiv.querySelector('.msg-content');
            let text = content ? content.innerText : msgDiv.innerText;
            // Remove tools text
            text = text.replace(/Copy/g, '').trim();

            navigator.clipboard.writeText(text).then(() => {
                const original = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check"></i>';
                setTimeout(() => btn.innerHTML = original, 1500);
            });
        }

        // ============================================
        // CLEAR CHAT
        // ============================================
        function clearChat() {
            if (chatHistory.length === 0) return;
            if (confirm('Sure ah? All chat history delete aagum!')) {
                localStorage.removeItem('sk_ai_chat_history');
                chatHistory = [];
                chatBox.innerHTML = '';
                loadChatHistory();
                renderSuggestions();
            }
        }

        // ============================================
        // OPEN RECORD
        // ============================================
        function openRecord(id) {
            if (window.opener) {
                window.opener.location.href = `master_device.php?q=${encodeURIComponent(id)}`;
            } else {
                window.location.href = `index.html?search=${encodeURIComponent(id)}`;
            }
        }

        // ============================================
        // VOICE RECOGNITION
        // ============================================
        const voiceBtn = document.getElementById('voiceBtn');
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (SpeechRecognition) {
            const recognition = new SpeechRecognition();
            recognition.lang = 'en-IN';
            recognition.continuous = false;
            recognition.interimResults = false;

            voiceBtn.onclick = () => {
                recognition.start();
                voiceBtn.style.background = 'var(--primary)';
                voiceBtn.innerHTML = '<i class="fa-solid fa-ear-listen"></i>';
            };

            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                userInput.value = transcript;
                voiceBtn.style.background = 'rgba(255,255,255,0.1)';
                voiceBtn.innerHTML = '<i class="fa-solid fa-microphone"></i>';
                sendMessage();
            };

            recognition.onerror = () => {
                voiceBtn.style.background = 'rgba(255,255,255,0.1)';
                voiceBtn.innerHTML = '<i class="fa-solid fa-microphone"></i>';
            };
            
            recognition.onend = () => {
                voiceBtn.style.background = 'rgba(255,255,255,0.1)';
                voiceBtn.innerHTML = '<i class="fa-solid fa-microphone"></i>';
            };
        } else {
            voiceBtn.style.display = 'none';
        }

        // ============================================
        // EXPORT FUNCTIONS
        // ============================================
        let exportFormat = 'csv';

        function showExportDialog() {
            const today = new Date().toISOString().split('T')[0];
            const firstDay = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
            document.getElementById('exportStartDate').value = firstDay;
            document.getElementById('exportEndDate').value = today;
            document.getElementById('exportModal').classList.add('active');
        }

        function closeExportDialog() {
            document.getElementById('exportModal').classList.remove('active');
        }

        function setFormat(fmt) {
            exportFormat = fmt;
            document.getElementById('fmtCsv').classList.toggle('active', fmt === 'csv');
            document.getElementById('fmtPdf').classList.toggle('active', fmt === 'pdf');
        }

        function downloadReport() {
            const type = document.getElementById('exportType').value;
            const startDate = document.getElementById('exportStartDate').value;
            const endDate = document.getElementById('exportEndDate').value;
            const params = new URLSearchParams({
                action: 'download',
                format: exportFormat,
                type: type,
                start_date: startDate,
                end_date: endDate
            });
            const url = 'api_ai_export.php?' + params.toString();
            if (exportFormat === 'csv') {
                window.location.href = url;
            } else {
                window.open(url, '_blank');
            }
            closeExportDialog();
            addMessage('📥 Downloading <b>' + type.toUpperCase() + '</b> report as ' + exportFormat.toUpperCase() + '...', 'ai');
        }

        document.getElementById('exportModal').addEventListener('click', function(e) {
            if (e.target === this) closeExportDialog();
        });

        // ============================================
        // KEYBOARD SHORTCUT
        // ============================================
        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && document.activeElement !== userInput) {
                e.preventDefault();
                userInput.focus();
            }
        });

        // ============================================
        // INIT
        // ============================================
        loadChatHistory();

        // Focus input on load
        setTimeout(() => userInput.focus(), 300);
    </script>
</body>
</html>
