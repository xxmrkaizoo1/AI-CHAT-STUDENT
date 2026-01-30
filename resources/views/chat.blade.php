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
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial;
            background: #f4f6fb;
            margin: 0;
            padding: 0;
        }

        .app {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
            display: flex;
            flex-direction: column;
            height: 90vh;
            overflow: hidden;
        }

        .header {
            padding: 16px 20px;
            border-bottom: 1px solid #eee;
        }

        .header h2 {
            margin: 0;
            font-size: 20px;
        }

        .header small {
            opacity: .7;
        }

        #chat {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #fafbff;
        }

        .bubble {
            max-width: 75%;
            padding: 12px 14px;
            border-radius: 14px;
            margin-bottom: 12px;
            white-space: pre-wrap;
            line-height: 1.45;
            font-size: 14px;
        }

        .me {
            background: #e9eaff;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }

        .ai {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            margin-right: auto;
            border-bottom-left-radius: 4px;
        }

        .input-area {
            border-top: 1px solid #eee;
            padding: 12px;
            background: #fff;
            display: flex;
            gap: 10px;
        }

        .input-area input {
            flex: 1;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        .input-area input:disabled {
            background: #f1f1f1;
        }

        .input-area button {
            padding: 12px 18px;
            border-radius: 12px;
            border: none;
            font-weight: 500;
            cursor: pointer;
        }

        #sendBtn {
            background: #4f46e5;
            color: #fff;
        }

        #stopBtn {
            background: #ef4444;
            color: #fff;
            display: none;
        }

        button:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .clear {
            padding: 10px;
            text-align: center;
            font-size: 12px;
        }

        .clear button {
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
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

        stopBtn.addEventListener('click', () => {
            if (controller) {
                controller.abort(); // ⛔ stop generation
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
                let result = "";

                while (true) {
                    const {
                        value,
                        done
                    } = await reader.read();
                    if (done) break;

                    result += decoder.decode(value, {
                        stream: true
                    });

                    aiDiv.innerHTML = renderMarkdownSafe(result);

                    // 🎨 apply syntax highlighting
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
