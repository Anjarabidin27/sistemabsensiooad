<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aplikasi SIHADIR (Presensi Wajah) ini dibuat hanya untuk keperluan tugas akademik dan bukan merupakan portal resmi Universitas Dian Nuswantoro (UDINUS).">
    <title>Login Mahasiswa - {{ \App\Models\SystemSetting::get('identity.system_name', 'SIHADIR') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon_v2.png') }}">
    
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: {{ \App\Models\SystemSetting::get('theme.primary_color', '#1B2A6B') }};
            --accent: {{ \App\Models\SystemSetting::get('theme.accent_color', '#F5A623') }};
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
        }

        /* ========== LEFT PANEL - BRANDING ========== */
        .login-left {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: url('{{ asset('images/login_bg_anime.png') }}') no-repeat center center / cover;
            min-height: 100vh;
        }

        /* Dark gradient overlay on top of image for readability */
        .login-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(10, 25, 80, 0.72) 0%,
                rgba(27, 42, 107, 0.60) 50%,
                rgba(10, 25, 80, 0.45) 100%
            );
            z-index: 1;
        }

        .login-brand {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 40px;
            animation: fadeInUp 0.8s ease both;
        }

        .login-brand-logo {
            height: 90px;
            width: auto;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 16px rgba(0,0,0,0.4)) brightness(1.1);
        }

        .login-brand-uni {
            font-size: clamp(2.4rem, 4vw, 4.2rem);
            font-weight: 900;
            letter-spacing: 6px;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5), 0 0 40px rgba(255,200,50,0.3);
            line-height: 1;
            margin-bottom: 6px;
        }

        .login-brand-tagline {
            font-size: clamp(0.75rem, 1.2vw, 1rem);
            font-weight: 500;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.80);
            margin-bottom: 28px;
        }

        .login-brand-divider {
            width: 60px;
            height: 3px;
            background: var(--accent);
            border-radius: 99px;
            margin: 0 auto 24px;
        }

        .login-brand-app {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent);
            letter-spacing: 2px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .login-brand-desc {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.70);
            margin-top: 8px;
            letter-spacing: 0.5px;
        }

        /* ========== RIGHT PANEL - FORM ========== */
        .login-right {
            width: 420px;
            min-width: 380px;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            overflow-y: auto;
        }

        .login-form-inner {
            width: 100%;
            max-width: 340px;
            animation: fadeInRight 0.7s ease both;
        }

        .login-form-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .login-form-sub {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
            display: block;
            margin-bottom: 6px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.9rem;
            font-family: 'Outfit', sans-serif;
            background: #fff;
            color: #1e293b;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(27, 42, 107, 0.10);
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 6px 0 24px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 0.82rem;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
        }

        .remember-label input[type=checkbox] {
            accent-color: var(--accent);
            width: 15px;
            height: 15px;
        }

        .link-admin {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s;
        }

        .link-admin:hover { color: var(--accent); }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 14px rgba(27, 42, 107, 0.25);
        }

        .btn-login:hover {
            background: #0f1f5c;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(27, 42, 107, 0.35);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            margin-top: 32px;
            font-size: 0.75rem;
            color: #94a3b8;
            text-align: center;
            line-height: 1.5;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.83rem;
            color: #dc2626;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* ========== MOBILE RESPONSIVE ========== */
        @media (max-width: 768px) {
            body { flex-direction: column; }

            .login-left {
                min-height: 220px;
                flex: none;
            }

            .login-brand-uni {
                font-size: 2.2rem;
                letter-spacing: 4px;
            }

            .login-brand-logo { height: 60px; }

            .login-right {
                width: 100%;
                min-width: unset;
                padding: 36px 24px;
            }

            .login-form-inner { max-width: 100%; }
        }
    </style>
</head>
<body>

    <!-- LEFT: Branding Panel -->
    <div class="login-left">
        <div class="login-brand">
            @php
                $logoPath = \App\Models\SystemSetting::get('identity.logo_path', 'images/logo_udinus.png');
                $logoUrl = str_starts_with($logoPath, 'images/') ? asset($logoPath) : asset('storage/' . $logoPath);
            @endphp
            <img src="{{ $logoUrl }}" alt="Logo UDINUS" class="login-brand-logo">

            <div class="login-brand-uni">UDINUS</div>
            <div class="login-brand-tagline">Universitas Dian Nuswantoro</div>
            <div class="login-brand-divider"></div>
            <div class="login-brand-app">SIHADIR</div>
            <div class="login-brand-desc">Sistem Presensi Wajah Digital</div>
        </div>
    </div>

    <!-- RIGHT: Login Form -->
    <div class="login-right">
        <div class="login-form-inner">
            <div class="login-form-title">Selamat Datang 👋</div>
            <div class="login-form-sub">Masuk ke portal mahasiswa SIHADIR</div>

            @if($errors->any())
                <div class="alert-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('student.login') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="student_number" class="form-label">NIM Mahasiswa</label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fa-solid fa-id-card"></i></span>
                        <input type="text" id="student_number" name="student_number" class="form-control"
                            placeholder="A11.2023.15023" required autofocus value="{{ old('student_number') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" value="1">
                        Ingat Saya
                    </label>
                    <a href="{{ route('admin.login') }}" class="link-admin">Login Admin →</a>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fa-solid fa-right-to-bracket" style="margin-right: 8px;"></i>
                    Masuk Portal
                </button>
            </form>

            <div class="login-footer">
                Aplikasi ini dibuat untuk keperluan tugas akademik.<br>
                Bukan portal resmi Universitas Dian Nuswantoro.
            </div>
        </div>
    </div>

</body>
</html>
