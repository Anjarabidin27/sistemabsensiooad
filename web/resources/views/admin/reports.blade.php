@extends('layouts.admin')

@section('title', 'Laporan Presensi')
@section('header-title', 'Laporan Kehadiran')
@section('header-subtitle', 'Rekapitulasi persentase kehadiran dan ekspor laporan CSV')

@push('styles')
<style>
/* ── Mobile Card Layout ─────────────────────────────── */
.course-summary-cards, .student-detail-cards { display: none; flex-direction: column; gap: 14px; }

.report-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 16px;
    box-shadow: var(--shadow-sm);
    transition: box-shadow 0.2s;
}
.report-card:hover { box-shadow: var(--shadow-md); }

.report-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
}

.report-card-info { flex: 1; min-width: 0; }

.report-card-title {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--text-main);
    margin-bottom: 4px;
}

.report-card-subtitle {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--primary);
    background: rgba(27,42,107,0.08);
    padding: 2px 8px;
    border-radius: 5px;
    display: inline-block;
    margin-bottom: 6px;
}

.report-card-lecturer {
    font-size: 0.8rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
}
.report-card-lecturer i {
    color: var(--primary);
}

.report-card-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    padding: 12px 0;
    border-top: 1px solid var(--border-color);
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 4px;
}

@media (max-width: 480px) {
    .report-card-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

.stat-box {
    text-align: center;
}
.stat-box-value {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 2px;
}
.stat-box-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    color: var(--text-muted);
    font-weight: 600;
}

.report-card-percent-badge {
    background: rgba(27, 42, 107, 0.08);
    color: var(--primary);
    border-radius: 30px;
    padding: 6px 12px;
    font-size: 1rem;
    font-weight: 800;
    text-align: center;
    white-space: nowrap;
    border: 1px solid rgba(27, 42, 107, 0.15);
}

@media (max-width: 768px) {
    .table-responsive { display: none !important; }
    .course-summary-cards, .student-detail-cards { display: flex; }
    
    /* Filter form responsiveness */
    .card-body form { flex-direction: column; align-items: stretch !important; }
    .card-body form .form-group { width: 100% !important; margin-bottom: 12px !important; }
    .card-body form .form-control { width: 100% !important; }
    .card-body form div[style*="display: flex"] { width: 100% !important; }
    .card-body form div[style*="display: flex"] .btn { flex: 1; justify-content: center; }
}
</style>
@endpush

