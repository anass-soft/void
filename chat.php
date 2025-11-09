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
            position: relative;
            overflow: hidden;
        }

        /* Neural Network Canvas */
        #neural-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            opacity: 0;
            transition: opacity 0.5s ease;
            pointer-events: none;
        }

        #neural-canvas.active {
            opacity: 0.3;
        }

        #chat-area {
            flex: 1;
            overflow-y: auto;
            padding: 32px;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
            scroll-behavior: smooth;
        }

        /* Custom Scrollbar */
        #chat-area::-webkit-scrollbar {
            width: 8px;
        }

        #chat-area::-webkit-scrollbar-track {
            background: #0a0a0a;
        }

        #chat-area::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
        }

        #chat-area::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        .message {
            margin-bottom: 24px;
            max-width: 90%;
            animation: fadeInUp 0.3s ease-out;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .message:hover {
            transform: translateZ(10px);
        }

        .message .message-actions {
            position: absolute;
            top: 8px;
            right: 8px;
            display: flex;
            gap: 6px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .message:hover .message-actions {
            opacity: 1;
        }

        .action-btn {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            padding: 4px 8px;
            color: #e0e0e0;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            background: rgba(102, 126, 234, 0.3);
            border-color: #667eea;
            transform: scale(1.1);
        }

        .action-btn.bookmarked {
            background: rgba(255, 215, 0, 0.2);
            border-color: #ffd700;
            color: #ffd700;
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
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 0.5;
            }
            50% {
                opacity: 0.9;
            }
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

        /* Stats Panel */
        #stats-panel {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 300px;
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 16px;
            padding: 0;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        #stats-panel.hidden {
            transform: translateX(350px);
            opacity: 0;
            pointer-events: none;
        }

        .stats-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #2a2a2a;
        }

        .stats-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .close-stats {
            background: transparent;
            border: none;
            color: #e0e0e0;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            line-height: 20px;
            transition: all 0.2s ease;
        }

        .close-stats:hover {
            color: #ff4757;
            transform: rotate(90deg);
        }

        .stats-content {
            padding: 20px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #2a2a2a;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: #b0b0b0;
            font-size: 14px;
        }

        .stat-value {
            color: #667eea;
            font-size: 18px;
            font-weight: 700;
        }

        /* Floating Action Menu */
        #floating-menu {
            position: fixed;
            bottom: 120px;
            right: 32px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 999;
        }

        .fab-btn {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(26, 26, 26, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(102, 126, 234, 0.3);
            color: #e0e0e0;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fab-btn:hover {
            transform: scale(1.1) translateY(-4px);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }

        .fab-btn:active {
            transform: scale(0.95);
        }

        .hidden {
            display: none;
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
            #stats-panel {
                width: calc(100% - 40px);
                right: 20px;
                left: 20px;
            }
            #floating-menu {
                bottom: 100px;
                right: 20px;
            }
            .fab-btn {
                width: 48px;
                height: 48px;
                font-size: 18px;
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
        <canvas id="neural-canvas"></canvas>
        <div id="chat-area"></div>
        <div id="stats-panel" class="hidden">
            <div class="stats-header">
                <h3>📊 Conversation Stats</h3>
                <button class="close-stats" onclick="toggleStats()">×</button>
            </div>
            <div class="stats-content">
                <div class="stat-item">
                    <span class="stat-label">Messages</span>
                    <span class="stat-value" id="stat-messages">0</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Est. Tokens</span>
                    <span class="stat-value" id="stat-tokens">0</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Avg Response Time</span>
                    <span class="stat-value" id="stat-time">0.0s</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Bookmarks</span>
                    <span class="stat-value" id="stat-bookmarks">0</span>
                </div>
            </div>
        </div>
        <div id="floating-menu">
            <button class="fab-btn" onclick="toggleStats()" title="Statistics">📊</button>
            <button class="fab-btn" onclick="exportChat('markdown')" title="Export as Markdown">📄</button>
            <button class="fab-btn" onclick="exportChat('json')" title="Export as JSON">💾</button>
            <button class="fab-btn" onclick="toggleTheme()" title="Toggle Theme">🎨</button>
        </div>
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
            messageInput.style.height = 'auto';

            // Show loading indicator with thinking counter
            const loadingDiv = appendMessage('assistant', '', true);
            let thinkingTime = 0;
            let thinkingInterval = setInterval(() => {
                thinkingTime += 0.1;
                loadingDiv.innerHTML = `✨ AI is thinking... (${thinkingTime.toFixed(1)}s)`;
            }, 100);

            // Stream AI response
            let fullResponse = '';
            let firstTokenReceived = false;
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
                            // Stop thinking counter
                            clearInterval(thinkingInterval);
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
                                    if (delta && !firstTokenReceived) {
                                        // First token received, stop thinking counter
                                        clearInterval(thinkingInterval);
                                        firstTokenReceived = true;
                                    }
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
                        clearInterval(thinkingInterval);
                        console.error('Stream error:', err);
                        loadingDiv.innerHTML = 'Error: ' + err.message;
                    });
                }
                read();
            }).catch(err => {
                clearInterval(thinkingInterval);
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

        // Auto-resize textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        sendBtn.onclick = sendMessage;
        messageInput.onkeydown = (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        };
        newChatBtn.onclick = createNewChat;
        quoteBtn.onclick = showQuote;

        // Stats tracking
        let messageCount = 0;
        let totalTokens = 0;
        let responseTimes = [];
        let bookmarkedMessages = new Set();

        function updateStats() {
            document.getElementById('stat-messages').textContent = messageCount;
            document.getElementById('stat-tokens').textContent = totalTokens;
            const avgTime = responseTimes.length > 0
                ? (responseTimes.reduce((a, b) => a + b, 0) / responseTimes.length).toFixed(1)
                : '0.0';
            document.getElementById('stat-time').textContent = avgTime + 's';
            document.getElementById('stat-bookmarks').textContent = bookmarkedMessages.size;
        }

        function estimateTokens(text) {
            return Math.ceil(text.split(/\s+/).length * 1.3);
        }

        function toggleStats() {
            document.getElementById('stats-panel').classList.toggle('hidden');
        }

        // Neural Network Canvas Animation
        const canvas = document.getElementById('neural-canvas');
        const ctx = canvas.getContext('2d');
        let particles = [];
        let animationId = null;

        function resizeCanvas() {
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
        }

        function createParticles() {
            particles = [];
            const particleCount = 50;
            for (let i = 0; i < particleCount; i++) {
                particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    vx: (Math.random() - 0.5) * 0.5,
                    vy: (Math.random() - 0.5) * 0.5,
                    radius: Math.random() * 2 + 1
                });
            }
        }

        function animateNeuralNetwork() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Update and draw particles
            particles.forEach(p => {
                p.x += p.vx;
                p.y += p.vy;

                if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
                if (p.y < 0 || p.y > canvas.height) p.vy *= -1;

                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                ctx.fillStyle = '#667eea';
                ctx.fill();
            });

            // Draw connections
            particles.forEach((p1, i) => {
                particles.slice(i + 1).forEach(p2 => {
                    const dist = Math.hypot(p1.x - p2.x, p1.y - p2.y);
                    if (dist < 150) {
                        ctx.beginPath();
                        ctx.moveTo(p1.x, p1.y);
                        ctx.lineTo(p2.x, p2.y);
                        ctx.strokeStyle = `rgba(102, 126, 234, ${1 - dist / 150})`;
                        ctx.lineWidth = 1;
                        ctx.stroke();
                    }
                });
            });

            animationId = requestAnimationFrame(animateNeuralNetwork);
        }

        function startNeuralAnimation() {
            canvas.classList.add('active');
            if (!animationId) {
                resizeCanvas();
                createParticles();
                animateNeuralNetwork();
            }
        }

        function stopNeuralAnimation() {
            canvas.classList.remove('active');
            if (animationId) {
                cancelAnimationFrame(animationId);
                animationId = null;
            }
        }

        window.addEventListener('resize', () => {
            if (animationId) {
                resizeCanvas();
                createParticles();
            }
        });

        // Export Functions
        function exportChat(format) {
            const messages = Array.from(document.querySelectorAll('.message'));
            const chatData = messages.map(msg => {
                const role = msg.classList.contains('user') ? 'User' : 'AI';
                const content = msg.textContent.replace(/^(AI: |User: )/, '').trim();
                return { role, content };
            });

            if (format === 'json') {
                const json = JSON.stringify(chatData, null, 2);
                downloadFile(json, 'chat-export.json', 'application/json');
            } else if (format === 'markdown') {
                let markdown = '# Chat Export\n\n';
                markdown += `**Date:** ${new Date().toLocaleString()}\n\n`;
                markdown += `**Total Messages:** ${chatData.length}\n\n---\n\n`;
                chatData.forEach(msg => {
                    markdown += `## ${msg.role}\n\n${msg.content}\n\n---\n\n`;
                });
                downloadFile(markdown, 'chat-export.md', 'text/markdown');
            }
        }

        function downloadFile(content, filename, mimeType) {
            const blob = new Blob([content], { type: mimeType });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        // Theme Toggle
        let currentTheme = 'default';
        function toggleTheme() {
            const themes = {
                'default': { bg: '#0a0a0a', accent: '#667eea', secondary: '#764ba2' },
                'ocean': { bg: '#001f3f', accent: '#0074D9', secondary: '#7FDBFF' },
                'forest': { bg: '#0d1b0d', accent: '#2ECC40', secondary: '#01FF70' },
                'sunset': { bg: '#1a0a00', accent: '#FF4136', secondary: '#FF851B' }
            };

            const themeNames = Object.keys(themes);
            const currentIndex = themeNames.indexOf(currentTheme);
            currentTheme = themeNames[(currentIndex + 1) % themeNames.length];

            const theme = themes[currentTheme];
            document.documentElement.style.setProperty('--bg-color', theme.bg);
            document.documentElement.style.setProperty('--accent-1', theme.accent);
            document.documentElement.style.setProperty('--accent-2', theme.secondary);
        }

        // Message Actions
        function toggleBookmark(messageElement, button) {
            const messageId = messageElement.dataset.messageId || Date.now();
            messageElement.dataset.messageId = messageId;

            if (bookmarkedMessages.has(messageId)) {
                bookmarkedMessages.delete(messageId);
                button.classList.remove('bookmarked');
                button.textContent = '⭐';
            } else {
                bookmarkedMessages.add(messageId);
                button.classList.add('bookmarked');
                button.textContent = '★';
            }
            updateStats();
        }

        function copyMessage(button) {
            const message = button.closest('.message');
            const text = message.textContent.replace(/^(✨ AI: |AI: |User: )/, '').replace(/⭐📋/g, '').trim();
            navigator.clipboard.writeText(text).then(() => {
                const originalText = button.textContent;
                button.textContent = '✓';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            });
        }

        // Initialize
        resizeCanvas();
        updateStats();

        // Initial load
        loadChats();
    </script>
</body>
</html>