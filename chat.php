<?php
// chat.php
// Main chat interface

require_once 'functions.php';

start_session();

if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}

$user_id = get_user_id();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chatbot</title>
    <style>
        /* Modern Dark Theme CSS */
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0a0a;
            color: #e0e0e0;
            display: flex;
            height: 100vh;
            font-size: 14px;
            line-height: 1.5;
        }

        #sidebar {
            width: 280px;
            background: #1a1a1a;
            padding: 24px;
            overflow-y: auto;
            border-right: 1px solid #2a2a2a;
            transition: all 0.2s ease-out;
        }

        #sidebar button {
            width: 100%;
            padding: 12px 16px;
            margin-bottom: 12px;
            background: transparent;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            color: #e0e0e0;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease-out;
        }

        #sidebar button:hover {
            background: #2a2a2a;
            border-color: #667eea;
            transform: translateY(-1px);
        }

        #sidebar button:active {
            transform: translateY(0);
        }

        #sidebar .logo {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        #chat-list {
            list-style: none;
            padding: 0;
        }

        #chat-list li {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #2a2a2a;
            border-radius: 6px;
            margin-bottom: 4px;
            transition: all 0.2s ease-out;
            font-weight: 500;
        }

        #chat-list li:hover {
            background: #1a1a1a;
            color: #e0e0e0;
            transform: translateX(4px);
        }

        #chat-list li.active {
            background: #2a2a2a;
            color: #ffffff;
        }

        .delete-btn {
            float: right;
            color: #ff4757;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s ease-out;
        }

        .delete-btn:hover {
            background: rgba(255, 71, 87, 0.1);
        }

        #main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: linear-gradient(180deg, #1a1a1a 0%, #0f0f0f 100%);
        }

        #chat-area {
            flex: 1;
            overflow-y: auto;
            padding: 32px;
            display: flex;
            flex-direction: column;
        }

        .message {
            margin-bottom: 24px;
            max-width: 90%;
            animation: fadeInUp 0.3s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .user {
            align-self: flex-end;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px 20px;
            border-radius: 16px;
            border-bottom-right-radius: 4px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .assistant {
            align-self: flex-start;
            background: #1a1a1a;
            padding: 16px 20px;
            border-radius: 16px;
            border: 1px solid #2a2a2a;
            border-bottom-left-radius: 4px;
            font-weight: 400;
        }

        .assistant::before {
            content: '✨ AI: ';
            font-weight: 600;
            color: #667eea;
        }

        /* Enhanced Code Block Styling */
        .code-block {
            background: #0d1117;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            margin: 16px 0;
            overflow: hidden;
        }

        .code-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px 8px 16px;
            border-bottom: 1px solid #2a2a2a;
        }

        .code-language {
            font-size: 12px;
            color: #667eea;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .copy-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 6px;
            color: #b0b0b0;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s ease-out;
        }

        .copy-btn:hover {
            background: #2a2a2a;
            border-color: #667eea;
            color: #e0e0e0;
            transform: translateY(-1px);
        }

        .copy-btn.copied {
            background: #667eea;
            border-color: #667eea;
            color: white;
        }

        .copy-icon {
            font-size: 14px;
        }

        .highlighted-code {
            display: block;
            padding: 20px;
            overflow-x: auto;
            font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.4;
        }

        pre:not(.code-block) {
            background: #1e1e1e;
            padding: 16px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 12px 0;
            border: 1px solid #2a2a2a;
        }

        code {
            font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
            color: #e0e0e0;
            white-space: pre-wrap;
        }

        .loading {
            font-style: italic;
            color: #888;
            opacity: 0.7;
        }

        /* Input Area Modernization */
        #input-area {
            padding: 24px 32px 32px 32px;
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        #message-input {
            flex: 1;
            padding: 16px 20px;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            color: #e0e0e0;
            font-size: 15px;
            font-family: inherit;
            resize: none;
            outline: none;
            transition: all 0.2s ease-out;
            min-height: 52px;
            max-height: 120px;
        }

        #message-input:focus {
            border-color: #667eea;
            background: #1f1f1f;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        #message-input::placeholder {
            color: #666;
        }

        #send-btn {
            padding: 16px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease-out;
            outline: none;
        }

        #send-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        #send-btn:active:not(:disabled) {
            transform: translateY(0);
        }

        #send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Quote Popup Modernization */
        #quote-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            border: 1px solid #2a2a2a;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            max-width: 400px;
            z-index: 1000;
        }

        #quote-text {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 16px;
            font-style: italic;
            color: #e0e0e0;
        }

        #quote-author {
            font-size: 14px;
            color: #b0b0b0;
            text-align: right;
            margin-bottom: 24px;
        }

        #close-quote {
            padding: 8px 16px;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 6px;
            color: #e0e0e0;
            cursor: pointer;
            transition: all 0.2s ease-out;
        }

        #close-quote:hover {
            background: #2a2a2a;
            border-color: #667eea;
        }

        @media (max-width: 768px) {
            #sidebar {
                width: 100%;
                position: absolute;
                z-index: 1;
                display: none;
            }
            #main {
                flex-direction: column;
            }
            #chat-area {
                padding: 20px;
            }
            #input-area {
                padding: 16px 20px 20px 20px;
            }
        }
        
        /* Syntax highlighting styles (VSCode-like dark theme) */
        .hljs-comment { color: #6a9955; } /* Green for comments */
        .hljs-keyword { color: #569cd6; } /* Blue for keywords */
        .hljs-string { color: #ce9178; } /* Orange for strings */
        .hljs-variable { color: #9cdcfe; } /* Light blue for variables */
        .hljs-function { color: #dcdcaa; } /* Yellow for functions */
        .hljs-number { color: #b5cea8; } /* Greenish for numbers */
        .hljs-operator { color: #d4d4d4; } /* Gray for operators */
        .hljs-punctuation { color: #d4d4d4; } /* Gray for punctuation */
        .hljs-tag { color: #808080; } /* Gray for tags like <?php ?> */
    </style>
</head>
<body>
    <div id="sidebar">
        <div class="logo">✨ AI Chat</div>
        <button id="new-chat-btn">New Chat</button>
        <button id="quote-btn">Show Quote</button>
        <button id="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
        <ul id="chat-list"></ul>
    </div>
    <div id="main">
        <div id="chat-area"></div>
        <div id="input-area">
            <textarea id="message-input" placeholder="Type your message..." rows="1"></textarea>
            <button id="send-btn">Send</button>
        </div>
    </div>
    <div id="quote-popup">
        <p id="quote-text"></p>
        <p id="quote-author"></p>
        <button id="close-quote">Close</button>
    </div>
    <script>
        // Vanilla JS for chat logic
        const userId = <?php echo $user_id; ?>;
        let currentChatId = null;

        const chatList = document.getElementById('chat-list');
        const chatArea = document.getElementById('chat-area');
        const messageInput = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-btn');
        const newChatBtn = document.getElementById('new-chat-btn');
        const quoteBtn = document.getElementById('quote-btn');
        const quotePopup = document.getElementById('quote-popup');
        const quoteText = document.getElementById('quote-text');
        const quoteAuthor = document.getElementById('quote-author');
        const closeQuote = document.getElementById('close-quote');

        function loadChats() {
            fetch('get_chats.php')
                .then(res => res.json())
                .then(data => {
                    chatList.innerHTML = '';
                    data.forEach(chat => {
                        const li = document.createElement('li');
                        li.textContent = chat.title;
                        li.dataset.chatId = chat.id;
                        li.onclick = () => loadChat(chat.id);
                        const del = document.createElement('span');
                        del.className = 'delete-btn';
                        del.textContent = 'X';
                        del.onclick = (e) => { e.stopPropagation(); deleteChat(chat.id); };
                        li.appendChild(del);
                        chatList.appendChild(li);
                    });
                });
        }

        function loadChat(chatId) {
            currentChatId = chatId;
            fetch(`get_messages.php?chat_id=${chatId}`)
                .then(res => res.json())
                .then(messages => {
                    chatArea.innerHTML = '';
                    messages.forEach(msg => {
                        appendMessage(msg.role, msg.content);
                    });
                    chatArea.scrollTop = chatArea.scrollHeight;
                });
        }

        function createNewChat() {
            fetch('create_chat.php', { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    loadChats();
                    loadChat(data.chat_id);
                });
        }

        function deleteChat(chatId) {
            if (confirm('Delete this chat?')) {
                fetch(`delete_chat.php?chat_id=${chatId}`, { method: 'POST' })
                    .then(() => {
                        loadChats();
                        if (currentChatId === chatId) {
                            chatArea.innerHTML = '';
                            currentChatId = null;
                        }
                    });
            }
        }

        function highlightCode(code) {
            // Detect language and return object with highlighted code and language
            let language = detectLanguage(code);
            let highlighted = applySyntaxHighlighting(code);
            return { code: highlighted, language: language };
        }

        function detectLanguage(code) {
            // Check for language indicators in code
            const firstLine = code.split('\n')[0].trim();
            const languageMap = {
                'php': 'PHP',
                'javascript': 'JavaScript',
                'js': 'JavaScript',
                'css': 'CSS',
                'html': 'HTML',
                'sql': 'SQL',
                'python': 'Python',
                'py': 'Python',
                'java': 'Java'
            };

            // Check for common language patterns
            if (firstLine.includes('<?php') || code.includes('function') && code.includes('$')) {
                return 'PHP';
            }
            if (code.includes('function') || code.includes('const') || code.includes('let ') || code.includes('var ')) {
                return 'JavaScript';
            }
            if (code.includes('{') && code.includes('}') && code.includes(':') && !code.includes('$')) {
                return 'CSS';
            }
            if (code.includes('<') && code.includes('>') && (code.includes('div') || code.includes('html'))) {
                return 'HTML';
            }
            if (code.toUpperCase().includes('SELECT') || code.toUpperCase().includes('INSERT') || code.toUpperCase().includes('FROM')) {
                return 'SQL';
            }
            if (code.includes('def ') || code.includes('import ') || code.includes('print(')) {
                return 'Python';
            }
            if (code.includes('public class') || code.includes('import java')) {
                return 'Java';
            }

            return 'Code';
        }

        function applySyntaxHighlighting(code) {
            // Simple regex-based syntax highlighting for PHP/JS-like code (VSCode dark theme inspired)
            let highlighted = code;

            // Comments
            highlighted = highlighted.replace(/\/\/(.*)$/gm, '<span class="hljs-comment">//$1</span>');
            highlighted = highlighted.replace(/\/\*[\s\S]*?\*\//gm, '<span class="hljs-comment">$&</span>');
            // Strings
            highlighted = highlighted.replace(/"([^"\\]*(?:\\.[^"\\]*)*)"|'([^'\\]*(?:\\.[^'\\]*)*)'/gm, '<span class="hljs-string">$&</span>');
            // Keywords
            const keywords = /\b(abstract|as|break|case|catch|class|const|continue|declare|default|do|echo|else|elseif|enddeclare|endforeach|endif|endswitch|endwhile|extends|final|finally|for|foreach|function|global|goto|if|implements|include|include_once|instanceof|insteadof|interface|namespace|new|private|protected|public|require|require_once|return|static|switch|throw|trait|try|use|var|while|yield|__CLASS__|__DIR__|__FILE__|__FUNCTION__|__LINE__|__METHOD__|__NAMESPACE__|__TRAIT__|let|var|async|await|import|export|default|try|catch|finally|throw|new|this|super|class|extends|static|public|private|protected)\b/g;
            highlighted = highlighted.replace(keywords, '<span class="hljs-keyword">$&</span>');
            // Functions
            highlighted = highlighted.replace(/(\bfunction\s+)([\w$]+)\b/g, '$1<span class="hljs-function">$2</span>');
            // Variables
            highlighted = highlighted.replace(/\$[\w]+/g, '<span class="hljs-variable">$&</span>');
            // Numbers
            highlighted = highlighted.replace(/\b\d+\b/g, '<span class="hljs-number">$&</span>');
            // Operators and punctuation
            highlighted = highlighted.replace(/[+*-\/=<>!&|?:;{},.()[\]]/g, '<span class="hljs-operator">$&</span>');
            // Tags like <?php ?>
            highlighted = highlighted.replace(/&lt;\?php|\?&gt;/g, '<span class="hljs-tag">$&</span>');

            return highlighted;
        }

        function copyCode(button) {
            const codeBlock = button.closest('.code-block');
            const codeElement = codeBlock.querySelector('.highlighted-code');
            const originalText = button.querySelector('.copy-text').textContent;
            const originalIcon = button.querySelector('.copy-icon').textContent;

            // Copy to clipboard
            navigator.clipboard.writeText(codeElement.textContent).then(() => {
                // Update button to copied state
                button.querySelector('.copy-icon').textContent = '✓';
                button.querySelector('.copy-text').textContent = 'Copied!';
                button.classList.add('copied');

                // Revert after 2 seconds
                setTimeout(() => {
                    button.querySelector('.copy-icon').textContent = originalIcon;
                    button.querySelector('.copy-text').textContent = originalText;
                    button.classList.remove('copied');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy text: ', err);
                // Keep original state if copy fails
            });
        }

        function appendMessage(role, content, isLoading = false) {
            const div = document.createElement('div');
            div.className = 'message ' + role;
            if (isLoading) {
                div.classList.add('loading');
                div.innerHTML = '✨ AI is thinking...';
            } else {
                // Bold markdown
                content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                // Code blocks with enhanced structure and copy functionality
                content = content.replace(/```([\s\S]*?)```/g, (match, p1) => {
                    const result = highlightCode(p1.trim());
                    return `
                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-language">${result.language}</span>
                                <button class="copy-btn" onclick="copyCode(this)">
                                    <span class="copy-icon">📋</span>
                                    <span class="copy-text">Copy</span>
                                </button>
                            </div>
                            <code class="highlighted-code">${result.code}</code>
                        </div>
                    `;
                });
                // Images
                content = content.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1">');
                div.innerHTML = content;
            }
            chatArea.appendChild(div);
            chatArea.scrollTop = chatArea.scrollHeight;
            return div; // Return div for updating during streaming
        }

        function sendMessage() {
            const content = messageInput.value.trim();
            if (!content || !currentChatId) return;
            appendMessage('user', content);
            fetch('send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ chat_id: currentChatId, content })
            });
            messageInput.value = '';

            // Show loading indicator
            const loadingDiv = appendMessage('assistant', '', true);

            // Stream AI response
            let fullResponse = '';
            fetch('api_proxy.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ chat_id: currentChatId })
            }).then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                const reader = res.body.getReader();
                const decoder = new TextDecoder();
                function read() {
                    reader.read().then(({ done, value }) => {
                        if (done) {
                            // Remove loading class and save if content exists
                            loadingDiv.classList.remove('loading');
                            if (fullResponse.trim()) {
                                fetch('send_message.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ chat_id: currentChatId, content: fullResponse, role: 'assistant' })
                                });
                            } else {
                                loadingDiv.innerHTML = 'Error: No response received.';
                            }
                            return;
                        }
                        const chunk = decoder.decode(value);
                        const lines = chunk.split('\n');
                        lines.forEach(line => {
                            if (line.startsWith('data: ')) {
                                const jsonStr = line.slice(6).trim();
                                if (jsonStr === '[DONE]') return;
                                try {
                                    const data = JSON.parse(jsonStr);
                                    const delta = data.choices?.[0]?.delta?.content || '';
                                    fullResponse += delta;
                                    // Update the div with markdown applied (including enhanced code blocks)
                                    let rendered = fullResponse.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                                        .replace(/```([\s\S]*?)```/g, (match, p1) => {
                                            const result = highlightCode(p1.trim());
                                            return `
                                                <div class="code-block">
                                                    <div class="code-header">
                                                        <span class="code-language">${result.language}</span>
                                                        <button class="copy-btn" onclick="copyCode(this)">
                                                            <span class="copy-icon">📋</span>
                                                            <span class="copy-text">Copy</span>
                                                        </button>
                                                    </div>
                                                    <code class="highlighted-code">${result.code}</code>
                                                </div>
                                            `;
                                        })
                                        .replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1">');
                                    loadingDiv.innerHTML = rendered;
                                } catch (err) {
                                    console.error('Parse error:', err);
                                }
                            }
                        });
                        chatArea.scrollTop = chatArea.scrollHeight;
                        read();
                    }).catch(err => {
                        console.error('Stream error:', err);
                        loadingDiv.innerHTML = 'Error: ' + err.message;
                    });
                }
                read();
            }).catch(err => {
                console.error(err);
                loadingDiv.innerHTML = 'Error: Network issue.';
            });
        }

        function showQuote() {
            fetch('get_quote.php')
                .then(res => res.json())
                .then(data => {
                    quoteText.textContent = data.quote;
                    quoteAuthor.textContent = `- ${data.author}`;
                    quotePopup.style.display = 'block';
                });
        }

        closeQuote.onclick = () => { quotePopup.style.display = 'none'; };

        sendBtn.onclick = sendMessage;
        messageInput.onkeydown = (e) => { if (e.key === 'Enter') sendMessage(); };
        newChatBtn.onclick = createNewChat;
        quoteBtn.onclick = showQuote;

        // Initial load
        loadChats();
    </script>
</body>
</html>