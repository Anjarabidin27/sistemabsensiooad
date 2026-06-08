@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('header-title', 'Dashboard')
@section('header-subtitle', 'Ikhtisar statistik dan performa presensi real-time')

@section('content')
    
    <!-- AI Engine Health Status Warning Bar -->
    @if(isset($aiHealth['status']) && $aiHealth['status'] !== 'ok')
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.25rem;"></i>
            <div>
                <strong style="display: block;">AI Engine Offline!</strong>
                <span style="font-size: 0.825rem;">Sistem pengenalan wajah tidak dapat digunakan untuk presensi maupun registrasi. Detail: {{ $aiHealth['message'] ?? 'Koneksi ditolak.' }}</span>
            </div>
        </div>
    @endif

    <!-- Stats Cards Grid -->
    <div class="stats-grid">
        <div class="stat-card students">
            <div class="stat-icon" style="background-color: rgba(27, 42, 107, 0.1); color: var(--primary);">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Mahasiswa</span>
                <span class="stat-val">{{ $totalStudents }}</span>
            </div>
            <div class="stat-desc">
                <i class="fa-solid fa-circle-check" style="color: var(--success);"></i>
                <span>Siswa aktif terdaftar</span>
            </div>
        </div>

        <div class="stat-card courses">
            <div class="stat-icon" style="background-color: rgba(14, 165, 233, 0.1); color: #0ea5e9;">
                <i class="fa-solid fa-book-open"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Total Matkul</span>
                <span class="stat-val">{{ $totalCourses }}</span>
            </div>
            <div class="stat-desc">
                <i class="fa-solid fa-calendar-days" style="color: #0ea5e9;"></i>
                <span>Mata kuliah semester ini</span>
            </div>
        </div>

        <div class="stat-card attendances">
            <div class="stat-icon" style="background-color: rgba(16, 185, 129, 0.1); color: var(--success);">
                <i class="fa-solid fa-clipboard-user"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Presensi Hari Ini</span>
                <span class="stat-val">{{ $totalCheckinsToday }}</span>
            </div>
            <div class="stat-desc">
                <i class="fa-solid fa-bolt" style="color: var(--warning);"></i>
                <span>Log presensi terverifikasi</span>
            </div>
        </div>

        <div class="stat-card rate">
            <div class="stat-icon" style="background-color: rgba(245, 166, 35, 0.1); color: var(--accent);">
                <i class="fa-solid fa-percent"></i>
            </div>
            <div class="stat-info">
                <span class="stat-label">Rasio Kehadiran</span>
                <span class="stat-val">{{ number_format($attendanceRate, 1) }}%</span>
            </div>
            <div class="stat-desc">
                <i class="fa-solid fa-chart-line" style="color: var(--accent);"></i>
                <span>Tingkat kehadiran kelas</span>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activity Grid -->
    <div class="grid-2">
        <!-- Chart Card -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Grafik Kehadiran 7 Hari Terakhir</span>
                <div style="font-size: 0.775rem; color: var(--text-muted); font-weight: 600;">Hadir vs Terlambat</div>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- System & Model Specs Card -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Spesifikasi AI & Koneksi</span>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">AI Engine Status</span>
                        @if(isset($aiHealth['status']) && $aiHealth['status'] === 'ok')
                            <span class="badge badge-active" style="font-size: 0.7rem;">ONLINE</span>
                        @else
                            <span class="badge badge-rejected" style="font-size: 0.7rem;">OFFLINE</span>
                        @endif
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Model Recognition</span>
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--primary);">{{ \App\Models\SystemSetting::get('theme.font_family', 'ArcFace') }} (ArcFace)</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Threshold Confidence</span>
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--primary);">{{ \App\Models\SystemSetting::get('attendance.confidence_threshold', 80) }}%</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Liveness Detection</span>
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--primary);">
                            {{ \App\Models\SystemSetting::get('attendance.liveness_detection', true) ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Batas Waktu Terlambat</span>
                        <span style="font-size: 0.85rem; font-weight: 700; color: var(--primary);">{{ \App\Models\SystemSetting::get('attendance.late_threshold_minutes', 15) }} Menit</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Logs Table -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Log Presensi Terkini</span>
            <a href="{{ route('admin.attendances.index') }}" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 6px 12px;">
                Lihat Semua Log
            </a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="hide-mobile">Snapshot</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th class="hide-mobile">Mata Kuliah</th>
                            <th>Waktu Presensi</th>
                            <th class="hide-mobile">Confidence</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttendances as $row)
                            <tr>
                                <td class="hide-mobile">
                                    @if($row->image_path)
                                        <img src="{{ asset('storage/' . $row->image_path) }}" alt="Face Snapshot" style="width: 40px; height: 40px; border-radius: var(--radius-sm); object-fit: cover; border: 1px solid var(--border-color);">
                                    @else
                                        <div style="width: 40px; height: 40px; border-radius: var(--radius-sm); background-color: var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 0.8rem;">
                                            <i class="fa-solid fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td style="font-weight: 700; color: var(--primary-dark);">{{ $row->student->student_number }}</td>
                                <td style="font-weight: 600;">{{ $row->student->name }}</td>
                                <td class="hide-mobile">{{ $row->course ? $row->course->name : '-' }}</td>
                                <td>{{ $row->check_in_at->isoFormat('D MMM Y, HH:mm') }} WIB</td>
                                <td class="hide-mobile" style="font-weight: 600;">{{ $row->confidence_percent }}</td>
                                <td>{!! $row->status_badge !!}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                    Belum ada presensi hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            
            const chartLabels = {!! json_encode($chartLabels) !!};
            const chartPresent = {!! json_encode($chartPresent) !!};
            const chartLate = {!! json_encode($chartLate) !!};

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [
                        {
                            label: 'Tepat Waktu',
                            data: chartPresent,
                            backgroundColor: '#10B981', // green
                            borderRadius: 6
                        },
                        {
                            label: 'Terlambat',
                            data: chartLate,
                            backgroundColor: '#F59E0B', // orange
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#e2e8f0'
                            },
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    family: 'Plus Jakarta Sans',
                                    weight: '600'
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
