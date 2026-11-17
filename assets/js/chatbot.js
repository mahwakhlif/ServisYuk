/**
 * ServisYuk AI Chatbot Client Script
 * Connects to Node.js AI Service (port 5005) with fallback to PHP API
 */
(function() {
    'use strict';

    const NODE_API_URL = 'http://localhost:5005/api/chat';
    const PHP_API_URL = 'index.php?page=api_chatbot';

    // State
    const state = {
        isOpen: false,
        isLoading: false,
        messages: []
    };

    // DOM Elements
    let toggleBtn, chatWindow, messagesContainer, chatInput, sendBtn, chipsContainer;

    function getCurrentUser() {
        const userDataElem = document.getElementById('servisyuk-chat-user-data');
        if (userDataElem) {
            try {
                return JSON.parse(userDataElem.textContent);
            } catch (e) {
                console.warn('Failed to parse user session for chat:', e);
            }
        }
        return { logged_in: false };
    }

    function init() {
        toggleBtn = document.getElementById('servisyuk-chat-toggle');
        chatWindow = document.getElementById('servisyuk-chat-window');
        messagesContainer = document.getElementById('chat-messages-container');
        chatInput = document.getElementById('chat-input-field');
        sendBtn = document.getElementById('chat-send-trigger');
        chipsContainer = document.getElementById('chat-quick-chips');

        if (!toggleBtn || !chatWindow) return;

        // Load existing messages or show welcome
        loadHistory();

        // Event listeners
        toggleBtn.addEventListener('click', toggleChat);
        
        const closeBtn = document.getElementById('chat-close-btn');
        if (closeBtn) closeBtn.addEventListener('click', closeChat);

        const clearBtn = document.getElementById('chat-clear-btn');
        if (clearBtn) clearBtn.addEventListener('click', clearHistory);

        sendBtn.addEventListener('click', handleSend);
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleSend();
            }
        });

        // Quick chip clicks
        if (chipsContainer) {
            chipsContainer.addEventListener('click', function(e) {
                const chip = e.target.closest('.chat-chip');
                if (chip && !state.isLoading) {
                    const prompt = chip.getAttribute('data-prompt') || chip.textContent.trim();
                    sendUserMessage(prompt);
                }
            });
        }
    }

    function toggleChat() {
        state.isOpen ? closeChat() : openChat();
    }

    function openChat() {
        state.isOpen = true;
        chatWindow.classList.add('open');
        toggleBtn.classList.add('active');
        setTimeout(() => chatInput.focus(), 300);
        scrollToBottom();
    }

    function closeChat() {
        state.isOpen = false;
        chatWindow.classList.remove('open');
        toggleBtn.classList.remove('active');
    }

    function loadHistory() {
        try {
            const saved = localStorage.getItem('servisyuk_chat_history');
            if (saved) {
                state.messages = JSON.parse(saved);
                renderAllMessages();
                return;
            }
        } catch (e) {
            console.warn('Failed to load chat history:', e);
        }

        // Default Welcome Message with user personalization
        const user = getCurrentUser();
        let greeting = 'Halo! 👋 Selamat datang di **ServisYuk**.';
        if (user && user.logged_in && user.name) {
            const roleName = user.role === 'customer' ? 'Pelanggan' : user.role === 'technician' ? 'Teknisi' : 'Admin';
            greeting = `Halo, **${user.name}**! 👋 Selamat datang kembali di **ServisYuk** (${roleName}).`;
        }

        const welcome = {
            role: 'assistant',
            content: `${greeting}\n\nSaya asisten virtual cerdas yang siap membantu Anda menjawab pertanyaan seputar identitas akun ("Siapa saya?"), seluruh data di database website ServisYuk, keluhan alat rumah tangga, estimasi tarif perbaikan, hingga pemesanan teknisi resmi bergaransi. Ada yang bisa saya bantu hari ini?`,
            time: getCurrentTime()
        };
        state.messages = [welcome];
        renderAllMessages();
    }

    function saveHistory() {
        try {
            localStorage.setItem('servisyuk_chat_history', JSON.stringify(state.messages.slice(-20)));
        } catch (e) {}
    }

    function clearHistory() {
        if (confirm('Bersihkan riwayat percakapan?')) {
            localStorage.removeItem('servisyuk_chat_history');
            state.messages = [];
            loadHistory();
        }
    }

    function getCurrentTime() {
        const d = new Date();
        return d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
    }

    function renderAllMessages() {
        messagesContainer.innerHTML = '';
        state.messages.forEach(msg => {
            appendMessageToDOM(msg.role, msg.content, msg.time);
        });
        scrollToBottom();
    }

    function appendMessageToDOM(role, content, time) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-message ${role === 'user' ? 'user' : 'bot'}`;

        const isBot = role === 'assistant' || role === 'bot';
        const formattedText = formatMarkdown(content);

        msgDiv.innerHTML = `
            ${isBot ? '<div class="chat-msg-avatar"><i class="fa-solid fa-robot"></i></div>' : ''}
            <div class="chat-msg-bubble">
                <div class="chat-msg-content">${formattedText}</div>
                <div class="chat-msg-time">${time || getCurrentTime()}</div>
            </div>
            ${!isBot ? '<div class="chat-msg-avatar" style="background:#2563eb; color:#fff;"><i class="fa-solid fa-user"></i></div>' : ''}
        `;

        messagesContainer.appendChild(msgDiv);
        scrollToBottom();
    }

    function showTyping() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chat-message bot';
        typingDiv.id = 'chat-typing-indicator';
        typingDiv.innerHTML = `
            <div class="chat-msg-avatar"><i class="fa-solid fa-robot"></i></div>
            <div class="chat-typing">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        scrollToBottom();
    }

    function hideTyping() {
        const typing = document.getElementById('chat-typing-indicator');
        if (typing) typing.remove();
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function formatMarkdown(text) {
        if (!text) return '';

        // 1. Convert Markdown Tables (| col1 | col2 |) to HTML Table
        text = text.replace(/((?:^[ \t]*\|.+?\|[ \t]*(?:\r?\n|$))+)/gm, function(tableBlock) {
            const rawLines = tableBlock.trim().split(/\r?\n/).filter(line => line.trim().startsWith('|'));
            if (rawLines.length < 2) return tableBlock;

            let html = '<div class="chat-table-wrapper"><table class="chat-table">';
            let isHeader = true;

            for (let i = 0; i < rawLines.length; i++) {
                const line = rawLines[i].trim();
                // Separator row like |:---|:---| or |---|---|
                if (/^\|([ \t]*:?-+:?[ \t]*\|)+$/.test(line)) {
                    isHeader = false;
                    continue;
                }

                const cells = line.split('|').slice(1, -1).map(c => c.trim());
                if (cells.length === 0) continue;

                if (isHeader && i === 0) {
                    html += '<thead><tr>';
                    cells.forEach(c => { html += `<th>${c}</th>`; });
                    html += '</tr></thead><tbody>';
                } else {
                    html += '<tr>';
                    cells.forEach(c => { html += `<td>${c}</td>`; });
                    html += '</tr>';
                }
            }
            if (isHeader) {
                html += '</table></div>';
            } else {
                html += '</tbody></table></div>';
            }
            return '\n\n' + html + '\n\n';
        });

        // 2. Protect table blocks (both generated and raw HTML tables)
        const protectedBlocks = [];
        text = text.replace(/<div class="chat-table-wrapper">[\s\S]*?<\/div>/g, function(match) {
            protectedBlocks.push(match);
            return `__PROTECTED_BLOCK_${protectedBlocks.length - 1}__`;
        });
        text = text.replace(/<table[\s\S]*?<\/table>/gi, function(match) {
            protectedBlocks.push(`<div class="chat-table-wrapper"><table class="chat-table">${match.replace(/^<table[^>]*>|<\/table>$/gi, '')}</table></div>`);
            return `__PROTECTED_BLOCK_${protectedBlocks.length - 1}__`;
        });

        // 3. Escape general HTML
        let escaped = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // 4. Inline formatting
        // Bold **text**
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Italic *text*
        escaped = escaped.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Links [text](url)
        escaped = escaped.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" style="color:#2563eb;text-decoration:underline;">$1</a>');

        // Bullet points
        escaped = escaped.replace(/^[\*\-]\s+(.*)$/gm, '<li>$1</li>');
        escaped = escaped.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');

        // Line breaks & paragraphs
        escaped = escaped.replace(/\n\n+/g, '</p><p>').replace(/\n/g, '<br>');

        // 5. Restore protected table blocks
        escaped = escaped.replace(/__PROTECTED_BLOCK_(\d+)__/g, function(_, index) {
            return protectedBlocks[Number(index)] || '';
        });

        // Clean up nested paragraphs around table wrappers
        escaped = escaped.replace(/<p>\s*(<div class="chat-table-wrapper">[\s\S]*?<\/div>)\s*<\/p>/g, '$1');
        escaped = escaped.replace(/<p>\s*<\/p>/g, '');

        return `<p>${escaped}</p>`;
    }

    async function handleSend() {
        const text = chatInput.value.trim();
        if (!text || state.isLoading) return;
        chatInput.value = '';
        await sendUserMessage(text);
    }

    async function sendUserMessage(text) {
        state.isLoading = true;
        sendBtn.disabled = true;

        const time = getCurrentTime();
        state.messages.push({ role: 'user', content: text, time: time });
        appendMessageToDOM('user', text, time);
        saveHistory();

        showTyping();

        try {
            // 1. Coba Node.js Microservice jika aktif (timeout 2.5s agar tidak menggantung UI)
            let reply = await callNodeService(text);

            if (!reply) {
                // 2. Fallback ke PHP API Endpoint
                reply = await callPhpService(text);
            }

            hideTyping();
            const botTime = getCurrentTime();
            state.messages.push({ role: 'assistant', content: reply, time: botTime });
            appendMessageToDOM('assistant', reply, botTime);
            saveHistory();

        } catch (err) {
            hideTyping();
            console.error('Chatbot error:', err);
            const errMsg = err.message || 'Maaf, terjadi kendala saat memproses pesan Anda. Silakan coba beberapa saat lagi.';
            appendMessageToDOM('assistant', errMsg, getCurrentTime());
        } finally {
            state.isLoading = false;
            sendBtn.disabled = false;
            chatInput.focus();
        }
    }

    // Call Node.js server
    async function callNodeService(userText) {
        try {
            const controller = new AbortController();
            // 4.5 seconds timeout: provides ample time for external LLM API to respond,
            // while falling back immediately to PHP if Node daemon is not running (connection refused)
            const timeoutId = setTimeout(() => controller.abort(), 4500);

            const res = await fetch(NODE_API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: userText,
                    messages: state.messages,
                    user: getCurrentUser()
                }),
                signal: controller.signal
            });
            clearTimeout(timeoutId);

            if (res.ok) {
                const data = await res.json();
                if (data.status === 'success' && data.reply) {
                    return data.reply;
                }
            }
        } catch (err) {
            console.warn('Node.js chat endpoint unreachable, trying PHP fallback:', err.message);
        }
        return null;
    }

    // Fallback to PHP backend
    async function callPhpService(userText) {
        const baseUrl = (typeof window !== 'undefined' && window.BASE_URL) ? window.BASE_URL.replace(/\/+$/, '') : '';
        const url = baseUrl ? `${baseUrl}/index.php?page=api_chatbot` : PHP_API_URL;

        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: userText,
                messages: state.messages,
                user: getCurrentUser()
            })
        });

        const rawText = await res.text();
        let data;
        try {
            data = JSON.parse(rawText);
        } catch (e) {
            console.error('[Chatbot] Respons server bukan JSON valid:', rawText);
            throw new Error('Respons dari server tidak valid (bukan JSON). Silakan periksa log server.');
        }

        if (res.ok && data.status === 'success' && data.reply) {
            return data.reply;
        }

        if (data && (data.reply || data.message)) {
            return data.reply || data.message;
        }

        throw new Error(data?.message || `Server mengembalikan status HTTP ${res.status}`);
    }

    // Initialize when DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
