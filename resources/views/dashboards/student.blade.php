<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Student Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial;
      background:#f8fafc;
      color:#0f172a;
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:24px;
    }
    .card{
      background:#fff;
      border:1px solid #e2e8f0;
      border-radius:18px;
      padding:28px;
      max-width:520px;
      width:100%;
      box-shadow:0 20px 45px rgba(15, 23, 42, 0.1);
      text-align:center;
    }
    .card h1{
      margin:0 0 12px;
      font-size:24px;
    }
    .card p{
      margin:0 0 20px;
      color:#475569;
      font-size:14px;
      line-height:1.6;
    }
    .btn{
      display:inline-block;
      padding:10px 16px;
      border-radius:10px;
      text-decoration:none;
      font-weight:600;
      background:linear-gradient(135deg,#22c55e,#16a34a);
      color:#fff;
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>Student Dashboard</h1>
    <p>You are logged in as a student. Continue to the study chat to ask questions.</p>
    <a class="btn" href="/chat">Go to Student Chat</a>
  </div>
</body>
</html>
