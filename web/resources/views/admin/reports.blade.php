@extends('layouts.admin')

@section('title', 'Laporan Presensi')
@section('header-title', 'Laporan Kehadiran')
@section('header-subtitle', 'Rekapitulasi persentase kehadiran dan ekspor laporan CSV')

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
            </div>
        </div>
    @endif

@endsection
