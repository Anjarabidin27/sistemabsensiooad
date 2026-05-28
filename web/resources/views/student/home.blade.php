@extends('layouts.student')

@section('title', 'Beranda Mahasiswa')

@section('content')
    <!-- Student Header (Compact & Cohesive Row) -->
    <header class="student-header" style="padding: 16px 20px; border-radius: 0 0 16px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
        <!-- Left Side: Profile Info -->
        <div style="display: flex; align-items: center; gap: 12px;">
            <img src="{{ $student->photo_url }}" alt="Profile Photo" style="width: 46px; height: 46px; border-radius: 50%; border: 2.5px solid rgba(255,255,255,0.25); object-fit: cover;">
            <div style="display: flex; flex-direction: column;">
                <h3 style="font-size: 0.9rem; font-weight: 700; color: white; line-height: 1.2;">{{ $student->name }}</h3>
                <span style="font-size: 0.725rem; color: rgba(255, 255, 255, 0.85); margin-top: 2px;">{{ $student->student_number }}</span>
            </div>
        </div>

        <!-- Right Side: University Logo & Action -->
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                @php
                    $logoPath = \App\Models\SystemSetting::get('identity.logo_path', 'images/logo_udinus.png');
                    $logoUrl = str_starts_with($logoPath, 'images/') ? asset($logoPath) : asset('storage/' . $logoPath);
                @endphp
                <img src="{{ $logoUrl }}" alt="Logo UDINUS" style="height: 24px; width: auto;">
                <span style="font-weight: 800; font-size: 0.75rem; color: white; letter-spacing: 0.5px;">{{ \App\Models\SystemSetting::get('identity.university_short', 'UDINUS') }}</span>
            </div>
            
            <form action="{{ route('student.logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" style="background: none; border: none; color: white; cursor: pointer; font-size: 1rem; opacity: 0.8; padding: 4px; display: flex; align-items: center;">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </header>

    <!-- Student Body Content -->
    <div class="student-body" style="margin-top: 0; padding-top: 10px;">
        
        <!-- Status & Flash Message Alert -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Card Wajah Belum Terdaftar Alert -->
        @if($student->face_embeddings_count == 0)
            <div class="card" style="border-left: 4px solid var(--danger);">
                <div class="card-body" style="padding: 16px; display: flex; align-items: center; gap: 14px;">
                    <div style="background-color: var(--danger-light); color: var(--danger); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                        <i class="fa-solid fa-face-frown"></i>
                    </div>
                    <div>
                        <h4 style="font-weight: 700; font-size: 0.9rem; color: var(--danger);">Wajah Belum Terdaftar</h4>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Anda belum mendaftarkan wajah. Hubungi admin untuk berfoto agar dapat melakukan scan presensi.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Stats Grid -->
        <div class="card">
            <div class="card-body" style="padding: 16px; display: grid; grid-template-columns: repeat(3, 1fr); text-align: center;">
                <div style="border-right: 1px solid var(--border-color);">
                    <span style="font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Hadir</span>
                    <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--success); margin-top: 4px;">{{ $present }}</h3>
                </div>
                <div style="border-right: 1px solid var(--border-color);">
                    <span style="font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Terlambat</span>
                    <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--warning); margin-top: 4px;">{{ $late }}</h3>
                </div>
                <div>
                    <span style="font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Rasio</span>
                    <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--primary); margin-top: 4px;">{{ number_format($attendanceRate, 0) }}%</h3>
                </div>
            </div>
        </div>

        <!-- Menu Navigation Grid -->
        <div class="student-menu-grid">
            <a href="{{ route('student.scanner') }}" class="student-menu-item">
                <div class="student-menu-icon icon-blue">
                    <i class="fa-solid fa-expand"></i>
                </div>
                <span>Presensi Wajah</span>
            </a>
            <a href="{{ route('student.history') }}" class="student-menu-item">
                <div class="student-menu-icon icon-orange">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <span>Riwayat Kehadiran</span>
            </a>
            <a href="{{ route('student.schedule') }}" class="student-menu-item">
                <div class="student-menu-icon icon-green">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <span>Jadwal Kuliah</span>
            </a>
        </div>

        <!-- Classes Scheduled Today -->
        <div class="card">
            <div class="card-header" style="padding: 14px 20px;">
                <span class="card-title" style="font-size: 0.95rem; font-weight: 700;">Jadwal Kuliah Hari Ini</span>
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">
                    {{ Carbon\Carbon::now()->isoFormat('D MMMM Y') }}
                </span>
            </div>
            <div class="card-body" style="padding: 0 16px;">
                @if($coursesToday->isEmpty())
                    <div style="padding: 30px 10px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-calendar-xmark" style="font-size: 2rem; opacity: 0.4; margin-bottom: 10px; display: block;"></i>
                        <span style="font-size: 0.8rem;">Tidak ada jadwal kuliah hari ini.</span>
                    </div>
                @else
                    <div style="display: flex; flex-direction: column;">
                        @foreach($coursesToday as $course)
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 0; border-bottom: 1px solid var(--border-color);">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span style="font-weight: 700; font-size: 0.875rem; color: var(--primary-dark);">{{ $course->name }}</span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">
                                        <i class="fa-regular fa-clock" style="margin-right: 4px;"></i>
                                        {{ substr($course->schedule_start, 0, 5) }} - {{ substr($course->schedule_end, 0, 5) }} · SKS {{ $course->credits }} · Ruang {{ $course->room }}
                                    </span>
                                </div>
                                @php
                                    $checkedToday = \App\Models\Attendance::where('student_id', $student->id)
                                        ->where('course_id', $course->id)
                                        ->whereDate('check_in_at', Carbon\Carbon::today())
                                        ->first();
                                @endphp
                                @if($checkedToday)
                                    <span class="badge badge-present" style="font-size: 0.65rem;">
                                        {{ $checkedToday->status === 'present' ? 'Hadir' : 'Terlambat' }}
                                    </span>
                                @else
                                    <a href="{{ route('student.scanner') }}?course_id={{ $course->id }}" class="btn btn-primary btn-sm" style="font-size: 0.7rem; padding: 4px 10px; border-radius: var(--radius-sm);">
                                        Scan
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Latest Log Today -->
        @if($latestCheckinToday)
            <div class="card" style="background-color: var(--success-light); border-color: rgba(16, 185, 129, 0.2);">
                <div class="card-body" style="padding: 16px; display: flex; align-items: center; gap: 12px;">
                    <div style="color: var(--success); font-size: 1.5rem;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <span style="font-size: 0.7rem; font-weight: 700; color: var(--success); text-transform: uppercase;">Presensi Hari Ini Berhasil</span>
                        <h4 style="font-weight: 700; font-size: 0.85rem; color: #065f46;">{{ $latestCheckinToday->course->name }}</h4>
                        <p style="font-size: 0.75rem; color: #065f46; opacity: 0.8; margin-top: 1px;">
                            Check-in pukul {{ $latestCheckinToday->check_in_at->format('H:i') }} WIB (Confidence: {{ $latestCheckinToday->confidence_percent }})
                        </p>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection
