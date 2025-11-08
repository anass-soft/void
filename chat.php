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
        /* Dark theme CSS */
        body { margin: 0; font-family: Arial, sans-serif; background: #121212; color: #e0e0e0; display: flex; height: 100vh; }
        #sidebar { width: 250px; background: #1e1e1e; padding: 20px; overflow-y: auto; border-right: 1px solid #333; }
        #chat-list { list-style: none; padding: 0; }
        #chat-list li { padding: 10px; cursor: pointer; border-bottom: 1px solid #333; }
        #chat-list li:hover { background: #333; }
        .delete-btn { float: right; color: red; cursor: pointer; }
        #main { flex: 1; display: flex; flex-direction: column; }
        #chat-area { flex: 1; overflow-y: auto; padding: 20px; }
        .message { margin-bottom: 15px; max-width: 80%; }
        .user { align-self: flex-end; background: #4caf50; color: white; padding: 10px; border-radius: 10px; }
        .assistant { align-self: flex-start; background: #333; padding: 10px; border-radius: 10px; }
        .assistant::before { content: 'AI: '; font-weight: bold; }
        pre { background: #1e1e1e; padding: 10px; border-radius: 5px; overflow-x: auto; margin: 5px 0; }
        code { font-family: monospace; color: #e0e0e0; white-space: pre-wrap; }
        .loading { font-style: italic; color: #888; }
        @media (max-width: 768px) { #sidebar { width: 100%; position: absolute; z-index: 1; display: none; } #main { flex-direction: column; } }
        
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
        <button id="new-chat-btn">New Chat</button>
        <button id="quote-btn">Show Quote</button>
        <button id="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
        <ul id="chat-list"></ul>
    </div>
    <div id="main">
        <div id="chat-area"></div>
        <div id="input-area">
            <input id="message-input" placeholder="Type your message...">
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
            // Simple regex-based syntax highlighting for PHP/JS-like code (VSCode dark theme inspired)
            // Comments
            code = code.replace(/\/\/(.*)$/gm, '<span class="hljs-comment">//$1</span>');
            code = code.replace(/\/\*[\s\S]*?\*\//gm, '<span class="hljs-comment">$&</span>');
            // Strings
            code = code.replace(/"([^"\\]*(?:\\.[^"\\]*)*)"|'([^'\\]*(?:\\.[^'\\]*)*)'/gm, '<span class="hljs-string">$&</span>');
            // Keywords
            const keywords = /\b(abstract|as|break|case|catch|class|const|continue|declare|default|do|echo|else|elseif|enddeclare|endforeach|endif|endswitch|endwhile|extends|final|finally|for|foreach|function|global|goto|if|implements|include|include_once|instanceof|insteadof|interface|namespace|new|private|protected|public|require|require_once|return|static|switch|throw|trait|try|use|var|while|yield|__CLASS__|__DIR__|__FILE__|__FUNCTION__|__LINE__|__METHOD__|__NAMESPACE__|__TRAIT__)\b/g;
            code = code.replace(keywords, '<span class="hljs-keyword">$&</span>');
            // Functions
            code = code.replace(/(\bfunction\s+)([\w$]+)\b/g, '$1<span class="hljs-function">$2</span>');
            // Variables
            code = code.replace(/\$[\w]+/g, '<span class="hljs-variable">$&</span>');
            // Numbers
            code = code.replace(/\b\d+\b/g, '<span class="hljs-number">$&</span>');
            // Operators and punctuation
            code = code.replace(/[+*-\/=<>!&|?:;{},.()[\]]/g, '<span class="hljs-operator">$&</span>');
            // Tags like <?php ?>
            code = code.replace(/&lt;\?php|\?&gt;/g, '<span class="hljs-tag">$&</span>');
            return code;
        }

        function appendMessage(role, content, isLoading = false) {
            const div = document.createElement('div');
            div.className = 'message ' + role;
            if (isLoading) {
                div.classList.add('loading');
                div.innerHTML = 'AI is thinking...';
            } else {
                // Bold markdown
                content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                // Code blocks with highlighting
                content = content.replace(/```([\s\S]*?)```/g, (match, p1) => {
                    const highlighted = highlightCode(p1.trim());
                    return `<pre><code>${highlighted}</code></pre>`;
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
                                    // Update the div with markdown applied (including highlighting for code)
                                    let rendered = fullResponse.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                                        .replace(/```([\s\S]*?)```/g, (match, p1) => {
                                            const highlighted = highlightCode(p1.trim());
                                            return `<pre><code>${highlighted}</code></pre>`;
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