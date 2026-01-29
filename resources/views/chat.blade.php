<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Student AI Chat</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <style>
    body{font-family:Arial; max-width:850px; margin:30px auto;}
    .box{border:1px solid #ddd; padding:12px; border-radius:10px; margin:10px 0; white-space:pre-wrap;}
    .me{background:#f5f5f5;}
    .ai{background:#eef7ff;}
    .row{display:flex; gap:10px; margin-top:15px;}
    input{flex:1; padding:10px; border:1px solid #ccc; border-radius:10px;}
    button{padding:10px 14px; border:0; border-radius:10px; cursor:pointer;}
    button:disabled{opacity:0.6; cursor:not-allowed;}
    .small{font-size:12px; opacity:.7;}
  </style>
</head>
<body>

  <h2>🎓 Student AI Chatbot (Ollama Streaming)</h2>
  <div class="small">AI will type the answer live (word-by-word)</div>

  <div id="chat">
    @foreach($messages as $m)
      <div class="box {{ $m->role === 'user' ? 'me' : 'ai' }}">
        <b>{{ $m->role === 'user' ? 'You' : 'AI' }}:</b>
        {{ $m->content }}
      </div>
    @endforeach
  </div>

  <form id="form" class="row">
    <input id="msg" placeholder="Ask anything..." autocomplete="off" />
    <button id="sendBtn" type="submit">Send</button>
  </form>

  <form method="POST" action="/chat/clear" style="margin-top:10px;">
    @csrf
    <button type="submit">Clear Chat</button>
  </form>

  <script>
    const chat = document.getElementById('chat');
    const form = document.getElementById('form');
    const msg = document.getElementById('msg');
    const sendBtn = document.getElementById('sendBtn');

    function addBubble(text, cls){
      const div = document.createElement('div');
      div.className = 'box ' + cls;
      div.innerHTML = text;
      chat.appendChild(div);
      window.scrollTo(0, document.body.scrollHeight);
      return div;
    }

    function escapeHtml(str){
      return (str ?? "")
        .toString()
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const text = msg.value.trim();
      if(!text) return;

      // 🔒 Disable input & button while generating
      msg.disabled = true;
      sendBtn.disabled = true;
      sendBtn.innerText = "Generating...";

      addBubble("<b>You:</b> " + escapeHtml(text), "me");
      msg.value = "";

      // Create empty AI bubble
      const aiDiv = addBubble("<b>AI:</b> ", "ai");

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

          const chunk = decoder.decode(value, { stream: true });
          result += chunk;

          aiDiv.innerHTML = "<b>AI:</b> " + escapeHtml(result);
          window.scrollTo(0, document.body.scrollHeight);
        }

      } catch (err) {
        aiDiv.innerHTML = "<b>AI:</b> Server error";
      } finally {
        // 🔓 Enable input & button after finished
        msg.disabled = false;
        sendBtn.disabled = false;
        sendBtn.innerText = "Send";
        msg.focus();
      }
    });

    window.scrollTo(0, document.body.scrollHeight);
  </script>

</body>
</html>