@section('content')

    <div class="card">
        <div class="card-header">
            <span class="card-title">Filter Laporan Rekapitulasi</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reports') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
                
                <div class="form-group" style="margin-bottom: 0; min-width: 220px;">
                    <label for="course_id" class="form-label">Mata Kuliah (Detail Mahasiswa)</label>
                    <select name="course_id" id="course_id" class="form-control">
                        <option value="">-- Pilih MK untuk Laporan Detail --</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ $selectedCourseId == $course->id ? 'selected' : '' }}>
                                {{ $course->name }} [{{ $course->code }}]
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="date_start" class="form-control" value="{{ $dateStart }}">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="date_end" class="form-control" value="{{ $dateEnd }}">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-filter"></i>
                        <span>Tampilkan</span>
                    </button>
                    
                    <a href="{{ route('admin.reports.export', [
                        'course_id' => $selectedCourseId,
                        'date_start' => $dateStart,
                        'date_end' => $dateEnd
                    ]) }}" class="btn btn-accent">
                        <i class="fa-solid fa-file-excel"></i>
                        <span>Ekspor CSV</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Course recap grid -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Ringkasan Kehadiran per Mata Kuliah</span>
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Periode: {{ Carbon\Carbon::parse($dateStart)->isoFormat('D MMM Y') }} s/d {{ Carbon\Carbon::parse($dateEnd)->isoFormat('D MMM Y') }}</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th>Dosen Pengampu</th>
                            <th style="text-align: center;">Tepat Waktu</th>
                            <th style="text-align: center;">Terlambat</th>
                            <th style="text-align: center;">Ditolak</th>
                            <th style="text-align: center;">Total Check-in</th>
                            <th style="text-align: center;">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courseStats as $stat)
                            @php
                                $totalValid = $stat['present'] + $stat['late'];
                                $percent = $stat['total'] > 0 ? ($totalValid / $stat['total']) * 100 : 0;
                            @endphp
                            <tr>
                                <td style="font-weight: 700; color: var(--primary-dark);">{{ $stat['course']->code }}</td>
                                <td style="font-weight: 600;">{{ $stat['course']->name }}</td>
                                <td>{{ $stat['course']->lecturer_name ?: '-' }}</td>
                                <td style="text-align: center; color: var(--success); font-weight: 700;">{{ $stat['present'] }}</td>
                                <td style="text-align: center; color: var(--warning); font-weight: 700;">{{ $stat['late'] }}</td>
                                <td style="text-align: center; color: var(--danger); font-weight: 700;">{{ $stat['rejected'] }}</td>
                                <td style="text-align: center; font-weight: 700; color: var(--primary-dark);">{{ $stat['total'] }}</td>
                                <td style="text-align: center; font-weight: 800; color: var(--primary);">
                                    {{ number_format($percent, 1) }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile: Card Layout -->
            <div class="course-summary-cards" style="padding: 14px;">
                @foreach($courseStats as $stat)
                    @php
                        $totalValid = $stat['present'] + $stat['late'];
                        $percent = $stat['total'] > 0 ? ($totalValid / $stat['total']) * 100 : 0;
                    @endphp
                    <div class="report-card">
                        <div class="report-card-top">
                            <div class="report-card-info">
                                <span class="report-card-subtitle">{{ $stat['course']->code }}</span>
                                <div class="report-card-title">{{ $stat['course']->name }}</div>
                                <div class="report-card-lecturer">
                                    <i class="fa-solid fa-user-tie"></i>
                                    <span>{{ $stat['course']->lecturer_name ?: '-' }}</span>
                                </div>
                            </div>
                            <div class="report-card-percent-badge">
                                {{ number_format($percent, 1) }}%
                            </div>
                        </div>

                        <div class="report-card-stats">
                            <div class="stat-box">
                                <div class="stat-box-value" style="color: var(--success);">{{ $stat['present'] }}</div>
                                <div class="stat-box-label">Hadir</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-value" style="color: var(--warning);">{{ $stat['late'] }}</div>
                                <div class="stat-box-label">Lambat</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-value" style="color: var(--danger);">{{ $stat['rejected'] }}</div>
                                <div class="stat-box-label">Tolak</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-value" style="color: var(--primary-dark);">{{ $stat['total'] }}</div>
                                <div class="stat-box-label">Total</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Detailed student report in course -->
    @if($selectedCourseId && !empty($detailedReport))
        <div class="card">
            <div class="card-header">
                <span class="card-title">Laporan Kehadiran Mahasiswa Detail</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th style="text-align: center;">Hadir</th>
                                <th style="text-align: center;">Terlambat</th>
                                <th style="text-align: center;">Ditolak</th>
                                <th style="text-align: center;">Total Kuliah</th>
                                <th style="text-align: center;">Persentase Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detailedReport as $row)
                                @php
                                    $presentAndLate = $row['present'] + $row['late'];
                                    // Total meetings is total times student checked in + missed? 
                                    // Since we track check-ins, let's show check-in percentage out of total class checkins.
                                    $percent = $row['total'] > 0 ? ($presentAndLate / $row['total']) * 100 : 0;
                                @endphp
                                <tr>
                                    <td style="font-weight: 700; color: var(--primary-dark);">{{ $row['student']->student_number }}</td>
                                    <td style="font-weight: 600;">{{ $row['student']->name }}</td>
                                    <td style="text-align: center; color: var(--success); font-weight: 700;">{{ $row['present'] }}</td>
                                    <td style="text-align: center; color: var(--warning); font-weight: 700;">{{ $row['late'] }}</td>
                                    <td style="text-align: center; color: var(--danger); font-weight: 700;">{{ $row['rejected'] }}</td>
                                    <td style="text-align: center; font-weight: 700;">{{ $row['total'] }}</td>
                                    <td style="text-align: center; font-weight: 800; color: var(--primary);">
                                        {{ number_format($percent, 1) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile: Card Layout -->
                <div class="student-detail-cards" style="padding: 14px;">
                    @foreach($detailedReport as $row)
                        @php
                            $presentAndLate = $row['present'] + $row['late'];
                            $percent = $row['total'] > 0 ? ($presentAndLate / $row['total']) * 100 : 0;
                        @endphp
                        <div class="report-card">
                            <div class="report-card-top">
                                <div class="report-card-info">
                                    <span class="report-card-subtitle">{{ $row['student']->student_number }}</span>
                                    <div class="report-card-title">{{ $row['student']->name }}</div>
                                </div>
                                <div class="report-card-percent-badge">
                                    {{ number_format($percent, 1) }}%
                                </div>
                            </div>

                            <div class="report-card-stats">
                                <div class="stat-box">
                                    <div class="stat-box-value" style="color: var(--success);">{{ $row['present'] }}</div>
                                    <div class="stat-box-label">Hadir</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-box-value" style="color: var(--warning);">{{ $row['late'] }}</div>
                                    <div class="stat-box-label">Lambat</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-box-value" style="color: var(--danger);">{{ $row['rejected'] }}</div>
                                    <div class="stat-box-label">Tolak</div>
                                </div>
                                <div class="stat-box">
                                    <div class="stat-box-value" style="color: var(--text-main);">{{ $row['total'] }}</div>
                                    <div class="stat-box-label">Total</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

@endsection
