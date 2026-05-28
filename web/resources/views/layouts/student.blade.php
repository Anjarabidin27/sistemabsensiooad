<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIHADIR') - UDINUS</title>
    
    <!-- CSS Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <!-- Font Awesome Icons & Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Dynamic Theme Color from Settings */
        :root {
            --primary: {{ \App\Models\SystemSetting::get('theme.primary_color', '#1B2A6B') }};
            --primary-light: {{ \App\Models\SystemSetting::get('theme.primary_color', '#1B2A6B') }}ee;
            --accent: {{ \App\Models\SystemSetting::get('theme.accent_color', '#F5A623') }};
        }
    </style>
    @yield('styles')
</head>
<body class="{{ \App\Models\SystemSetting::get('theme.dark_mode', false) ? 'dark-theme' : '' }}">

    <div class="student-layout">
        <!-- Main Content -->
        @yield('content')

        <!-- Bottom Navigation -->
        <nav class="student-nav">
            <a href="{{ route('student.home') }}" class="student-nav-item {{ Route::is('student.home') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i>
                <span>Beranda</span>
            </a>
            <a href="{{ route('student.scanner') }}" class="student-nav-item {{ Route::is('student.scanner') ? 'active' : '' }}">
                <i class="fa-solid fa-expand"></i>
                <span>Scan Wajah</span>
            </a>
            <a href="{{ route('student.history') }}" class="student-nav-item {{ Route::is('student.history') ? 'active' : '' }}">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Riwayat</span>
            </a>
            <a href="{{ route('student.schedule') }}" class="student-nav-item {{ Route::is('student.schedule') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Jadwal</span>
            </a>
            <a href="{{ route('student.profile') }}" class="student-nav-item {{ Route::is('student.profile') ? 'active' : '' }}">
                <i class="fa-solid fa-user"></i>
                <span>Profil</span>
            </a>
        </nav>
    </div>

    @yield('scripts')
</body>
</html>
