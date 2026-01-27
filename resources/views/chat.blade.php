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
    .small{font-size:12px; opacity:.7;}
  </style>
</head>
<body>

  <h2>🎓 Student AI Chatbot (My Own AI)</h2>
  <div class="small">Type a message → your local AI will classify & reply</div>

  {{-- Chat history --}}
  <div id="chat">
    @foreach($messages as $m)
      <div class="box {{ $m->role === 'user' ? 'me' : 'ai' }}">
        <b>{{ $m->role === 'user' ? 'You' : 'AI' }}:</b>
        {{ $m->content }}
      </div>
    @endforeach
  </div>

  {{-- Input --}}
  <form id="form" class="row">
    <input id="msg" placeholder="Ask anything..." autocomplete="off" />
    <button type="submit">Send</button>
  </form>

  {{-- Clear chat --}}
  <form method="POST" action="/chat/clear" style="margin-top:10px;">
    @csrf
    <button type="submit">Clear Chat</button>
  </form>

  <script>
    const chat = document.getElementById('chat');
    const form = document.getElementById('form');
    const msg = document.getElementById('msg');

    function addBubble(text, cls){
      const div = document.createElement('div');
      div.className = 'box ' + cls;
      div.innerHTML = text;
      chat.appendChild(div);
      window.scrollTo(0, document.body.scrollHeight);
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const text = msg.value.trim();
      if(!text) return;

      // show user message
      addBubble("<b>You:</b> " + escapeHtml(text), "me");
      msg.value = "";

      // loading bubble
      const loading = document.createElement('div');
      loading.className = 'box ai';
      loading.innerHTML = "<b>AI:</b> typing...";
      chat.appendChild(loading);

      try {
        const res = await fetch("/chat", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({ message: text })
        });

        const data = await res.json();
        loading.remove();

        // show label if exists
        const label = data.label ? (" (" + escapeHtml(data.label) + ")") : "";
        addBubble("<b>AI" + label + ":</b> " + escapeHtml(data.reply ?? "Error"), "ai");

      } catch (err) {
        loading.remove();
        addBubble("<b>AI:</b> Server error", "ai");
      }
    });

    function escapeHtml(str){
      return (str ?? "")
        .toString()
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
    }

    // auto scroll on load
    window.scrollTo(0, document.body.scrollHeight);
  </script>

</body>
</html>
