<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Teacher Dashboard - Sessions</title>
    <style>
        body {
            font-family: system-ui, Arial;
            margin: 20px;
            background: #f8fafc;
        }

        .box {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 14px;
        }

        a {
            color: #4f46e5;
            text-decoration: none;
        }

        .top {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 10px;
            width: 280px;
        }

        button {
            padding: 10px 14px;
            border: 0;
            border-radius: 10px;
            background: #4f46e5;
            color: #fff;
            cursor: pointer;
        }

        .muted {
            opacity: .7;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="box">
        <div class="top">
            <div>
                <h2 style="margin:0">👩‍🏫 Teacher Dashboard</h2>
                <div class="muted">All student chat sessions</div>
            </div>

            <form method="GET" action="/teacher/chats" style="display:flex;gap:8px">
                <input name="q" value="{{ $q }}" placeholder="Search message text...">
                <button type="submit">Search</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Session ID</th>
                    <th>Last Time</th>
                    <th>Open</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $s)
                    <tr>
                        <td style="font-family:monospace">{{ $s->session_id }}</td>
                        <td>{{ $s->last_time }}</td>
                        <td><a href="/teacher/chats/{{ $s->session_id }}">View</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No sessions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="display:flex;gap:16px;margin-bottom:22px;flex-wrap:wrap">

            <div style="flex:1;padding:14px;border-radius:12px;background:#ecfdf5">
                <b>✅ Good Messages</b>
                <div style="font-size:24px">{{ $goodCount }}</div>
            </div>

            <div style="flex:1;padding:14px;border-radius:12px;background:#fee2e2">
                <b>❌ Bad Language</b>
                <div style="font-size:24px">{{ $badCount }}</div>
            </div>

            <div style="flex:1;padding:14px;border-radius:12px;background:#fff7ed">
                <b>⛔ Blocked Topics</b>
                <div style="font-size:24px">{{ $blockedCount }}</div>
            </div>

            <div style="flex:1;padding:14px;border-radius:12px;background:#eef2ff">
                <b>📊 Total Messages</b>
                <div style="font-size:24px">{{ $total }}</div>
            </div>

        </div>


        <div style="margin-top:12px">
            {{ $sessions->links() }}
        </div>
    </div>
</body>

</html>
