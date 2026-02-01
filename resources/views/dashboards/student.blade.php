<!doctype html>
<html>
<head><meta charset="utf-8"><title>Student Dashboard</title></head>
<body style="font-family:system-ui;margin:30px;">
  <h2>👨‍🎓 Student Dashboard</h2>
  <p>Hi, {{ auth()->user()->name }}</p>

  <a href="/chat">Open Chatbot</a>

  <form method="POST" action="/logout" style="margin-top:20px;">
    @csrf
    <button type="submit">Logout</button>
  </form>
</body>
</html>
