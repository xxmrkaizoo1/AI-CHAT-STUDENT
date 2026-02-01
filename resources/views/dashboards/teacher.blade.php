<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Teacher Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial;
      background:#fff7ed;
      color:#7c2d12;
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:24px;
    }
    .card{
      background:#fff;
      border:1px solid #fed7aa;
      border-radius:18px;
      padding:28px;
      max-width:520px;
      width:100%;
      box-shadow:0 20px 45px rgba(124, 45, 18, 0.12);
      text-align:center;
    }
    .card h1{
      margin:0 0 12px;
      font-size:24px;
    }
    .card p{
      margin:0 0 20px;
      color:#9a3412;
      font-size:14px;
      line-height:1.6;
    }
    .btn{
      display:inline-block;
      padding:10px 16px;
      border-radius:10px;
      text-decoration:none;
      font-weight:600;
      background:linear-gradient(135deg,#f59e0b,#d97706);
      color:#fff;
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>Teacher Dashboard</h1>
    <p>You are logged in as a teacher. Continue to the chat to monitor student questions.</p>
    <a class="btn" href="/chat">Go to Teacher Chat</a>
  </div>
</body>
</html>
