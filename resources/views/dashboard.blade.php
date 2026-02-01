<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Main Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * {
            box-sizing: border-box
        }

        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial;
            background: radial-gradient(circle at top, #eef2ff, #f8fafc 40%, #ffffff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .wrap {
            width: min(980px, 100%);
            background: #ffffffcc;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, .06);
            border-radius: 22px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .10);
            overflow: hidden;
        }

        .top {
            padding: 22px 24px;
            background: linear-gradient(90deg, #4f46e5, #6366f1);
            color: #fff;
        }

        .top h1 {
            margin: 0;
            font-size: 22px
        }

        .top p {
            margin: 6px 0 0;
            opacity: .9;
            font-size: 13px
        }

        .content {
            padding: 22px 24px 26px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 18px;
            transition: .2s ease;
            position: relative;
            overflow: hidden;
            min-height: 160px;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(0, 0, 0, .08);
        }

        .badge {
            display: inline-block;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #eef2ff;
            color: #3730a3;
            border: 1px solid #e0e7ff;
            margin-bottom: 10px;
        }

        .title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .desc {
            margin: 8px 0 14px;
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
        }

        .btn {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid transparent;
            transition: .2s ease;
            font-size: 14px;
        }

        .btn-student {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
        }

        .btn-teacher {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
        }

        .btn-outline {
            background: #fff;
            border-color: #e5e7eb;
            color: #111827;
            margin-left: 8px;
        }

        .bottom {
            padding: 14px 24px;
            border-top: 1px solid #eef2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            font-size: 12px;
            color: #6b7280;
            background: #fafbff;
        }

        form.inline {
            display: inline;
        }

        button.link-button {
            border: none;
            background: none;
            color: #4f46e5;
            cursor: pointer;
            font-weight: 600;
        }

        @media (max-width: 760px) {
            .content {
                grid-template-columns: 1fr
            }
        }
    </style>
</head>

<body>

    <div class="wrap">
        <div class="top">
            <h1>🎓 Student AI Chatbot System</h1>
            <p>Main dashboard — choose where to go</p>
        </div>

        <div class="content">

            <!-- Student -->
            <div class="card">
                <div class="badge">👨‍🎓 Student</div>
                <h3 class="title">Student Dashboard</h3>
                <p class="desc">
                    Ask study questions, get AI help, view your chat history.
                </p>

                @if (session('user_role') === 'student')
                    <a class="btn btn-student" href="/chat">Open Chat</a>
                @else
                    <form method="POST" action="{{ route('login.student') }}" class="inline">
                        @csrf
                        <button class="btn btn-student" type="submit">Login as Student</button>
                    </form>
                @endif
            </div>

            <!-- Teacher -->
            <div class="card">
                <div class="badge">👩‍🏫 Teacher</div>
                <h3 class="title">Teacher Dashboard</h3>
                <p class="desc">
                    Monitor sessions, view statistics (good/bad/blocked), charts & reports.
                </p>

                @if (session('user_role') === 'teacher')
                    <a class="btn btn-teacher" href="/chat">Open Chat</a>
                @else
                    <form method="POST" action="{{ route('login.teacher') }}" class="inline">
                        @csrf
                        <button class="btn btn-teacher" type="submit">Login as Teacher</button>
                    </form>
                @endif
            </div>

        </div>

        <div class="bottom">
            <div>
                @if (session()->has('user_role'))
                    Logged in as <b>{{ ucfirst(session('user_role')) }}</b>
                @else
                    Not logged in
                @endif
            </div>

            <div>
                @if (session()->has('user_role'))
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="link-button">
                            Logout
                        </button>
                    </form>
                @endif
            </div>
        </div>

    </div>

</body>

</html>
