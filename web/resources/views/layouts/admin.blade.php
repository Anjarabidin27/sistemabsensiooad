<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - SIHADIR</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon_v2.png') }}">
    
    <!-- CSS Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js for stats -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Dynamic Theme Color from Settings */
        :root {
            --primary: {{ \App\Models\SystemSetting::get('theme.primary_color', '#1B2A6B') }};
            --primary-light: {{ \App\Models\SystemSetting::get('theme.primary_color', '#1B2A6B') }}ee;
            --accent: {{ \App\Models\SystemSetting::get('theme.accent_color', '#F5A623') }};
        }
    </style>
    @yield('styles')
    @stack('styles')
</head>
<body class="{{ \App\Models\SystemSetting::get('theme.dark_mode', false) ? 'dark-theme' : '' }}">

    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                @php
                    $logoPath = \App\Models\SystemSetting::get('identity.logo_path', 'images/logo_udinus.png');
                    $logoUrl = str_starts_with($logoPath, 'images/') ? asset($logoPath) : asset('storage/' . $logoPath);
                @endphp
                <img src="{{ $logoUrl }}" alt="Logo" class="sidebar-logo">
                <div class="sidebar-brand">
                    {{ \App\Models\SystemSetting::get('identity.system_name', 'SIHADIR') }}
                    <span>{{ \App\Models\SystemSetting::get('identity.university_short', 'UDINUS') }}</span>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li class="sidebar-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Route::is('admin.students.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.students.index') }}">
                        <i class="fa-solid fa-user-graduate"></i>
                        <span>Data Mahasiswa</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Route::is('admin.courses.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.courses.index') }}">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Data Mata Kuliah</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Route::is('admin.attendances.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.attendances.index') }}">
                        <i class="fa-solid fa-clipboard-user"></i>
                        <span>Log Kehadiran</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Route::is('admin.reports') ? 'active' : '' }}">
                    <a href="{{ route('admin.reports') }}">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>Laporan Kehadiran</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Route::is('admin.settings') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings') }}">
                        <i class="fa-solid fa-gears"></i>
                        <span>Pengaturan Sistem</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-full btn-sm" style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout Admin</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Content Area -->
        <main class="admin-content">
            <!-- Top Header -->
            <header class="admin-header">
                <div class="admin-header-title">
                    <h1>@yield('header-title', 'Dashboard')</h1>
                    <p>@yield('header-subtitle', 'Sistem Informasi Kehadiran Pengenalan Wajah')</p>
                </div>
                <div class="admin-header-actions">
                    <div class="admin-user-profile">
                        <div class="admin-avatar">
                            AD
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-weight: 700; font-size: 0.85rem; color: var(--text-main);">{{ Auth::user()->name }}</span>
                            <span style="font-size: 0.725rem; color: var(--text-muted);">Administrator</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Slot -->
            @yield('content')
            <footer style="text-align: center; padding: 1rem; color: var(--text-muted); font-size: 0.8rem; margin-top: auto;">
                Aplikasi ini dibuat hanya untuk keperluan tugas akademik.
            </footer>
        </main>
    </div>

    @yield('scripts')
    @stack('scripts')
</body>
</html>
