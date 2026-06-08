@extends('layouts.admin')

@section('title', 'Log Kehadiran')
@section('header-title', 'Log Kehadiran')
@section('header-subtitle', 'Jurnal riwayat presensi kehadiran mahasiswa lengkap')

@push('styles')
<style>
/* ── Mobile Card Layout ─────────────────────────────── */
.attendance-cards { display: none; flex-direction: column; gap: 14px; }

.attendance-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 16px;
    box-shadow: var(--shadow-sm);
    transition: box-shadow 0.2s;
}
.attendance-card:hover { box-shadow: var(--shadow-md); }

.attendance-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
}

.attendance-card-snapshot {
    width: 52px;
    height: 52px;
    border-radius: var(--radius-sm);
    object-fit: cover;
    border: 1px solid var(--border-color);
    flex-shrink: 0;
}

.attendance-card-snapshot-empty {
    width: 52px;
    height: 52px;
    border-radius: var(--radius-sm);
    background-color: var(--bg-main);
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    flex-shrink: 0;
}

.attendance-card-student { flex: 1; min-width: 0; }

.attendance-card-name {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--text-main);
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.attendance-card-nim {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--primary);
    background: rgba(27,42,107,0.08);
    padding: 2px 8px;
    border-radius: 5px;
    display: inline-block;
}

.attendance-card-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 12px;
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 12px;
}
.attendance-card-meta span { display: flex; align-items: flex-start; gap: 5px; }
.attendance-card-meta i { color: var(--primary); width: 14px; margin-top: 2px; flex-shrink: 0; }

.attendance-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 10px;
    border-top: 1px solid var(--border-color);
    gap: 8px;
}

@media (max-width: 768px) {
    .table-responsive { display: none !important; }
    .attendance-cards { display: flex; }

    /* Filter area stacked */
    .card-header form { flex-direction: column; }
    .card-header form .form-control,
    .card-header form .btn { max-width: 100% !important; width: 100%; }
    .card-header form > div { flex-direction: column; width: 100%; }
    .card-header form > div input { max-width: 100% !important; width: 100%; }
    .card-header { flex-direction: column; }
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
            <form action="{{ route('admin.attendances.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; flex-grow: 1; align-items: center;">
                <input type="text" name="search" class="form-control" placeholder="Cari NIM atau Nama..." style="max-width: 200px;" value="{{ request('search') }}">
                
                <select name="course_id" class="form-control" style="max-width: 220px;">
                    <option value="">-- Semua Mata Kuliah --</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->name }} [{{ $course->code }}]
                        </option>
                    @endforeach
                </select>

                <select name="status" class="form-control" style="max-width: 150px;">
                    <option value="">-- Semua Status --</option>
                    <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Hadir</option>
                    <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Terlambat</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Tanggal:</span>
                    <input type="date" name="date" class="form-control" style="max-width: 160px;" value="{{ request('date') }}">
                </div>

                <button type="submit" class="btn btn-secondary">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Cari</span>
                </button>
                @if(request('search') || request('course_id') || request('status') || request('date'))
                    <a href="{{ route('admin.attendances.index') }}" class="btn btn-secondary" style="background-color: var(--border-color);">Reset</a>
                @endif
            </form>
        </div>

        <!-- Desktop: Attendances Table -->
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Snapshot</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Mata Kuliah</th>
                            <th>Tanggal & Waktu</th>
                            <th>Akurasi AI</th>
                            <th>IP Address</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $row)
                            <tr>
                                <td>
                                    @if($row->image_path)
                                        <a href="{{ asset('storage/' . $row->image_path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $row->image_path) }}" alt="Snapshot" style="width: 44px; height: 44px; border-radius: var(--radius-sm); object-fit: cover; border: 1.5px solid var(--border-color);">
                                        </a>
                                    @else
                                        <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); background-color: var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 0.8rem;">
                                            <i class="fa-solid fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td style="font-weight: 700; color: var(--primary-dark);">{{ $row->student->student_number }}</td>
                                <td style="font-weight: 600;">{{ $row->student->name }}</td>
                                <td>
                                    @if($row->course)
                                        <strong>{{ $row->course->name }}</strong><br>
                                        <span style="font-size: 0.725rem; color: var(--text-muted);">{{ $row->course->code }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    {{ $row->check_in_at->isoFormat('dddd, D MMMM Y') }}<br>
                                    <span style="font-weight: 600; color: var(--text-muted); font-size: 0.8rem;">
                                        <i class="fa-regular fa-clock" style="margin-right: 4px;"></i>
                                        {{ $row->check_in_at->format('H:i:s') }} WIB
                                    </span>
                                </td>
                                <td style="font-weight: 700; color: var(--primary);">{{ $row->confidence_percent }}</td>
                                <td style="font-family: monospace; font-size: 0.8rem; color: var(--text-muted);">{{ $row->ip_address ?: '-' }}</td>
                                <td>{!! $row->status_badge !!}</td>
                                <td style="text-align: right;">
                                    <form action="{{ route('admin.attendances.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus log kehadiran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger); border-color: rgba(239,68,68,0.2);" title="Hapus Log">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    Tidak ada data log kehadiran ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile: Card Layout -->
            <div class="attendance-cards" style="padding: 14px;">
                @forelse($attendances as $row)
                    <div class="attendance-card">
                        <div class="attendance-card-header">
                            @if($row->image_path)
                                <a href="{{ asset('storage/' . $row->image_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $row->image_path) }}" alt="Snapshot" class="attendance-card-snapshot">
                                </a>
                            @else
                                <div class="attendance-card-snapshot-empty">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            @endif
                            <div class="attendance-card-student">
                                <div class="attendance-card-name">{{ $row->student->name }}</div>
                                <span class="attendance-card-nim">{{ $row->student->student_number }}</span>
                            </div>
                            {!! $row->status_badge !!}
                        </div>

                        <div class="attendance-card-meta">
                            <span>
                                <i class="fa-solid fa-book-open"></i>
                                {{ $row->course ? $row->course->name : '-' }}
                            </span>
                            <span>
                                <i class="fa-solid fa-microchip-ai"></i>
                                {{ $row->confidence_percent }}
                            </span>
                            <span style="grid-column: 1 / -1;">
                                <i class="fa-regular fa-clock"></i>
                                {{ $row->check_in_at->isoFormat('D MMM Y, HH:mm') }} WIB
                            </span>
                        </div>

                        <div class="attendance-card-footer">
                            <span style="font-size: 0.75rem; color: var(--text-muted);">
                                <i class="fa-solid fa-network-wired" style="margin-right: 4px;"></i>
                                {{ $row->ip_address ?: '-' }}
                            </span>
                            <form action="{{ route('admin.attendances.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Hapus log ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--danger); border-color: rgba(239,68,68,0.2);">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Tidak ada data log kehadiran ditemukan.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 10px;">
        {{ $attendances->links() }}
    </div>

@endsection
