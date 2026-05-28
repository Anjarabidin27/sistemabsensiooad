<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - {{ \App\Models\SystemSetting::get('identity.system_name', 'SIHADIR') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: {{ \App\Models\SystemSetting::get('theme.primary_color', '#1B2A6B') }};
            --accent: {{ \App\Models\SystemSetting::get('theme.accent_color', '#F5A623') }};
        }
    </style>
</head>
<body>

    <div class="auth-wrapper" style="background: radial-gradient(circle at 10% 20%, rgb(15, 23, 42) 0%, rgb(8, 12, 28) 90.1%);">
        <div class="auth-card" style="border-top: 5px solid var(--accent);">
            <div class="auth-header">
                @php
                    $logoPath = \App\Models\SystemSetting::get('identity.logo_path', 'images/logo_udinus.png');
                    $logoUrl = str_starts_with($logoPath, 'images/') ? asset($logoPath) : asset('storage/' . $logoPath);
                @endphp
                <img src="{{ $logoUrl }}" alt="Logo UDINUS" class="auth-logo">
                <h1 class="auth-title">ADMINISTRATOR</h1>
                <p class="auth-subtitle">{{ \App\Models\SystemSetting::get('identity.university_name', 'Universitas Dian Nuswantoro') }}</p>
            </div>
            
            <div class="auth-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('admin.login') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Admin</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 14px; top: 12px; color: var(--text-muted);">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <input type="email" id="email" name="email" class="form-control" placeholder="admin@udinus.ac.id" style="padding-left: 40px;" required autofocus value="{{ old('email') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 14px; top: 12px; color: var(--text-muted);">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" style="padding-left: 40px;" required>
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 0.825rem; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" name="remember" value="1" style="accent-color: var(--accent);">
                            Ingat Saya
                        </label>
                        <a href="{{ route('student.login') }}" style="font-size: 0.825rem; font-weight: 600; color: var(--accent);">← Portal Mahasiswa</a>
                    </div>

                    <button type="submit" class="btn btn-accent btn-full" style="margin-top: 20px;">
                        Login Admin
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
