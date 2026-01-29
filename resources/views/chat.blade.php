<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Student AI Chat</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <style>
    *{box-sizing:border-box;}
    body{
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial;
      background:#f4f6fb;
      margin:0;
      padding:0;
    }

    .app{
      max-width:900px;
      margin:30px auto;
      background:#fff;
      border-radius:16px;
      box-shadow:0 10px 30px rgba(0,0,0,.08);
      display:flex;
      flex-direction:column;
      height:90vh;
      overflow:hidden;
    }

    .header{
      padding:16px 20px;
      border-bottom:1px solid #eee;
    }
    .header h2{margin:0;font-size:20px;}
    .header small{opacity:.7;}

    #chat{
      flex:1;
      padding:20px;
      overflow-y:auto;
      background:#fafbff;
    }

    .bubble{
      max-width:75%;
      padding:12px 14px;
      border-radius:14px;
      margin-bottom:12px;
      white-space:pre-wrap;
      line-height:1.45;
      font-size:14px;
    }

    .me{
      background:#e9eaff;
      margin-left:auto;
      border-bottom-right-radius:4px;
    }

    .ai{
      background:#ffffff;
      border:1px solid #e5e7eb;
      margin-right:auto;
      border-bottom-left-radius:4px;
    }

    .input-area{
      border-top:1px solid #eee;
      padding:12px;
      background:#fff;
      display:flex;
      gap:10px;
    }

    .input-area input{
      flex:1;
      padding:12px 14px;
      border-radius:12px;
      border:1px solid #ddd;
      font-size:14px;
    }

    .input-area input:disabled{
      background:#f1f1f1;
    }

    .input-area button{
      padding:12px 18px;
      border-radius:12px;
      border:none;
      background:#4f46e5;
      color:#fff;
      font-weight:500;
      cursor:pointer;
    }

    .input-area button:disabled{
      background:#a5a5a5;
      cursor:not-allowed;
    }

    .clear{
      padding:10px;
      text-align:center;
      font-size:12px;
    }
    .clear button{
      background:none;
      border:none;
      color:#888;
      cursor:pointer;
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
    @foreach($messages as $m)
      <div class="bubble {{ $m->role === 'user' ? 'me' : 'ai' }}">
        {{ $m->content }}
      </div>
    @endforeach
  </div>

  <form id="form" class="input-area">
    <input id="msg" placeholder="Ask a study question..." autocomplete="off" />
    <button id="sendBtn" type="submit">Send</button>
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

  function addBubble(text, cls){
    const div = document.createElement('div');
    div.className = 'bubble ' + cls;
    div.innerHTML = text;
    chat.appendChild(div);
    chat.scrollTop = chat.scrollHeight;
    return div;
  }

  function escapeHtml(str){
    return (str ?? "").toString()
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;");
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const text = msg.value.trim();
    if(!text) return;

    msg.disabled = true;
    sendBtn.disabled = true;
    sendBtn.innerText = "Thinking...";

    addBubble(escapeHtml(text), "me");
    msg.value = "";

    const aiDiv = addBubble("", "ai");

    try {
      const res = await fetch("/chat", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ message: text })
      });

      const reader = res.body.getReader();
      const decoder = new TextDecoder();
      let result = "";

      while (true) {
        const { value, done } = await reader.read();
        if (done) break;

        result += decoder.decode(value, { stream:true });
        aiDiv.innerHTML = escapeHtml(result);
        chat.scrollTop = chat.scrollHeight;
      }

    } catch {
      aiDiv.innerHTML = "⚠️ Server error";
    } finally {
      msg.disabled = false;
      sendBtn.disabled = false;
      sendBtn.innerText = "Send";
      msg.focus();
    }
  });
</script>

</body>
</html>
