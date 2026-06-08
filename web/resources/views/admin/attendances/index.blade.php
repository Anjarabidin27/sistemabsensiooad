@extends('layouts.admin')

@section('title', 'Log Kehadiran')
@section('header-title', 'Log Kehadiran')
@section('header-subtitle', 'Jurnal riwayat presensi kehadiran mahasiswa lengkap')

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

        <!-- Attendances Table -->
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="hide-mobile">Snapshot</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th class="hide-mobile">Mata Kuliah</th>
                            <th>Tanggal & Waktu</th>
                            <th class="hide-mobile">Akurasi AI</th>
                            <th class="hide-mobile">IP Address</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $row)
                            <tr>
                                <td class="hide-mobile">
                                    @if($row->image_path)
                                        <a href="{{ asset('storage/' . $row->image_path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $row->image_path) }}" alt="Snapshot" style="width: 44px; height: 44px; border-radius: var(--radius-sm); object-fit: cover; border: 1px solid var(--border-color);">
                                        </a>
                                    @else
                                        <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); background-color: var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 0.8rem;">
                                            <i class="fa-solid fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td style="font-weight: 700; color: var(--primary-dark);">{{ $row->student->student_number }}</td>
                                <td style="font-weight: 600;">{{ $row->student->name }}</td>
                                <td class="hide-mobile">
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
                                <td class="hide-mobile" style="font-weight: 700; color: var(--primary);">{{ $row->confidence_percent }}</td>
                                <td class="hide-mobile" style="font-family: monospace; font-size: 0.8rem; color: var(--text-muted);">{{ $row->ip_address ?: '-' }}</td>
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
        </div>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 10px;">
        {{ $attendances->links() }}
    </div>

@endsection
