<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Teacher Dashboard - Session</title>
  <style>
    body{font-family:system-ui,Arial;margin:20px;background:#f8fafc;}
    .box{max-width:900px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;}
    .msg{padding:12px;border-radius:12px;margin:10px 0;white-space:pre-wrap;}
    .user{background:#eef2ff;}
    .ai{background:#f8fafc;border:1px solid #eee;}
    a{color:#4f46e5;text-decoration:none;}
    .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;}
    .id{font-family:monospace;font-size:12px;opacity:.7;}
  </style>
</head>
<body>
  <div class="box">
    <div class="top">
      <div>
        <h2 style="margin:0">🗂️ Session Messages</h2>
        <div class="id">Session: {{ $sessionId }}</div>
      </div>
      <a href="/teacher/chats">← Back</a>
    </div>

    @foreach($messages as $m)
      <div class="msg {{ $m->role === 'user' ? 'user' : 'ai' }}">
        <b>{{ $m->role === 'user' ? 'Student' : 'AI' }}:</b>
        {{ $m->content }}
      </div>
    @endforeach
  </div>
</body>
</html>
