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
    <!-- Preload background agar gambar langsung siap sebelum CSS render -->
    <link rel="preload" href="{{ asset('images/login_bg_anime.webp') }}" as="image" type="image/webp">
    
    <style>
        :root {
            --primary: {{ \App\Models\SystemSetting::get('theme.primary_color', '#1B2A6B') }};
            --accent: {{ \App\Models\SystemSetting::get('theme.accent_color', '#F5A623') }};
        }
    </style>
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                @php
                    $logoPath = \App\Models\SystemSetting::get('identity.logo_path', 'images/logo_udinus.png');
                    $logoUrl = str_starts_with($logoPath, 'images/') ? asset($logoPath) : asset('storage/' . $logoPath);
                @endphp
                <img src="{{ $logoUrl }}" alt="Logo UDINUS" class="auth-logo">
                <h1 class="auth-title">{{ \App\Models\SystemSetting::get('identity.system_name', 'SIHADIR') }}</h1>
                <p class="auth-subtitle">Presensi Wajah · Portal Mahasiswa</p>
            </div>
            
            <div class="auth-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('student.login') }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label for="student_number" class="form-label">NIM Mahasiswa</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 14px; top: 12px; color: var(--text-muted);">
                                <i class="fa-solid fa-id-card"></i>
                            </span>
                            <input type="text" id="student_number" name="student_number" class="form-control" placeholder="A11.2023.15023" style="padding-left: 40px;" required autofocus value="{{ old('student_number') }}">
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
                        <a href="{{ route('admin.login') }}" style="font-size: 0.825rem; font-weight: 600; color: var(--accent);">Login Admin →</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full" style="margin-top: 20px;">
                        Masuk Portal
                    </button>
                </form>
            </div>
            <div class="auth-footer">
                Aplikasi ini dibuat hanya untuk keperluan tugas akademik.
            </div>
        </div>
    </div>

    <!-- Script to fade-in background once fully loaded to prevent top-to-bottom loading artifact -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var wrapper = document.querySelector('.auth-wrapper');
            var bgUrl = "{{ asset('images/login_bg_anime.webp') }}";
            var img = new Image();
            img.src = bgUrl;
            img.onload = function() {
                wrapper.classList.add('bg-loaded');
            };
            // Fallback in case loading takes too long or fails
            setTimeout(function() {
                wrapper.classList.add('bg-loaded');
            }, 1000);
        });
    </script>
</body>
</html>
