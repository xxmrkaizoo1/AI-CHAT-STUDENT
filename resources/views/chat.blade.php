<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Student AI Chat</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- highlight.js for code syntax coloring -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/highlight.min.js"></script>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial;
            background: linear-gradient(135deg, #eef2ff, #f8fafc);
            margin: 0;
            padding: 0;
        }

        .app {
            max-width: 920px;
            margin: 32px auto;
            background: #ffffffcc;
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .12);
            display: flex;
            flex-direction: column;
            height: 92vh;
            overflow: hidden;
        }

        /* Header */
        .header {
            padding: 18px 22px;
            border-bottom: 1px solid rgba(0, 0, 0, .05);
            background: linear-gradient(90deg, #4f46e5, #6366f1);
            color: white;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .header small {
            opacity: .85;
        }

        #chat {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
            background: linear-gradient(#f8fafc, #f1f5f9);
        }


        .bubble {
            max-width: 72%;
            padding: 14px 16px;
            border-radius: 16px;
            margin-bottom: 14px;
            line-height: 1.55;
            font-size: 14px;
            animation: fadeUp .25s ease;
            word-wrap: break-word;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .me {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 6px;
        }

        .ai {
            background: white;
            border: 1px solid #e5e7eb;
            margin-right: auto;
            border-bottom-left-radius: 6px;
        }

        /* Input area */
        .input-area {
            border-top: 1px solid #e5e7eb;
            padding: 14px;
            background: #ffffff;
            display: flex;
            gap: 10px;
        }

        .input-area input {
            flex: 1;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            font-size: 14px;
            outline: none;
        }

        .input-area input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .15);
        }

        .input-area input:disabled {
            background: #f1f5f9;
        }

        /* Buttons */
        .input-area button {
            padding: 14px 18px;
            border-radius: 14px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all .2s ease;
        }

        #sendBtn {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
        }

        #sendBtn:hover {
            transform: translateY(-1px);
        }

        #stopBtn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            display: none;
        }

        button:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        /* Clear */
        .clear {
            padding: 10px;
            text-align: center;
            font-size: 12px;
            background: #f8fafc;
        }

        .clear button {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
        }

        /* Cursor */
        .cursor {
            display: inline-block;
            margin-left: 3px;
            font-weight: bold;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }
    </style>

</head>

<body>

    <div class="app">
        <div class="header">
            <h2>🎓 Student AI Chatbot</h2>
            <small>Local AI • Ollama • Streaming</small>
        </div>

        <div id="chat">
            @foreach ($messages as $m)
                <div class="bubble {{ $m->role === 'user' ? 'me' : 'ai' }}">
                    {{ $m->content }}
                </div>
            @endforeach
        </div>

        <form id="form" class="input-area">
            <input id="msg" placeholder="Ask a study question..." autocomplete="off" />
            <button id="sendBtn" type="submit">Send</button>
            <button id="stopBtn" type="button">⛔ Stop</button>
        </form>

        <form method="POST" action="/chat/clear" class="clear">
            @csrf
            <button type="submit">Clear chat</button>
        </form>
    </div>
    <script>
        const chat = document.getElementById('chat');
        const form = document.getElementById('form');
        const msg = document.getElementById('msg');
        const sendBtn = document.getElementById('sendBtn');
        const stopBtn = document.getElementById('stopBtn');

        let controller = null; // AbortController
        const cursor = '<span class="cursor">|</span>';

        function addBubble(text, cls) {
            const div = document.createElement('div');
            div.className = 'bubble ' + cls;
            div.innerHTML = text;
            chat.appendChild(div);
            chat.scrollTop = chat.scrollHeight;
            return div;
        }

        function escapeHtml(str) {
            return (str ?? "").toString()
                .replaceAll("&", "&amp;")
                .replaceAll("<", "&lt;")
                .replaceAll(">", "&gt;")
                .replaceAll('"', "&quot;")
                .replaceAll("'", "&#039;");
        }

        // ✅ MARKDOWN + CODE HIGHLIGHT (SAFE)
        function renderMarkdownSafe(text) {
            let s = escapeHtml(text);

            // ```language\ncode```
            s = s.replace(/```(\w+)?\n([\s\S]*?)```/g, (match, lang, code) => {
                const l = (lang || "plaintext").toLowerCase();
                return `
<pre style="background:#0b1020;color:#e5e7eb;padding:12px;border-radius:12px;overflow:auto;">
<code class="language-${l}">${code}</code>
</pre>`;
            });

            // inline code `code`
            s = s.replace(/`([^`]+)`/g,
            `<code style="background:#f1f5f9;padding:2px 6px;border-radius:6px;">$1</code>`
        );

        // bold **text**
        s = s.replace(/\*\*([^*]+)\*\*/g, `<b>$1</b>`);

        // italic *text*
        s = s.replace(/\*([^*]+)\*/g, `<i>$1</i>`);

            // new lines
            s = s.replace(/\n/g, "<br>");

            return s;
        }

        // ⛔ STOP BUTTON
        stopBtn.addEventListener('click', () => {
            if (controller) {
                controller.abort();
                controller = null;
            }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const text = msg.value.trim();
            if (!text) return;

            // 🔒 lock UI
            msg.disabled = true;
            sendBtn.disabled = true;
            sendBtn.style.display = "none";
            stopBtn.style.display = "inline-block";

            addBubble(renderMarkdownSafe(text), "me");
            msg.value = "";

            const aiDiv = addBubble("", "ai");

            controller = new AbortController();
            let result = "";

            try {
                const res = await fetch("/chat", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        message: text
                    }),
                    signal: controller.signal
                });

                const reader = res.body.getReader();
                const decoder = new TextDecoder();

                while (true) {
                    const {
                        value,
                        done
                    } = await reader.read();
                    if (done) break;

                    result += decoder.decode(value, {
                        stream: true
                    });

                    // ✨ show cursor while typing
                    aiDiv.innerHTML = renderMarkdownSafe(result) + cursor;

                    // 🎨 syntax highlighting
                    aiDiv.querySelectorAll('pre code').forEach(block => {
                        hljs.highlightElement(block);
                    });

                    chat.scrollTop = chat.scrollHeight;
                }

            } catch (err) {
                if (err.name !== "AbortError") {
                    aiDiv.innerHTML = "⚠️ Server error";
                }
            } finally {
                controller = null;

                // ✅ remove cursor when finished / stopped
                aiDiv.innerHTML = renderMarkdownSafe(result);

                // 🎨 final highlight pass
                aiDiv.querySelectorAll('pre code').forEach(block => {
                    hljs.highlightElement(block);
                });

                // 🔓 unlock UI
                msg.disabled = false;
                sendBtn.disabled = false;
                sendBtn.style.display = "inline-block";
                stopBtn.style.display = "none";
                msg.focus();
            }
        });
    </script>



</body>

</html>
