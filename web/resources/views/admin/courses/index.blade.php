@extends('layouts.admin')

@section('title', 'Data Mata Kuliah')
@section('header-title', 'Data Mata Kuliah')
@section('header-subtitle', 'Kelola jadwal perkuliahan, SKS, ruangan, dan dosen pengampu')

@push('styles')
<style>
/* ── Mobile Card Layout ─────────────────────────────── */
.course-cards { display: none; flex-direction: column; gap: 14px; }

.course-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 16px;
    box-shadow: var(--shadow-sm);
    transition: box-shadow 0.2s;
}
.course-card:hover { box-shadow: var(--shadow-md); }

.course-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 10px;
}
.course-card-code {
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--primary);
    background: rgba(27,42,107,0.08);
    padding: 3px 8px;
    border-radius: 6px;
    letter-spacing: 0.04em;
}
.course-card-name {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--text-main);
    line-height: 1.3;
    flex: 1;
    margin: 6px 0 4px;
}
.course-card-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 12px;
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 12px;
}
.course-card-meta span { display: flex; align-items: center; gap: 5px; }
.course-card-meta i { color: var(--primary); width: 14px; }
.course-card-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding-top: 10px;
    border-top: 1px solid var(--border-color);
}
.course-card-actions .btn { flex: 1; min-width: 0; font-size: 0.78rem; padding: 7px 10px; justify-content: center; }

