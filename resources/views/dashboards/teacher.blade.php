<!doctype html>
<html>
<head><meta charset="utf-8"><title>Teacher Dashboard</title></head>
<body style="font-family:system-ui;margin:30px;">
  <h2>👩‍🏫 Teacher Dashboard</h2>
  <p>Hi, {{ auth()->user()->name }}</p>

  <a href="/teacher/chats">View Student Chats + Statistics</a>

  <form method="POST" action="/logout" style="margin-top:20px;">
    @csrf
    <button type="submit">Logout</button>
  </form>
</body>
</html>
