<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Smart Room IoT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #5C35C9;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(255,255,255,0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(0,0,0,0.15) 0%, transparent 50%);
        }
        .login-box { width: 100%; max-width: 400px; }

        .brand { text-align: center; margin-bottom: 28px; }
        .brand-logo {
            width: 60px; height: 60px; border-radius: 18px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 12px;
            backdrop-filter: blur(8px);
        }
        .brand-logo i { font-size: 28px; color: #fff; }
        .brand-title { font-size: 22px; font-weight: 700; color: #fff; }
        .brand-sub   { font-size: 13px; color: rgba(255,255,255,0.65); margin-top: 4px; }

        .card {
            background: #fff;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        }
        .card-title { font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 22px; }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: #6B7280; margin-bottom: 6px; }
        .form-group input {
            width: 100%;
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 14px; color: #111827;
            background: #F9FAFB;
            outline: none; transition: border-color 0.15s;
        }
        .form-group input:focus { border-color: #5C35C9; background: #EDE9FA; }

        .error-msg {
            background: #FEE2E2; color: #DC2626;
            border: 1px solid #FCA5A5;
            border-radius: 10px; padding: 10px 14px;
            font-size: 13px; margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }

        .btn {
            width: 100%;
            background: #5C35C9; color: #fff;
            border: none; border-radius: 10px;
            padding: 13px; font-size: 15px; font-weight: 700;
            cursor: pointer; margin-top: 4px;
            transition: background 0.15s;
            box-shadow: 0 4px 14px rgba(92,53,201,0.35);
        }
        .btn:hover { background: #4322A8; }

        .footer { text-align: center; margin-top: 16px; font-size: 11px; color: #9CA3AF; }
    </style>
</head>
<body>
<div class="login-box">

    <div class="brand">
        <div class="brand-logo"><i class="ti ti-cpu"></i></div>
        <div class="brand-title">Smart Room IoT</div>
        <div class="brand-sub">Admin Dashboard · ESP32 + Laravel</div>
    </div>

    <div class="card">
        <div class="card-title">Sign in to continue</div>

        @if($errors->any())
            <div class="error-msg">
                <i class="ti ti-alert-circle"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="admin@smartroom.local"
                       required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn">Sign in</button>
        </form>

        <div class="footer">Smart Room v1.0.0 &nbsp;·&nbsp; Capstone Project</div>
    </div>

</div>
</body>
</html>