/* GPS Badge */
.gps-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.72rem; font-weight: 700;
    padding: 3px 8px; border-radius: 6px;
}
.gps-badge.on  { background: rgba(16,185,129,0.1); color: #065f46; border: 1px solid rgba(16,185,129,0.25); }
.gps-badge.off { background: rgba(107,114,128,0.1); color: #6b7280; border: 1px solid rgba(107,114,128,0.2); }
.gps-badge.warn { background: rgba(245,158,11,0.1); color: #92400e; border: 1px solid rgba(245,158,11,0.25); }

@media (max-width: 768px) {
    .table-responsive { display: none !important; }
    .course-cards { display: flex; }

    /* Filter area jadi stack vertical */
    .card-header form { flex-direction: column; }
    .card-header form .form-control,
    .card-header form .btn { max-width: 100% !important; width: 100%; }
    .card-header { flex-direction: column; }
    .card-header > a { width: 100%; justify-content: center; }
}
</style>
@endpush

@section('content')

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="card">
        <!-- Search & Filter Header -->
        <div class="card-header" style="flex-wrap: wrap; gap: 16px; padding: 18px 24px;">
            <form action="{{ route('admin.courses.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; flex-grow: 1;">
                <input type="text" name="search" class="form-control" placeholder="Cari Kode, Nama, Dosen..." style="max-width: 250px;" value="{{ request('search') }}">
                
                <select name="day" class="form-control" style="max-width: 150px;">
                    <option value="">-- Semua Hari --</option>
                    @php $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']; @endphp
                    @foreach($days as $idx => $dName)
                        <option value="{{ $idx }}" {{ request('day') !== null && request('day') == $idx ? 'selected' : '' }}>{{ $dName }}</option>
                    @endforeach
                </select>

                <select name="semester" class="form-control" style="max-width: 200px;">
                    <option value="">-- Semua Semester --</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-secondary">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Filter</span>
                </button>
                @if(request('search') || request('day') !== null || request('semester'))
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary" style="background-color: var(--border-color);">Reset</a>
                @endif
            </form>

            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Mata Kuliah</span>
            </a>
        </div>

        <!-- ── Desktop Table ─────────────────────────────── -->
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Dosen Pengampu</th>
                            <th>Jadwal Kuliah</th>
                            <th>Ruangan</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>GPS</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                            @php
                                $hasCoords = $course->latitude && $course->longitude;
                                $gpsOn = $course->location_required && $hasCoords;
                                $gpsWarn = $course->location_required && !$hasCoords;
                            @endphp
                            <tr>
                                <td style="font-weight: 700; color: var(--primary-dark);">{{ $course->code }}</td>
                                <td style="font-weight: 600;">{{ $course->name }}</td>
                                <td>{{ $course->credits }}</td>
                                <td>{{ $course->lecturer_name ?: '-' }}</td>
                                <td style="font-weight: 600; color: var(--primary);">
                                    {{ $course->day_name }}, {{ substr($course->schedule_start, 0, 5) }} - {{ substr($course->schedule_end, 0, 5) }} WIB
                                </td>
                                <td>{{ $course->room ?: '-' }}</td>
                                <td>{{ $course->semester ?: '-' }}</td>
                                <td>
                                    @if($course->is_active)
                                        <span class="badge badge-active">AKTIF</span>
                                    @else
                                        <span class="badge badge-inactive">NON-AKTIF</span>
                                    @endif
                                </td>
                                <td>
                                    @if($gpsOn)
                                        <span class="gps-badge on"><i class="fa-solid fa-location-dot"></i> ON</span>
                                    @elseif($gpsWarn)
                                        <span class="gps-badge warn"><i class="fa-solid fa-triangle-exclamation"></i> No Coords</span>
                                    @else
                                        <span class="gps-badge off"><i class="fa-solid fa-location-dot"></i> OFF</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 6px; align-items: center;">
                                        <!-- Edit -->
                                        <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-secondary btn-sm" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <!-- Toggle GPS -->
                                        <form action="{{ route('admin.courses.toggle-location', $course->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $course->location_required ? 'btn-primary' : 'btn-secondary' }}" title="{{ $course->location_required ? 'Nonaktifkan GPS' : 'Aktifkan GPS' }}" style="{{ $course->location_required ? '' : 'opacity:0.6;' }}">
                                                <i class="fa-solid fa-location-dot"></i>
                                            </button>
                                        </form>
                                        <!-- Hapus -->
                                        <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Yakin hapus mata kuliah ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger); border-color: rgba(239,68,68,0.2);" title="Hapus">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    Tidak ada data mata kuliah ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── Mobile Card Layout ────────────────────────── -->
        <div class="card-body">
            <div class="course-cards">
                @forelse($courses as $course)
                    @php
                        $hasCoords = $course->latitude && $course->longitude;
                        $gpsOn = $course->location_required && $hasCoords;
                        $gpsWarn = $course->location_required && !$hasCoords;
                    @endphp
                    <div class="course-card">
                        <div class="course-card-header">
                            <div style="flex:1;">
                                <span class="course-card-code">{{ $course->code }}</span>
                                <div class="course-card-name">{{ $course->name }}</div>
                            </div>
                            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
                                @if($course->is_active)
                                    <span class="badge badge-active">AKTIF</span>
                                @else
                                    <span class="badge badge-inactive">NON-AKTIF</span>
                                @endif
                                @if($gpsOn)
                                    <span class="gps-badge on"><i class="fa-solid fa-location-dot"></i> GPS ON</span>
                                @elseif($gpsWarn)
                                    <span class="gps-badge warn"><i class="fa-solid fa-triangle-exclamation"></i> No Coords</span>
                                @else
                                    <span class="gps-badge off"><i class="fa-solid fa-location-dot"></i> GPS OFF</span>
                                @endif
                            </div>
                        </div>

                        <div class="course-card-meta">
                            <span><i class="fa-solid fa-user-tie"></i> {{ $course->lecturer_name ?: '-' }}</span>
                            <span><i class="fa-solid fa-door-open"></i> {{ $course->room ?: '-' }}</span>
                            <span><i class="fa-solid fa-calendar-days"></i> {{ $course->day_name }}</span>
                            <span><i class="fa-solid fa-clock"></i> {{ substr($course->schedule_start,0,5) }}–{{ substr($course->schedule_end,0,5) }}</span>
                            <span><i class="fa-solid fa-graduation-cap"></i> {{ $course->credits }} SKS</span>
                            <span><i class="fa-solid fa-layer-group"></i> {{ $course->semester ?: '-' }}</span>
                        </div>

                        <div class="course-card-actions">
                            <!-- Edit -->
                            <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-secondary btn-sm">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                            <!-- Toggle GPS -->
                            <form action="{{ route('admin.courses.toggle-location', $course->id) }}" method="POST" style="flex:1;">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $course->location_required ? 'btn-primary' : 'btn-secondary' }}" style="width:100%; {{ $course->location_required ? '' : 'opacity:0.65;' }}">
                                    <i class="fa-solid fa-location-dot"></i>
                                    GPS {{ $course->location_required ? 'ON' : 'OFF' }}
                                </button>
                            </form>
                            <!-- Hapus -->
                            <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?')" style="flex:0 0 auto;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary btn-sm" style="color:var(--danger); border-color:rgba(239,68,68,0.2);">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center; padding: 40px; color: var(--text-muted);">
                        <i class="fa-solid fa-book-open" style="font-size:2rem; margin-bottom:10px; display:block;"></i>
                        Tidak ada data mata kuliah ditemukan.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 10px;">
        {{ $courses->links() }}
    </div>

@endsection
